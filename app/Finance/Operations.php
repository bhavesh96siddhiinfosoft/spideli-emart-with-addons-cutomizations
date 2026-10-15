<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;

/**
 * The money actions of Phase A, each one Firestore transaction with its
 * audit entry: reversal, manual adjustment, platform account creation and
 * settings saves.
 */
class Operations
{
    private $db;
    private $ledger;

    public function __construct(Firestore $db = null)
    {
        $this->db = $db ?: app(Firestore::class);
        $this->ledger = new Ledger();
    }

    public function db()
    {
        return $this->db;
    }

    private function adminId()
    {
        return (string)Auth::id();
    }

    /** reverse() of spec 5.2 + audit. $context records who asked / approved. */
    public function reverse($txId, $reason, array $context = [])
    {
        return $this->db->transaction(function (Transaction $t) use ($txId, $reason, $context) {
            $original = null;
            $written = $this->ledger->reverse($t, $txId, $this->adminId(), $reason, $original);
            Audit::add($t, 'ledger.reverse', 'ledger_transactions', $txId,
                ['status' => $original['status'] ?? 'posted'],
                ['status' => 'reversed', 'reversedBy' => array_column($written, 'txId')] + $context,
                $reason);
            return $written;
        });
    }

    /** Manual adjustment (spec 6.5.3) + audit; the posting's source is the audit entry. */
    public function adjust($uid, $cur, $direction, $amount, $reason, array $context = [])
    {
        if (!in_array($direction, ['credit', 'debit'], true)) {
            throw new FinanceException('invalid', 'Choose whether money goes into or out of the wallet.');
        }
        return $this->db->transaction(function (Transaction $t) use ($uid, $cur, $direction, $amount, $reason, $context) {
            $user = $t->get('users/' . $uid);
            if (!$user) {
                throw new FinanceException('user_missing', 'No customer has this id.');
            }
            $auditId = Audit::newId();
            $written = $this->ledger->adjust($t, $uid, $cur, $direction, $amount, $reason, $this->adminId(),
                ['kind' => 'admin_audit_logs', 'id' => $auditId]);
            $t->create('admin_audit_logs/' . $auditId, Audit::entry('ledger.manual_adjustment', 'ledger_transactions', $written['txId'], null, [
                'userId' => $uid, 'currency' => $cur, 'direction' => $direction, 'amount' => $amount,
                'walletId' => $written['walletId'], 'reference' => $written['reference'],
            ] + $context, $reason));
            return $written;
        });
    }

    /** financial_accounts/platform_{CUR} (spec 6.2). */
    public function createPlatformAccount($cur)
    {
        if (!Money::isCurrency($cur)) {
            throw new FinanceException('invalid_currency', 'Choose a 3-letter currency code.');
        }
        $id = Ledger::platformId($cur);
        return $this->db->transaction(function (Transaction $t) use ($id, $cur) {
            if ($t->get('financial_accounts/' . $id)) {
                throw new FinanceException('account_exists', "The account $id already exists.");
            }
            $doc = [
                'id' => $id, 'kind' => 'platform', 'currency' => $cur, 'balance' => 0,
                'normalSide' => 'credit', 'status' => 'active', 'lastTxId' => '',
                'createdAt' => Value::serverTimestamp(), 'updatedAt' => Value::serverTimestamp(),
            ];
            $t->create('financial_accounts/' . $id, $doc);
            Audit::add($t, 'platform_account.create', 'financial_accounts', $id, null, $doc);
            return $id;
        });
    }

