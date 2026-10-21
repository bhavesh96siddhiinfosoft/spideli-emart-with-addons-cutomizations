<?php

namespace App\Finance;

/**
 * A credit score per member (client document "Tontine and Loan"): how
 * reliably a customer contributes to their tontines, repays their loans and
 * lends, on a 300-850 scale with a band and plain-language factors.
 *
 * Stored as credit_scores/{uid} = { userId, score, band, factors, inputs,
 * updatedAt } - written only by the panel: recalculated for everyone each
 * night (Jobs) and at once after a default or a restriction. The loan review
 * suggests the borrower's current score. Demo records are ignored.
 */
class CreditScores
{
    private $db;

    public function __construct(Firestore $db)
    {
        $this->db = $db;
    }

    /** Band of a score (the same bands as the loan review). */
    public static function band($score)
    {
        return $score >= 750 ? 'Excellent' : ($score >= 670 ? 'Good' : ($score >= 580 ? 'Fair' : 'Poor'));
    }

    /**
     * The score from a member's inputs (see gather()):
     * ['score' => int, 'band' => string, 'factors' => [[label, impact, detail]], 'inputs' => counts].
     */
    public static function score(array $u, array $in, \DateTimeInterface $now)
    {
        $score = 600;
        $factors = [];
        $add = function ($points, $label, $impact, $detail) use (&$score, &$factors) {
            $score += $points;
            $factors[] = ['label' => $label, 'impact' => $impact, 'detail' => $detail];
        };

        $level = (string)($u['kycLevel'] ?? '');
        if ($level !== '') {
            $add($level === 'basic' ? 25 : 50, 'Identity verified', 'positive', "Verification level \"$level\".");
        } else {
            $add(-50, 'Identity not verified', 'negative', 'No completed identity verification.');
        }

        if ($in['contribOnTime']) {
            $add(min(100, 5 * $in['contribOnTime']), 'Contributions on time', 'positive', $in['contribOnTime'] . ' tontine contribution(s) paid by the due date.');
        }
        if ($in['contribLate']) {
            $add(-min(100, 10 * $in['contribLate']), 'Contributions paid late', 'negative', $in['contribLate'] . ' tontine contribution(s) paid after the due date.');
        }
        if ($in['contribMissed']) {
            $add(-min(150, 25 * $in['contribMissed']), 'Contributions missed', 'negative', $in['contribMissed'] . ' tontine contribution(s) past their due date and not paid.');
        }

        if ($in['loansRepaid']) {
            $add(min(100, 50 * $in['loansRepaid']), 'Loans repaid', 'positive', $in['loansRepaid'] . ' loan(s) fully repaid.');
        }
        if ($in['instOnTime']) {
            $add(min(100, 10 * $in['instOnTime']), 'Repayments on time', 'positive', $in['instOnTime'] . ' loan instalment(s) paid without penalty.');
        }
        if ($in['instLate']) {
            $add(-min(100, 20 * $in['instLate']), 'Repayments paid late', 'negative', $in['instLate'] . ' loan instalment(s) paid with a late penalty.');
        }
        if ($in['instOverdue']) {
            $add(-min(160, 40 * $in['instOverdue']), 'Repayments overdue', 'negative', $in['instOverdue'] . ' loan instalment(s) overdue now.');
        }
        if ($in['loansDefaulted']) {
            $add(-min(300, 150 * $in['loansDefaulted']), 'Loans defaulted', 'negative', $in['loansDefaulted'] . ' loan(s) not repaid (defaulted).');
        }

        if ($in['loansLent']) {
            $add(min(50, 10 * $in['loansLent']), 'Lends to others', 'positive', 'Lent money in ' . $in['loansLent'] . ' loan(s).');
        }

        $since = $u['createdAt'] ?? null;
        if ($since instanceof \DateTimeInterface) {
            $months = max(0, (int)floor(($now->getTimestamp() - $since->getTimestamp()) / (30 * 86400)));
            $score += min(50, $months * 2);
            $factors[] = $months < 12
                ? ['label' => 'Short history', 'impact' => 'negative', 'detail' => 'Less than a year using Spideli.']
                : ['label' => 'Long-standing customer', 'impact' => 'positive', 'detail' => "Using Spideli for $months months."];
        }
        if (!empty($u['financeRestriction'])) {
            $add(-100, 'Finance restriction', 'negative', 'The account is currently restricted.');
        }

        $score = max(300, min(850, $score));
        return ['score' => $score, 'band' => self::band($score), 'factors' => $factors, 'inputs' => $in];
    }

