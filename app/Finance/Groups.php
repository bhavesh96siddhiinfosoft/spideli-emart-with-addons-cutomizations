<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;

/**
 * Phase C decisions for collective savings (spec 6.7) and tontines (6.8).
 * Each is one Firestore transaction: every read first (state, shares,
 * accounts), then the postings and the document updates, then the audit.
 */
class Groups
{
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

    /* ==================================================== collective savings */

    /**
     * A member's share of a goal: Σ their succeeded contributions − Σ their
     * completed withdrawals (spec 6.7).
     */
    private function share(Transaction $t, $goalId, $uid)
    {
        $in = 0;
        foreach ($t->query('savings_contributions', [['goalId', '==', $goalId], ['userId', '==', $uid]]) as $c) {
            if (($c['data']['status'] ?? '') === 'succeeded') {
                $in += (int)($c['data']['amount'] ?? 0);
            }
        }
        $out = 0;
        foreach ($t->query('savings_withdrawals', [['goalId', '==', $goalId], ['userId', '==', $uid]]) as $w) {
            if (($w['data']['status'] ?? '') === 'completed') {
                $out += (int)($w['data']['amount'] ?? 0);
            }
        }
        return max(0, $in - $out);
    }

    /** Pays a group member's withdrawal request: goal pool → their wallet, at most their own share. */
    public function savingsWithdrawalPay($withdrawalId)
    {
        return $this->db->transaction(function (Transaction $t) use ($withdrawalId) {
            $w = $this->get($t, 'savings_withdrawals/' . $withdrawalId, 'withdrawal');
            if (($w['status'] ?? '') !== 'requested') {
                throw new FinanceException('state_changed', 'This withdrawal is now "' . ($w['status'] ?? '?') . '".');
            }
            $goal = $this->get($t, 'savings_goals/' . $w['goalId'], 'goal');
            if (($goal['status'] ?? '') !== 'active') {
                throw new FinanceException('invalid_state', 'The goal is not active.');
            }
            $amount = (int)($w['amount'] ?? 0);
            $cur = $goal['currency'] ?? ($w['currency'] ?? '');
            $share = $this->share($t, $goal['id'], $w['userId']);
            if ($amount <= 0 || $amount > $share) {
                throw new FinanceException('withdrawal_not_allowed', 'The member asks ' . Money::format($amount, $cur) . ' but their own share is ' . Money::format($share, $cur) . '.');
            }
            if ($amount > (int)($goal['savedAmount'] ?? 0)) {
                throw new FinanceException('insufficient_funds', 'The goal holds less than this amount.');
            }
            $wallet = $this->ledger->walletIdFor($t, $w['userId'], $cur);
            $state = Ledger::newState();
            $plan = $this->ledger->plan($t, $state, 'savings_withdrawal', $cur, 'goal_' . $goal['id'], $wallet, $amount, 0,
                [$w['userId']], ['kind' => 'savings_withdrawals', 'id' => $withdrawalId], 'Savings withdrawal: ' . ($goal['name'] ?? ''));
            $tx = $this->ledger->write($t, $state, $plan, Auth::id());
            $t->update('savings_goals/' . $goal['id'], ['savedAmount' => Value::increment(-$amount), 'lastTxId' => $tx['txId'], 'updatedAt' => Value::serverTimestamp()]);
            $t->update('savings_withdrawals/' . $withdrawalId, ['status' => 'completed', 'txId' => $tx['txId'], 'completedAt' => Value::serverTimestamp(), 'processedBy' => $this->admin()]);
            Audit::add($t, 'savings.withdrawal_pay', 'savings_withdrawals', $withdrawalId, ['status' => 'requested'], ['status' => 'completed', 'txId' => $tx['txId'], 'amount' => $amount]);
            return array_merge($w, ['goalName' => $goal['name'] ?? '', 'currency' => $cur]);
        });
    }

