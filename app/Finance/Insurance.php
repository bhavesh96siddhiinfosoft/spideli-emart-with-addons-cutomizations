<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;

/**
 * Phase D2 insurance management (spec 6.11, data 3.10, rules R.11):
 * partners, the product catalog, pricing quotes (the panel suggests the
 * premium from the product's rating; the admin confirms it), activating or
 * refusing paid policies, renewals, cancellations and claims.
 *
 * A policy is insurance_policies/{quoteId}; its first premium is
 * insurance_premiums/{policyId}_1 (paid by the customer on acceptance); the
 * panel creates the later ones (_2, _3, ...) when it activates the policy.
 * The daily work (expiry, premiums due / overdue, lapse, renewals) is in
 * Jobs, using the helpers here.
 */
class Insurance
{
    public const CATEGORIES = ['health', 'life', 'auto', 'home', 'travel', 'business', 'agriculture', 'device', 'funeral'];
    public const FREQUENCIES = ['annual' => 1, 'semiannual' => 2, 'quarterly' => 4, 'monthly' => 12];

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

    private static function needReason($reason)
    {
        if (trim((string)$reason) === '') {
            throw new FinanceException('invalid', 'A reason is required.');
        }
    }

    private static function state($doc, array $allowed, $what)
    {
        if (!in_array($doc['status'] ?? '', $allowed, true)) {
            throw new FinanceException('state_changed', "This $what is now \"" . ($doc['status'] ?? '?') . '".');
        }
    }