    /**
     * Counts every member's inputs from the records. $data = [
     *   'tontines' => rows, 'cycles' => rows, 'contributions' => rows,
     *   'loans' => rows, 'schedules' => [loanId => data], 'fundings' => rows ]
     * Returns [uid => inputs].
     */
    public static function gather(array $data, \DateTimeInterface $now)
    {
        $live = function (array $rows) {
            return array_values(array_filter($rows, function ($r) { return empty($r['data']['demo']); }));
        };
        $empty = ['contribOnTime' => 0, 'contribLate' => 0, 'contribMissed' => 0, 'loansRepaid' => 0, 'loansDefaulted' => 0,
                  'instOnTime' => 0, 'instLate' => 0, 'instOverdue' => 0, 'loansLent' => 0];
        $out = [];
        $bump = function ($uid, $key) use (&$out, $empty) {
            if (!is_string($uid) || $uid === '') {
                return;
            }
            $out[$uid] = $out[$uid] ?? $empty;
            $out[$uid][$key]++;
        };
        $grace = 86400;   // a contribution paid within a day of the due date counts as on time

        // contributions: on time / late, and missed cycles of the tontines a member belongs to
        $paid = [];
        foreach ($live($data['contributions'] ?? []) as $c) {
            $paid[($c['data']['cycleId'] ?? '') . '|' . ($c['data']['userId'] ?? '')] = $c['data'];
        }
        $members = [];
        foreach ($live($data['tontines'] ?? []) as $t) {
            $members[$t['id']] = (array)($t['data']['memberIds'] ?? []);
        }
        foreach ($live($data['cycles'] ?? []) as $cy) {
            $due = $cy['data']['dueDate'] ?? null;
            $tid = $cy['data']['tontineId'] ?? '';
            if (!$due instanceof \DateTimeInterface) {
                continue;
            }
            foreach ($members[$tid] ?? [] as $uid) {
                $c = $paid[$cy['id'] . '|' . $uid] ?? null;
                if ($c) {
                    $at = $c['paidAt'] ?? $c['createdAt'] ?? null;
                    $late = $at instanceof \DateTimeInterface && $at->getTimestamp() > $due->getTimestamp() + $grace;
                    $bump($uid, $late ? 'contribLate' : 'contribOnTime');
                } elseif ($now->getTimestamp() > $due->getTimestamp() + $grace) {
                    $bump($uid, 'contribMissed');
                }
            }
        }

        // loans as borrower: repaid / defaulted, and each instalment
        foreach ($live($data['loans'] ?? []) as $l) {
            $uid = $l['data']['borrowerId'] ?? '';
            $status = $l['data']['status'] ?? '';
            if ($status === 'repaid') {
                $bump($uid, 'loansRepaid');
            } elseif ($status === 'defaulted') {
                $bump($uid, 'loansDefaulted');
            }
            foreach ((array)($data['schedules'][$l['id']]['installments'] ?? []) as $i) {
                $i = (array)$i;
                $s = $i['status'] ?? '';
                if ($s === 'overdue') {
                    $bump($uid, 'instOverdue');
                } elseif ($s === 'paid') {
                    $bump($uid, (int)($i['penalty'] ?? 0) > 0 ? 'instLate' : 'instOnTime');
                }
            }
        }

        // lending: one per loan a member funded (refunded fundings do not count)
        $lent = [];
        foreach ($live($data['fundings'] ?? []) as $f) {
            if (($f['data']['status'] ?? '') !== 'refunded') {
                $lent[($f['data']['lenderId'] ?? '') . '|' . ($f['data']['loanId'] ?? '')] = true;
            }
        }
        foreach (array_keys($lent) as $key) {
            $bump(explode('|', $key)[0], 'loansLent');
        }

        foreach ($out as $uid => $in) {
            $out[$uid] = $in + $empty;
        }
        return ['inputs' => $out, 'empty' => $empty];
    }