    public function savingsWithdrawalRefuse($withdrawalId, $reason)
    {
        self::needReason($reason);
        return $this->db->transaction(function (Transaction $t) use ($withdrawalId, $reason) {
            $w = $this->get($t, 'savings_withdrawals/' . $withdrawalId, 'withdrawal');
            if (($w['status'] ?? '') !== 'requested') {
                throw new FinanceException('state_changed', 'This withdrawal is now "' . ($w['status'] ?? '?') . '".');
            }
            // rejectionReason - never the member's own "reason".
            $t->update('savings_withdrawals/' . $withdrawalId, ['status' => 'rejected', 'rejectionReason' => $reason, 'processedBy' => $this->admin()]);
            Audit::add($t, 'savings.withdrawal_refuse', 'savings_withdrawals', $withdrawalId, ['status' => 'requested'], ['status' => 'rejected'], $reason);
            return $w;
        });
    }

    /**
     * Pays the whole pool out to the members and closes the goal: a requested
     * cancellation (final status "cancelled") or a goal paid out after its
     * target (final status "completed"). Each member gets their share, scaled
     * down pro rata when the pool holds less; the rounding remainder goes to
     * the owner. The pool ends at exactly 0.
     * Returns [memberId => amount paid].
     */
    public function savingsPayOut($goalId, $finalStatus)
    {
        if (!in_array($finalStatus, ['cancelled', 'completed'], true)) {
            throw new FinanceException('invalid', 'Unknown final status.');
        }
        return $this->db->transaction(function (Transaction $t) use ($goalId, $finalStatus) {
            $goal = $this->get($t, 'savings_goals/' . $goalId, 'goal');
            $need = $finalStatus === 'cancelled' ? 'cancellation_requested' : 'active';
            if (($goal['status'] ?? '') !== $need) {
                throw new FinanceException('state_changed', 'The goal is now "' . ($goal['status'] ?? '?') . '".');
            }
            $cur = $goal['currency'] ?? '';
            $pool = $t->get('financial_accounts/goal_' . $goalId);
            $balance = $pool ? (int)($pool['data']['balance'] ?? 0) : 0;
            $members = array_values(array_unique(array_filter((array)($goal['memberIds'] ?? []), 'is_string')));
            $owner = $goal['ownerId'] ?? ($members[0] ?? '');
            if ($owner !== '' && !in_array($owner, $members, true)) {
                $members[] = $owner;
            }
            $shares = [];
            foreach ($members as $m) {
                $shares[$m] = $this->share($t, $goalId, $m);
            }
            $pay = self::split($shares, $balance, $owner);
            $wallets = [];
            foreach ($pay as $m => $amount) {
                if ($amount > 0) {
                    $wallets[$m] = $this->ledger->walletIdFor($t, $m, $cur);
                }
            }

            $state = Ledger::newState();
            $plans = [];
            foreach ($pay as $m => $amount) {
                if ($amount > 0) {
                    $plans[$m] = $this->ledger->plan($t, $state, 'savings_withdrawal', $cur, 'goal_' . $goalId, $wallets[$m], $amount, 0,
                        [$m], ['kind' => 'savings_goals', 'id' => $goalId], ($finalStatus === 'cancelled' ? 'Goal cancelled: ' : 'Goal paid out: ') . ($goal['name'] ?? ''));
                }
            }
            if ($balance > 0 && ($state['accounts']['goal_' . $goalId]['balance'] ?? null) !== 0) {
                throw new FinanceException('invalid_state', 'The pool would not end at 0.');
            }
            $last = $goal['lastTxId'] ?? '';
            foreach ($plans as $p) {
                $last = $this->ledger->write($t, $state, $p, Auth::id())['txId'];
            }
            $changes = ['status' => $finalStatus, 'savedAmount' => 0, 'lastTxId' => $last, 'updatedAt' => Value::serverTimestamp()];
            if ($finalStatus === 'cancelled') {
                $changes['cancelledAt'] = Value::serverTimestamp();
            }
            $t->update('savings_goals/' . $goalId, $changes);
            Audit::add($t, 'savings.pay_out_' . $finalStatus, 'savings_goals', $goalId, ['status' => $goal['status'], 'balance' => $balance],
                ['status' => $finalStatus, 'paid' => $pay]);
            return $pay;
        });
    }