    /** A timestamp, or a date string the app may have written (coverStart may be either). */
    public static function date($v)
    {
        if ($v instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($v);
        }
        if (is_string($v) && trim($v) !== '') {
            try {
                return new \DateTimeImmutable($v);
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }

    public static function periods($frequency)
    {
        return self::FREQUENCIES[$frequency] ?? 1;
    }

    /* =========================================================== pricing */

    /**
     * The premium the product's rating gives for a customer's answers:
     * basePremium × each factor's multiplier = the premium for a year; the
     * instalment adds the frequency's loading and divides by the number of
     * payments a year. Null when the product has no rating.
     * Returns ['premium', 'installment', 'lines' => [explanations]].
     */
    public static function suggest(array $product, array $answers, $frequency)
    {
        $rating = (array)($product['rating'] ?? []);
        $base = (float)($rating['basePremium'] ?? 0);
        if ($base <= 0) {
            return null;
        }
        $cur = $product['currency'] ?? '';
        $lines = [__('Base premium (a year)') . ': ' . Money::format((int)round($base), $cur)];
        $multiplier = 1.0;
        foreach ((array)($rating['factors'] ?? []) as $f) {
            $key = $f['key'] ?? '';
            $label = $f['label'] ?? $key;
            $answer = $answers[$key] ?? null;
            $m = 1.0;
            if (($f['type'] ?? '') === 'select') {
                foreach ((array)($f['options'] ?? []) as $o) {
                    if ((string)($o['value'] ?? '') === (string)$answer) {
                        $m = (float)($o['multiplier'] ?? 1);
                        $answer = $o['label'] ?? $answer;
                    }
                }
            } else {
                $bands = (array)($f['bands'] ?? []);
                usort($bands, function ($a, $b) { return (float)($a['upTo'] ?? 0) <=> (float)($b['upTo'] ?? 0); });
                if ($bands && is_numeric($answer)) {
                    $m = (float)(end($bands)['multiplier'] ?? 1);
                    foreach ($bands as $b) {
                        if ((float)$answer <= (float)($b['upTo'] ?? 0)) {
                            $m = (float)($b['multiplier'] ?? 1);
                            break;
                        }
                    }
                }
            }
            $multiplier *= $m;
            $lines[] = $label . ': ' . ($answer === null || $answer === '' ? '—' : $answer . (isset($f['unit']) && $f['unit'] !== '' && ($f['type'] ?? '') !== 'select' ? ' ' . $f['unit'] : '')) . ' → ×' . $m;
        }
        $premium = (int)round($base * $multiplier);
        $loading = (float)($rating['frequencies'][$frequency] ?? 0);
        $periods = self::periods($frequency);
        $installment = (int)round($premium * (1 + $loading / 100) / $periods);
        $lines[] = __('Frequency') . ': ' . $frequency . ($loading ? ' (+' . $loading . '%)' : '') . ' → ' . $periods . ' × ' . Money::format($installment, $cur);
        return ['premium' => $premium, 'installment' => $installment, 'lines' => $lines];
    }

    /* ========================================================== partners */

    public function savePartner($id, array $in)
    {
        $p = [
            'name' => trim((string)($in['name'] ?? '')),
            'licenceNumber' => trim((string)($in['licenceNumber'] ?? '')),
            'country' => trim((string)($in['country'] ?? '')),
            'contact' => trim((string)($in['contact'] ?? '')),
        ];
        if ($p['name'] === '') {
            throw new FinanceException('invalid', 'The partner needs a name.');
        }
        $isNew = !$id;
        $id = $id ?: Money::random(20);
        return $this->db->transaction(function (Transaction $t) use ($id, $isNew, $p) {
            $before = $isNew ? null : $this->get($t, 'insurance_partners/' . $id, 'partner');
            if ($isNew) {
                $t->create('insurance_partners/' . $id, $p + ['createdAt' => Value::serverTimestamp(), 'updatedAt' => Value::serverTimestamp()]);
            } else {
                $t->update('insurance_partners/' . $id, $p + ['updatedAt' => Value::serverTimestamp()]);
            }
            Audit::add($t, $isNew ? 'insurance.partner_create' : 'insurance.partner_update', 'insurance_partners', $id, $before ? array_intersect_key($before, $p) : null, $p);
            return $id;
        });
    }

    /* ========================================================== products */

    /**
     * The product fields of spec 3.10 from the form. $partners: id => data.
     * The currency is fixed once the product exists.
     */
    public static function productFromInput(array $in, array $partners, array $existing = null)
    {
        $name = trim((string)($in['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 200) {
            throw new FinanceException('invalid', 'The product needs a name (at most 200 characters).');
        }
        $partnerId = (string)($in['partnerId'] ?? '');
        if (!isset($partners[$partnerId])) {
            throw new FinanceException('invalid', 'Choose the insurer (partner).');
        }
        $cur = $existing['currency'] ?? (string)($in['currency'] ?? '');
        if (!Money::isCurrency($cur)) {
            throw new FinanceException('invalid', 'Choose the product\'s currency.');
        }
        $category = (string)($in['category'] ?? '');
        if (!in_array($category, self::CATEGORIES, true)) {
            throw new FinanceException('invalid', 'Choose the category.');
        }
        $status = (string)($in['status'] ?? 'open');
        if (!in_array($status, ['open', 'closed'], true)) {
            throw new FinanceException('invalid', 'Unknown product status.');
        }
        $num = function ($v, $label, $max = null) {
            $v = trim((string)$v);
            if ($v === '') {
                return 0;
            }
            if (!is_numeric($v) || (float)$v < 0 || ($max !== null && (float)$v > $max)) {
                throw new FinanceException('invalid', "$label: " . __('a number from 0') . ($max !== null ? " " . __('to') . " $max" : '') . '.');
            }
            return (float)$v == (int)$v ? (int)$v : round((float)$v, 4);
        };
        $months = (int)($in['termMonths'] ?? 12);
        if ($months < 1 || $months > 120) {
            throw new FinanceException('invalid', 'The term is 1 to 120 months.');
        }

        $coverages = [];
        foreach ((array)($in['coverages'] ?? []) as $c) {
            $cn = trim((string)($c['name'] ?? ''));
            if ($cn === '') {
                continue;
            }
            $coverages[] = ['name' => $cn, 'description' => trim((string)($c['description'] ?? '')), 'limit' => Money::toMinor($c['limit'] ?? '', $cur) ?? 0];
        }
        $exclusions = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string)($in['exclusions'] ?? ''))), 'strlen'));

