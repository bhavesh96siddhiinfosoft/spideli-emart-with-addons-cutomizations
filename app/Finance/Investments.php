<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;

/**
 * Phase D1 fund management (spec 6.10, data 3.9, rules R.10).
 *
 * The admin runs each fund's waiting orders at one price: the fund's current
 * unit price (navPerUnit, set by "Add a unit price") and its date as the
 * trade date. A subscription's money was already posted wallet → platform by
 * the app, so executing it posts nothing; a refused one is reversed. A
 * redemption is executed (units sold, holding reduced) and then settled:
 * platform → the customer's Finance wallet, always.
 *
 * Unit prices are in minor units and may have decimals; units have up to 6
 * decimals; every amount is a whole number of minor units.
 */
class Investments
{
    public const ASSET_CLASSES = ['money_market', 'bonds', 'equities', 'real_estate', 'agriculture', 'infrastructure', 'mixed'];
    public const STATUSES = ['open', 'closed', 'suspended'];

    private $db;
    private $ledger;

    public function __construct(Firestore $db = null)
    {
        $this->db = $db ?: app(Firestore::class);
        $this->ledger = new Ledger();
    }

    private function admin()
    {
        return 'admin:' . Auth::id();
    }

    private function get(Transaction $t, $path, $what)
    {
        $snap = $t->get($path);
        if (!$snap) {
            throw new FinanceException('missing', "This $what no longer exists.");
        }
        return $snap['data'] + ['id' => $snap['id']];
    }

    private static function now()
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    /* ========================================================= numbers */

    /** A unit price typed in major units ("1043", "10.0425") → minor units, up to 4 extra decimals. */
    public static function navToMinor($input, $currency)
    {
        $input = trim(str_replace([' ', "\u{00A0}", ','], '', (string)$input));
        if (!preg_match('/^\d+(\.\d+)?$/', $input) || (float)$input <= 0) {
            throw new FinanceException('invalid', 'The unit price must be a number above 0.');
        }
        return round((float)$input * (10 ** Money::exponent($currency)), 4);
    }

    /** Minor-unit price → the form value (major units, no trailing zeros). */
    public static function navInput($nav, $currency)
    {
        $exp = Money::exponent($currency);
        $s = number_format((float)$nav / (10 ** $exp), $exp + 4, '.', '');
        return rtrim(rtrim($s, '0'), '.');
    }

    /** 1043 XAF → "1 043 XAF"; 1004.8 XAF → "1 004.8 XAF". */
    public static function navText($nav, $currency)
    {
        $exp = Money::exponent($currency);
        $major = (float)$nav / (10 ** $exp);
        $decimals = max($exp, strlen(substr(strrchr(self::navInput($nav, $currency), '.') ?: '', 1)));
        return number_format($major, $decimals, '.', ' ') . ' ' . $currency;
    }

    public static function unitsText($units)
    {
        $s = number_format((float)$units, 6, '.', ' ');
        return rtrim(rtrim($s, '0'), '.');
    }

    private static function units($u)
    {
        $u = round((float)$u, 6);
        return $u < 0.000001 ? 0.0 : $u;
    }

    private static function addDays(\DateTimeInterface $at, $days)
    {
        return \DateTimeImmutable::createFromInterface($at)->modify('+' . (int)$days . ' days');
    }

    /**
     * A holding's valuation at $nav (spec 6.10): once lockedUntil has passed
     * every unit is redeemable; values are rounded to whole minor units.
     */
    public static function valued(array $h, $nav, \DateTimeInterface $now)
    {
        $units = self::units($h['units'] ?? 0);
        $locked = ($h['lockedUntil'] ?? null) instanceof \DateTimeInterface && $h['lockedUntil'] > $now;
        $redeemable = $locked ? min($units, self::units($h['redeemableUnits'] ?? 0)) : $units;
        $current = (int)round($units * (float)$nav);
        return [
            'units' => $units,
            'redeemableUnits' => $redeemable,
            'currentValue' => $current,
            'redeemableValue' => (int)round($redeemable * (float)$nav),
            'unrealizedGains' => $current - (int)($h['investedAmount'] ?? 0),
        ];
    }

