<?php

namespace App\Http\Controllers;

use App\Finance\FinanceException;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\Groups;
use App\Finance\Ledger;
use App\Finance\Loans;
use App\Finance\Locks;
use App\Finance\Money;
use App\Finance\Notices;
use App\Finance\Notifier;
use App\Finance\Regions;
use App\Finance\Scope;
use Illuminate\Http\Request;

/**
 * Spideli Finance, admin Phase C (spec 6.7 - 6.9): collective savings,
 * tontines and member loans. Decisions are in App\Finance\Groups and
 * App\Finance\Loans; this controller reads, scopes to the admin's regions,
 * locks the item being worked on and notifies the customers.
 */
class FinanceGroupsController extends Controller
{
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

    private function names(array $uids)
    {
        $uids = array_values(array_unique(array_filter($uids, 'is_string')));
        $out = [];
        if ($uids) {
            foreach ($this->db()->getMany(array_map(function ($u) { return 'users/' . $u; }, $uids)) as $doc) {
                if ($doc) {
                    $out[$doc['id']] = trim(($doc['data']['firstName'] ?? '') . ' ' . ($doc['data']['lastName'] ?? '')) ?: ($doc['data']['email'] ?? $doc['id']);
                }
            }
        }
        return $out;
    }

    private function notify($uid, $type, array $values, array $data = [])
    {
        return (new Notifier($this->db()))->send($uid, $type, $values, $data);
    }

    private function done($msg, $note = '')
    {
        return back()->with('success', trim($msg . ($note !== '' ? ' ' . __('Notification:') . ' ' . $note : '')));
    }

    /* ============================================================= savings */

    public function savings(Request $request)
    {
        $db = $this->db();
        $status = $request->input('status', 'active');
        $rows = $status === 'all' ? $db->query('savings_goals', [], [], 500) : $db->query('savings_goals', [['status', '==', $status]], [], 500);
        $rows = self::sortBy(Scope::filter($db, $rows, 'memberIds'), 'createdAt');
        $requests = Scope::filter($db, $db->query('savings_withdrawals', [['status', '==', 'requested']], [], 300), 'userId');
        $cancellations = Scope::filter($db, $db->query('savings_goals', [['status', '==', 'cancellation_requested']], [], 300), 'memberIds');
        return view('finance.savings.index', [
            'rows' => $rows, 'status' => $status,
            'requests' => self::sortBy($requests, 'createdAt', true), 'cancellations' => $cancellations,
            'names' => $this->names(array_merge(array_map(function ($r) { return $r['data']['ownerId'] ?? null; }, array_merge($rows, $cancellations)),
                array_map(function ($r) { return $r['data']['userId'] ?? null; }, $requests))),
            'goalNames' => array_column(array_map(function ($r) { return ['id' => $r['id'], 'name' => $r['data']['name'] ?? '']; },
                $db->query('savings_goals', [], [], 500)), 'name', 'id'),
        ]);
    }

    public function saving($id)
    {
        $db = $this->db();
        $goal = $db->get('savings_goals/' . $id);
        abort_unless($goal && Scope::allows($db, $goal, 'memberIds'), 404);
        $g = $goal['data'];
        $contributions = self::sortBy($db->query('savings_contributions', [['goalId', '==', $id]], [], 500), 'createdAt');
        $withdrawals = self::sortBy($db->query('savings_withdrawals', [['goalId', '==', $id]], [], 300), 'createdAt');
        $pool = $db->get('financial_accounts/goal_' . $id);
        $members = array_values(array_unique(array_merge((array)($g['memberIds'] ?? []), [$g['ownerId'] ?? ''])));
        $shares = [];
        foreach ($members as $m) {
            $in = array_sum(array_map(function ($c) use ($m) { return ($c['data']['userId'] ?? '') === $m && ($c['data']['status'] ?? '') === 'succeeded' ? (int)$c['data']['amount'] : 0; }, $contributions));
            $out = array_sum(array_map(function ($w) use ($m) { return ($w['data']['userId'] ?? '') === $m && ($w['data']['status'] ?? '') === 'completed' ? (int)$w['data']['amount'] : 0; }, $withdrawals));
            $shares[$m] = ['in' => $in, 'out' => $out, 'share' => max(0, $in - $out)];
        }
        $balance = $pool ? (int)($pool['data']['balance'] ?? 0) : null;
        $actionable = in_array($g['status'] ?? '', ['cancellation_requested', 'active'], true);
        $lockedBy = $actionable && $this->can('finance.groups.manage') ? Locks::take('savings_goals/' . $id) : null;
        return view('finance.savings.show', [
            'id' => $id, 'g' => $g, 'contributions' => $contributions, 'withdrawals' => $withdrawals, 'balance' => $balance,
            'shares' => $shares, 'names' => $this->names(array_merge($members, (array)($g['invitedIds'] ?? []))),
            'preview' => Groups::split(array_map(function ($s) { return $s['share']; }, $shares), (int)($balance ?? 0), $g['ownerId'] ?? ''),
            'lockedBy' => $lockedBy, 'canManage' => $this->can('finance.groups.manage') && $lockedBy === null,
        ]);
    }