        $frequencies = [];
        foreach (array_keys(self::FREQUENCIES) as $fq) {
            if (!empty($in['frequencyOn'][$fq])) {
                $frequencies[$fq] = $num($in['loading'][$fq] ?? '', __('Loading') . " ($fq)", 100);
            }
        }
        if (!$frequencies) {
            throw new FinanceException('invalid', 'Offer at least one payment frequency.');
        }
        $factors = [];
        foreach ((array)($in['factors'] ?? []) as $f) {
            $key = trim((string)($f['key'] ?? ''));
            if ($key === '') {
                continue;
            }
            if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,39}$/', $key)) {
                throw new FinanceException('invalid', 'A factor key is letters, digits and _ (e.g. age, vehicle_value).');
            }
            $type = ($f['type'] ?? '') === 'select' ? 'select' : 'number';
            $factor = ['key' => $key, 'label' => trim((string)($f['label'] ?? '')) ?: $key, 'type' => $type];
            $rows = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string)($f['values'] ?? ''))), 'strlen'));
            if ($type === 'number') {
                $factor['unit'] = trim((string)($f['unit'] ?? ''));
                foreach (['min', 'max'] as $k) {
                    if (trim((string)($f[$k] ?? '')) !== '') {
                        $factor[$k] = $num($f[$k], $factor['label'] . " $k");
                    }
                }
                $bands = [];
                foreach ($rows as $r) {
                    if (!preg_match('/^([0-9.]+)\s*[=:]\s*([0-9.]+)$/', $r, $m)) {
                        throw new FinanceException('invalid', $factor['label'] . ': ' . __('each line is "up to = multiplier", e.g. 30 = 1.2'));
                    }
                    $bands[] = ['upTo' => $num($m[1], $factor['label']), 'multiplier' => (float)$m[2]];
                }
                usort($bands, function ($a, $b) { return $a['upTo'] <=> $b['upTo']; });
                $factor['bands'] = $bands;
            } else {
                $options = [];
                foreach ($rows as $r) {
                    $parts = array_map('trim', explode('|', $r));
                    if (count($parts) !== 3 || $parts[0] === '' || !is_numeric($parts[2])) {
                        throw new FinanceException('invalid', $factor['label'] . ': ' . __('each line is "value | label | multiplier", e.g. car | Car | 1.1'));
                    }
                    $options[] = ['value' => $parts[0], 'label' => $parts[1] ?: $parts[0], 'multiplier' => (float)$parts[2]];
                }
                if (!$options) {
                    throw new FinanceException('invalid', $factor['label'] . ': ' . __('a list needs its choices.'));
                }
                $factor['options'] = $options;
            }
            $factors[] = $factor;
        }

        $p = [
            'partnerId' => $partnerId,
            'partnerName' => $partners[$partnerId]['name'] ?? '',
            'category' => $category,
            'name' => $name,
            'summary' => trim((string)($in['summary'] ?? '')),
            'description' => trim((string)($in['description'] ?? '')),
            'currency' => $cur,
            'order' => (int)($in['order'] ?? 0),
            'coverages' => $coverages,
            'exclusions' => $exclusions,
            'deductible' => Money::toMinor($in['deductible'] ?? '', $cur) ?? 0,
            'waitingPeriodDays' => (int)$num($in['waitingPeriodDays'] ?? '', __('Waiting period'), 3650),
            'termMonths' => $months,
            'rating' => [
                'basePremium' => Money::toMinor($in['basePremium'] ?? '', $cur) ?? 0,
                'frequencies' => $frequencies,
                'factors' => $factors,
            ],
            'terms' => trim(str_replace("\r\n", "\n", (string)($in['terms'] ?? ''))),
            'status' => $status,
            'published' => !empty($in['published']),
        ];
        if ($p['published'] && ($p['terms'] === '' || !$coverages)) {
            throw new FinanceException('invalid', 'A published product needs its terms and at least one coverage.');
        }
        return $p;
    }

    /**
     * Creates or edits a product. Documents are uploaded to
     * financial_resources/insurance_products/{id}/ (readable by signed-in
     * customers). termsVersion + 1 when the terms text changes.
     */
    public function saveProduct($id, array $p, array $newDocuments = [], array $removeDocuments = [])
    {
        $isNew = !$id;
        $id = $id ?: Money::random(20);
        foreach ($newDocuments as $i => $d) {
            $newDocuments[$i] = ['name' => $d['name'], 'storagePath' => Storage::upload($d['file'],
                'financial_resources/insurance_products/' . $id . '/' . (int)floor(microtime(true) * 1000) . '-' . $i . '-' . Storage::safeName($d['fileName'] ?? $d['name']))];
        }
        return $this->db->transaction(function (Transaction $t) use ($id, $isNew, $p, $newDocuments, $removeDocuments) {
            $before = $isNew ? null : $this->get($t, 'insurance_products/' . $id, 'product');
            $docs = [];
            foreach ((array)($before['documents'] ?? []) as $i => $d) {
                if (!in_array((string)$i, array_map('strval', $removeDocuments), true)) {
                    $docs[] = $d;
                }
            }
            $p['documents'] = array_merge($docs, array_values($newDocuments));
            if ($isNew) {
                $p += ['id' => $id, 'termsVersion' => 1, 'createdAt' => Value::serverTimestamp(), 'updatedAt' => Value::serverTimestamp(), 'createdBy' => $this->admin()];
                $t->create('insurance_products/' . $id, $p);
                Audit::add($t, 'insurance.product_create', 'insurance_products', $id, null, $p);
                return $id;
            }
            $p['termsVersion'] = (int)($before['termsVersion'] ?? 1) + (($before['terms'] ?? '') !== $p['terms'] ? 1 : 0);
            $p['updatedAt'] = Value::serverTimestamp();
            $p['updatedBy'] = $this->admin();
            $t->update('insurance_products/' . $id, $p);
            [$b, $a] = Audit::diff($before, $p);
            Audit::add($t, 'insurance.product_update', 'insurance_products', $id, $b, $a);
            return $id;
        });
    }

    /* ============================================================ quotes */

    /**
     * Prices a requested quote. $f: premium, installmentAmount, deductible
     * (minor units), coverStart (DateTime), validUntil (DateTime),
     * insurerReference. Terms, coverages and documents come from the product.
     */
    public function priceQuote($quoteId, array $f)
    {
        if ((int)$f['premium'] <= 0 || (int)$f['installmentAmount'] <= 0) {
            throw new FinanceException('invalid', 'The premium and the instalment must be above 0.');
        }
        if (!($f['validUntil'] instanceof \DateTimeInterface) || $f['validUntil'] <= self::now()) {
            throw new FinanceException('invalid', 'The quote must stay valid until a future date.');
        }
        return $this->db->transaction(function (Transaction $t) use ($quoteId, $f) {
            $q = $this->get($t, 'insurance_quotes/' . $quoteId, 'quote');
            self::state($q, ['requested'], 'quote');
            if (!empty($q['demo'])) {
                throw new FinanceException('demo', 'Demo records are never changed.');
            }
            $p = $this->get($t, 'insurance_products/' . ($q['productId'] ?? ''), 'product');
            $months = (int)($q['termMonths'] ?? 0) ?: (int)($p['termMonths'] ?? 12);
            $start = $f['coverStart'] instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($f['coverStart']) : self::date($q['coverStart'] ?? null);
            if (!$start) {
                throw new FinanceException('invalid', 'Choose the date the cover starts.');
            }
            $changes = [
                'status' => 'quoted',
                'premium' => (int)$f['premium'],
                'installmentAmount' => (int)$f['installmentAmount'],
                'deductible' => (int)$f['deductible'],
                'coverStart' => $start,
                'coverEnd' => $start->modify("+$months months"),
                'termMonths' => $months,
                'validUntil' => \DateTimeImmutable::createFromInterface($f['validUntil']),
                'terms' => $p['terms'] ?? '',
                'termsVersion' => (int)($p['termsVersion'] ?? 1),
                'coverages' => array_values((array)($p['coverages'] ?? [])),
                'documents' => array_values((array)($p['documents'] ?? [])),
                'insurerReference' => trim((string)($f['insurerReference'] ?? '')),
                'pricedBy' => $this->admin(),
                'updatedAt' => Value::serverTimestamp(),
            ];
            $t->update('insurance_quotes/' . $quoteId, $changes);
            Audit::add($t, 'insurance.quote_price', 'insurance_quotes', $quoteId, ['status' => 'requested'],
                ['status' => 'quoted', 'premium' => $changes['premium'], 'installmentAmount' => $changes['installmentAmount'], 'validUntil' => $changes['validUntil']]);
            return array_merge($q, $changes);
        });
    }

    public function refuseQuote($quoteId, $reason)
    {
        self::needReason($reason);
        return $this->db->transaction(function (Transaction $t) use ($quoteId, $reason) {
            $q = $this->get($t, 'insurance_quotes/' . $quoteId, 'quote');
            self::state($q, ['requested'], 'quote');
            $t->update('insurance_quotes/' . $quoteId, ['status' => 'rejected', 'reason' => $reason, 'pricedBy' => $this->admin(), 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'insurance.quote_refuse', 'insurance_quotes', $quoteId, ['status' => 'requested'], ['status' => 'rejected'], $reason);
            return $q;
        });
    }

    /* ========================================================== policies */

    /**
     * The premium schedule of one cover period: payments a year × years,
     * one every 12 / payments months from coverStart. [n => dueDate] from 1.
     */
    public static function schedule(\DateTimeInterface $start, $frequency, $termMonths)
    {
        $periods = self::periods($frequency);
        $every = intdiv(12, $periods);
        $count = max(1, (int)round($termMonths / $every));
        $out = [];
        for ($n = 1; $n <= $count; $n++) {
            $out[$n] = \DateTimeImmutable::createFromInterface($start)->modify('+' . (($n - 1) * $every) . ' months');
        }
        return $out;
    }

    /**
     * Activates a paid policy: policy number, documents (already uploaded
     * by the caller to insurance_policies/{uid}/{policyId}/), the later
     * premiums (_2, _3, ... "upcoming") and nextPremiumDue.
     */
    public function activate($policyId, $policyNumber, array $documents = [])
    {
        $policyNumber = trim((string)$policyNumber);
        if ($policyNumber === '' || mb_strlen($policyNumber) > 100) {
            throw new FinanceException('invalid', 'Enter the policy number (at most 100 characters).');
        }
        return $this->db->transaction(function (Transaction $t) use ($policyId, $policyNumber, $documents) {
            $p = $this->get($t, 'insurance_policies/' . $policyId, 'policy');
            self::state($p, ['pending_activation'], 'policy');
            if (!empty($p['demo'])) {
                throw new FinanceException('demo', 'Demo records are never changed.');
            }
            $first = $t->get('insurance_premiums/' . $policyId . '_1');
            if (!$first || ($first['data']['status'] ?? '') !== 'paid') {
                throw new FinanceException('not_paid', 'The first premium is not paid.');
            }
            $start = self::date($p['coverStart'] ?? null) ?: self::now();
            $end = self::date($p['coverEnd'] ?? null);
            $months = $end ? max(1, (int)round(($end->getTimestamp() - $start->getTimestamp()) / (86400 * 30.4375))) : 12;
            $dates = self::schedule($start, $p['frequency'] ?? 'annual', $months);
            $existing = [];
            foreach ($dates as $n => $due) {
                if ($n > 1) {
                    $existing[$n] = $t->get('insurance_premiums/' . $policyId . '_' . $n);
                }
            }
            $amount = (int)($p['installmentAmount'] ?? 0) ?: (int)($p['premium'] ?? 0);
            $next = null;
            foreach ($dates as $n => $due) {
                if ($n === 1 || $existing[$n]) {
                    continue;
                }
                $t->create('insurance_premiums/' . $policyId . '_' . $n, ['id' => $policyId . '_' . $n, 'userId' => $p['userId'] ?? '', 'policyId' => $policyId,
                    'amount' => $amount, 'currency' => $p['currency'] ?? '', 'dueDate' => $due, 'status' => 'upcoming',
                    'createdAt' => Value::serverTimestamp(), 'updatedAt' => Value::serverTimestamp()]);
                $next = $next ?: $due;
            }
            $changes = ['status' => 'active', 'policyNumber' => $policyNumber, 'coverStart' => $start,
                'documents' => array_merge(array_values((array)($p['documents'] ?? [])), $documents),
                'nextPremiumDue' => $next, 'activatedAt' => Value::serverTimestamp(), 'activatedBy' => $this->admin(), 'updatedAt' => Value::serverTimestamp()];
            $t->update('insurance_policies/' . $policyId, $changes);
            Audit::add($t, 'insurance.policy_activate', 'insurance_policies', $policyId, ['status' => 'pending_activation'],
                ['status' => 'active', 'policyNumber' => $policyNumber, 'premiums' => count($dates), 'documents' => count($documents)]);
            return array_merge($p, ['policyNumber' => $policyNumber, 'coverStart' => $start]);
        });
    }

    /** Refuses a paid policy: the first premium's posting is reversed (money back). */
    public function refusePolicy($policyId, $reason)
    {
        self::needReason($reason);
        return $this->db->transaction(function (Transaction $t) use ($policyId, $reason) {
            $p = $this->get($t, 'insurance_policies/' . $policyId, 'policy');
            self::state($p, ['pending_activation'], 'policy');
            $first = $t->get('insurance_premiums/' . $policyId . '_1');
            $txId = $first['data']['txId'] ?? '';
            $reversal = [];
            if ($txId !== '') {
                $reversal = $this->ledger->reverse($t, $txId, Auth::id(), 'Insurance policy refused: ' . $reason);
            }
            $t->update('insurance_policies/' . $policyId, ['status' => 'rejected', 'reason' => $reason, 'updatedAt' => Value::serverTimestamp()]);
            if ($first && $reversal) {
                $t->update('insurance_premiums/' . $policyId . '_1', ['refundedAt' => Value::serverTimestamp(), 'refundTxIds' => array_column($reversal, 'txId'), 'updatedAt' => Value::serverTimestamp()]);
            }
            Audit::add($t, 'insurance.policy_refuse', 'insurance_policies', $policyId, ['status' => 'pending_activation'],
                ['status' => 'rejected', 'reversal' => array_column($reversal, 'txId')], $reason);
            return array_merge($p, ['refunded' => (int)($first['data']['amount'] ?? 0)]);
        });
    }

    /** Puts a policy into renewal (the daily job, or an admin): renewal_due at its yearly premium. */
    public function startRenewal($policyId, $premium = null)
    {
        return $this->db->transaction(function (Transaction $t) use ($policyId, $premium) {
            $p = $this->get($t, 'insurance_policies/' . $policyId, 'policy');
            self::state($p, ['active'], 'policy');
            $end = self::date($p['coverEnd'] ?? null);
            if (!$end) {
                throw new FinanceException('invalid', 'The policy has no cover end date.');
            }
            $amount = $premium !== null ? (int)$premium : (int)($p['premium'] ?? 0);
            if ($amount <= 0) {
                throw new FinanceException('invalid', 'The renewal premium must be above 0.');
            }
            $t->update('insurance_policies/' . $policyId, ['status' => 'renewal_due', 'renewalPremium' => $amount, 'renewalDate' => $end, 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'insurance.renewal_start', 'insurance_policies', $policyId, ['status' => 'active'], ['status' => 'renewal_due', 'renewalPremium' => $amount]);
            return array_merge($p, ['renewalPremium' => $amount, 'renewalDate' => $end]);
        });
    }

    /** Changes the renewal premium while the customer has not paid it. */
    public function setRenewalPremium($policyId, $amount)
    {
        if ((int)$amount <= 0) {
            throw new FinanceException('invalid', 'The renewal premium must be above 0.');
        }
        return $this->db->transaction(function (Transaction $t) use ($policyId, $amount) {
            $p = $this->get($t, 'insurance_policies/' . $policyId, 'policy');
            self::state($p, ['renewal_due'], 'policy');
            if (!empty($p['renewalRequestedAt'])) {
                throw new FinanceException('state_changed', 'The customer already paid the renewal.');
            }
            $t->update('insurance_policies/' . $policyId, ['renewalPremium' => (int)$amount, 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'insurance.renewal_premium', 'insurance_policies', $policyId, ['renewalPremium' => $p['renewalPremium'] ?? null], ['renewalPremium' => (int)$amount]);
            return $p;
        });
    }

    /** The renewed policy's fields: the next cover period, renewal fields cleared. */
    private static function renewed(array $p, $paid)
    {
        $start = self::date($p['coverEnd'] ?? null) ?: self::now();
        $old = self::date($p['coverStart'] ?? null);
        $months = $old ? max(1, (int)round(($start->getTimestamp() - $old->getTimestamp()) / (86400 * 30.4375))) : 12;
        return [
            'status' => 'active', 'coverStart' => $start, 'coverEnd' => $start->modify("+$months months"),
            'premium' => (int)($p['renewalPremium'] ?? $p['premium'] ?? 0), 'lastRenewalPaid' => (int)$paid, 'renewedAt' => Value::serverTimestamp(),
            'renewalRequestedAt' => Value::delete(), 'renewalTxId' => Value::delete(), 'renewalPaidAmount' => Value::delete(),
            'nextPremiumDue' => null, 'updatedAt' => Value::serverTimestamp(),
        ];
    }

    /**
     * Renews a policy whose customer paid the renewal in the app
     * (renewalRequestedAt + renewalTxId, a posted insurance_premium).
     */
    public function renewPaid($policyId)
    {
        return $this->db->transaction(function (Transaction $t) use ($policyId) {
            $p = $this->get($t, 'insurance_policies/' . $policyId, 'policy');
            self::state($p, ['renewal_due'], 'policy');
            if (empty($p['renewalRequestedAt']) || empty($p['renewalTxId'])) {
                throw new FinanceException('not_paid', 'The customer has not paid the renewal.');
            }
            $tx = $t->get('ledger_transactions/' . $p['renewalTxId']);
            if (!$tx || ($tx['data']['type'] ?? '') !== 'insurance_premium' || ($tx['data']['status'] ?? '') !== 'posted') {
                throw new FinanceException('not_paid', 'The renewal payment is not a posted insurance premium.');
            }
            $paid = (int)($tx['data']['lines'][0]['amount'] ?? 0);
            $changes = self::renewed($p, $paid);
            $t->update('insurance_policies/' . $policyId, $changes);
            Audit::add($t, 'insurance.renew', 'insurance_policies', $policyId, ['status' => 'renewal_due', 'coverEnd' => $p['coverEnd'] ?? null],
                ['status' => 'active', 'coverEnd' => $changes['coverEnd'], 'paid' => $paid, 'txId' => $p['renewalTxId']]);
            return array_merge($p, ['coverEnd' => $changes['coverEnd']]);
        });
    }

    /**
     * Auto-renewal at the renewal date (the customer switched autoRenew on):
     * insurance_premium wallet → platform of renewalPremium, then renewed.
     * Returns the policy, or null when the wallet holds too little.
     */
    public function autoRenew($policyId)
    {
        try {
            return $this->db->transaction(function (Transaction $t) use ($policyId) {
                $p = $this->get($t, 'insurance_policies/' . $policyId, 'policy');
                self::state($p, ['renewal_due'], 'policy');
                if (empty($p['autoRenew']) || !empty($p['renewalRequestedAt'])) {
                    throw new FinanceException('not_needed', 'Not an automatic renewal.');
                }
                $amount = (int)($p['renewalPremium'] ?? 0);
                $cur = $p['currency'] ?? '';
                $uid = $p['userId'] ?? '';
                $wallet = $this->ledger->walletIdFor($t, $uid, $cur);
                $state = Ledger::newState();
                $plan = $this->ledger->plan($t, $state, 'insurance_premium', $cur, $wallet, Ledger::platformId($cur), $amount, 0,
                    [$uid], ['kind' => 'insurance_policies', 'id' => $policyId], 'Insurance auto-renewal: ' . ($p['productName'] ?? ''));
                $tx = $this->ledger->write($t, $state, $plan, Auth::id() ?: 'job');   // createdBy admin:job when the daily job runs it
                $changes = self::renewed($p, $amount);
                $changes['lastRenewalTxId'] = $tx['txId'];
                $t->update('insurance_policies/' . $policyId, $changes);
                Audit::add($t, 'job.insurance_auto_renew', 'insurance_policies', $policyId, ['status' => 'renewal_due'],
                    ['status' => 'active', 'coverEnd' => $changes['coverEnd'], 'paid' => $amount, 'txId' => $tx['txId']]);
                return array_merge($p, ['coverEnd' => $changes['coverEnd']]);
            });
        } catch (FinanceException $e) {
            if (in_array($e->reason, ['insufficient_funds', 'account_missing'], true)) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * The unused part of what was paid for the current cover period: paid
     * premiums of the period (and a renewal paid for it) minus the share of
     * the year already covered. A suggestion only; the admin decides.
     */
    public static function refundSuggestion(array $p, array $premiums, \DateTimeInterface $now)
    {
        $start = self::date($p['coverStart'] ?? null);
        $end = self::date($p['coverEnd'] ?? null);
        if (!$start || !$end || $end <= $start) {
            return 0;
        }
        $paid = (int)($p['lastRenewalPaid'] ?? 0);
        foreach ($premiums as $pr) {
            $d = $pr['data'] ?? $pr;
            $due = self::date($d['dueDate'] ?? null);
            if (($d['status'] ?? '') === 'paid' && empty($d['refundedAt']) && (!$due || ($due >= $start->modify('-1 day') && $due < $end))) {
                $paid += (int)($d['amount'] ?? 0);
            }
        }
        $total = $end->getTimestamp() - $start->getTimestamp();
        $used = min($total, max(0, $now->getTimestamp() - $start->getTimestamp()));
        $cost = (int)round((int)($p['premium'] ?? 0) * ($total / (365.25 * 86400)) * $used / $total);
        return max(0, $paid - $cost);
    }

    /**
     * Decides a cancellation request: cancel (with a refund the admin
     * entered, 0 allowed: platform → wallet) or keep (back to statusBefore).
     */
    public function cancellation($policyId, $cancel, $refund, $note = '')
    {
        $refund = (int)$refund;
        if ($refund < 0) {
            throw new FinanceException('invalid', 'The refund cannot be negative.');
        }
        return $this->db->transaction(function (Transaction $t) use ($policyId, $cancel, $refund, $note) {
            $p = $this->get($t, 'insurance_policies/' . $policyId, 'policy');
            self::state($p, ['cancellation_requested'], 'policy');
            if (!$cancel) {
                $back = in_array($p['statusBefore'] ?? '', ['active', 'renewal_due'], true) ? $p['statusBefore'] : 'active';
                $t->update('insurance_policies/' . $policyId, ['status' => $back, 'updatedAt' => Value::serverTimestamp()]);
                Audit::add($t, 'insurance.cancellation_keep', 'insurance_policies', $policyId, ['status' => 'cancellation_requested'], ['status' => $back], $note);
                return array_merge($p, ['status' => $back]);
            }
            $txId = '';
            if ($refund > 0) {
                $cur = $p['currency'] ?? '';
                $uid = $p['userId'] ?? '';
                $wallet = $this->ledger->walletIdFor($t, $uid, $cur);
                $state = Ledger::newState();
                $plan = $this->ledger->plan($t, $state, 'manual_adjustment', $cur, Ledger::platformId($cur), $wallet, $refund, 0,
                    [$uid], ['kind' => 'insurance_policies', 'id' => $policyId], 'Insurance cancelled, refund: ' . ($p['productName'] ?? ''));
                $txId = $this->ledger->write($t, $state, $plan, Auth::id())['txId'];
            }
            $t->update('insurance_policies/' . $policyId, ['status' => 'cancelled', 'cancelledAt' => Value::serverTimestamp(), 'refundAmount' => $refund,
                'refundTxId' => $txId, 'nextPremiumDue' => null, 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'insurance.cancellation_cancel', 'insurance_policies', $policyId, ['status' => 'cancellation_requested'],
                ['status' => 'cancelled', 'refund' => $refund, 'txId' => $txId], $note);
            return array_merge($p, ['refund' => $refund]);
        });
    }

    /* ============================================================ claims */

    /**
     * One step of a claim. $action: review | info | approve | pay | reject.
     * Every step adds an entry to insurance_claims/{id}/events.
     */
    public function claim($claimId, $action, $text = '', $amount = null)
    {
        $from = ['review' => ['submitted'], 'info' => ['submitted', 'under_review'], 'approve' => ['submitted', 'under_review'],
            'pay' => ['approved'], 'reject' => ['submitted', 'under_review', 'info_requested']];
        if (!isset($from[$action])) {
            throw new FinanceException('invalid', 'Unknown action.');
        }
        if (in_array($action, ['info', 'reject'], true)) {
            self::needReason($text);
        }
        return $this->db->transaction(function (Transaction $t) use ($claimId, $action, $text, $amount, $from) {
            $c = $this->get($t, 'insurance_claims/' . $claimId, 'claim');
            self::state($c, $from[$action], 'claim');
            if (!empty($c['demo'])) {
                throw new FinanceException('demo', 'Demo records are never changed.');
            }
            $cur = $c['currency'] ?? '';
            $uid = $c['userId'] ?? '';
            $changes = ['updatedAt' => Value::serverTimestamp()];
            $event = ['userId' => $uid, 'authorId' => $this->admin(), 'createdAt' => Value::serverTimestamp()];
            switch ($action) {
                case 'review':
                    $changes['status'] = 'under_review';
                    $event += ['type' => 'under_review', 'message' => ''];
                    break;
                case 'info':
                    $changes += ['status' => 'info_requested', 'reason' => $text];
                    $event += ['type' => 'info_requested', 'message' => $text];
                    break;
                case 'approve':
                    $amount = (int)$amount;
                    if ($amount <= 0 || $amount > (int)($c['amountClaimed'] ?? 0)) {
                        throw new FinanceException('invalid', 'The approved amount must be above 0 and at most the amount claimed.');
                    }
                    $changes += ['status' => 'approved', 'approvedAmount' => $amount, 'reason' => trim((string)$text)];
                    $event += ['type' => 'approved', 'message' => trim((string)$text), 'amount' => $amount];
                    break;
                case 'pay':
                    $amount = (int)($c['approvedAmount'] ?? 0);
                    $wallet = $this->ledger->walletIdFor($t, $uid, $cur);
                    $state = Ledger::newState();
                    $plan = $this->ledger->plan($t, $state, 'insurance_claim_payout', $cur, Ledger::platformId($cur), $wallet, $amount, 0,
                        [$uid], ['kind' => 'insurance_claims', 'id' => $claimId], 'Insurance claim: ' . ($c['productName'] ?? ''));
                    $tx = $this->ledger->write($t, $state, $plan, Auth::id());
                    $changes += ['status' => 'paid', 'paidAmount' => $amount, 'paidAt' => Value::serverTimestamp(), 'txId' => $tx['txId'], 'reference' => $tx['reference'],
                        'insurerReference' => trim((string)$text) ?: ($c['insurerReference'] ?? '')];
                    $event += ['type' => 'paid', 'message' => '', 'amount' => $amount];
                    break;
                case 'reject':
                    $changes += ['status' => 'rejected', 'reason' => $text];
                    $event += ['type' => 'rejected', 'message' => $text];
                    break;
            }
            $t->update('insurance_claims/' . $claimId, $changes);
            $t->create('insurance_claims/' . $claimId . '/events/' . Money::random(20), $event);
            Audit::add($t, 'insurance.claim_' . $action, 'insurance_claims', $claimId, ['status' => $c['status']],
                array_intersect_key($changes, array_flip(['status', 'approvedAmount', 'paidAmount', 'txId'])), in_array($action, ['info', 'reject'], true) ? $text : '');
            return array_merge($c, $changes);
        });
    }
}