    /**
     * Performance (%) from the price history: ytd, one / three / five years
     * and since inception, each against the last price on or before the start
     * of the period. A period the history does not cover keeps $current
     * (typed by the admin). [['date' => DateTime, 'nav' => float], ...]
     */
    public static function performance(array $history, array $current = [])
    {
        usort($history, function ($a, $b) { return $a['date'] <=> $b['date']; });
        if (!$history) {
            return $current;
        }
        $last = end($history);
        $base = function (\DateTimeInterface $before) use ($history) {
            $found = null;
            foreach ($history as $v) {
                if ($v['date'] <= $before) {
                    $found = $v;
                }
            }
            return $found;
        };
        $pct = function ($b) use ($last) {
            return $b && $b['nav'] > 0 ? round(($last['nav'] / $b['nav'] - 1) * 100, 2) : null;
        };
        $at = \DateTimeImmutable::createFromInterface($last['date']);
        $out = $current;
        $first = $history[0];
        if (count($history) > 1) {
            $out['sinceInception'] = $pct($first);
        }
        $jan1 = $at->setDate((int)$at->format('Y'), 1, 1)->setTime(0, 0);
        $ytd = $base($jan1->modify('-1 second'));
        if ($ytd) {
            $out['ytd'] = $pct($ytd);
        } elseif (count($history) > 1 && $first['date'] >= $jan1) {
            $out['ytd'] = $pct($first);     // launched this year
        }
        foreach (['oneYear' => 1, 'threeYears' => 3, 'fiveYears' => 5] as $k => $years) {
            $b = $base($at->modify("-$years years"));
            if ($b) {
                $out[$k] = $pct($b);
            }
        }
        return array_filter($out, function ($v) { return $v !== null && $v !== ''; });
    }

    /* =========================================================== funds */