    public function savingAction(Request $request, $id, $action)
    {
        $groups = new Groups($this->db());
        $reason = trim((string)$request->input('reason'));
        try {
            Locks::assertMine('savings_goals/' . $id);
            switch ($action) {
                case 'pay-withdrawal':
                    $w = $groups->savingsWithdrawalPay((string)$request->input('withdrawalId'));
                    return $this->done(__('Withdrawal paid.'), $this->notify($w['userId'], 'savings_withdrawal_completed',
                        ['amount' => Money::format((int)$w['amount'], $w['currency']), 'goalName' => $w['goalName']], ['goalId' => $id]));
                case 'refuse-withdrawal':
                    $w = $groups->savingsWithdrawalRefuse((string)$request->input('withdrawalId'), $reason);
                    $goal = $this->db()->get('savings_goals/' . $id);
                    return $this->done(__('Withdrawal refused.'), $this->notify($w['userId'], 'savings_withdrawal_rejected',
                        ['amount' => Money::format((int)$w['amount'], $w['currency'] ?? ''), 'goalName' => $goal['data']['name'] ?? '', 'reason' => $reason], ['goalId' => $id]));
                case 'cancel':
                case 'complete':
                    $paid = $groups->savingsPayOut($id, $action === 'cancel' ? 'cancelled' : 'completed');
                    Locks::release('savings_goals/' . $id);
                    return $this->done(__('The pool was paid out to the members:') . ' ' . count(array_filter($paid)) . '.');
                case 'keep':
                    $groups->savingsKeep($id);
                    Locks::release('savings_goals/' . $id);
                    return $this->done(__('The goal is kept active.'));
            }
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    /* ============================================================ tontines */

    public function tontines(Request $request)
    {
        $db = $this->db();
        $status = $request->input('status', 'active');
        $rows = $status === 'all' ? $db->query('tontines', [], [], 500) : $db->query('tontines', [['status', '==', $status]], [], 500);
        $rows = self::sortBy(Scope::filter($db, $rows, 'memberIds'), 'createdAt');
        $payouts = $db->query('tontine_payouts', [['status', '==', 'pending_approval']], [], 300);
        $disputes = $db->query('tontine_disputes', [['status', 'in', ['open', 'under_review']]], [], 300);
        $tontineNames = [];
        foreach ($db->getMany(array_values(array_unique(array_map(function ($r) { return 'tontines/' . ($r['data']['tontineId'] ?? ''); }, array_merge($payouts, $disputes))))) as $doc) {
            if ($doc) {
                $tontineNames[$doc['id']] = $doc['data'];
            }
        }
        // Only payouts nobody but the panel can approve: the beneficiary is the only admin.
        $payouts = array_values(array_filter($payouts, function ($p) use ($tontineNames) {
            $t = $tontineNames[$p['data']['tontineId'] ?? ''] ?? null;
            return $t && array_values((array)($t['adminIds'] ?? [])) === [$p['data']['beneficiaryUserId'] ?? ''];
        }));
        return view('finance.tontines.index', [
            'rows' => $rows, 'status' => $status,
            'payouts' => Scope::filter($db, $payouts, 'beneficiaryUserId'), 'disputes' => Scope::filter($db, $disputes, 'raisedBy'),
            'tontines' => $tontineNames,
            'names' => $this->names(array_merge(array_map(function ($p) { return $p['data']['beneficiaryUserId'] ?? null; }, $payouts),
                array_map(function ($d) { return $d['data']['raisedBy'] ?? null; }, $disputes))),
        ]);
    }

    public function tontine($id)
    {
        $db = $this->db();
        $tn = $db->get('tontines/' . $id);
        abort_unless($tn && Scope::allows($db, $tn, 'memberIds'), 404);
        $t = $tn['data'];
        $members = self::sortBy($db->query('tontine_members', [['tontineId', '==', $id]], [], 300), 'createdAt', true);
        usort($members, function ($a, $b) { return ((int)($a['data']['payoutPosition'] ?? 99)) <=> ((int)($b['data']['payoutPosition'] ?? 99)); });
        $cycles = $db->query('tontine_cycles', [['tontineId', '==', $id]], [], 300);
        usort($cycles, function ($a, $b) { return ((int)($a['data']['number'] ?? 0)) <=> ((int)($b['data']['number'] ?? 0)); });
        $contribs = $db->query('tontine_contributions', [['tontineId', '==', $id]], [], 2000);
        $payouts = $db->query('tontine_payouts', [['tontineId', '==', $id]], [], 300);
        $disputes = self::sortBy($db->query('tontine_disputes', [['tontineId', '==', $id]], [], 300), 'createdAt');
        $pot = $db->get('financial_accounts/' . ($t['potAccountId'] ?? ('tontine_' . $id)));

        // Audit (spec 6.8): each cycle's collected = Σ its paid contributions; pot = Σ collected − Σ paid out.
        $paidBy = [];
        $sumByCycle = [];
        foreach ($contribs as $c) {
            if (($c['data']['status'] ?? '') === 'paid') {
                $cid = $c['data']['cycleId'] ?? '';
                $paidBy[$cid][$c['data']['userId'] ?? ''] = true;
                $sumByCycle[$cid] = ($sumByCycle[$cid] ?? 0) + (int)($c['data']['amount'] ?? 0);
            }
        }
        $flags = [];
        $collected = 0;
        foreach ($cycles as $c) {
            $want = $sumByCycle[$c['id']] ?? 0;
            $collected += $want;
            if (array_key_exists('collectedAmount', $c['data']) && (int)$c['data']['collectedAmount'] !== $want) {
                $flags[] = __('Cycle') . ' ' . ($c['data']['number'] ?? '?') . ': ' . __('collected says') . ' ' . Money::format((int)$c['data']['collectedAmount'], $t['currency'] ?? '') . ', ' . __('paid contributions add up to') . ' ' . Money::format($want, $t['currency'] ?? '');
            }
        }
        $paidOut = array_sum(array_map(function ($p) { return ($p['data']['status'] ?? '') === 'paid' ? (int)($p['data']['amount'] ?? 0) : 0; }, $payouts));
        $expectedPot = $collected - $paidOut;
        if ($pot && (int)($pot['data']['balance'] ?? 0) !== $expectedPot) {
            $flags[] = __('Pot balance') . ' ' . Money::format((int)$pot['data']['balance'], $t['currency'] ?? '') . ' ≠ ' . __('collected minus paid out') . ' ' . Money::format($expectedPot, $t['currency'] ?? '');
        }
        $pending = array_values(array_filter($payouts, function ($p) use ($t) {
            return ($p['data']['status'] ?? '') === 'pending_approval' && array_values((array)($t['adminIds'] ?? [])) === [$p['data']['beneficiaryUserId'] ?? ''];
        }));
        $actionable = $pending || in_array($t['status'] ?? '', ['active', 'suspended'], true);
        $lockedBy = $actionable && $this->can('finance.groups.manage') ? Locks::take('tontines/' . $id) : null;
        $uids = array_merge(array_map(function ($m) { return $m['data']['userId'] ?? null; }, $members), array_map(function ($d) { return $d['data']['raisedBy'] ?? null; }, $disputes),
            array_map(function ($d) { return $d['data']['againstUserId'] ?? null; }, $disputes));
        return view('finance.tontines.show', [
            'id' => $id, 't' => $t, 'members' => $members, 'cycles' => $cycles, 'paidBy' => $paidBy, 'payouts' => $payouts, 'pending' => $pending,
            'disputes' => $disputes, 'pot' => $pot, 'flags' => $flags, 'names' => $this->names($uids),
            'lockedBy' => $lockedBy, 'canManage' => $this->can('finance.groups.manage') && $lockedBy === null,
        ]);
    }

    public function tontineAction(Request $request, $id, $action)
    {
        $groups = new Groups($this->db());
        $reason = trim((string)$request->input('reason'));
        try {
            Locks::assertMine('tontines/' . $id);
            switch ($action) {
                case 'approve-payout':
                    $p = $groups->tontinePayoutApprove((string)$request->input('payoutId'));
                    return $this->done(__('Payout paid.'), $this->notify($p['beneficiaryUserId'], 'tontine_payout_paid',
                        ['amount' => Money::format((int)$p['amount'], $p['currency']), 'tontineName' => $p['tontineName']], ['tontineId' => $id]));
                case 'suspend':
                case 'resume':
                    $groups->tontineSuspend($id, $action === 'suspend', $reason);
                    return $this->done($action === 'suspend' ? __('Tontine suspended.') : __('Tontine resumed.'));
                case 'dispute':
                    $groups->dispute((string)$request->input('disputeId'), (string)$request->input('decision'), $reason, $request->boolean('restrictMember'));
                    return $this->done(__('Dispute updated.'));
            }
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    /* =============================================================== loans */

    public function loans(Request $request)
    {
        $db = $this->db();
        $tab = $request->input('tab', 'review');
        $tabs = [
            'review' => ['submitted', 'under_review'],
            'voting' => ['voting'],
            'funding' => ['funding', 'agreement_pending'],
            'active' => ['active', 'overdue'],
            'closed' => ['repaid', 'rejected', 'cancelled', 'defaulted'],
        ];
        abort_unless(isset($tabs[$tab]), 404);
        $counts = [];
        foreach ($tabs as $k => $statuses) {
            $counts[$k] = count(Scope::filter($db, $db->query('loan_applications', [['status', 'in', $statuses]], [], 500), 'borrowerId'));
        }
        $rows = self::sortBy(Scope::filter($db, $db->query('loan_applications', [['status', 'in', $tabs[$tab]]], [], 500), 'borrowerId'), 'createdAt', $tab !== 'closed');
        return view('finance.loans.index', [
            'tab' => $tab, 'rows' => $rows, 'counts' => $counts, 'stuckDays' => (int)config('finance.loan_stuck_days', 14),
            'names' => $this->names(array_map(function ($r) { return $r['data']['borrowerId'] ?? null; }, $rows)),
        ]);
    }

    /** A loan is stuck when in funding / agreement_pending longer than the configured days. */
    public static function stuck(array $l)
    {
        if (!in_array($l['status'] ?? '', ['funding', 'agreement_pending'], true)) {
            return false;
        }
        $since = $l['status'] === 'agreement_pending' ? ($l['fundedAt'] ?? null) : ($l['votingClosedAt'] ?? $l['updatedAt'] ?? $l['createdAt'] ?? null);
        return $since instanceof \DateTimeInterface && $since < (new \DateTimeImmutable())->modify('-' . (int)config('finance.loan_stuck_days', 14) . ' days');
    }

    public function loan($id)
    {
        $db = $this->db();
        $doc = $db->get('loan_applications/' . $id);
        abort_unless($doc && Scope::allows($db, $doc, 'borrowerId'), 404);
        $l = $doc['data'] + ['id' => $id];
        $regions = new Regions($db);
        $u = $db->get('users/' . ($l['borrowerId'] ?? ''));
        [$lending, $lendingFrom] = $regions->moduleSettings('lending', array_merge([(string)($l['regionId'] ?? '')], (array)($u['data']['regionIds'] ?? [])));
        $fundings = $db->query('loan_fundings', [['loanId', '==', $id]], [], 300);
        $assessment = $db->get('credit_assessments/' . $id);
        $loans = new Loans($db);
        $acc = $db->get('financial_accounts/loan_' . $id);
        $owed = [];
        foreach ($fundings as $f) {
            if (($f['data']['status'] ?? '') === 'succeeded' && (int)($l['amount'] ?? 0) > 0) {
                $owed[$f['id']] = intdiv((int)($l['repaidAmount'] ?? 0) * (int)($f['data']['amount'] ?? 0), (int)$l['amount']) - (int)($f['data']['collected'] ?? 0);
            }
        }
        $decision = in_array($l['status'] ?? '', ['submitted', 'under_review', 'voting', 'funding', 'agreement_pending', 'active', 'overdue', 'repaid'], true);
        $lockedBy = $decision && $this->can('finance.groups.manage') ? Locks::take('loan_applications/' . $id) : null;
        return view('finance.loans.show', [
            'id' => $id, 'l' => $l, 'lending' => $lending, 'lendingFrom' => $lendingFrom,
            'passes' => Loans::votePasses($l, $lending), 'votingOver' => ($l['votingEndsAt'] ?? null) instanceof \DateTimeInterface && $l['votingEndsAt'] <= new \DateTimeImmutable(),
            'fundings' => $fundings, 'owed' => $owed, 'agreement' => $db->get('loan_agreements/' . $id), 'schedule' => $db->get('loan_schedules/' . $id),
            'repayments' => self::sortBy($db->query('loan_repayments', [['loanId', '==', $id]], [], 300), 'createdAt'),
            'assessment' => $assessment ? $assessment['data'] : null, 'suggestion' => $loans->suggest($l),
            'account' => $acc ? $acc['data'] : null, 'stuck' => self::stuck($l),
            'names' => $this->names(array_merge([$l['borrowerId'] ?? null], (array)($l['eligibleVoterIds'] ?? []), array_map(function ($f) { return $f['data']['lenderId'] ?? null; }, $fundings))),
            'lockedBy' => $lockedBy, 'canManage' => $this->can('finance.groups.manage') && $lockedBy === null,
        ]);
    }

    public function loanAction(Request $request, $id, $action)
    {
        $db = $this->db();
        $loans = new Loans($db);
        $notifier = new Notifier($db);
        $reason = trim((string)$request->input('reason'));
        try {
            Locks::assertMine('loan_applications/' . $id);
            $doc = $db->get('loan_applications/' . $id);
            $l = ($doc ? $doc['data'] : []) + ['id' => $id];
            $amount = Money::format((int)($l['amount'] ?? 0), $l['currency'] ?? '');
            $u = $db->get('users/' . ($l['borrowerId'] ?? ''));
            [$lending] = (new Regions($db))->moduleSettings('lending', array_merge([(string)($l['regionId'] ?? '')], (array)($u['data']['regionIds'] ?? [])));
            switch ($action) {
                case 'review':
                    $loans->startReview($id);
                    return $this->done(__('Review started.'), $notifier->send($l['borrowerId'], 'loan_submitted', ['amount' => $amount], ['loanId' => $id]));
                case 'assessment':
                    $loans->saveAssessment($id, $request->input('score'), $request->input('band'), (array)$request->input('factors', []));
                    return $this->done(__('Credit assessment saved.'));
                case 'open-vote':
                    $opened = $loans->openVote($id, (int)($lending['voting']['durationHours'] ?? 0));
                    $fresh = $db->get('loan_applications/' . $id)['data'] + ['id' => $id];
                    $ends = $fresh['votingEndsAt'] ?? null;
                    $fresh['votingEndsAtText'] = $ends instanceof \DateTimeInterface ? $ends->setTimezone(new \DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : '';
                    return $this->done(__('Vote opened.'), Notices::loanVoteOpened($notifier, $fresh));
                case 'reject':
                    $loans->reject($id, $reason);
                    Locks::release('loan_applications/' . $id);
                    return $this->done(__('Loan rejected.'), $notifier->send($l['borrowerId'], 'loan_rejected', ['amount' => $amount, 'reason' => $reason], ['loanId' => $id]));
                case 'close-vote':
                    $r = $loans->closeVote($id, $lending);
                    return $this->done(__('Vote closed:') . ' ' . $r['status'] . '.', Notices::loanVoteClosed($notifier, $l, $r['status']));
                case 'default':
                    $loans->markDefault($id, $reason);
                    Locks::release('loan_applications/' . $id);
                    return $this->done(__('Loan marked defaulted; the borrower is restricted from loans.'));
                case 'refund':
                    $r = $loans->refundStuck($id, $reason);
                    $notes = [$notifier->send($l['borrowerId'], 'loan_rejected', ['amount' => $amount, 'reason' => $reason], ['loanId' => $id])];
                    foreach ($r['fundings'] as $f) {
                        $notes[] = $notifier->send($f['lenderId'] ?? '', 'loan_rejected',
                            ['amount' => Money::format((int)($f['amount'] ?? 0), $l['currency'] ?? ''), 'reason' => __('the loan was cancelled and your money refunded')], ['loanId' => $id]);
                    }
                    Locks::release('loan_applications/' . $id);
                    return $this->done(__('Every lender was refunded; the loan is cancelled.'), implode(' / ', $notes));
                case 'pay-lenders':
                    $r = $loans->payLenders($id);
                    $borrower = trim(($u['data']['firstName'] ?? '') . ' ' . ($u['data']['lastName'] ?? ''));
                    $notes = [];
                    foreach ($r['paid'] as $lender => $paid) {
                        $notes[] = $notifier->send($lender, 'loan_lender_repayment', ['amount' => Money::format($paid, $l['currency'] ?? ''), 'borrowerName' => $borrower], ['loanId' => $id]);
                    }
                    return $this->done(__('Lenders paid.'), implode(' / ', $notes));
                case 'sweep':
                    $left = $loans->sweep($id);
                    return $this->done(__('Rounding moved to the platform account:') . ' ' . Money::format($left, $l['currency'] ?? '') . '.');
            }
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }
}
