<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;

/**
 * Daily bank reconciliation (spec 6.5.3): per day and currency, what the
 * ledger says went through Spideli's bank / provider accounts, against the
 * statement the admin enters.
 *
 *  - in:  gateway payments received that day (payment_receipts);
 *  - out: payouts completed that day - withdrawals (what the customer
 *         receives), external transfers (the amount received) and bills.
 *
 * Saved as reconciliations/{yyyy-MM-dd}_{CUR} = {date, currency, expected,
 * actual, difference, expectedIn, expectedOut, actualIn, actualOut, notes,
 * status, by, at}; saving the same day again updates it (audited).
 */
class Reconciliation
{
    private $db;

    public function __construct(Firestore $db = null)
    {
        $this->db = $db ?: app(Firestore::class);
    }

    public static function id(\DateTimeInterface $day, $currency)
    {
        return $day->format('Y-m-d') . '_' . $currency;
    }

    /** The day's documents of a collection by one date field (single-field index only). */
    private function day($collection, $field, \DateTimeInterface $from, \DateTimeInterface $to)
    {
        return $this->db->query($collection, [[$field, '>=', $from], [$field, '<', $to]], [[$field, 'asc']], 5000);
    }

    /**
     * What the ledger expects for a day (00:00-24:00 in the panel's
     * timezone) and currency: totals and the lines behind them.
     */
    public function expected(\DateTimeImmutable $day, $currency)
    {
        $from = $day->setTime(0, 0);
        $to = $from->modify('+1 day');
        $in = ['total' => 0, 'count' => 0, 'verified' => 0, 'byMethod' => []];
        foreach ($this->day('payment_receipts', 'createdAt', $from, $to) as $r) {
            $d = $r['data'];
            if (($d['currency'] ?? '') !== $currency || !empty($d['demo'])) {
                continue;
            }
            $amount = (int)($d['amount'] ?? 0);
            $in['total'] += $amount;
            $in['count']++;
            $in['verified'] += !empty($d['verified']) ? 1 : 0;
            $m = (string)($d['method'] ?? '?');
            $in['byMethod'][$m] = ($in['byMethod'][$m] ?? 0) + $amount;
        }
        $out = ['total' => 0, 'count' => 0, 'byKind' => []];
        foreach ([
            ['withdrawals', 'wallet_operations', function ($d) { return ($d['type'] ?? '') === 'withdrawal' ? (int)($d['amount'] ?? 0) : null; }],
            ['transfers', 'money_transfers', function ($d) { return ($d['channel'] ?? 'internal') !== 'internal' ? (int)($d['receiveAmount'] ?? $d['amount'] ?? 0) : null; }],
            ['bills', 'utility_payment_requests', function ($d) { return (int)($d['amount'] ?? 0); }],
        ] as [$kind, $col, $paid]) {
            foreach ($this->day($col, 'completedAt', $from, $to) as $r) {
                $d = $r['data'];
                if (($d['status'] ?? '') !== 'completed' || ($d['currency'] ?? '') !== $currency || !empty($d['demo'])) {
                    continue;
                }
                $amount = $paid($d);
                if ($amount === null) {
                    continue;
                }
                $out['total'] += $amount;
                $out['count']++;
                $out['byKind'][$kind] = ($out['byKind'][$kind] ?? 0) + $amount;
            }
        }
        return ['in' => $in, 'out' => $out, 'net' => $in['total'] - $out['total']];
    }

    /**
     * Saves a day's reconciliation from the statement totals the admin
     * entered (minor units). Returns the record.
     */
    public function save(\DateTimeImmutable $day, $currency, $actualIn, $actualOut, $notes)
    {
        if (!Money::isCurrency($currency)) {
            throw new FinanceException('invalid', 'Choose the currency.');
        }
        if ($actualIn === null || $actualOut === null || $actualIn < 0 || $actualOut < 0) {
            throw new FinanceException('invalid', 'Enter the statement\'s money in and money out (0 when none).');
        }
        if ($day > new \DateTimeImmutable('today', $day->getTimezone())) {
            throw new FinanceException('invalid', 'A future day cannot be reconciled.');
        }
        $e = $this->expected($day, $currency);
        $notes = trim((string)$notes);
        $actual = $actualIn - $actualOut;
        $diffIn = $actualIn - $e['in']['total'];
        $diffOut = $actualOut - $e['out']['total'];
        if (($diffIn !== 0 || $diffOut !== 0) && $notes === '') {
            throw new FinanceException('invalid', 'There is a difference: explain it in the notes.');
        }
        $id = self::id($day, $currency);
        $record = [
            'date' => $day->format('Y-m-d'), 'currency' => $currency,
            'expected' => $e['net'], 'actual' => $actual, 'difference' => $actual - $e['net'],
            'expectedIn' => $e['in']['total'], 'expectedOut' => $e['out']['total'], 'actualIn' => $actualIn, 'actualOut' => $actualOut,
            'differenceIn' => $diffIn, 'differenceOut' => $diffOut,
            'receipts' => $e['in']['count'], 'receiptsVerified' => $e['in']['verified'], 'payouts' => $e['out']['count'],
            'status' => $diffIn === 0 && $diffOut === 0 ? 'matched' : 'difference',
            'notes' => $notes, 'by' => 'admin:' . Auth::id(), 'byEmail' => (string)(Auth::user()->email ?? ''), 'at' => Value::serverTimestamp(),
        ];
        $this->db->transaction(function (Transaction $t) use ($id, $record) {
            $before = $t->get('reconciliations/' . $id);
            $t->set('reconciliations/' . $id, $record);
            Audit::add($t, $before ? 'reconciliation.update' : 'reconciliation.create', 'reconciliations', $id,
                $before ? array_intersect_key($before['data'], array_flip(['actualIn', 'actualOut', 'difference', 'status', 'notes'])) : null,
                array_intersect_key($record, array_flip(['expectedIn', 'expectedOut', 'actualIn', 'actualOut', 'difference', 'status', 'notes'])));
        });
        return $record;
    }

    /**
     * Days of the last $days (yesterday back) with no reconciliation, per
     * currency: [CUR => [Y-m-d, ...]].
     */
    public function missing(array $currencies, $days, \DateTimeZone $tz)
    {
        $out = [];
        $today = new \DateTimeImmutable('today', $tz);
        $paths = [];
        foreach ($currencies as $cur) {
            for ($i = 1; $i <= $days; $i++) {
                $paths[] = 'reconciliations/' . self::id($today->modify("-$i days"), $cur);
            }
        }
        $found = $paths ? $this->db->getMany($paths) : [];
        foreach ($currencies as $cur) {
            for ($i = 1; $i <= $days; $i++) {
                $d = $today->modify("-$i days");
                if (empty($found['reconciliations/' . self::id($d, $cur)])) {
                    $out[$cur][] = $d->format('Y-m-d');
                }
            }
        }
        return $out;
    }
}
