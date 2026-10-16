<?php

namespace App\Http\Controllers;

use App\Finance\Firestore;
use App\Finance\FourEyes;
use App\Finance\Regions;
use App\Finance\Reports;
use App\Finance\Scope;

/**
 * Spideli Finance home (spec 6.1): what waits for the team and the key
 * figures, each tile linking to its queue. A tile shows only when the
 * admin's role can open that queue; an admin tied to regions counts only
 * their regions' customers. Money figures come from App\Finance\Reports, so
 * the dashboard and the reports always agree.
 */
class FinanceDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function db()
    {
        return app(Firestore::class);
    }

    private static function perms()
    {
        return json_decode(session('user_permissions') ?? '[]', true) ?: [];
    }

    /**
     * Records waiting in a queue: a count aggregation for an unbound admin
     * (null when Firestore refuses it), else the records read and scoped.
     */
    private function waiting($collection, array $where, $userField)
    {
        if (!Regions::allowedIds()) {
            return $this->db()->count($collection, $where);
        }
        return count(Scope::filter($this->db(), $this->db()->query($collection, $where, [], 2000), $userField));
    }

    public function index()
    {
        $p = self::perms();
        abort_unless(array_filter($p, function ($r) { return strpos($r, 'finance.') === 0; }), 403);
        $can = function ($route) use ($p) { return in_array($route, $p, true); };
        $now = new \DateTimeImmutable();
        $tz = new \DateTimeZone(config('app.timezone'));
        $monthStart = new \DateTimeImmutable('first day of this month 00:00', $tz);

        // What waits for the team, by area.
        $queues = [];
        $add = function ($area, $label, $n, $url, $urgent = false) use (&$queues) {
            $queues[$area][] = ['label' => $label, 'n' => $n, 'url' => $url, 'urgent' => $urgent];
        };
        if ($can('finance.kyc')) {
            $add(__('Customers'), __('Identity checks to review'), $this->waiting('kyc_applications', [['status', 'in', ['submitted', 'under_review']]], 'userId'), route('finance.kyc'), true);
        }
        if ($can('finance.payments')) {
            $add(__('Payments'), __('Withdrawals to pay'), $this->waiting('wallet_operations', [['type', '==', 'withdrawal'], ['status', '==', 'pending']], 'userId'), route('finance.payments', ['kind' => 'withdrawals']), true);
            $add(__('Payments'), __('External transfers to pay'), $this->waiting('money_transfers', [['status', '==', 'processing']], 'senderId'), route('finance.payments', ['kind' => 'transfers']), true);
            $add(__('Payments'), __('Bills to pay'), $this->waiting('utility_payment_requests', [['status', '==', 'processing']], 'userId'), route('finance.payments', ['kind' => 'bills']), true);
            $add(__('Payments'), __('Gateway payments to verify'), $this->waiting('payment_receipts', [['verified', '==', false]], 'userId'), route('finance.payments', ['kind' => 'receipts']));
        }
        if ($can('finance.ledger')) {
            $add(__('Payments'), __('Approvals waiting'), FourEyes::pendingCount(), route('finance.approvals'));
        }
        if ($can('finance.groups')) {
            $add(__('Groups and loans'), __('Savings withdrawals to decide'), $this->waiting('savings_withdrawals', [['status', '==', 'requested']], 'userId'), route('finance.savings'));
            $add(__('Groups and loans'), __('Tontine payouts to approve'), $this->waiting('tontine_payouts', [['status', '==', 'pending_approval']], 'beneficiaryUserId'), route('finance.tontines'));
            $add(__('Groups and loans'), __('Loans to review'), $this->waiting('loan_applications', [['status', 'in', ['submitted', 'under_review']]], 'borrowerId'), route('finance.loans', ['tab' => 'review']));
            $add(__('Groups and loans'), __('Loans active'), $this->waiting('loan_applications', [['status', '==', 'active']], 'borrowerId'), route('finance.loans', ['tab' => 'active']));
            $add(__('Groups and loans'), __('Loans overdue'), $this->waiting('loan_applications', [['status', '==', 'overdue']], 'borrowerId'), route('finance.loans', ['tab' => 'active']), true);
        }
        if ($can('finance.investments')) {
            $add(__('Investments'), __('Subscriptions to execute'), $this->waiting('investment_subscriptions', [['status', '==', 'paid']], 'userId'), route('finance.investments'));
            $add(__('Investments'), __('Redemptions to execute'), $this->waiting('investment_redemptions', [['status', '==', 'submitted']], 'userId'), route('finance.investments'));
            $add(__('Investments'), __('Redemptions to settle'), $this->waiting('investment_redemptions', [['status', '==', 'settling']], 'userId'), route('finance.investments'));
        }
        if ($can('finance.insurance')) {
            $add(__('Insurance'), __('Quotes to price'), $this->waiting('insurance_quotes', [['status', '==', 'requested']], 'userId'), route('finance.insurance', ['tab' => 'quotes']));
            $add(__('Insurance'), __('Policies to activate'), $this->waiting('insurance_policies', [['status', '==', 'pending_activation']], 'userId'), route('finance.insurance', ['tab' => 'activate']));
            $add(__('Insurance'), __('Open claims'), $this->waiting('insurance_claims', [['status', 'in', ['submitted', 'under_review', 'info_requested', 'approved']]], 'userId'), route('finance.insurance', ['tab' => 'claims']));
            $add(__('Insurance'), __('Cancellations to decide'), $this->waiting('insurance_policies', [['status', '==', 'cancellation_requested']], 'userId'), route('finance.insurance', ['tab' => 'cancellations']));
        }
        if ($can('finance.financing')) {
            $add(__('Financing'), __('Applications to review'), $this->waiting('financing_applications', [['status', 'in', ['submitted', 'under_review']]], 'ownerId'), route('finance.financing'));
        }
        if ($can('finance.tickets')) {
            $tickets = Scope::filter($this->db(), $this->db()->query('support_tickets', [['status', 'in', ['open', 'in_progress']]], [], 2000), 'userId');
            $late = count(array_filter($tickets, function ($t) use ($now) { $s = \App\Finance\Support::sla($t['data'], $now); return $s && $s['late']; }));
            $add(__('Support'), __('Open tickets'), count($tickets), route('finance.tickets'));
            $add(__('Support'), __('Tickets late'), $late, route('finance.tickets'), true);
        }

        // Key figures (Reports): money held, assets, this month's contributions, customers.
        $figures = [];
        $reports = new Reports($this->db(), $monthStart, $now->modify('+1 minute'));
        foreach (array_filter(['users' => $can('finance.customers'), 'money' => $can('finance.ledger'), 'aum' => $can('finance.investments'), 'contributions' => $can('finance.groups')]) as $section => $_) {
            try {
                $figures[$section] = $reports->build($section);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        $stale = null;
        if ($can('finance.content')) {
            // The strictest region's convert.maxRateAgeHours (default 48), as on the rates page.
            $regions = new Regions($this->db());
            $maxAge = null;
            foreach ($regions->visible(true) as $r) {
                [$wallet] = $regions->moduleSettings('wallet', [$r['id']]);
                $h = (int)($wallet['convert']['maxRateAgeHours'] ?? 0);
                $maxAge = min($maxAge ?? PHP_INT_MAX, $h > 0 ? $h : 48);
            }
            $maxAge = $maxAge ?? 48;
            $rates = (new \App\Finance\Content($this->db()))->latestRates();
            $stale = ['pairs' => count($rates), 'maxAge' => $maxAge, 'old' => count(array_filter($rates, function ($r) use ($now, $maxAge) {
                return !(($r['observedAt'] ?? null) instanceof \DateTimeInterface) || $r['observedAt'] < $now->modify('-' . $maxAge . ' hours');
            }))];
        }
        return view('finance.dashboard', ['queues' => $queues, 'figures' => $figures, 'stale' => $stale, 'monthStart' => $monthStart,
            'canReports' => $can('finance.reports')]);
    }
}
