<?php

namespace App\Finance;

/**
 * Phase D4 reporting (spec 6.16): live figures read from Firestore when the
 * page opens - aggregation count / sum for totals, and the documents of the
 * chosen period for flows. Each report is a table (title, columns, rows)
 * the page shows and exports to CSV.
 *
 * Only equality filters are aggregated (no composite index needed); period
 * reports read the period's documents by one date field and filter the rest
 * in PHP. An admin tied to regions sees their regions' customers and
 * currencies only.
 */
class Reports
{
    public const SECTIONS = ['users', 'money', 'aum', 'contributions', 'activity', 'loans', 'insurance', 'payouts'];
    private const MAX_ROWS = 20000;

    private $db;
    private $regions;
    private $from;
    private $to;
    public $truncated = [];

    public function __construct(Firestore $db, \DateTimeInterface $from, \DateTimeInterface $to)
    {
        $this->db = $db;
        $this->regions = new Regions($db);
        $this->from = \DateTimeImmutable::createFromInterface($from);
        $this->to = \DateTimeImmutable::createFromInterface($to);
    }

    /** The currencies of the finance regions this admin may see. */
    public function currencies()
    {
        return $this->regions->financeCurrencies();
    }

    /** The documents of a period by one date field (paged, at most MAX_ROWS). */
    private function period($collection, $field)
    {
        $rows = [];
        $seen = [];
        $after = null;
        while (count($rows) < self::MAX_ROWS) {
            $page = $this->db->query($collection, [[$field, '>=', $this->from], [$field, '<', $this->to]], [[$field, 'asc']], 1000, null, $after);
            $new = 0;
            foreach ($page as $r) {
                if (!isset($seen[$r['id']])) {
                    $seen[$r['id']] = true;
                    $rows[] = $r;
                    $new++;
                }
            }
            if ($new === 0 || count($page) < 1000) {
                break;
            }
            $after = [end($page)['data'][$field]];
        }
        if (count($rows) >= self::MAX_ROWS) {
            $this->truncated[] = $collection;
        }
        return $rows;
    }

    /**
     * Σ a number field over the documents matching equality filters, read and
     * added here: a sum aggregation with a filter needs a composite index
     * (filter field + summed field) the project does not have.
     */
    private function total($collection, $field, array $where)
    {
        $rows = $this->db->query($collection, $where, [], self::MAX_ROWS);
        if (count($rows) >= self::MAX_ROWS) {
            $this->truncated[] = $collection;
        }
        $sum = 0;
        foreach ($rows as $r) {
            $sum += (int)($r['data'][$field] ?? 0);
        }
        return $sum;
    }

    private function cur($code)
    {
        return in_array($code, $this->currencies(), true);
    }

    private static function fmt($minor, $cur)
    {
        return Money::format((int)$minor, $cur);
    }

    public function build($section)
    {
        if (!in_array($section, self::SECTIONS, true)) {
            throw new FinanceException('invalid', 'Unknown report.');
        }
        return $this->$section();
    }

    /* ============================================================ reports */

    public function users()
    {
        // Only filters Firestore serves without a composite index: equalities
        // and array-contains (merged); the restricted ones are read and
        // counted here. An admin tied to regions gets the sum of theirs.
        $allowed = Regions::allowedIds();
        $count = function (array $extra) use ($allowed) {
            if (!$allowed) {
                return $this->db->count('users', array_merge([['role', '==', 'customer']], $extra));
            }
            $n = 0;
            foreach ($allowed as $rid) {
                $c = $this->db->count('users', array_merge([['role', '==', 'customer'], ['regionIds', 'array-contains', $rid]], $extra));
                if ($c === null) {
                    return null;
                }
                $n += $c;
            }
            return $n;
        };
        $mine = function (array $u) use ($allowed) {
            return ($u['role'] ?? '') === 'customer' && (!$allowed || array_intersect((array)($u['regionIds'] ?? []), $allowed));
        };
        $new = count(array_filter($this->period('users', 'createdAt'), function ($u) use ($mine) { return $mine($u['data']); }));
        $restricted = count(array_filter($this->db->query('users', [['financeRestriction', '!=', null]], [], 5000), function ($u) use ($mine) { return $mine($u['data']); }));
        $rows = [
            [__('Customers'), $count([])],
            [__('Identity verified (KYC approved)'), $count([['kycStatus', '==', 'approved']])],   // null (refused) shows "—"
            [__('Restricted in Finance'), $restricted],
            [__('New in the period'), $new],
        ];
        foreach ($this->regions->visible(true) as $r) {
            $rows[] = [__('Customers in') . ' ' . ($r['name'] ?? $r['id']), $this->db->count('users', [['role', '==', 'customer'], ['regionIds', 'array-contains', $r['id']]])];
        }
        return ['title' => __('Users'), 'columns' => [__('Figure'), __('Number')], 'rows' => $rows,
            'note' => __('Totals now; "new" counts the customers created in the period. A customer in two regions counts in each.')];
    }