    /**
     * Splits $balance among the members' shares: in full when it covers them,
     * else pro rata (floored); whatever is left (rounding, or a balance above
     * the shares) goes to the owner, so the total is exactly $balance.
     */
    public static function split(array $shares, $balance, $owner)
    {
        $total = array_sum($shares);
        $pay = [];
        foreach ($shares as $m => $s) {
            $pay[$m] = $total <= $balance ? $s : ($total > 0 ? intdiv($s * $balance, $total) : 0);
        }
        $rest = $balance - array_sum($pay);
        if ($rest !== 0 && $owner !== '') {
            $pay[$owner] = ($pay[$owner] ?? 0) + $rest;
        }
        return $pay;
    }

    public function savingsKeep($goalId)
    {
        return $this->db->transaction(function (Transaction $t) use ($goalId) {
            $goal = $this->get($t, 'savings_goals/' . $goalId, 'goal');
            if (($goal['status'] ?? '') !== 'cancellation_requested') {
                throw new FinanceException('state_changed', 'No cancellation is requested.');
            }
            $t->update('savings_goals/' . $goalId, ['status' => 'active', 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'savings.keep', 'savings_goals', $goalId, ['status' => 'cancellation_requested'], ['status' => 'active']);
        });
    }

    /* ============================================================= tontines */

    /**
     * Approves a payout the beneficiary cannot approve (they are the only
     * admin): pays the cycle's collected amount from the pot and moves the
     * tontine to its next cycle (or completes it).
     */
    public function tontinePayoutApprove($payoutId)
    {
        return $this->db->transaction(function (Transaction $t) use ($payoutId) {
            $p = $this->get($t, 'tontine_payouts/' . $payoutId, 'payout');
            if (($p['status'] ?? '') !== 'pending_approval') {
                throw new FinanceException('state_changed', 'This payout is now "' . ($p['status'] ?? '?') . '".');
            }
            $tontine = $this->get($t, 'tontines/' . $p['tontineId'], 'tontine');
            if (($tontine['status'] ?? '') !== 'active') {
                throw new FinanceException('invalid_state', 'The tontine is not active (' . ($tontine['status'] ?? '?') . ').');
            }
            $admins = array_values((array)($tontine['adminIds'] ?? []));
            if ($admins !== [$p['beneficiaryUserId']]) {
                throw new FinanceException('not_needed', 'Another tontine admin can approve this payout in the app.');
            }
            $cycleId = $p['cycleId'] ?? ($p['tontineId'] . '_' . ($p['cycleNumber'] ?? ''));
            $cycle = $this->get($t, 'tontine_cycles/' . $cycleId, 'cycle');
            $number = (int)($cycle['number'] ?? $p['cycleNumber'] ?? 0);
            $next = $t->get('tontine_cycles/' . $p['tontineId'] . '_' . ($number + 1));
            $amount = (int)($cycle['collectedAmount'] ?? 0);
            if ($amount <= 0) {
                throw new FinanceException('nothing_to_collect', 'This cycle has collected nothing.');
            }
            $cur = $tontine['currency'] ?? ($cycle['currency'] ?? '');
            $wallet = $this->ledger->walletIdFor($t, $p['beneficiaryUserId'], $cur);
            $state = Ledger::newState();
            $plan = $this->ledger->plan($t, $state, 'tontine_payout', $cur, $tontine['potAccountId'] ?? ('tontine_' . $p['tontineId']), $wallet, $amount, 0,
                [$p['beneficiaryUserId']], ['kind' => 'tontine_payouts', 'id' => $payoutId], 'Tontine payout: ' . ($tontine['name'] ?? '') . ' #' . $number);
            $tx = $this->ledger->write($t, $state, $plan, Auth::id());

            $t->update('tontine_payouts/' . $payoutId, ['status' => 'paid', 'amount' => $amount, 'txId' => $tx['txId'], 'reference' => $tx['reference'],
                'approvedBy' => $this->admin(), 'approvedAt' => Value::serverTimestamp(), 'paidAt' => Value::serverTimestamp()]);
            $t->update('tontine_cycles/' . $cycleId, ['status' => 'paid', 'paidAt' => Value::serverTimestamp(), 'lastTxId' => $tx['txId'], 'updatedAt' => Value::serverTimestamp()]);
            if ($next) {
                $t->update('tontine_cycles/' . $next['id'], ['status' => 'collecting', 'updatedAt' => Value::serverTimestamp()]);
                $t->update('tontines/' . $p['tontineId'], ['currentCycle' => $number + 1, 'nextDueAt' => $next['data']['dueDate'] ?? null, 'updatedAt' => Value::serverTimestamp()]);
            } else {
                $t->update('tontines/' . $p['tontineId'], ['status' => 'completed', 'completedAt' => Value::serverTimestamp(), 'updatedAt' => Value::serverTimestamp()]);
            }
            Audit::add($t, 'tontine.payout_approve', 'tontine_payouts', $payoutId, ['status' => 'pending_approval'],
                ['status' => 'paid', 'amount' => $amount, 'txId' => $tx['txId'], 'completed' => !$next]);
            return array_merge($p, ['amount' => $amount, 'currency' => $cur, 'tontineName' => $tontine['name'] ?? '']);   // the amount paid, not the request's
        });
    }

    public function tontineSuspend($tontineId, $suspend, $reason)
    {
        self::needReason($reason);
        return $this->db->transaction(function (Transaction $t) use ($tontineId, $suspend, $reason) {
            $tn = $this->get($t, 'tontines/' . $tontineId, 'tontine');
            $from = $suspend ? 'active' : 'suspended';
            if (($tn['status'] ?? '') !== $from) {
                throw new FinanceException('state_changed', 'The tontine is now "' . ($tn['status'] ?? '?') . '".');
            }
            $to = $suspend ? 'suspended' : 'active';
            $t->update('tontines/' . $tontineId, ['status' => $to, 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, $suspend ? 'tontine.suspend' : 'tontine.resume', 'tontines', $tontineId, ['status' => $from], ['status' => $to], $reason);
        });
    }

    /**
     * Dispute workflow: open → under_review → resolved / rejected. Resolving
     * may restrict the member it is against in this tontine.
     */
    public function dispute($disputeId, $action, $resolution = '', $restrictMember = false)
    {
        return $this->db->transaction(function (Transaction $t) use ($disputeId, $action, $resolution, $restrictMember) {
            $d = $this->get($t, 'tontine_disputes/' . $disputeId, 'dispute');
            $status = $d['status'] ?? '';
            if ($action === 'review') {
                if ($status !== 'open') {
                    throw new FinanceException('state_changed', 'The dispute is now "' . $status . '".');
                }
                $t->update('tontine_disputes/' . $disputeId, ['status' => 'under_review']);
                Audit::add($t, 'tontine.dispute_review', 'tontine_disputes', $disputeId, ['status' => $status], ['status' => 'under_review']);
                return $d;
            }
            if (!in_array($action, ['resolved', 'rejected'], true)) {
                throw new FinanceException('invalid', 'Unknown action.');
            }
            if (!in_array($status, ['open', 'under_review'], true)) {
                throw new FinanceException('state_changed', 'The dispute is already "' . $status . '".');
            }
            self::needReason($resolution);
            $member = null;
            if ($restrictMember && $action === 'resolved' && !empty($d['againstUserId'])) {
                $member = $t->get('tontine_members/' . $d['tontineId'] . '_' . $d['againstUserId']);
            }
            $t->update('tontine_disputes/' . $disputeId, ['status' => $action, 'resolution' => $resolution, 'resolvedBy' => $this->admin(), 'resolvedAt' => Value::serverTimestamp()]);
            if ($member) {
                $t->update('tontine_members/' . $member['id'], ['status' => 'restricted']);
            }
            Audit::add($t, 'tontine.dispute_' . $action, 'tontine_disputes', $disputeId, ['status' => $status],
                ['status' => $action, 'memberRestricted' => (bool)$member], $resolution);
            return $d;
        });
    }

    private static function needReason($reason)
    {
        if (trim((string)$reason) === '') {
            throw new FinanceException('invalid', 'A reason is required.');
        }
    }
}