    /**
     * Saves one module's settings for several regions at once
     * (finance_settings/{regionId}_{module}): the same form values, each
     * region with its own currency. One transaction for all regions, an
     * audit entry per region. createdAt is kept, updatedAt set; fields the
     * form does not manage are kept.
     *
     * $regions: [regionId => currency code]; $methods: [regionId => the
     * payment methods its gateways offer] - a method field keeps only those.
     * Returns [regionId => changed fields]; a region whose document did not
     * change is not written.
     */
    public function saveSettings($doc, array $input, array $regions, array $methods = [])
    {
        if (!$regions) {
            throw new FinanceException('no_region', 'Choose at least one region.');
        }
        return $this->db->transaction(function (Transaction $t) use ($doc, $input, $regions, $methods) {
            $plans = [];
            $errors = [];
            foreach ($regions as $regionId => $currency) {
                $path = 'finance_settings/' . Regions::docId($regionId, $doc);
                $snap = $t->get($path);
                $stored = $snap ? $snap['data'] : [];
                [$data, $regionErrors] = SettingsSchema::parse($doc, $input, $stored, $currency, $methods[$regionId] ?? null);
                foreach ($regionErrors as $field => $message) {
                    $errors[$field] = $message;
                }
                $plans[$regionId] = [$path, $snap, $stored, $data];
            }
            if ($errors) {
                throw new FinanceException('invalid_settings', 'Some fields need attention.', $errors);
            }

            $changed = [];
            foreach ($plans as $regionId => [$path, $snap, $stored, $data]) {
                $data['regionId'] = $regionId;
                $data['module'] = $doc;
                unset($data['updatedAt'], $data['updatedBy'], $data['createdAt']);
                $compare = $stored;
                unset($compare['updatedAt'], $compare['updatedBy'], $compare['createdAt']);
                [$before, $after] = Audit::diff($compare, $data);
                if (!$before && !$after) {
                    continue;
                }
                $data['createdAt'] = $stored['createdAt'] ?? Value::serverTimestamp();
                $data['updatedAt'] = Value::serverTimestamp();
                $data['updatedBy'] = 'admin:' . $this->adminId();
                $t->set($path, $data);
                Audit::add($t, $snap ? 'settings.update' : 'settings.create', 'finance_settings', Regions::docId($regionId, $doc), $before, $after);
                $changed[$regionId] = array_keys($before + $after);
            }
            return $changed;
        });
    }

    /**
     * First time a region is enabled for Finance: every module it has no
     * settings for yet starts from the app's current platform_settings/{module}
     * values (left untouched), in the region's own currency. Amounts in
     * another currency are dropped. Never overwrites an existing document.
     * Returns the modules created.
     */
    public function seedRegion($regionId, $currency, array $methods = null)
    {
        if (!Money::isCurrency($currency)) {
            throw new FinanceException('no_currency', 'This region has no currency. Set it in Region Management.');
        }
        return $this->db->transaction(function (Transaction $t) use ($regionId, $currency, $methods) {
            $todo = [];
            foreach (SettingsSchema::docs() as $module) {
                $path = 'finance_settings/' . Regions::docId($regionId, $module);
                if ($t->get($path)) {
                    continue;
                }
                $source = $t->get('platform_settings/' . $module);
                if ($source) {
                    $todo[$module] = [$path, $source['data']];
                }
            }
            foreach ($todo as $module => [$path, $data]) {
                $data = self::inCurrency($module, $data, $currency);
                if ($methods !== null) {
                    $data = self::withMethods($module, $data, $methods);
                }
                unset($data['updatedAt'], $data['updatedBy']);
                $data['regionId'] = $regionId;
                $data['module'] = $module;
                $data['createdAt'] = Value::serverTimestamp();
                $data['updatedAt'] = Value::serverTimestamp();
                $data['updatedBy'] = 'admin:' . $this->adminId();
                $t->create($path, $data);
                Audit::add($t, 'settings.seed', 'finance_settings', Regions::docId($regionId, $module), null,
                    ['from' => 'platform_settings/' . $module, 'currency' => $currency]);
            }
            return array_keys($todo);
        });
    }

    /** A copied document's payment-method fields narrowed to what the region's gateways offer. */
    private static function withMethods($module, array $data, array $methods)
    {
        foreach (SettingsSchema::realFields($module) as $path => $f) {
            $value = SettingsSchema::getPath($data, $path);
            if (SettingsSchema::isMethodField($f) && is_array($value)) {
                SettingsSchema::setPath($data, $path, array_values(array_intersect($value, $methods)));
            }
        }
        return $data;
    }

    /** A copied document in the region's currency: currencies and every money map narrowed to it. */
    private static function inCurrency($module, array $data, $currency)
    {
        $keep = [$currency];
        if ($module === 'wallet') {
            $keep = array_values(array_unique(array_merge($keep, (array)SettingsSchema::getPath($data, 'convert.currencies'))));
        }
        if ($module !== 'intelligence') {
            $data['currencies'] = $keep;
        }
        foreach (SettingsSchema::realFields($module) as $path => $f) {
            if ($f['type'] !== 'money') {
                continue;
            }
            $map = SettingsSchema::getPath($data, $path);
            if (!is_array($map)) {
                continue;
            }
            $map = array_intersect_key($map, array_flip($keep));
            if ($map) {
                SettingsSchema::setPath($data, $path, $map);
            } else {
                SettingsSchema::unsetPath($data, $path);
            }
        }
        return $data;
    }
}