    public function money()
    {
        $rows = [];
        foreach ($this->currencies() as $cur) {
            $line = [$cur];
            foreach (['wallet', 'savings_goal', 'tontine_pot', 'loan'] as $kind) {
                $line[] = self::fmt($this->total('financial_accounts', 'balance', [['kind', '==', $kind], ['currency', '==', $cur]]), $cur);
            }
            $platform = $this->db->get('financial_accounts/' . Ledger::platformId($cur));
            $line[] = $platform ? self::fmt((int)($platform['data']['balance'] ?? 0), $cur) : '—';
            $rows[] = $line;
        }
        return ['title' => __('Money held'), 'columns' => [__('Currency'), __('Customer wallets'), __('Savings goals'), __('Tontine pots'), __('Member loans'), __('Platform account')],
            'rows' => $rows, 'note' => __('Balances now. The platform account is Spideli\'s position: negative when it has paid out more than it received.')];
    }

    public function aum()
    {
        $rows = [];
        foreach ($this->db->query('investment_funds', [], [], 500) as $f) {
            $cur = $f['data']['currency'] ?? '';
            if (!$this->cur($cur)) {
                continue;
            }
            $rows[] = [($f['data']['name'] ?? $f['id']) . (!empty($f['data']['demo']) ? ' (demo)' : ''), $cur,
                $this->db->count('investment_holdings', [['fundId', '==', $f['id']]]),
                self::fmt($this->total('investment_holdings', 'investedAmount', [['fundId', '==', $f['id']]]), $cur),
                self::fmt($this->total('investment_holdings', 'currentValue', [['fundId', '==', $f['id']]]), $cur)];
        }
        return ['title' => __('Assets under management'), 'columns' => [__('Fund'), __('Currency'), __('Holders'), __('Invested'), __('Value now')], 'rows' => $rows,
            'note' => __('The holdings at their last valuation.')];
    }

    public function contributions()
    {
        $sum = [];
        foreach ([['savings_contributions', 'succeeded', __('Collective savings')], ['tontine_contributions', 'paid', __('Tontines')]] as [$col, $status, $label]) {
            foreach ($this->period($col, 'createdAt') as $c) {
                $cur = $c['data']['currency'] ?? '';
                if (($c['data']['status'] ?? '') === $status && $this->cur($cur) && empty($c['data']['demo'])) {
                    $sum[$label][$cur]['n'] = ($sum[$label][$cur]['n'] ?? 0) + 1;
                    $sum[$label][$cur]['amount'] = ($sum[$label][$cur]['amount'] ?? 0) + (int)($c['data']['amount'] ?? 0);
                }
            }
        }
        $rows = [];
        foreach ($sum as $label => $byCur) {
            foreach ($byCur as $cur => $v) {
                $rows[] = [$label, $cur, $v['n'], self::fmt($v['amount'], $cur)];
            }
        }
        return ['title' => __('Contributions'), 'columns' => [__('Kind'), __('Currency'), __('Number'), __('Amount')], 'rows' => $rows,
            'note' => __('Savings contributions that succeeded and tontine contributions paid in the period.')];
    }

    public function activity()
    {
        $by = [];
        foreach ($this->period('ledger_transactions', 'createdAt') as $tx) {
            $d = $tx['data'];
            $cur = $d['currency'] ?? '';
            if (!$this->cur($cur)) {
                continue;
            }
            $k = ($d['type'] ?? '?') . '|' . $cur;
            $by[$k]['n'] = ($by[$k]['n'] ?? 0) + 1;
            $by[$k]['amount'] = ($by[$k]['amount'] ?? 0) + (int)($d['lines'][0]['amount'] ?? 0);
            $by[$k]['reversed'] = ($by[$k]['reversed'] ?? 0) + (($d['status'] ?? '') === 'reversed' ? 1 : 0);
        }
        ksort($by);
        $rows = [];
        foreach ($by as $k => $v) {
            [$type, $cur] = explode('|', $k);
            $rows[] = [$type, $cur, $v['n'], self::fmt($v['amount'], $cur), $v['reversed']];
        }
        return ['title' => __('Activity by posting type'), 'columns' => [__('Type'), __('Currency'), __('Postings'), __('Amount'), __('Reversed since')], 'rows' => $rows,
            'note' => __('Every ledger posting of the period, by type.')];
    }

    public function loans()
    {
        $rows = [];
        foreach (['submitted', 'under_review', 'voting', 'funding', 'agreement_pending', 'active', 'overdue', 'repaid', 'defaulted', 'rejected', 'cancelled'] as $status) {
            $line = [$status, $this->db->count('loan_applications', [['status', '==', $status]])];
            foreach ($this->currencies() as $cur) {
                $line[] = self::fmt($this->total('loan_applications', 'amount', [['status', '==', $status], ['currency', '==', $cur]]), $cur);
            }
            $rows[] = $line;
        }
        return ['title' => __('Member loans'), 'columns' => array_merge([__('Status'), __('Number')], array_map(function ($c) { return __('Amount') . ' ' . $c; }, $this->currencies())),
            'rows' => $rows, 'note' => __('Every loan by its status now.')];
    }

