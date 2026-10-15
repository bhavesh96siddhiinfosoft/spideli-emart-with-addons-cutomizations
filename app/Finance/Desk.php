<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;

/**
 * Phase B decisions (spec 6.3 - 6.5): KYC, restrictions, gateway receipts
 * and the payout queues. Each decision is one Firestore transaction that
 * re-reads the document, checks its state, writes it and its audit entry;
 * a refund is a reversal of the request's posting in the same transaction.
 * Notifications are sent by the caller after the decision is saved.
 */
class Desk
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

    /**
     * The common shape of a decision: read $collection/$id, require one of
     * $statuses, optionally reverse its posting, write $changes, audit.
     * Returns [the document before, the reversal postings].
     */
    private function decide($collection, $id, array $statuses, array $changes, $action, $reason = '', $reverse = false, callable $check = null)
    {
        return $this->db->transaction(function (Transaction $t) use ($collection, $id, $statuses, $changes, $action, $reason, $reverse, $check) {
            $snap = $t->get($collection . '/' . $id);
            if (!$snap) {
                throw new FinanceException('missing', 'This request no longer exists.');
            }
            $d = $snap['data'];
            if (!in_array($d['status'] ?? '', $statuses, true)) {
                throw new FinanceException('state_changed', 'This request is now "' . ($d['status'] ?? '?') . '": refresh the page.');
            }
            if ($check) {
                $check($d);
            }
            $written = [];
            if ($reverse) {
                if (empty($d['txId'])) {
                    throw new FinanceException('no_posting', 'This request has no posting to reverse.');
                }
                $written = $this->ledger->reverse($t, $d['txId'], Auth::id(), $reason !== '' ? $reason : $action);
            }
            $t->update($collection . '/' . $id, $changes + ['updatedAt' => Value::serverTimestamp()]);
            $after = $changes;
            if ($written) {
                $after['reversalTxIds'] = array_column($written, 'txId');
            }
            Audit::add($t, $action, $collection, $id, ['status' => $d['status'] ?? null], $after, $reason);
            return [$d, $written];
        });
    }

    /* =============================================================== KYC */

    private function review(Transaction $t, array $app, $action, $reason = '')
    {
        $id = Audit::newId();
        $t->create('kyc_reviews/' . $id, ['id' => $id, 'applicationId' => $app['id'], 'userId' => $app['userId'] ?? '',
            'action' => $action, 'reason' => (string)$reason, 'by' => $this->admin(), 'at' => Value::serverTimestamp()]);
    }

    public function kycStart($appId)
    {
        return $this->db->transaction(function (Transaction $t) use ($appId) {
            $a = $this->kycApp($t, $appId, ['submitted']);
            $t->update('kyc_applications/' . $appId, ['status' => 'under_review', 'updatedAt' => Value::serverTimestamp()]);
            $this->review($t, $a, 'start_review');
            Audit::add($t, 'kyc.start_review', 'kyc_applications', $appId, ['status' => 'submitted'], ['status' => 'under_review']);
            return $a;
        });
    }

    public function kycDocument($docId, $accept, $note = '')
    {
        return $this->db->transaction(function (Transaction $t) use ($docId, $accept, $note) {
            $snap = $t->get('kyc_documents/' . $docId);
            if (!$snap) {
                throw new FinanceException('missing', 'This document no longer exists.');
            }
            $d = $snap['data'];
            if (($d['status'] ?? '') !== 'uploaded') {
                throw new FinanceException('state_changed', 'This document is already "' . ($d['status'] ?? '?') . '".');
            }
            $app = $t->get('kyc_applications/' . ($d['applicationId'] ?? ''));
            if (!$app || !in_array($app['data']['status'] ?? '', ['submitted', 'under_review'], true)) {
                throw new FinanceException('state_changed', 'The application is no longer waiting for review.');
            }
            $status = $accept ? 'accepted' : 'rejected';
            $t->update('kyc_documents/' . $docId, ['status' => $status, 'note' => (string)$note, 'updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'kyc.document_' . $status, 'kyc_documents', $docId, ['status' => 'uploaded'], ['status' => $status], $note);
            return $d;
        });
    }

    public function kycRequestInfo($appId, $reason)
    {
        return $this->kycClose($appId, 'additional_info_required', 'request_info', $reason);
    }

    public function kycReject($appId, $reason)
    {
        return $this->kycClose($appId, 'rejected', 'reject', $reason, ['kycStatus' => 'rejected']);
    }

    private function kycClose($appId, $status, $action, $reason, array $userChanges = [])
    {
        if (trim((string)$reason) === '') {
            throw new FinanceException('invalid', 'A reason is required: the customer sees it.');
        }
        return $this->db->transaction(function (Transaction $t) use ($appId, $status, $action, $reason, $userChanges) {
            $a = $this->kycApp($t, $appId, ['submitted', 'under_review']);
            $t->update('kyc_applications/' . $appId, ['status' => $status, 'reason' => $reason, 'updatedAt' => Value::serverTimestamp()]);
            if ($userChanges) {
                $t->update('users/' . $a['userId'], $userChanges);
            }
            $this->review($t, $a, $action, $reason);
            Audit::add($t, 'kyc.' . $action, 'kyc_applications', $appId, ['status' => $a['status']], ['status' => $status] + $userChanges, $reason);
            return $a;
        });
    }

    /**
     * Approves when every requirement of the level is met by accepted
     * documents of this application ($kyc = the region's KYC settings).
     */
    public function kycApprove($appId, array $kyc)
    {
        return $this->db->transaction(function (Transaction $t) use ($appId, $kyc) {
            $a = $this->kycApp($t, $appId, ['under_review']);
            $docs = $t->query('kyc_documents', [['applicationId', '==', $appId]]);
            $missing = self::kycMissing($kyc, $a['level'] ?? '', array_column($docs, 'data'));
            if ($missing) {
                throw new FinanceException('kyc_documents_missing', 'Not every requirement is met by accepted documents: ' . implode('; ', $missing) . '.');
            }
            $t->update('kyc_applications/' . $appId, ['status' => 'approved', 'updatedAt' => Value::serverTimestamp()]);
            $t->update('users/' . $a['userId'], ['kycLevel' => $a['level'], 'kycStatus' => 'approved']);
            $this->review($t, $a, 'approve');
            Audit::add($t, 'kyc.approve', 'kyc_applications', $appId, ['status' => 'under_review'],
                ['status' => 'approved', 'kycLevel' => $a['level']]);
            return $a;
        });
    }

    /**
     * The requirements of $level not met: a requirement (anyOf) is met when
     * one of its document types has every side (documentTypes[type].sides)
     * accepted. Returns readable descriptions of the unmet ones.
     */
    public static function kycMissing(array $kyc, $level, array $docs)
    {
        $groups = $kyc['levels'][$level]['requiredDocuments'] ?? null;
        if (!is_array($groups) || !$groups) {
            return ["the level \"$level\" is not defined in this region's KYC settings"];
        }
        $accepted = [];
        foreach ($docs as $d) {
            if (($d['status'] ?? '') === 'accepted') {
                $accepted[$d['type'] ?? ''][$d['side'] ?? ''] = true;
            }
        }
        $missing = [];
        foreach ($groups as $g) {
            $met = false;
            foreach ((array)($g['anyOf'] ?? []) as $type) {
                $sides = (array)($kyc['documentTypes'][$type]['sides'] ?? ['front']);
                if ($sides && count(array_filter($sides, function ($s) use ($accepted, $type) { return !empty($accepted[$type][$s]); })) === count($sides)) {
                    $met = true;
                    break;
                }
            }
            if (!$met) {
                $missing[] = implode(' or ', (array)($g['anyOf'] ?? []));
            }
        }
        return $missing;
    }

    private function kycApp(Transaction $t, $appId, array $statuses)
    {
        $snap = $t->get('kyc_applications/' . $appId);
        if (!$snap) {
            throw new FinanceException('missing', 'This application no longer exists.');
        }
        $a = $snap['data'] + ['id' => $appId];
        if (!in_array($a['status'] ?? '', $statuses, true)) {
            throw new FinanceException('state_changed', 'This application is now "' . ($a['status'] ?? '?') . '": refresh the page.');
        }
        if (empty($a['userId'])) {
            throw new FinanceException('invalid', 'This application names no customer.');
        }
        return $a;
    }

    /* ======================================================= restriction */

    public const SCOPES = ['all', 'wallet', 'transfers', 'payments', 'savings', 'tontines', 'loans'];

    public function restrict($uid, array $scopes, $reason)
    {
        $scopes = array_values(array_intersect(self::SCOPES, $scopes));
        if (!$scopes || in_array('all', $scopes, true)) {
            $scopes = ['all'];
        }
        if (trim((string)$reason) === '') {
            throw new FinanceException('invalid', 'A reason is required (a documented review, spec 6.3).');
        }
        return $this->db->transaction(function (Transaction $t) use ($uid, $scopes, $reason) {
            $u = $t->get('users/' . $uid);
            if (!$u) {
                throw new FinanceException('missing', 'No customer has this id.');
            }
            $value = ['status' => 'restricted', 'reason' => $reason, 'since' => Value::serverTimestamp(), 'scopes' => $scopes, 'by' => $this->admin()];
            $t->update('users/' . $uid, ['financeRestriction' => $value]);
            Audit::add($t, 'customer.restrict', 'users', $uid, ['financeRestriction' => $u['data']['financeRestriction'] ?? null], ['financeRestriction' => $value], $reason);
        });
    }

    public function lift($uid, $reason)
    {
        return $this->db->transaction(function (Transaction $t) use ($uid, $reason) {
            $u = $t->get('users/' . $uid);
            if (!$u || empty($u['data']['financeRestriction'])) {
                throw new FinanceException('state_changed', 'This customer is not restricted.');
            }
            $t->update('users/' . $uid, ['financeRestriction' => Value::delete()]);
            Audit::add($t, 'customer.lift_restriction', 'users', $uid, ['financeRestriction' => $u['data']['financeRestriction']], ['financeRestriction' => null], $reason);
        });
    }

    /* ========================================================== receipts */

    public function receiptVerify($id)
    {
        return $this->db->transaction(function (Transaction $t) use ($id) {
            $snap = $t->get('payment_receipts/' . $id);
            if (!$snap || ($snap['data']['verified'] ?? false) === true) {
                throw new FinanceException('state_changed', 'This receipt is already checked or no longer exists.');
            }
            $t->update('payment_receipts/' . $id, ['verified' => true, 'verifiedAt' => Value::serverTimestamp(), 'verifiedBy' => $this->admin()]);
            Audit::add($t, 'receipt.verify', 'payment_receipts', $id, ['verified' => false], ['verified' => true]);
            return $snap['data'];
        });
    }

    /**
     * The provider did not receive the money (spec 6.5.1): reverse the
     * deposit. When the wallet no longer holds it, the reversal is impossible:
     * the receipt is marked disputed with the amount owed and the customer is
     * restricted (scope all) until it is recovered.
     * Returns ['reversed' => bool, 'receipt' => data].
     */
    public function receiptDispute($id, $note)
    {
        if (trim((string)$note) === '') {
            throw new FinanceException('invalid', 'Say what the provider reported.');
        }
        try {
            $r = $this->db->transaction(function (Transaction $t) use ($id, $note) {
                $snap = $t->get('payment_receipts/' . $id);
                if (!$snap || ($snap['data']['verified'] ?? false) === true || !empty($snap['data']['disputed'])) {
                    throw new FinanceException('state_changed', 'This receipt is already decided or no longer exists.');
                }
                $d = $snap['data'];
                if (empty($d['txId'])) {
                    throw new FinanceException('no_posting', 'This receipt names no posting.');
                }
                $written = $this->ledger->reverse($t, $d['txId'], Auth::id(), 'Gateway payment not received: ' . $note);
                $t->update('payment_receipts/' . $id, ['verified' => false, 'disputed' => true, 'note' => $note]);
                Audit::add($t, 'receipt.dispute', 'payment_receipts', $id, ['verified' => false],
                    ['disputed' => true, 'reversalTxIds' => array_column($written, 'txId')], $note);
                return $d;
            });
            return ['reversed' => true, 'receipt' => $r];
        } catch (FinanceException $e) {
            if ($e->reason !== 'insufficient_funds') {
                throw $e;
            }
        }
        // The money is already spent: record what is owed and restrict.
        $r = $this->db->transaction(function (Transaction $t) use ($id, $note) {
            $snap = $t->get('payment_receipts/' . $id);
            $d = $snap['data'];
            $owed = Money::format((int)($d['amount'] ?? 0), $d['currency'] ?? '');
            $u = !empty($d['userId']) ? $t->get('users/' . $d['userId']) : null;
            $text = $note . ' - not recoverable from the wallet: the customer owes ' . $owed . '.';
            $t->update('payment_receipts/' . $id, ['verified' => false, 'disputed' => true, 'note' => $text]);
            if ($u && empty($u['data']['financeRestriction'])) {
                $t->update('users/' . $d['userId'], ['financeRestriction' => ['status' => 'restricted', 'reason' => 'Unpaid gateway payment ' . ($d['reference'] ?? $id) . ': owes ' . $owed,
                    'since' => Value::serverTimestamp(), 'scopes' => ['all'], 'by' => $this->admin()]]);
            }
            Audit::add($t, 'receipt.dispute_unrecoverable', 'payment_receipts', $id, ['verified' => false],
                ['disputed' => true, 'owed' => $owed, 'restricted' => (bool)$u], $text);
            return $d;
        });
        return ['reversed' => false, 'receipt' => $r];
    }

    /* ===================================================== payout queues */

    public function withdrawalComplete($id, $payoutReference)
    {
        if (trim((string)$payoutReference) === '') {
            throw new FinanceException('invalid', 'Enter the payout reference (from the mobile money / bank payment).');
        }
        return $this->decide('wallet_operations', $id, ['pending'], ['status' => 'completed', 'payoutReference' => trim($payoutReference),
            'completedAt' => Value::serverTimestamp(), 'processedBy' => $this->admin()], 'withdrawal.complete', '', false,
            function ($d) { if (($d['type'] ?? '') !== 'withdrawal') { throw new FinanceException('invalid', 'Not a withdrawal.'); } })[0];
    }

    public function withdrawalFail($id, $reason)
    {
        $this->needReason($reason);
        return $this->decide('wallet_operations', $id, ['pending'], ['status' => 'reversed', 'reason' => $reason,
            'completedAt' => Value::serverTimestamp(), 'processedBy' => $this->admin()], 'withdrawal.reverse', $reason, true,
            function ($d) { if (($d['type'] ?? '') !== 'withdrawal') { throw new FinanceException('invalid', 'Not a withdrawal.'); } })[0];
    }

    /** Starting a transfer: the customer can no longer ask to cancel it. */
    public function transferStart($id)
    {
        return $this->decide('money_transfers', $id, ['processing'], ['adminLockedAt' => Value::serverTimestamp()], 'transfer.start', '', false,
            function ($d) {
                if (!empty($d['cancelRequested'])) {
                    throw new FinanceException('cancel_requested', 'The sender asked to cancel: cancel it instead.');
                }
                if (!empty($d['adminLockedAt'])) {
                    throw new FinanceException('state_changed', 'This transfer is already started.');
                }
            })[0];
    }

    public function transferComplete($id, $providerReference, $recipientVerified)
    {
        if (trim((string)$providerReference) === '') {
            throw new FinanceException('invalid', 'Enter the provider reference of the payment.');
        }
        return $this->decide('money_transfers', $id, ['processing'], ['status' => 'completed', 'providerReference' => trim($providerReference),
            'completedAt' => Value::serverTimestamp(), 'recipientVerified' => (bool)$recipientVerified], 'transfer.complete', '', false,
            function ($d) {
                if (empty($d['adminLockedAt'])) {
                    throw new FinanceException('invalid', 'Start the transfer first, before paying it.');
                }
            })[0];
    }

    public function transferFail($id, $reason)
    {
        $this->needReason($reason);
        return $this->decide('money_transfers', $id, ['processing'], ['status' => 'reversed', 'reason' => $reason,
            'completedAt' => Value::serverTimestamp()], 'transfer.reverse', $reason, true)[0];
    }

    /** The sender asked to cancel and the payout has not started. */
    public function transferCancel($id)
    {
        return $this->decide('money_transfers', $id, ['processing'], ['status' => 'cancelled', 'reason' => 'cancelled_by_sender',
            'completedAt' => Value::serverTimestamp()], 'transfer.cancel', 'cancelled_by_sender', true,
            function ($d) {
                if (empty($d['cancelRequested'])) {
                    throw new FinanceException('invalid', 'The sender did not ask to cancel this transfer.');
                }
                if (!empty($d['adminLockedAt'])) {
                    throw new FinanceException('state_changed', 'The payout has already started.');
                }
            })[0];
    }

    public function billComplete($id, array $fields)
    {
        $changes = ['status' => 'completed', 'completedAt' => Value::serverTimestamp()];
        if (trim((string)($fields['billerReference'] ?? '')) === '') {
            throw new FinanceException('invalid', 'Enter the biller reference.');
        }
        foreach (['billerReference', 'customerName', 'token', 'tokenLabel', 'units'] as $k) {
            $v = trim((string)($fields[$k] ?? ''));
            if ($v !== '') {
                $changes[$k] = $v;
            }
        }
        return $this->decide('utility_payment_requests', $id, ['processing'], $changes, 'bill.complete')[0];
    }

    public function billFail($id, $reason)
    {
        $this->needReason($reason);
        return $this->decide('utility_payment_requests', $id, ['processing'], ['status' => 'reversed', 'reason' => $reason,
            'completedAt' => Value::serverTimestamp()], 'bill.reverse', $reason, true)[0];
    }

    private function needReason($reason)
    {
        if (trim((string)$reason) === '') {
            throw new FinanceException('invalid', 'A reason is required: the customer sees it.');
        }
    }
}
