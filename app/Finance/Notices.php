<?php

namespace App\Finance;

/**
 * Phase C notifications shared by the screens and the scheduled jobs, so
 * the same event always sends the same notices.
 */
class Notices
{
    private static function name(Firestore $db, $uid)
    {
        $u = $uid ? $db->get('users/' . $uid) : null;
        return $u ? trim(($u['data']['firstName'] ?? '') . ' ' . ($u['data']['lastName'] ?? '')) : '';
    }

    /** After a vote closes: the borrower is told; on "funding" the group is invited to lend. */
    public static function loanVoteClosed(Notifier $n, array $loan, $status)
    {
        $db = app(Firestore::class);
        $amount = Money::format((int)($loan['amount'] ?? 0), $loan['currency'] ?? '');
        $data = ['loanId' => $loan['id'] ?? ''];
        if ($status === 'funding') {
            $values = ['borrowerName' => self::name($db, $loan['borrowerId'] ?? ''), 'groupName' => $loan['groupName'] ?? '', 'amount' => $amount];
            foreach ((array)($loan['eligibleVoterIds'] ?? []) as $uid) {
                $n->send($uid, 'loan_funding_open', $values, $data);
            }
            return $n->send($loan['borrowerId'] ?? '', 'loan_approved', ['amount' => $amount], $data);
        }
        return $n->send($loan['borrowerId'] ?? '', 'loan_rejected', ['amount' => $amount, 'reason' => __('the group vote did not approve it')], $data);
    }

    public static function loanVoteOpened(Notifier $n, array $loan)
    {
        $db = app(Firestore::class);
        $ends = $loan['votingEndsAtText'] ?? '';
        $values = ['borrowerName' => self::name($db, $loan['borrowerId'] ?? ''), 'groupName' => $loan['groupName'] ?? '',
            'amount' => Money::format((int)($loan['amount'] ?? 0), $loan['currency'] ?? ''), 'votingEndsAt' => $ends];
        $sent = 0;
        foreach ((array)($loan['eligibleVoterIds'] ?? []) as $uid) {
            $n->send($uid, 'loan_vote_requested', $values, ['loanId' => $loan['id'] ?? '']);
            $sent++;
        }
        return $sent . ' ' . __('voters notified');
    }
}