    public function insurance()
    {
        $rows = [];
        foreach (['pending_activation', 'active', 'renewal_due', 'lapsed', 'cancellation_requested', 'cancelled', 'expired', 'rejected'] as $s) {
            $rows[] = [__('Policies') . ': ' . $s, $this->db->count('insurance_policies', [['status', '==', $s]]), ''];
        }
        $premiums = [];
        foreach ($this->period('insurance_premiums', 'paidAt') as $p) {
            if (($p['data']['status'] ?? '') === 'paid' && empty($p['data']['refundedAt']) && $this->cur($p['data']['currency'] ?? '')) {
                $premiums[$p['data']['currency']] = ($premiums[$p['data']['currency']] ?? 0) + (int)($p['data']['amount'] ?? 0);
            }
        }
        $claims = [];
        foreach ($this->period('insurance_claims', 'paidAt') as $c) {
            if (($c['data']['status'] ?? '') === 'paid' && $this->cur($c['data']['currency'] ?? '')) {
                $claims[$c['data']['currency']] = ($claims[$c['data']['currency']] ?? 0) + (int)($c['data']['paidAmount'] ?? 0);
            }
        }
        foreach ($this->currencies() as $cur) {
            $in = $premiums[$cur] ?? 0;
            $out = $claims[$cur] ?? 0;
            $rows[] = [__('Premiums collected') . " ($cur)", self::fmt($in, $cur), ''];
            $rows[] = [__('Claims paid') . " ($cur)", self::fmt($out, $cur), $in > 0 ? __('Loss ratio') . ' ' . round($out * 100 / $in, 1) . '%' : ''];
        }
        return ['title' => __('Insurance'), 'columns' => [__('Figure'), __('Value'), __('Note')], 'rows' => $rows,
            'note' => __('Policies by status now; premiums and claims paid in the period (refunded premiums excluded).')];
    }

    public function payouts()
    {
        $rows = [];
        $now = new \DateTimeImmutable();
        foreach ([
            [__('Withdrawals'), 'wallet_operations', ['pending'], function ($d) { return ($d['type'] ?? '') === 'withdrawal'; }],
            [__('External transfers'), 'money_transfers', ['processing'], function ($d) { return ($d['channel'] ?? 'internal') !== 'internal'; }],
            [__('Bill payments'), 'utility_payment_requests', ['processing'], function ($d) { return true; }],
        ] as [$label, $col, $waitingStatus, $keep]) {
            $waiting = array_values(array_filter($this->db->query($col, [['status', 'in', $waitingStatus]], [], 2000), function ($r) use ($keep) { return $keep($r['data']) && empty($r['data']['demo']); }));
            $oldest = null;
            foreach ($waiting as $w) {
                $c = $w['data']['createdAt'] ?? null;
                if ($c instanceof \DateTimeInterface && (!$oldest || $c < $oldest)) {
                    $oldest = $c;
                }
            }
            $hours = [];
            foreach ($this->period($col, 'completedAt') as $r) {
                $c = $r['data']['createdAt'] ?? null;
                $done = $r['data']['completedAt'] ?? null;
                if ($keep($r['data']) && ($r['data']['status'] ?? '') === 'completed' && $c instanceof \DateTimeInterface && $done instanceof \DateTimeInterface) {
                    $hours[] = ($done->getTimestamp() - $c->getTimestamp()) / 3600;
                }
            }
            sort($hours);
            $rows[] = [$label, count($waiting), $oldest ? round(($now->getTimestamp() - $oldest->getTimestamp()) / 3600, 1) . ' h' : '—', count($hours),
                $hours ? round(array_sum($hours) / count($hours), 1) . ' h' : '—', $hours ? round($hours[(int)floor((count($hours) - 1) / 2)], 1) . ' h' : '—', $hours ? round(end($hours), 1) . ' h' : '—'];
        }
        return ['title' => __('Payout queues and processing times'), 'columns' => [__('Queue'), __('Waiting now'), __('Oldest waiting'), __('Completed in the period'), __('Average time'), __('Median time'), __('Longest time')],
            'rows' => $rows, 'note' => __('Time from the request to its completion.')];
    }

    /** One report as CSV text (UTF-8 with a byte-order mark, for Excel). */
    public static function csv(array $report)
    {
        $f = fopen('php://temp', 'r+');
        fwrite($f, "\xEF\xBB\xBF");
        fputcsv($f, $report['columns']);
        foreach ($report['rows'] as $r) {
            fputcsv($f, array_map(function ($v) { return $v === null ? '' : (string)$v; }, $r));
        }
        rewind($f);
        return stream_get_contents($f);
    }
}
