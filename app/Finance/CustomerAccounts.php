<?php

namespace App\Finance;

/**
 * Suspending, re-activating and deleting a customer (spec 6.3), done on the
 * server so Firebase sign-in follows and every change is in the audit log:
 *
 *  - suspend      users.active = false and the sign-in account disabled;
 *  - re-activate  users.active = true and the sign-in account enabled;
 *  - delete       a SOFT delete, for every customer, so the history is kept:
 *                 users/{uid} stays with isDeleted = true and isDeletedDate
 *                 (when), inactive, the push token cleared; the Firebase
 *                 sign-in account is deleted (the Customers screen then
 *                 removes the website login). Refused while money or a
 *                 Finance obligation is open (deleteCheck()).
 */
class CustomerAccounts
{
    private $db;
    private $auth;

    public function __construct(Firestore $db, AuthAccounts $auth = null)
    {
        $this->db = $db;
        $this->auth = $auth ?: new AuthAccounts($db);
    }

    /** The customer's users/{uid} document, or a FinanceException. */
    private function customer($uid)
    {
        $u = $this->db->get('users/' . $uid);
        if (!$u || ($u['data']['role'] ?? 'customer') !== 'customer') {
            throw new FinanceException('not_found', 'This customer no longer exists.');
        }
        return $u['data'];
    }

    /**
     * Suspends ($active false) or re-activates a customer: Firebase sign-in
     * first (so a refusal changes nothing), then the profile and the audit
     * entry in one commit. Returns a short note for the admin.
     */
    public function setActive($uid, $active, $reason)
    {
        $u = $this->customer($uid);
        if (!empty($u['isDeleted'])) {
            throw new FinanceException('deleted', 'This customer was deleted.');
        }
        $reason = trim((string)$reason);
        if (!$active && $reason === '') {
            throw new FinanceException('reason', 'Write why the customer is suspended.');
        }
        $signIn = $this->auth->setDisabled($uid, !$active);

        $after = ['active' => (bool)$active];
        if (array_key_exists('isActive', $u)) {
            $after['isActive'] = (bool)$active;      // the edit form also writes it: keep both the same
        }
        $before = array_intersect_key($u, $after);
        $this->db->batch([
            $this->db->write('update', 'users/' . $uid, $after),
            Audit::write($this->db, $active ? 'customer.activate' : 'customer.suspend', 'users', $uid, $before, $after + ['signInDisabled' => !$active], $reason),
        ]);
        if ($signIn === 'missing') {
            return $active ? 'Customer re-activated (they have no sign-in account).' : 'Customer suspended (they have no sign-in account).';
        }
        return $active ? 'Customer re-activated: they can sign in again.' : 'Customer suspended: they can no longer sign in (an open session ends within the hour).';
    }