    /**
     * The fund fields of spec 3.9 from the form. $existing is the stored fund
     * (null for a new one): its currency, documents, price and terms version
     * are kept by the caller's rules.
     */
    public static function fundFromInput(array $in, array $existing = null)
    {
        $name = trim((string)($in['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 120) {
            throw new FinanceException('invalid', 'The fund needs a name (at most 120 characters).');
        }
        $cur = $existing['currency'] ?? (string)($in['currency'] ?? '');
        if (!Money::isCurrency($cur)) {
            throw new FinanceException('invalid', 'Choose the fund\'s currency.');
        }
        $asset = (string)($in['assetClass'] ?? '');
        if (!in_array($asset, self::ASSET_CLASSES, true)) {
            throw new FinanceException('invalid', 'Choose the asset class.');
        }
        $risk = (int)($in['riskLevel'] ?? 0);
        if ($risk < 1 || $risk > 7) {
            throw new FinanceException('invalid', 'The risk level is 1 to 7.');
        }
        $status = (string)($in['status'] ?? 'open');
        if (!in_array($status, self::STATUSES, true)) {
            throw new FinanceException('invalid', 'Unknown fund status.');
        }
        $percent = function ($v, $label) {
            $v = trim((string)$v);
            if ($v === '') {
                return 0;
            }
            if (!is_numeric($v) || (float)$v < 0 || (float)$v > 100) {
                throw new FinanceException('invalid', "$label must be a percentage from 0 to 100.");
            }
            return round((float)$v, 4) == (int)$v ? (int)$v : round((float)$v, 4);
        };
        $days = function ($v, $label) {
            $v = trim((string)$v);
            if ($v !== '' && (!ctype_digit($v) || (int)$v > 3650)) {
                throw new FinanceException('invalid', "$label must be a whole number of days.");
            }
            return (int)$v;
        };
        $min = Money::toMinor($in['minInvestment'] ?? '', $cur) ?? 0;
        $minAdd = Money::toMinor($in['minAdditional'] ?? '', $cur);
        $f = [
            'name' => $name,
            'manager' => trim((string)($in['manager'] ?? '')),
            'description' => trim((string)($in['description'] ?? '')),
            'objective' => trim((string)($in['objective'] ?? '')),
            'eligibility' => trim((string)($in['eligibility'] ?? '')),
            'assetClass' => $asset,
            'riskLevel' => $risk,
            'currency' => $cur,
            'order' => (int)($in['order'] ?? 0),
            'minInvestment' => $min,
            'minAdditional' => $minAdd ?? $min,
            'fees' => [
                'subscriptionPercent' => $percent($in['subscriptionPercent'] ?? '', __('The subscription fee')),
                'managementPercent' => $percent($in['managementPercent'] ?? '', __('The management fee')),
                'redemptionPercent' => $percent($in['redemptionPercent'] ?? '', __('The redemption fee')),
                'performancePercent' => $percent($in['performancePercent'] ?? '', __('The performance fee')),
            ],
            'lockUpDays' => $days($in['lockUpDays'] ?? '', __('The lock-up')),
            'settlementDays' => $days($in['settlementDays'] ?? '', __('The settlement time')),
            'redemption' => ['allowed' => !empty($in['redemptionAllowed']), 'partial' => !empty($in['redemptionAllowed']) && !empty($in['redemptionPartial'])],
            'terms' => trim(str_replace("\r\n", "\n", (string)($in['terms'] ?? ''))),
            'status' => $status,
            'published' => !empty($in['published']),
        ];
        $perf = [];
        foreach (['ytd', 'oneYear', 'threeYears', 'fiveYears', 'sinceInception'] as $k) {
            $v = trim((string)($in['performance'][$k] ?? ''));
            if ($v !== '') {
                if (!is_numeric($v)) {
                    throw new FinanceException('invalid', 'Performance figures are percentages.');
                }
                $perf[$k] = round((float)$v, 2);
            }
        }
        $f['performance'] = $perf ?: Value::map([]);
        if ($f['published'] && $f['terms'] === '') {
            throw new FinanceException('invalid', 'A published fund needs its terms: customers sign them when they subscribe.');
        }
        return $f;
    }

    /**
     * Creates or edits a fund. A new fund needs its first unit price (it is
     * also its first valuation). The terms version goes up by one whenever
     * the terms text changes. Returns ['id', 'termsChanged', 'holders'].
     */
    public function saveFund($fundId, array $f, array $newDocuments = [], array $removeDocuments = [], $firstNav = null)
    {
        $isNew = $fundId === null || $fundId === '';
        $fundId = $isNew ? Money::random(20) : $fundId;
        foreach ($newDocuments as $i => $d) {
            $newDocuments[$i] = ['name' => $d['name'], 'storagePath' => Storage::upload($d['file'],
                'investment_funds/' . $fundId . '/' . (int)floor(microtime(true) * 1000) . '-' . $i . '-' . Storage::safeName($d['fileName'] ?? $d['name']))];
        }
        return $this->db->transaction(function (Transaction $t) use ($isNew, $fundId, $f, $newDocuments, $removeDocuments, $firstNav) {
            $before = $isNew ? null : $this->get($t, 'investment_funds/' . $fundId, 'fund');
            $holders = [];
            if (!$isNew && ($before['terms'] ?? '') !== $f['terms']) {
                foreach ($t->query('investment_holdings', [['fundId', '==', $fundId]]) as $h) {
                    if ((float)($h['data']['units'] ?? 0) > 0 && empty($h['data']['demo'])) {
                        $holders[] = $h['data']['userId'] ?? '';
                    }
                }
            }
            $docs = [];
            foreach ((array)($before['documents'] ?? []) as $i => $d) {
                if (!in_array((string)$i, array_map('strval', $removeDocuments), true)) {
                    $docs[] = $d;
                }
            }
            $docs = array_merge($docs, $newDocuments);
            $f['documents'] = $docs;
            $now = self::now();
            if ($isNew) {
                if (!$firstNav || $firstNav <= 0) {
                    throw new FinanceException('invalid', 'A new fund needs its first unit price.');
                }
                $f += ['id' => $fundId, 'navPerUnit' => (float)$firstNav, 'navDate' => $now, 'termsVersion' => 1,
                    'createdAt' => Value::serverTimestamp(), 'updatedAt' => Value::serverTimestamp(), 'createdBy' => $this->admin()];
                $t->create('investment_funds/' . $fundId, $f);
                $t->create('investment_valuations/' . Money::random(20), ['fundId' => $fundId, 'date' => $now, 'navPerUnit' => (float)$firstNav, 'createdBy' => $this->admin()]);
                Audit::add($t, 'investment.fund_create', 'investment_funds', $fundId, null, $f);
                return ['id' => $fundId, 'termsChanged' => false, 'holders' => []];
            }
            $termsChanged = ($before['terms'] ?? '') !== $f['terms'];
            $f['termsVersion'] = (int)($before['termsVersion'] ?? 1) + ($termsChanged ? 1 : 0);
            $f['updatedAt'] = Value::serverTimestamp();
            $f['updatedBy'] = $this->admin();
            $t->update('investment_funds/' . $fundId, $f);
            [$b, $a] = Audit::diff($before, $f);
            Audit::add($t, 'investment.fund_update', 'investment_funds', $fundId, $b, $a);
            return ['id' => $fundId, 'termsChanged' => $termsChanged, 'holders' => array_values(array_unique(array_filter($holders)))];
        });
    }

    /**
     * Adds a unit price: a valuation in the history; when it is the newest,
     * it becomes the fund's price (navPerUnit, navDate) and the performance is
     * recomputed. Then every holding of the fund is revalued.
     */
    public function addPrice($fundId, \DateTimeInterface $date, $nav)
    {
        if ($date > self::now()->modify('+1 hour')) {
            throw new FinanceException('invalid', 'A unit price cannot be dated in the future.');
        }
        $this->db->transaction(function (Transaction $t) use ($fundId, $date, $nav) {
            $fund = $this->get($t, 'investment_funds/' . $fundId, 'fund');
            $valuations = $t->query('investment_valuations', [['fundId', '==', $fundId]]);
            // The newest price becomes the fund's price. A price of the same day replaces it too:
            // typed to the minute, it may read as older than the current one, so it is dated
            // just after it.
            $tz = new \DateTimeZone(config('app.timezone'));
            $current = $fund['navDate'] ?? null;
            $date = \DateTimeImmutable::createFromInterface($date);
            $newest = !($current instanceof \DateTimeInterface) || $date >= $current;
            if (!$newest && $date->setTimezone($tz)->format('Y-m-d') === \DateTimeImmutable::createFromInterface($current)->setTimezone($tz)->format('Y-m-d')) {
                $date = \DateTimeImmutable::createFromInterface($current)->modify('+1 second');
                $newest = true;
            }
            $history = [['date' => $date, 'nav' => (float)$nav]];
            foreach ($valuations as $v) {
                if (($v['data']['date'] ?? null) instanceof \DateTimeInterface) {
                    $history[] = ['date' => $v['data']['date'], 'nav' => (float)($v['data']['navPerUnit'] ?? 0)];
                }
            }
            $changes = ['performance' => self::performance($history, (array)($fund['performance'] ?? [])) ?: Value::map([]), 'updatedAt' => Value::serverTimestamp()];
            if ($newest) {
                $changes += ['navPerUnit' => (float)$nav, 'navDate' => $date];
            }
            $t->create('investment_valuations/' . Money::random(20), ['fundId' => $fundId, 'date' => $date, 'navPerUnit' => (float)$nav, 'createdBy' => $this->admin()]);
            $t->update('investment_funds/' . $fundId, $changes);
            Audit::add($t, 'investment.price_add', 'investment_funds', $fundId,
                ['navPerUnit' => $fund['navPerUnit'] ?? null, 'performance' => $fund['performance'] ?? null],
                ['navPerUnit' => $newest ? (float)$nav : ($fund['navPerUnit'] ?? null), 'date' => $date->format('Y-m-d H:i'), 'newest' => $newest, 'performance' => $changes['performance']]);
        });
        return $this->revalue($fundId);
    }

    /**
     * Revalues every holding of a fund at its current price (and unlocks the
     * units whose lock-up has passed). Batches of 400 writes, each with its
     * audit entry. Returns the number of holdings changed.
     */
    public function revalue($fundId, \DateTimeInterface $now = null)
    {
        $now = $now ?: self::now();
        $fund = $this->db->get('investment_funds/' . $fundId);
        if (!$fund || (float)($fund['data']['navPerUnit'] ?? 0) <= 0) {
            return 0;
        }
        $nav = (float)$fund['data']['navPerUnit'];
        $writes = [];
        $count = 0;
        foreach ($this->db->query('investment_holdings', [['fundId', '==', $fundId]]) as $h) {
            $v = self::valued($h['data'], $nav, $now);
            $same = true;
            foreach ($v as $k => $x) {
                if (!array_key_exists($k, $h['data']) || (float)$h['data'][$k] !== (float)$x) {
                    $same = false;
                }
            }
            if ($same) {
                continue;
            }
            $writes[] = $this->db->write('update', 'investment_holdings/' . $h['id'], $v + ['valuedAt' => Value::serverTimestamp()]);
            $count++;
            if (count($writes) === 400) {
                $writes[] = Audit::write($this->db, 'investment.revalue', 'investment_funds', $fundId, null, ['navPerUnit' => $nav, 'holdings' => 400]);
                $this->db->batch($writes);
                $writes = [];
            }
        }
        if ($writes) {
            $writes[] = Audit::write($this->db, 'investment.revalue', 'investment_funds', $fundId, null, ['navPerUnit' => $nav, 'holdings' => count($writes)]);
            $this->db->batch($writes);
        }
        return $count;
    }

    /* =================================================== subscriptions */

    /**
     * Executes one paid subscription at the fund's current price: fee, units,
     * the holding (created or added to) and the investment transactions.
     * Nothing is posted: the money was posted when the customer paid.
     */
    public function executeSubscription($subId, $fundId)
    {
        return $this->db->transaction(function (Transaction $t) use ($subId, $fundId) {
            $s = $this->get($t, 'investment_subscriptions/' . $subId, 'subscription');
            if (($s['status'] ?? '') !== 'paid' || ($s['fundId'] ?? '') !== $fundId) {
                throw new FinanceException('state_changed', 'This subscription is now "' . ($s['status'] ?? '?') . '".');
            }
            if (!empty($s['demo'])) {
                throw new FinanceException('demo', 'Demo records are never executed.');
            }
            $fund = $this->get($t, 'investment_funds/' . $fundId, 'fund');
            $nav = (float)($fund['navPerUnit'] ?? 0);
            $tradeDate = $fund['navDate'] ?? null;
            if ($nav <= 0 || !($tradeDate instanceof \DateTimeInterface)) {
                throw new FinanceException('no_price', 'The fund has no unit price yet.');
            }
            $cur = $fund['currency'] ?? '';
            if (($s['currency'] ?? '') !== $cur) {
                throw new FinanceException('currency_mismatch', 'The subscription is not in the fund\'s currency.');
            }
            $uid = $s['userId'] ?? '';
            $hid = $uid . '_' . $fundId;
            $old = $t->get('investment_holdings/' . $hid);
            $h = $old ? $old['data'] : [];

            $amount = (int)($s['amount'] ?? 0);
            $fee = (int)round($amount * (float)($fund['fees']['subscriptionPercent'] ?? 0) / 100);
            $net = $amount - $fee;
            $units = self::units($net / $nav);
            if ($amount <= 0 || $units <= 0) {
                throw new FinanceException('invalid_amount', 'The amount buys no units at this price.');
            }
            $now = self::now();
            $lockDays = (int)($fund['lockUpDays'] ?? 0);
            $lockedUntil = $h['lockedUntil'] ?? null;
            if ($lockDays > 0) {
                $mine = self::addDays($tradeDate, $lockDays);
                $lockedUntil = $lockedUntil instanceof \DateTimeInterface && $lockedUntil > $mine ? $lockedUntil : $mine;
            }
            $already = self::valued($h, $nav, $now)['redeemableUnits'];
            $holding = [
                'userId' => $uid, 'fundId' => $fundId, 'fundName' => $fund['name'] ?? '', 'assetClass' => $fund['assetClass'] ?? '', 'currency' => $cur,
                'units' => self::units((float)($h['units'] ?? 0) + $units),
                'redeemableUnits' => $lockDays > 0 ? $already : self::units($already + $units),
                'investedAmount' => (int)($h['investedAmount'] ?? 0) + $net,
                'realizedGains' => (int)($h['realizedGains'] ?? 0),
                'lockedUntil' => $lockedUntil,
            ];
            $holding = self::valued($holding, $nav, $now) + $holding;
            $holding['valuedAt'] = Value::serverTimestamp();

            $t->update('investment_subscriptions/' . $subId, [
                'status' => 'completed', 'units' => $units, 'navPerUnit' => $nav, 'netAmount' => $net, 'fee' => $fee,
                'tradeDate' => $tradeDate, 'processedBy' => $this->admin(), 'updatedAt' => Value::serverTimestamp(),
            ]);
            if ($old) {
                $t->update('investment_holdings/' . $hid, $holding);
            } else {
                $t->create('investment_holdings/' . $hid, $holding + ['createdAt' => Value::serverTimestamp()]);
            }
            $ref = $s['reference'] ?? '';
            $t->create('investment_transactions/' . Money::random(20), ['userId' => $uid, 'fundId' => $fundId, 'type' => 'subscription', 'amount' => $net,
                'units' => $units, 'navPerUnit' => $nav, 'currency' => $cur, 'reference' => $ref, 'sourceId' => $subId, 'createdAt' => Value::serverTimestamp()]);
            if ($fee > 0) {
                $t->create('investment_transactions/' . Money::random(20), ['userId' => $uid, 'fundId' => $fundId, 'type' => 'fee', 'amount' => $fee,
                    'units' => 0, 'navPerUnit' => $nav, 'currency' => $cur, 'reference' => $ref, 'sourceId' => $subId, 'createdAt' => Value::serverTimestamp()]);
            }
            Audit::add($t, 'investment.subscription_execute', 'investment_subscriptions', $subId, ['status' => 'paid'],
                ['status' => 'completed', 'units' => $units, 'navPerUnit' => $nav, 'fee' => $fee, 'netAmount' => $net]);
            return ['userId' => $uid, 'amount' => $amount, 'units' => $units, 'nav' => $nav, 'currency' => $cur, 'fundName' => $fund['name'] ?? ''];
        });
    }

    /** Refuses a paid subscription: its posting is reversed (money back to the wallet). */
    public function refuseSubscription($subId, $reason)
    {
        self::needReason($reason);
        return $this->db->transaction(function (Transaction $t) use ($subId, $reason) {
            $s = $this->get($t, 'investment_subscriptions/' . $subId, 'subscription');
            if (!in_array($s['status'] ?? '', ['paid', 'processing'], true)) {
                throw new FinanceException('state_changed', 'This subscription is now "' . ($s['status'] ?? '?') . '".');
            }
            if (empty($s['txId'])) {
                throw new FinanceException('posting_missing', 'This subscription has no posting to give back.');
            }
            $reversal = $this->ledger->reverse($t, $s['txId'], Auth::id(), 'Investment subscription refused: ' . $reason);
            $t->update('investment_subscriptions/' . $subId, ['status' => 'rejected', 'reason' => $reason, 'processedBy' => $this->admin(), 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'investment.subscription_refuse', 'investment_subscriptions', $subId, ['status' => $s['status']],
                ['status' => 'rejected', 'reversal' => array_column($reversal, 'txId')], $reason);
            return $s;
        });
    }

    /* ===================================================== redemptions */

    /**
     * Executes one submitted redemption at the fund's current price: the
     * units sold (all redeemable units when "full"), the fee and net amount,
     * the holding reduced pro rata with the gain booked. The money is paid by
     * settle(); the redemption waits as "settling" until then.
     */
    public function executeRedemption($redId, $fundId)
    {
        return $this->db->transaction(function (Transaction $t) use ($redId, $fundId) {
            $r = $this->get($t, 'investment_redemptions/' . $redId, 'redemption');
            if (($r['status'] ?? '') !== 'submitted' || ($r['fundId'] ?? '') !== $fundId) {
                throw new FinanceException('state_changed', 'This redemption is now "' . ($r['status'] ?? '?') . '".');
            }
            if (!empty($r['demo'])) {
                throw new FinanceException('demo', 'Demo records are never executed.');
            }
            $fund = $this->get($t, 'investment_funds/' . $fundId, 'fund');
            $nav = (float)($fund['navPerUnit'] ?? 0);
            $tradeDate = $fund['navDate'] ?? null;
            if ($nav <= 0 || !($tradeDate instanceof \DateTimeInterface)) {
                throw new FinanceException('no_price', 'The fund has no unit price yet.');
            }
            $cur = $fund['currency'] ?? '';
            $uid = $r['userId'] ?? '';
            $hid = $uid . '_' . $fundId;
            $h = $this->get($t, 'investment_holdings/' . $hid, 'holding');
            $now = self::now();
            $h = self::valued($h, $nav, $now) + $h;
            $redeemable = (float)$h['redeemableUnits'];
            if ($redeemable <= 0) {
                $until = ($h['lockedUntil'] ?? null) instanceof \DateTimeInterface ? $h['lockedUntil']->format('Y-m-d') : '';
                throw new FinanceException('locked', 'No unit can be redeemed yet' . ($until ? " (locked until $until)" : '') . '. Refuse it or wait.');
            }
            if (!empty($r['full'])) {
                $units = $redeemable;
            } else {
                $units = min($redeemable, self::units((int)($r['amount'] ?? 0) / $nav));
            }
            if ($units <= 0) {
                throw new FinanceException('invalid_amount', 'The amount sells no units at this price.');
            }
            $gross = (int)round($units * $nav);
            $fee = (int)round($gross * (float)($fund['fees']['redemptionPercent'] ?? 0) / 100);
            $net = $gross - $fee;

            $held = (float)$h['units'];
            $all = abs($held - $units) < 0.000001;
            $investedOut = $all ? (int)$h['investedAmount'] : (int)round((int)$h['investedAmount'] * $units / $held);
            $holding = [
                'units' => $all ? 0.0 : self::units($held - $units),
                'redeemableUnits' => $all ? 0.0 : self::units($redeemable - $units),
                'investedAmount' => (int)$h['investedAmount'] - $investedOut,
                'realizedGains' => (int)($h['realizedGains'] ?? 0) + ($net - $investedOut),
                'lockedUntil' => $h['lockedUntil'] ?? null,
            ];
            $holding = self::valued($holding, $nav, $now) + $holding;
            unset($holding['lockedUntil']);
            $holding['valuedAt'] = Value::serverTimestamp();
            $settleOn = self::addDays($tradeDate > $now ? $tradeDate : $now, (int)($fund['settlementDays'] ?? 0));

            $t->update('investment_redemptions/' . $redId, [
                'status' => 'settling', 'units' => $units, 'navPerUnit' => $nav, 'grossAmount' => $gross, 'fee' => $fee, 'netAmount' => $net,
                'tradeDate' => $tradeDate, 'expectedSettlementDate' => $settleOn, 'processedBy' => $this->admin(), 'updatedAt' => Value::serverTimestamp(),
            ]);
            $t->update('investment_holdings/' . $hid, $holding);
            $ref = $r['reference'] ?? '';
            $t->create('investment_transactions/' . Money::random(20), ['userId' => $uid, 'fundId' => $fundId, 'type' => 'redemption', 'amount' => $gross,
                'units' => $units, 'navPerUnit' => $nav, 'currency' => $cur, 'reference' => $ref, 'sourceId' => $redId, 'createdAt' => Value::serverTimestamp()]);
            if ($fee > 0) {
                $t->create('investment_transactions/' . Money::random(20), ['userId' => $uid, 'fundId' => $fundId, 'type' => 'fee', 'amount' => $fee,
                    'units' => 0, 'navPerUnit' => $nav, 'currency' => $cur, 'reference' => $ref, 'sourceId' => $redId, 'createdAt' => Value::serverTimestamp()]);
            }
            Audit::add($t, 'investment.redemption_execute', 'investment_redemptions', $redId, ['status' => 'submitted'],
                ['status' => 'settling', 'units' => $units, 'navPerUnit' => $nav, 'grossAmount' => $gross, 'fee' => $fee, 'netAmount' => $net]);
            return ['userId' => $uid, 'net' => $net, 'currency' => $cur, 'fundName' => $fund['name'] ?? '', 'settleOn' => $settleOn];
        });
    }

    /** Pays a settling redemption: investment_redemption platform → the customer's Finance wallet. */
    public function settle($redId)
    {
        return $this->db->transaction(function (Transaction $t) use ($redId) {
            $r = $this->get($t, 'investment_redemptions/' . $redId, 'redemption');
            if (($r['status'] ?? '') !== 'settling') {
                throw new FinanceException('state_changed', 'This redemption is now "' . ($r['status'] ?? '?') . '".');
            }
            $net = (int)($r['netAmount'] ?? 0);
            $cur = $r['currency'] ?? '';
            $uid = $r['userId'] ?? '';
            $txId = '';
            if ($net > 0) {
                $wallet = $this->ledger->walletIdFor($t, $uid, $cur);
                $state = Ledger::newState();
                $plan = $this->ledger->plan($t, $state, 'investment_redemption', $cur, Ledger::platformId($cur), $wallet, $net, 0,
                    [$uid], ['kind' => 'investment_redemptions', 'id' => $redId], 'Investment redemption: ' . ($r['fundName'] ?? ''));
                $txId = $this->ledger->write($t, $state, $plan, Auth::id())['txId'];
            }
            $t->update('investment_redemptions/' . $redId, ['status' => 'settled', 'settledAt' => Value::serverTimestamp(), 'txId' => $txId,
                'settledBy' => $this->admin(), 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'investment.redemption_settle', 'investment_redemptions', $redId, ['status' => 'settling'], ['status' => 'settled', 'txId' => $txId, 'amount' => $net]);
            return $r;
        });
    }

    public function refuseRedemption($redId, $reason)
    {
        self::needReason($reason);
        return $this->db->transaction(function (Transaction $t) use ($redId, $reason) {
            $r = $this->get($t, 'investment_redemptions/' . $redId, 'redemption');
            if (!in_array($r['status'] ?? '', ['submitted', 'processing'], true)) {
                throw new FinanceException('state_changed', 'This redemption is now "' . ($r['status'] ?? '?') . '".');
            }
            $t->update('investment_redemptions/' . $redId, ['status' => 'rejected', 'reason' => $reason, 'processedBy' => $this->admin(), 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'investment.redemption_refuse', 'investment_redemptions', $redId, ['status' => $r['status']], ['status' => 'rejected'], $reason);
            return $r;
        });
    }

    /* ========================================================= reports */

    /** Uploads a customer's PDF report to investment_reports/{uid}/… and records it. */
    public function addReport($uid, $title, $period, $file, $fileName)
    {
        $title = trim((string)$title);
        $period = trim((string)$period);
        if ($title === '' || $period === '') {
            throw new FinanceException('invalid', 'A report needs a title and a period.');
        }
        if (!$this->db->get('users/' . $uid)) {
            throw new FinanceException('missing', 'This customer does not exist.');
        }
        $path = Storage::upload($file, 'investment_reports/' . $uid . '/' . (int)floor(microtime(true) * 1000) . '-' . Storage::safeName($fileName));
        $id = Money::random(20);
        $doc = ['userId' => $uid, 'title' => $title, 'period' => $period, 'storagePath' => $path, 'createdAt' => Value::serverTimestamp(), 'createdBy' => $this->admin()];
        $this->db->batch([
            $this->db->write('create', 'investment_reports/' . $id, $doc),
            Audit::write($this->db, 'investment.report_add', 'investment_reports', $id, null, ['userId' => $uid, 'title' => $title, 'period' => $period, 'storagePath' => $path]),
        ]);
        return $id;
    }

    private static function needReason($reason)
    {
        if (trim((string)$reason) === '') {
            throw new FinanceException('invalid', 'A reason is required.');
        }
    }
}