    /** One member's score from targeted reads (no write). */
    public function compute($uid)
    {
        $user = $this->db->get('users/' . $uid);
        $u = $user ? $user['data'] : [];
        $tontines = $this->db->query('tontines', [['memberIds', 'array-contains', $uid]], [], 100);
        $cycles = [];
        foreach ($tontines as $t) {
            $cycles = array_merge($cycles, $this->db->query('tontine_cycles', [['tontineId', '==', $t['id']]], [], 200));
        }
        $loans = $this->db->query('loan_applications', [['borrowerId', '==', $uid]], [], 100);
        $schedules = [];
        foreach ($loans as $l) {
            $s = $this->db->get('loan_schedules/' . $l['id']);
            if ($s) {
                $schedules[$l['id']] = $s['data'];
            }
        }
        $now = new \DateTimeImmutable();
        $g = self::gather([
            'tontines' => $tontines, 'cycles' => $cycles,
            'contributions' => $this->db->query('tontine_contributions', [['userId', '==', $uid]], [], 500),
            'loans' => $loans, 'schedules' => $schedules,
            'fundings' => $this->db->query('loan_fundings', [['lenderId', '==', $uid]], [], 300),
        ], $now);
        return self::score($u, $g['inputs'][$uid] ?? $g['empty'], $now);
    }

    /** Recalculates and stores one member's score; best effort, returns the result or null. */
    public function refresh($uid)
    {
        try {
            $r = $this->compute($uid);
            $this->db->batch([$this->db->write('set', 'credit_scores/' . $uid, self::doc($uid, $r))]);
            return $r;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    private static function doc($uid, array $r)
    {
        return ['userId' => $uid, 'score' => $r['score'], 'band' => $r['band'], 'factors' => $r['factors'] ?: Value::map([]),
                'inputs' => $r['inputs'], 'updatedAt' => Value::serverTimestamp()];
    }

    /**
     * Every member who uses tontines, loans or the Finance wallet: one bulk
     * read per collection, then one write per member (in batches of 400).
     * Returns the number of scores written.
     */
    public function recalculateAll()
    {
        $now = new \DateTimeImmutable();
        $loans = $this->db->query('loan_applications', [], [], 5000);
        $schedules = [];
        foreach ($this->db->query('loan_schedules', [], [], 5000) as $s) {
            $schedules[$s['id']] = $s['data'];
        }
        $tontines = $this->db->query('tontines', [], [], 5000);
        $fundings = $this->db->query('loan_fundings', [], [], 10000);
        $g = self::gather([
            'tontines' => $tontines, 'cycles' => $this->db->query('tontine_cycles', [], [], 20000),
            'contributions' => $this->db->query('tontine_contributions', [], [], 50000),
            'loans' => $loans, 'schedules' => $schedules, 'fundings' => $fundings,
        ], $now);

        // the members to score: anyone in a tontine, a loan, a funding, or with a Finance wallet
        $uids = array_keys($g['inputs']);
        foreach ($tontines as $t) {
            if (empty($t['data']['demo'])) {
                $uids = array_merge($uids, (array)($t['data']['memberIds'] ?? []));
            }
        }
        foreach ($loans as $l) {
            if (empty($l['data']['demo'])) {
                $uids[] = $l['data']['borrowerId'] ?? '';
            }
        }
        foreach ($this->db->query('financial_accounts', [['kind', '==', 'wallet']], [], 20000) as $w) {
            $uids[] = $w['data']['userId'] ?? '';
        }
        $uids = array_values(array_unique(array_filter($uids, function ($u) { return is_string($u) && $u !== ''; })));

        $written = 0;
        foreach (array_chunk($uids, 400) as $chunk) {
            $users = $this->db->getMany(array_map(function ($u) { return 'users/' . $u; }, $chunk));
            $writes = [];
            foreach ($chunk as $uid) {
                $doc = $users['users/' . $uid] ?? null;
                if (!$doc || ($doc['data']['role'] ?? 'customer') !== 'customer' || !empty($doc['data']['isDeleted'])) {
                    continue;
                }
                $writes[] = $this->db->write('set', 'credit_scores/' . $uid, self::doc($uid, self::score($doc['data'], $g['inputs'][$uid] ?? $g['empty'], $now)));
            }
            if ($writes) {
                $this->db->batch($writes);
                $written += count($writes);
            }
        }
        return $written;
    }
}