    /**
     * Whether the customer used Finance, and what stops their deletion.
     * ['usesFinance' => bool, 'blockers' => [text, ...]]
     */
    public function deleteCheck($uid)
    {
        $this->customer($uid);
        $q = function ($collection, array $where, $limit = 50) {
            return $this->db->query($collection, $where, [], $limit);
        };
        $blockers = [];
        $uses = false;

        foreach ($q('financial_accounts', [['userId', '==', $uid]]) as $w) {
            $uses = true;
            if ((int)($w['data']['balance'] ?? 0) > 0) {
                $blockers[] = 'Their Finance wallet holds ' . Money::format((int)$w['data']['balance'], $w['data']['currency'] ?? '') . ': pay it out first.';
            }
        }
        $open = [
            ['wallet_operations', [['userId', '==', $uid], ['status', '==', 'pending']], 'A withdrawal is waiting to be paid.'],
            ['money_transfers', [['senderId', '==', $uid], ['status', '==', 'processing']], 'An external transfer is waiting to be paid.'],
            ['utility_payment_requests', [['userId', '==', $uid], ['status', '==', 'processing']], 'A bill payment is waiting to be paid.'],
        ];
        foreach ($open as [$collection, $where, $text]) {
            if ($q($collection, $where, 1)) {
                $uses = true;
                $blockers[] = $text;
            }
        }
        foreach ($q('loan_applications', [['borrowerId', '==', $uid]]) as $l) {
            $uses = true;
            if (in_array($l['data']['status'] ?? '', ['funding', 'agreement_pending', 'active', 'overdue'], true)) {
                $blockers[] = 'They have a loan still open (' . ($l['data']['status'] ?? '') . ').';
            }
        }
        foreach ($q('loan_applications', [['lenderIds', 'array-contains', $uid]]) as $l) {
            $uses = true;
            if (in_array($l['data']['status'] ?? '', ['funding', 'agreement_pending', 'active', 'overdue'], true)) {
                $blockers[] = 'They lent money in a loan still open.';
            }
        }
        foreach ($q('tontines', [['memberIds', 'array-contains', $uid]]) as $t) {
            $uses = true;
            if (in_array($t['data']['status'] ?? '', ['forming', 'active'], true)) {
                $blockers[] = 'They are a member of the tontine "' . ($t['data']['name'] ?? '') . '".';
            }
        }
        foreach ($q('savings_goals', [['memberIds', 'array-contains', $uid]]) as $g) {
            $uses = true;
            if (($g['data']['status'] ?? '') !== 'cancelled' && (int)($g['data']['savedAmount'] ?? 0) > 0) {
                $blockers[] = 'The savings goal "' . ($g['data']['name'] ?? '') . '" still holds money.';
            }
        }
        foreach ($q('investment_holdings', [['userId', '==', $uid]]) as $h) {
            $uses = true;
            if ((float)($h['data']['units'] ?? 0) > 0) {
                $blockers[] = 'They hold units of ' . ($h['data']['fundName'] ?? 'a fund') . '.';
            }
        }
        foreach ($q('insurance_policies', [['userId', '==', $uid]]) as $p) {
            $uses = true;
            if (in_array($p['data']['status'] ?? '', ['pending_activation', 'active', 'renewal_due', 'cancellation_requested'], true)) {
                $blockers[] = 'Their ' . ($p['data']['productName'] ?? 'insurance') . ' policy is still running.';
            }
        }
        foreach ([['kyc_applications', 'userId'], ['financing_applications', 'ownerId'], ['virtual_cards', 'userId']] as [$collection, $field]) {
            if (!$uses && $q($collection, [[$field, '==', $uid]], 1)) {
                $uses = true;
            }
        }
        if (!$uses && $q('ledger_transactions', [['userIds', 'array-contains', $uid]], 1)) {
            $uses = true;
        }
        return ['usesFinance' => $uses, 'blockers' => array_values(array_unique($blockers))];
    }

    /**
     * Soft-deletes a customer, keeping the history: checks the blockers again,
     * deletes the Firebase sign-in account, then marks users/{uid}
     * isDeleted = true with isDeletedDate, in one commit with the audit entry
     * (which also records the admin). Returns a note for the admin.
     */
    public function softDelete($uid, $reason)
    {
        $u = $this->customer($uid);
        $check = $this->deleteCheck($uid);
        if ($check['blockers']) {
            throw new FinanceException('blocked', 'This customer cannot be deleted yet: ' . implode(' ', $check['blockers']));
        }
        $this->auth->delete($uid);

        /* 30 Oct (point 84): a HARD delete, at the user's request - users/{uid}
         * is removed, not marked isDeleted. Their orders and wallet rows stay.
         * The audit entry keeps who it was and who deleted them. An older
         * profile already marked isDeleted is removed the same way. */
        $this->db->batch([
            $this->db->write('delete', 'users/' . $uid, []),
            Audit::write($this->db, 'customer.delete', 'users', $uid,
                array_intersect_key($u, ['email' => 1, 'phoneNumber' => 1, 'firstName' => 1, 'lastName' => 1, 'active' => 1]),
                ['profileDeleted' => true, 'signInDeleted' => true],
                trim((string)$reason) ?: 'Deleted from Customers'),
        ]);
        return 'Customer deleted: their sign-in and profile are removed, and the same email can register again.';
    }
}
