<?php

namespace App\Http\Controllers;

use App\Finance\Audit;
use App\Finance\AuthAccounts;
use App\Finance\CreditScores;
use App\Finance\Desk;
use App\Finance\FinanceException;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\Locks;
use App\Finance\Money;
use App\Finance\Notifier;
use App\Finance\Regions;
use App\Finance\Storage;
use Illuminate\Http\Request;

/**
 * Spideli Finance, admin Phase B (SPIDELI_FINANCE_ADMIN_WEB_SPEC.md 6.3 - 6.5):
 * Finance customers and restrictions, the KYC queue and review, gateway
 * receipts, and the payout queues (wallet withdrawals, external transfers,
 * bill payments). Decisions are in App\Finance\Desk; this controller reads,
 * scopes to the admin's regions, locks queue items and notifies customers.
 */
class FinanceDeskController extends Controller
{
    /** Queue kinds: collection, the waiting query, the route label. */
    private const QUEUES = [
        'withdrawals' => ['wallet_operations', [['type', '==', 'withdrawal'], ['status', '==', 'pending']]],
        'transfers' => ['money_transfers', [['status', '==', 'processing']]],
        'bills' => ['utility_payment_requests', [['status', '==', 'processing']]],
        'receipts' => ['payment_receipts', [['verified', '==', false]]],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    private function db()
    {
        return app(Firestore::class);
    }

    private function can($route)
    {
        return in_array($route, json_decode(session('user_permissions') ?? '[]', true) ?: [], true);
    }

    private function failure(\Throwable $e)
    {
        if ($e instanceof FinanceException) {
            return __($e->getMessage());
        }
        report($e);
        return $e instanceof FirestoreError
            ? 'Firebase refused the request (' . $e->status . '). Nothing was changed.'
            : 'Something went wrong. Nothing was changed.';
    }

    /** Sorts newest (or oldest) first on a timestamp field. */
    private static function sortBy(array $rows, $field, $oldestFirst = false)
    {
        usort($rows, function ($a, $b) use ($field, $oldestFirst) {
            $x = $a['data'][$field] ?? null;
            $y = $b['data'][$field] ?? null;
            $x = $x instanceof \DateTimeInterface ? $x->getTimestamp() : 0;
            $y = $y instanceof \DateTimeInterface ? $y->getTimestamp() : 0;
            return $oldestFirst ? $x <=> $y : $y <=> $x;
        });
        return $rows;
    }

    /**
     * Region scope for a region-bound admin: a record is visible when its
     * regionId (written by the app once it applies the region spec) or else
     * its customer's regionIds meet the admin's regions. Unbound admins see all.
     */
    private function scope(array $rows, $userField = 'userId')
    {
        $allowed = Regions::allowedIds();
        if (!$allowed) {
            return $rows;
        }
        $uids = array_values(array_unique(array_filter(array_map(function ($r) use ($userField) {
            return $r['data'][$userField] ?? null;
        }, $rows), 'is_string')));
        $users = $uids ? $this->db()->getMany(array_map(function ($u) { return 'users/' . $u; }, $uids)) : [];
        return array_values(array_filter($rows, function ($r) use ($allowed, $users, $userField) {
            if (!empty($r['data']['regionId'])) {
                return in_array($r['data']['regionId'], $allowed, true);
            }
            $u = $users['users/' . ($r['data'][$userField] ?? '')] ?? null;
            return $u && array_intersect((array)($u['data']['regionIds'] ?? []), $allowed);
        }));
    }

    private function names(array $uids)
    {
        $uids = array_values(array_unique(array_filter($uids, 'is_string')));
        $out = [];
        if ($uids) {
            foreach ($this->db()->getMany(array_map(function ($u) { return 'users/' . $u; }, $uids)) as $doc) {
                if ($doc) {
                    $d = $doc['data'];
                    $out[$doc['id']] = trim(trim(($d['firstName'] ?? '') . ' ' . ($d['lastName'] ?? '')) . ' ' . (!empty($d['email']) ? '(' . $d['email'] . ')' : ''));
                }
            }
        }
        return $out;
    }

    private function notify($uid, $type, array $values, array $data = [])
    {
        return (new Notifier($this->db()))->send($uid, $type, $values, $data);
    }

    /* ========================================================= customers */

    /**
     * Customers whose first or last name starts with the search (Firestore
     * has no text search: a prefix range per name field, tried as typed, in
     * lower case and capitalised). "Awa Te" matches first name Awa* and last
     * name Te*.
     */
    private static function byName(Firestore $db, $q)
    {
        $words = preg_split('/\s+/', trim($q));
        $first = $words[0];
        $rest = strtolower(trim(implode(' ', array_slice($words, 1))));
        $found = [];
        foreach (['firstName', 'lastName'] as $field) {
            foreach (array_unique([$first, strtolower($first), ucfirst(strtolower($first)), strtoupper($first)]) as $v) {
                foreach ($db->query('users', [[$field, '>=', $v], [$field, '<', $v . "\u{f8ff}"]], [], 50) as $r) {
                    $found[$r['id']] = $r;
                }
            }
        }
        if ($rest !== '') {
            $typed = strtolower(implode(' ', $words));
            $found = array_filter($found, function ($r) use ($rest, $typed) {
                $d = $r['data'];
                return strpos(strtolower(trim(($d['firstName'] ?? '') . ' ' . ($d['lastName'] ?? ''))), $typed) === 0
                    || strpos(strtolower((string)($d['lastName'] ?? '')), $rest) === 0;
            });
        }
        return array_values($found);
    }

    public function customers(Request $request)
    {
        $db = $this->db();
        $q = trim((string)$request->input('q'));
        $filter = $request->input('filter', 'verified');
        $rows = [];
        if ($q !== '') {
            if (strpos($q, '@') !== false) {
                $rows = $db->query('users', [['email', '==', $q]], [], 20);
            } elseif (preg_match('/^\+?[0-9 ]{6,}$/', $q)) {
                $p = str_replace(' ', '', $q);
                $rows = $db->query('users', [['phoneNumber', '==', $p]], [], 20) ?: $db->query('users', [['phoneNumber', '==', '+' . ltrim($p, '+')]], [], 20);
            } else {
                $u = $db->get('users/' . $q);
                $rows = $u ? [$u] : self::byName($db, $q);
            }
        } elseif ($filter === 'restricted') {
            $rows = $db->query('users', [['financeRestriction.status', '==', 'restricted']], [], 300);
        } elseif ($filter === 'pending') {
            $rows = $db->query('users', [['kycStatus', 'in', ['submitted', 'under_review', 'additional_info_required']]], [], 300);
        } elseif ($filter === 'suspended') {
            $rows = $db->query('users', [['active', '==', false]], [], 500);   // suspended, and deleted ones kept for Finance
        } elseif ($filter === 'joined') {
            $rows = [];   // the date range below reads them
        } else {
            $filter = 'verified';
            $rows = $db->query('users', [['kycStatus', '==', 'approved']], [], 300);
        }

        /* Joined between two dates (users.createdAt): narrows any list; with
         * "Joined in these dates" it is the list itself. */
        $tz = new \DateTimeZone(config('app.timezone'));
        $from = \DateTimeImmutable::createFromFormat('!Y-m-d', (string)$request->input('from'), $tz) ?: null;
        $to = \DateTimeImmutable::createFromFormat('!Y-m-d', (string)$request->input('to'), $tz) ?: null;
        if ($filter === 'joined' && $q === '') {
            $range = [];
            if ($from) {
                $range[] = ['createdAt', '>=', $from];
            }
            if ($to) {
                $range[] = ['createdAt', '<', $to->modify('+1 day')];
            }
            $rows = $range ? $db->query('users', $range, [], 500) : [];
        }
        if ($from || $to) {
            $rows = array_values(array_filter($rows, function ($r) use ($from, $to) {
                $at = $r['data']['createdAt'] ?? null;
                return $at instanceof \DateTimeInterface && (!$from || $at >= $from) && (!$to || $at < $to->modify('+1 day'));
            }));
        }
        $rows = array_values(array_filter($rows, function ($r) { return ($r['data']['role'] ?? 'customer') === 'customer'; }));
        $region = (string)$request->input('region');
        $regions = new Regions($db);
        if ($region !== '') {
            $rows = array_values(array_filter($rows, function ($r) use ($region) { return in_array($region, (array)($r['data']['regionIds'] ?? []), true); }));
        }
        $allowed = Regions::allowedIds();
        if ($allowed) {
            $rows = array_values(array_filter($rows, function ($r) use ($allowed) { return (bool)array_intersect((array)($r['data']['regionIds'] ?? []), $allowed); }));
        }
        return view('finance.customers.index', [
            'rows' => $rows, 'q' => $q, 'filter' => $filter, 'region' => $region,
            'from' => $from ? $from->format('Y-m-d') : '', 'to' => $to ? $to->format('Y-m-d') : '',
            'canEditCustomer' => $this->can('users.edit'),
            'regions' => $regions->visible(false), 'regionNames' => array_map(function ($r) { return $r['name'] ?? ''; }, $regions->all()),
        ]);
    }

    public function customer($uid)
    {
        $db = $this->db();
        $user = $db->get('users/' . $uid);
        abort_unless($user, 404);
        abort_unless($this->scope([$user], 'id') || !Regions::allowedIds(), 404);
        $regions = new Regions($db);

        $ledger = self::sortBy($db->query('ledger_transactions', [['userIds', 'array-contains', $uid]], [], 200), 'createdAt');
        $kyc = self::sortBy($db->query('kyc_applications', [['userId', '==', $uid]]), 'createdAt');
        $transfers = array_slice(self::sortBy($db->query('money_transfers', [['participantIds', 'array-contains', $uid]], [], 100), 'createdAt'), 0, 30);
        $bills = array_slice(self::sortBy($db->query('utility_payment_requests', [['userId', '==', $uid]], [], 100), 'createdAt'), 0, 30);
        $operations = array_slice(self::sortBy($db->query('wallet_operations', [['userId', '==', $uid]], [], 100), 'createdAt'), 0, 30);

        /* The customer's other Finance records (spec 6.3), each shown only to
         * admins who may open that module; a read that fails leaves its
         * section empty with a note instead of breaking the page. */
        $failed = [];
        $read = function ($label, $collection, array $where, $limit = 100) use ($db, &$failed) {
            try {
                return $db->query($collection, $where, [], $limit);
            } catch (\Throwable $e) {
                report($e);
                $failed[] = $label;
                return [];
            }
        };
        $perm = ['groups' => $this->can('finance.groups'), 'investments' => $this->can('finance.investments'),
                 'insurance' => $this->can('finance.insurance'), 'financing' => $this->can('finance.financing'),
                 'ledger' => $this->can('finance.ledger'), 'audit' => $this->can('finance.audit')];
        $loans = [];
        if ($perm['groups']) {
            foreach ($read(__('Loans'), 'loan_applications', [['borrowerId', '==', $uid]]) as $l) {
                $loans[$l['id']] = $l + ['role' => 'borrower'];
            }
            foreach ($read(__('Loans'), 'loan_applications', [['lenderIds', 'array-contains', $uid]]) as $l) {
                $loans[$l['id']] = $loans[$l['id']] ?? ($l + ['role' => 'lender']);
            }
        }
        $records = [
            'goals' => $perm['groups'] ? self::sortBy($read(__('Savings goals'), 'savings_goals', [['memberIds', 'array-contains', $uid]]), 'createdAt') : null,
            'tontines' => $perm['groups'] ? self::sortBy($read(__('Tontines'), 'tontines', [['memberIds', 'array-contains', $uid]]), 'createdAt') : null,
            'loans' => $perm['groups'] ? self::sortBy(array_values($loans), 'createdAt') : null,
            'holdings' => $perm['investments'] ? $read(__('Investments'), 'investment_holdings', [['userId', '==', $uid]]) : null,
            'policies' => $perm['insurance'] ? self::sortBy($read(__('Insurance policies'), 'insurance_policies', [['userId', '==', $uid]]), 'createdAt') : null,
            'claims' => $perm['insurance'] ? self::sortBy($read(__('Insurance claims'), 'insurance_claims', [['userId', '==', $uid]]), 'createdAt') : null,
            'financing' => $perm['financing'] ? self::sortBy($read(__('Project financing'), 'financing_applications', [['ownerId', '==', $uid]]), 'createdAt') : null,
            'cards' => self::sortBy($read(__('Virtual cards'), 'virtual_cards', [['userId', '==', $uid]]), 'createdAt'),
        ];

        /* Audit trail: the panel's entries about the customer or one of their
         * records (admin_audit_logs.docId), newest first. */
        $audit = null;
        if ($perm['audit']) {
            $ids = [$uid];
            foreach (array_merge($kyc, $transfers, $bills, $operations, ...array_values(array_filter($records, 'is_array'))) as $r) {
                $ids[] = $r['id'];
            }
            $audit = [];
            foreach (array_chunk(array_values(array_unique($ids)), 30) as $chunk) {
                $audit = array_merge($audit, $read(__('Audit trail'), 'admin_audit_logs', [['docId', 'in', $chunk]], 200));
            }
            $audit = array_slice(self::sortBy($audit, 'at'), 0, 50);
        }

        return view('finance.customers.show', $records + [
            'uid' => $uid,
            'u' => $user['data'],
            'regionNames' => array_map(function ($r) { return $r['name'] ?? ''; }, $regions->all()),
            'wallets' => $db->query('financial_accounts', [['userId', '==', $uid]]),
            'kyc' => $kyc,
            'ledger' => array_slice($ledger, 0, 50),
            'transfers' => $transfers,
            'bills' => $bills,
            'operations' => $operations,
            'auth' => (new AuthAccounts($db))->lookup($uid),
            'creditScore' => ($cs = $db->get('credit_scores/' . $uid)) ? $cs['data'] : null,
            'audit' => $audit,
            'failed' => array_values(array_unique($failed)),
            'perm' => $perm,
            'scopes' => Desk::SCOPES,
            'canRestrict' => $this->can('finance.customers.restrict'),
            'canResetTwoStep' => $this->can('finance.customers.two-step'),
            'tickets' => $this->can('finance.tickets') ? $db->query('support_tickets', [['userId', '==', $uid]], [], 100) : null,
            'canTicket' => $this->can('finance.tickets.manage'),
        ]);
    }

    /**
     * Reset a customer's two-step verification (spec 6.3): after the admin
     * checked who is asking (a lost phone), remove every second factor in
     * Firebase Authentication. The reason - how the identity was checked - is
     * required and kept in the audit log with the factors removed (masked).
     */
    public function resetTwoStep(Request $request, $uid)
    {
        $db = $this->db();
        $user = $db->get('users/' . $uid);
        abort_unless($user && ($user['data']['role'] ?? 'customer') === 'customer', 404);
        abort_unless($this->scope([$user], 'id') || !Regions::allowedIds(), 404);
        $reason = trim((string)$request->input('reason'));
        if ($reason === '') {
            return back()->with('error', __('Write how the customer\'s identity was checked.'));
        }
        try {
            $auth = new AuthAccounts($db);
            $account = $auth->lookup($uid);
            if (!is_array($account)) {
                return back()->with('error', $account === 'missing' ? __('This customer has no sign-in account.') : __('Firebase could not be reached. Nothing was changed.'));
            }
            if (!$account['twoStep']) {
                return back()->with('error', __('Two-step verification is already off for this customer.'));
            }
            $auth->resetTwoStep($uid);
            $removed = array_map(function ($f) { return trim($f['kind'] . ' ' . $f['hint']); }, $account['twoStep']);
            $db->batch([Audit::write($db, 'customer.two_step_reset', 'users', $uid, ['twoStep' => $removed], ['twoStep' => []], $reason)]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', __('Firebase refused the change. Nothing was changed.'));
        }
        return back()->with('success', __('Two-step verification removed: the customer can sign in without the code and turn it on again in the app.'));
    }

    /** Recalculates the customer's credit score now (it is also recalculated every night). */
    public function creditScore($uid)
    {
        $db = $this->db();
        $user = $db->get('users/' . $uid);
        abort_unless($user && ($user['data']['role'] ?? 'customer') === 'customer', 404);
        abort_unless($this->scope([$user], 'id') || !Regions::allowedIds(), 404);
        $r = (new CreditScores($db))->refresh($uid);
        return $r ? back()->with('success', __('Credit score recalculated:') . ' ' . $r['score'] . ' (' . __($r['band']) . ').')
                  : back()->with('error', __('The score could not be calculated now. Try again.'));
    }

    public function restrict(Request $request, $uid)
    {
        try {
            (new Desk($this->db()))->restrict($uid, (array)$request->input('scopes', []), trim((string)$request->input('reason')));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
        return back()->with('success', __('Restriction saved.'));
    }

    public function lift(Request $request, $uid)
    {
        try {
            (new Desk($this->db()))->lift($uid, trim((string)$request->input('reason')));
        } catch (\Throwable $e) {
            return back()->with('error', $this->failure($e));
        }
        return back()->with('success', __('Restriction lifted.'));
    }

    /* =============================================================== KYC */

    public function kycQueue()
    {
        $db = $this->db();
        $rows = $this->scope($db->query('kyc_applications', [['status', 'in', ['submitted', 'under_review']]], [], 300));
        $rows = self::sortBy($rows, 'submittedAt', true);
        return view('finance.kyc.index', [
            'rows' => $rows,
            'names' => $this->names(array_map(function ($r) { return $r['data']['userId'] ?? null; }, $rows)),
            'locks' => Locks::held(array_map(function ($r) { return 'kyc_applications/' . $r['id']; }, $rows)),
        ]);
    }

    /** The KYC settings that apply to an application: its region's, else the customer's first Finance region, else the app's. */
    private function kycSettings(array $app, array $user)
    {
        $db = $this->db();
        $regions = new Regions($db);
        $candidates = array_values(array_filter(array_merge([(string)($app['regionId'] ?? '')], (array)($user['regionIds'] ?? []))));
        foreach ($candidates as $rid) {
            if (!empty($regions->all()[$rid]['financeEnabled'])) {
                $doc = $db->get('finance_settings/' . Regions::docId($rid, 'kyc'));
                if ($doc) {
                    return [$doc['data'], $regions->name($rid)];
                }
            }
        }
        $doc = $db->get('platform_settings/kyc');
        return [$doc ? $doc['data'] : [], __('the app\'s settings')];
    }

    public function kycShow($id)
    {
        $db = $this->db();
        $app = $db->get('kyc_applications/' . $id);
        abort_unless($app && $this->scope([$app]), 404);
        $a = $app['data'];
        $user = $db->get('users/' . ($a['userId'] ?? '')) ?: ['data' => []];
        [$kyc, $kycFrom] = $this->kycSettings($a, $user['data']);

        $docs = array_values(array_filter($db->query('kyc_documents', [['applicationId', '==', $id]]), function ($d) {
            return ($d['data']['status'] ?? '') !== 'superseded';
        }));
        $links = [];
        foreach ($docs as $d) {
            if (!empty($d['data']['storagePath'])) {
                try {
                    $links[$d['id']] = Storage::signedUrl($d['data']['storagePath'], 300);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
        $open = in_array($a['status'] ?? '', ['submitted', 'under_review'], true);
        $lockedBy = $open && $this->can('finance.kyc.decide') ? Locks::take('kyc_applications/' . $id) : null;

        return view('finance.kyc.show', [
            'id' => $id, 'a' => $a, 'user' => $user['data'], 'docs' => $docs, 'links' => $links,
            'reviews' => self::sortBy($db->query('kyc_reviews', [['applicationId', '==', $id]]), 'at', true),
            'missing' => Desk::kycMissing($kyc, $a['level'] ?? '', array_column($docs, 'data')),
            'kycFrom' => $kycFrom, 'open' => $open, 'lockedBy' => $lockedBy,
            'canDecide' => $this->can('finance.kyc.decide') && $lockedBy === null,
        ]);
    }

    public function kycAction(Request $request, $id, $action)
    {
        $db = $this->db();
        $desk = new Desk($db);
        $reason = trim((string)$request->input('reason'));
        try {
            Locks::assertMine('kyc_applications/' . $id);
            switch ($action) {
                case 'start':
                    $desk->kycStart($id);
                    $msg = __('Review started.');
                    break;
                case 'info':
                    $a = $desk->kycRequestInfo($id, $reason);
                    $msg = __('More information requested.') . ' ' . __('Notification:') . ' ' . $this->notify($a['userId'], 'kyc_additional_info_required', ['reason' => $reason], ['applicationId' => $id]);
                    Locks::release('kyc_applications/' . $id);
                    break;
                case 'reject':
                    $a = $desk->kycReject($id, $reason);
                    $msg = __('Application rejected.') . ' ' . __('Notification:') . ' ' . $this->notify($a['userId'], 'kyc_rejected', ['reason' => $reason], ['applicationId' => $id]);
                    Locks::release('kyc_applications/' . $id);
                    break;
                case 'approve':
                    $app = $db->get('kyc_applications/' . $id);
                    $user = $db->get('users/' . ($app['data']['userId'] ?? '')) ?: ['data' => []];
                    [$kyc] = $this->kycSettings($app['data'] ?? [], $user['data']);
                    $a = $desk->kycApprove($id, $kyc);
                    $msg = __('Identity verified.') . ' ' . __('Notification:') . ' ' . $this->notify($a['userId'], 'kyc_approved', ['level' => $a['level']], ['applicationId' => $id]);
                    Locks::release('kyc_applications/' . $id);
                    break;
                default:
                    abort(404);
            }
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
        return back()->with('success', $msg);
    }

    public function kycDocument(Request $request, $id, $docId)
    {
        try {
            Locks::assertMine('kyc_applications/' . $id);
            (new Desk($this->db()))->kycDocument($docId, $request->input('decision') === 'accept', trim((string)$request->input('note')));
        } catch (\Throwable $e) {
            return back()->with('error', $this->failure($e));
        }
        return back()->with('success', __('Document updated.'));
    }

    /* ========================================================== payments */

    public function payments(Request $request)
    {
        $db = $this->db();
        $kind = $request->input('kind', 'withdrawals');
        abort_unless(isset(self::QUEUES[$kind]), 404);
        $counts = [];
        foreach (self::QUEUES as $k => [$collection, $where]) {
            $counts[$k] = $db->count($collection, $where);
        }
        [$collection, $where] = self::QUEUES[$kind];
        $userField = $kind === 'transfers' ? 'senderId' : 'userId';
        $rows = $this->scope($db->query($collection, $where, [], 300), $userField);
        if ($kind === 'receipts') {
            $rows = array_values(array_filter($rows, function ($r) { return empty($r['data']['disputed']); }));
        }
        $rows = self::sortBy($rows, 'createdAt', true);
        return view('finance.payments.index', [
            'kind' => $kind, 'rows' => $rows, 'counts' => $counts,
            'names' => $this->names(array_map(function ($r) use ($userField) { return $r['data'][$userField] ?? null; }, $rows)),
            'locks' => Locks::held(array_map(function ($r) use ($collection) { return $collection . '/' . $r['id']; }, $rows)),
        ]);
    }

    public function paymentShow($kind, $id)
    {
        abort_unless(isset(self::QUEUES[$kind]), 404);
        $db = $this->db();
        $collection = self::QUEUES[$kind][0];
        $doc = $db->get($collection . '/' . $id);
        $userField = $kind === 'transfers' ? 'senderId' : 'userId';
        abort_unless($doc && $this->scope([$doc], $userField), 404);
        $d = $doc['data'];
        $open = $kind === 'receipts' ? (($d['verified'] ?? false) !== true && empty($d['disputed'])) : in_array($d['status'] ?? '', ['pending', 'processing'], true);
        $lockedBy = $open && $this->can('finance.payments.process') ? Locks::take($collection . '/' . $id) : null;
        $tx = !empty($d['txId']) ? $db->get('ledger_transactions/' . $d['txId']) : null;
        return view('finance.payments.show', [
            'kind' => $kind, 'id' => $id, 'd' => $d, 'tx' => $tx, 'open' => $open, 'lockedBy' => $lockedBy,
            'customer' => $this->names([$d[$userField] ?? null])[$d[$userField] ?? ''] ?? '', 'uid' => $d[$userField] ?? '',
            'canProcess' => $this->can('finance.payments.process') && $lockedBy === null,
        ]);
    }

    public function paymentAction(Request $request, $kind, $id, $action)
    {
        abort_unless(isset(self::QUEUES[$kind]), 404);
        $collection = self::QUEUES[$kind][0];
        $desk = new Desk($this->db());
        $reason = trim((string)$request->input('reason'));
        $note = '';
        try {
            Locks::assertMine($collection . '/' . $id);
            $done = true;
            switch ("$kind.$action") {
                case 'withdrawals.complete':
                    $d = $desk->withdrawalComplete($id, $request->input('payoutReference'));
                    $note = $this->notify($d['userId'] ?? '', 'wallet_withdrawal_completed', ['amount' => Money::format((int)($d['amount'] ?? 0), $d['currency'] ?? '')], ['operationId' => $id]);
                    break;
                case 'withdrawals.fail':
                    $d = $desk->withdrawalFail($id, $reason);
                    $note = $this->notify($d['userId'] ?? '', 'wallet_withdrawal_failed', ['amount' => Money::format((int)($d['total'] ?? $d['amount'] ?? 0), $d['currency'] ?? ''), 'reason' => $reason], ['operationId' => $id]);
                    break;
                case 'transfers.start':
                    $desk->transferStart($id);
                    $done = false;
                    break;
                case 'transfers.complete':
                    $d = $desk->transferComplete($id, $request->input('providerReference'), $request->boolean('recipientVerified'));
                    $note = $this->notify($d['senderId'] ?? '', 'transfer_sent', ['amount' => Money::format((int)($d['amount'] ?? 0), $d['currency'] ?? ''), 'recipientName' => $d['recipientName'] ?? ''], ['transferId' => $id]);
                    break;
                case 'transfers.fail':
                    $d = $desk->transferFail($id, $reason);
                    $note = $this->notify($d['senderId'] ?? '', 'transfer_reversed', ['amount' => Money::format((int)($d['amount'] ?? 0), $d['currency'] ?? ''), 'recipientName' => $d['recipientName'] ?? '', 'reason' => $reason], ['transferId' => $id]);
                    break;
                case 'transfers.cancel':
                    $d = $desk->transferCancel($id);
                    $note = $this->notify($d['senderId'] ?? '', 'transfer_reversed', ['amount' => Money::format((int)($d['amount'] ?? 0), $d['currency'] ?? ''), 'recipientName' => $d['recipientName'] ?? '', 'reason' => __('cancelled at your request')], ['transferId' => $id]);
                    break;
                case 'bills.complete':
                    $fields = $request->only(['billerReference', 'customerName', 'token', 'tokenLabel', 'units']);
                    $d = $desk->billComplete($id, $fields);
                    $values = ['amount' => Money::format((int)($d['amount'] ?? 0), $d['currency'] ?? ''), 'billerName' => $d['billerName'] ?? '', 'account' => $d['account'] ?? ''];
                    $note = $this->notify($d['userId'] ?? '', 'bill_paid', $values, ['requestId' => $id]);
                    if (trim((string)($fields['token'] ?? '')) !== '') {
                        $note .= ' / ' . $this->notify($d['userId'] ?? '', 'bill_token', $values + ['token' => trim($fields['token'])], ['requestId' => $id]);
                    }
                    break;
                case 'bills.fail':
                    $d = $desk->billFail($id, $reason);
                    $note = $this->notify($d['userId'] ?? '', 'bill_reversed', ['amount' => Money::format((int)($d['amount'] ?? 0), $d['currency'] ?? ''), 'billerName' => $d['billerName'] ?? '', 'reason' => $reason], ['requestId' => $id]);
                    break;
                case 'receipts.verify':
                    $desk->receiptVerify($id);
                    break;
                case 'receipts.dispute':
                    $r = $desk->receiptDispute($id, $reason);
                    $note = $r['reversed'] ? __('The deposit was reversed.') : __('The wallet no longer holds the money: the receipt is marked disputed with the amount owed and the customer is restricted.');
                    break;
                default:
                    abort(404);
            }
            if ($done) {
                Locks::release($collection . '/' . $id);
            }
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
        $msg = $done ? __('Done.') : __('Started: the sender can no longer cancel. Pay the recipient, then complete it.');
        if ($note !== '') {
            $msg .= ' ' . (in_array($kind, ['receipts'], true) ? $note : __('Notification:') . ' ' . $note);
        }
        return $done ? redirect()->route('finance.payments', ['kind' => $kind])->with('success', $msg) : back()->with('success', $msg);
    }
}
