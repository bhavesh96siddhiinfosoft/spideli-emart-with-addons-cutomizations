<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;

/**
 * Phase C decisions for member loans (spec 6.9). Each is one Firestore
 * transaction with its audit entry; refunds reverse the lenders' postings,
 * lender payouts and the rounding sweep post from the loan account.
 */
class Loans
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

    private function loan(Transaction $t, $loanId, array $statuses)
    {
        $snap = $t->get('loan_applications/' . $loanId);
        if (!$snap) {
            throw new FinanceException('missing', 'This loan no longer exists.');
        }
        $l = $snap['data'] + ['id' => $loanId];
        if ($statuses && !in_array($l['status'] ?? '', $statuses, true)) {
            throw new FinanceException('state_changed', 'This loan is now "' . ($l['status'] ?? '?') . '": refresh the page.');
        }
        return $l;
    }

    private function setStatus(Transaction $t, array $l, $status, array $extra, $action, $reason = '')
    {
        $t->update('loan_applications/' . $l['id'], ['status' => $status, 'updatedAt' => Value::serverTimestamp()] + $extra);
        Audit::add($t, $action, 'loan_applications', $l['id'], ['status' => $l['status'] ?? null], ['status' => $status] + array_filter($extra, 'is_scalar'), $reason);
    }

    /* ================================================================ review */

    public function startReview($loanId)
    {
        return $this->db->transaction(function (Transaction $t) use ($loanId) {
            $l = $this->loan($t, $loanId, ['submitted']);
            $this->setStatus($t, $l, 'under_review', [], 'loan.start_review');
            return $l;
        });
    }

    /** Rejects before any money moved (submitted, under_review or voting). */
    public function reject($loanId, $reason)
    {
        self::needReason($reason);
        return $this->db->transaction(function (Transaction $t) use ($loanId, $reason) {
            $l = $this->loan($t, $loanId, ['submitted', 'under_review', 'voting']);
            $this->setStatus($t, $l, 'rejected', ['reason' => $reason], 'loan.reject', $reason);
            return $l;
        });
    }

    /**
     * Opens the vote for $hours. The eligible voters are those still active
     * members of the group (minus the borrower); there must be at least one.
     */
    public function openVote($loanId, $hours)
    {
        $hours = (int)$hours;
        if ($hours < 1) {
            throw new FinanceException('invalid', 'The region\'s lending settings have no vote duration (voting.durationHours).');
        }
        return $this->db->transaction(function (Transaction $t) use ($loanId, $hours) {
            $l = $this->loan($t, $loanId, ['under_review']);
            $group = $t->get('tontines/' . ($l['groupId'] ?? ''));
            $members = $group ? (array)($group['data']['memberIds'] ?? []) : [];
            $voters = array_values(array_filter((array)($l['eligibleVoterIds'] ?? []), function ($v) use ($members, $l) {
                return in_array($v, $members, true) && $v !== ($l['borrowerId'] ?? '');
            }));
            if (!$voters) {
                throw new FinanceException('loan_no_voters', 'No eligible voter is still a member of the group.');
            }
            $ends = (new \DateTimeImmutable())->modify('+' . $hours . ' hours');
            $this->setStatus($t, $l, 'voting', ['votingEndsAt' => Value::timestamp($ends), 'eligibleVoterIds' => $voters], 'loan.open_vote');
            return $l + ['eligibleVoterIds' => $voters];
        });
    }

    /**
     * The vote result (customer spec 6.9): passes when
     * cast×100 ≥ eligible×quorum and yes×100 ≥ cast×approval (both default 101).
     */
    public static function votePasses(array $l, array $lending)
    {
        $eligible = count((array)($l['eligibleVoterIds'] ?? []));
        $yes = (int)($l['votes']['yes'] ?? 0);
        $cast = $yes + (int)($l['votes']['no'] ?? 0);
        $quorum = (int)($lending['voting']['quorumPercent'] ?? 101);
        $approval = (int)($lending['voting']['approvalPercent'] ?? 101);
        return $eligible > 0 && $cast * 100 >= $eligible * $quorum && $yes * 100 >= $cast * $approval;
    }

    /** Closes a vote past votingEndsAt that nobody closed. Returns the new status. */
    public function closeVote($loanId, array $lending)
    {
        return $this->db->transaction(function (Transaction $t) use ($loanId, $lending) {
            $l = $this->loan($t, $loanId, ['voting']);
            $ends = $l['votingEndsAt'] ?? null;
            if (!$ends instanceof \DateTimeInterface || $ends > new \DateTimeImmutable()) {
                throw new FinanceException('invalid_state', 'The vote is still open.');
            }
            $pass = self::votePasses($l, $lending);
            $extra = ['votingClosedAt' => Value::serverTimestamp()];
            if (!$pass) {
                $extra['reason'] = 'vote';
            }
            $this->setStatus($t, $l, $pass ? 'funding' : 'rejected', $extra, 'loan.close_vote');
            return ['loan' => $l, 'status' => $pass ? 'funding' : 'rejected'];
        });
    }

    public function markDefault($loanId, $reason)
    {
        self::needReason($reason);
        return $this->db->transaction(function (Transaction $t) use ($loanId, $reason) {
            $l = $this->loan($t, $loanId, ['active', 'overdue']);
            $u = $t->get('users/' . $l['borrowerId']);
            $this->setStatus($t, $l, 'defaulted', ['reason' => $reason], 'loan.default', $reason);
            // After a documented review: the borrower is kept out of new loans.
            if ($u && empty($u['data']['financeRestriction'])) {
                $t->update('users/' . $l['borrowerId'], ['financeRestriction' => ['status' => 'restricted', 'reason' => 'Loan defaulted: ' . $reason,
                    'since' => Value::serverTimestamp(), 'scopes' => ['loans'], 'by' => $this->admin()]]);
            }
            return $l;
        });
    }

    /* =========================================================== credit score */

    public function saveAssessment($loanId, $score, $band, array $factors)
    {
        $score = (int)$score;
        if ($score < 300 || $score > 850 || trim((string)$band) === '') {
            throw new FinanceException('invalid', 'The score is 300 - 850 and needs a band.');
        }
        $factors = array_values(array_filter(array_map(function ($f) {
            $label = trim((string)($f['label'] ?? ''));
            return $label === '' ? null : ['label' => $label, 'impact' => ($f['impact'] ?? '') === 'negative' ? 'negative' : 'positive', 'detail' => trim((string)($f['detail'] ?? ''))];
        }, $factors)));
        return $this->db->transaction(function (Transaction $t) use ($loanId, $score, $band, $factors) {
            $l = $this->loan($t, $loanId, []);
            $old = $t->get('credit_assessments/' . $loanId);
            $doc = ['loanId' => $loanId, 'borrowerId' => $l['borrowerId'], 'score' => $score, 'band' => trim($band), 'factors' => $factors, 'assessedAt' => Value::serverTimestamp()];
            $t->set('credit_assessments/' . $loanId, $doc);
            Audit::add($t, 'loan.credit_assessment', 'credit_assessments', $loanId, $old ? ['score' => $old['data']['score'] ?? null] : null, ['score' => $score, 'band' => $band]);
        });
    }

    /**
     * A suggested assessment from the borrower's history, in plain words the
     * borrower can read: identity verification, tontine payments, past loans,
     * how long they have used Spideli. The admin edits it before saving.
     */
    public function suggest(array $loan)
    {
        $uid = $loan['borrowerId'] ?? '';
        $user = $this->db->get('users/' . $uid);
        $u = $user ? $user['data'] : [];
        $score = 600;
        $factors = [];

        $level = (string)($u['kycLevel'] ?? '');
        if ($level !== '') {
            $pts = $level === 'basic' ? 25 : 50;
            $score += $pts;
            $factors[] = ['label' => 'Identity verified', 'impact' => 'positive', 'detail' => "Verification level \"$level\"."];
        } else {
            $score -= 50;
            $factors[] = ['label' => 'Identity not verified', 'impact' => 'negative', 'detail' => 'No completed identity verification.'];
        }

        $paid = count($this->db->query('tontine_contributions', [['userId', '==', $uid]], [], 200));
        if ($paid > 0) {
            $score += min(100, $paid * 5);
            $factors[] = ['label' => 'Tontine contributions', 'impact' => 'positive', 'detail' => "$paid contribution(s) paid."];
        }

        $past = $this->db->query('loan_applications', [['borrowerId', '==', $uid]], [], 50);
        $repaid = 0;
        $bad = 0;
        foreach ($past as $p) {
            if ($p['id'] === ($loan['id'] ?? '')) {
                continue;
            }
            $s = $p['data']['status'] ?? '';
            if ($s === 'repaid') {
                $repaid++;
            } elseif (in_array($s, ['defaulted', 'overdue'], true)) {
                $bad++;
            }
        }
        if ($repaid) {
            $score += min(100, $repaid * 50);
            $factors[] = ['label' => 'Loans repaid', 'impact' => 'positive', 'detail' => "$repaid earlier loan(s) fully repaid."];
        }
        if ($bad) {
            $score -= min(250, $bad * 125);
            $factors[] = ['label' => 'Late or unpaid loans', 'impact' => 'negative', 'detail' => "$bad earlier loan(s) overdue or defaulted."];
        }

        $since = $u['createdAt'] ?? null;
        if ($since instanceof \DateTimeInterface) {
            $months = (int)floor((time() - $since->getTimestamp()) / (30 * 86400));
            $score += min(50, $months * 2);
            if ($months < 12) {
                $factors[] = ['label' => 'Short history', 'impact' => 'negative', 'detail' => 'Less than a year using Spideli.'];
            } else {
                $factors[] = ['label' => 'Long-standing customer', 'impact' => 'positive', 'detail' => "Using Spideli for $months months."];
            }
        }
        if (!empty($u['financeRestriction'])) {
            $score -= 100;
            $factors[] = ['label' => 'Finance restriction', 'impact' => 'negative', 'detail' => 'The account is currently restricted.'];
        }

        $score = max(300, min(850, $score));
        $band = $score >= 750 ? 'Excellent' : ($score >= 670 ? 'Good' : ($score >= 580 ? 'Fair' : 'Poor'));
        return ['score' => $score, 'band' => $band, 'factors' => $factors];
    }

    /* ================================================================= money */

    /**
     * Refunds a stuck loan (funding, or agreement_pending never accepted):
     * every succeeded funding is reversed (loan account → lender wallet) and
     * marked refunded; the loan is cancelled. The loan account ends at 0.
     * Returns the fundings refunded.
     */
    public function refundStuck($loanId, $reason)
    {
        self::needReason($reason);
        return $this->db->transaction(function (Transaction $t) use ($loanId, $reason) {
            $l = $this->loan($t, $loanId, ['funding', 'agreement_pending']);
            $fundings = array_values(array_filter($t->query('loan_fundings', [['loanId', '==', $loanId]]), function ($f) {
                return ($f['data']['status'] ?? '') === 'succeeded';
            }));
            $state = Ledger::newState();
            $plans = [];
            $snapshots = [];
            foreach ($fundings as $f) {
                $txId = $f['data']['txId'] ?? '';
                $tx = $txId !== '' ? $t->get('ledger_transactions/' . $txId) : null;
                if (!$tx || !Ledger::isWellFormed($tx['data']) || ($tx['data']['status'] ?? '') === 'reversed') {
                    throw new FinanceException('not_reversible', 'The funding ' . $f['id'] . ' has no reversible posting.');
                }
                $snapshots[$f['id']] = $tx['data'];
            }
            // Plan every reversal (all reads) before writing any.
            foreach ($fundings as $f) {
                $o = $snapshots[$f['id']];
                $plans[$f['id']] = $this->ledger->plan($t, $state, 'reversal', $o['currency'], $o['lines'][1]['accountId'], $o['lines'][0]['accountId'],
                    $o['lines'][1]['amount'], 0, $o['userIds'] ?? [], $o['source'] ?? ['kind' => 'loan_fundings', 'id' => $f['id']], 'Loan refunded: ' . $reason, $o['id']);
            }
            $pool = $state['accounts']['loan_' . $loanId]['balance'] ?? null;
            if ($fundings && $pool !== 0) {
                throw new FinanceException('invalid_state', 'The loan account would not end at 0 (' . var_export($pool, true) . ').');
            }
            foreach ($fundings as $f) {
                $w = $this->ledger->write($t, $state, $plans[$f['id']], Auth::id());
                $t->update('ledger_transactions/' . $snapshots[$f['id']]['id'], ['status' => 'reversed', 'reversedBy' => [$w['txId']], 'reversedAt' => Value::serverTimestamp()]);
                $t->update('loan_fundings/' . $f['id'], ['status' => 'refunded', 'updatedAt' => Value::serverTimestamp()]);
            }
            $this->setStatus($t, $l, 'cancelled', ['reason' => $reason, 'cancelledAt' => Value::serverTimestamp()], 'loan.refund_stuck', $reason);
            return ['loan' => $l, 'fundings' => array_map(function ($f) { return $f['data']; }, $fundings)];
        });
    }

    /**
     * Pays every lender what they are owed so far: per funding,
     * floor(repaidAmount × funding.amount / loan.amount) − collected.
     * Returns [lenderId => amount paid].
     */
    public function payLenders($loanId)
    {
        return $this->db->transaction(function (Transaction $t) use ($loanId) {
            $l = $this->loan($t, $loanId, ['active', 'overdue', 'repaid']);
            $cur = $l['currency'] ?? '';
            $principal = (int)($l['amount'] ?? 0);
            $repaid = (int)($l['repaidAmount'] ?? 0);
            if ($principal <= 0 || $repaid <= 0) {
                throw new FinanceException('nothing_to_collect', 'Nothing has been repaid yet.');
            }
            $fundings = array_values(array_filter($t->query('loan_fundings', [['loanId', '==', $loanId]]), function ($f) {
                return ($f['data']['status'] ?? '') === 'succeeded';
            }));
            $due = [];
            foreach ($fundings as $f) {
                $owed = intdiv($repaid * (int)($f['data']['amount'] ?? 0), $principal) - (int)($f['data']['collected'] ?? 0);
                if ($owed > 0) {
                    $due[$f['id']] = $owed;
                }
            }
            if (!$due) {
                throw new FinanceException('nothing_to_collect', 'Every lender has collected their share.');
            }
            $wallets = [];
            foreach ($fundings as $f) {
                if (isset($due[$f['id']])) {
                    $wallets[$f['id']] = $this->ledger->walletIdFor($t, $f['data']['lenderId'], $cur);
                }
            }
            $state = Ledger::newState();
            $plans = [];
            foreach ($fundings as $f) {
                if (isset($due[$f['id']])) {
                    $plans[$f['id']] = $this->ledger->plan($t, $state, 'loan_lender_payout', $cur, 'loan_' . $loanId, $wallets[$f['id']], $due[$f['id']], 0,
                        [$f['data']['lenderId']], ['kind' => 'loan_fundings', 'id' => $f['id']], 'Loan repayment share');
                }
            }
            $paid = [];
            foreach ($fundings as $f) {
                if (isset($plans[$f['id']])) {
                    $this->ledger->write($t, $state, $plans[$f['id']], Auth::id());
                    $t->update('loan_fundings/' . $f['id'], ['collected' => Value::increment($due[$f['id']]), 'updatedAt' => Value::serverTimestamp()]);
                    $paid[$f['data']['lenderId']] = ($paid[$f['data']['lenderId']] ?? 0) + $due[$f['id']];
                }
            }
            Audit::add($t, 'loan.pay_lenders', 'loan_applications', $loanId, null, ['paid' => $paid]);
            return ['loan' => $l, 'paid' => $paid];
        });
    }

    /** A repaid loan whose lenders all collected: the few units left go to the platform as a fee. */
    public function sweep($loanId)
    {
        return $this->db->transaction(function (Transaction $t) use ($loanId) {
            $l = $this->loan($t, $loanId, ['repaid']);
            $principal = (int)($l['amount'] ?? 0);
            $repaid = (int)($l['repaidAmount'] ?? 0);
            foreach ($t->query('loan_fundings', [['loanId', '==', $loanId]]) as $f) {
                if (($f['data']['status'] ?? '') === 'succeeded'
                    && intdiv($repaid * (int)($f['data']['amount'] ?? 0), max(1, $principal)) > (int)($f['data']['collected'] ?? 0)) {
                    throw new FinanceException('invalid_state', 'A lender has not collected their share yet: pay the lenders first.');
                }
            }
            $acc = $t->get('financial_accounts/loan_' . $loanId);
            $left = $acc ? (int)($acc['data']['balance'] ?? 0) : 0;
            if ($left <= 0) {
                throw new FinanceException('nothing_to_collect', 'The loan account is already empty.');
            }
            $cur = $l['currency'] ?? '';
            $state = Ledger::newState();
            $plan = $this->ledger->plan($t, $state, 'fee', $cur, 'loan_' . $loanId, Ledger::platformId($cur), $left, 0, [], ['kind' => 'loan_applications', 'id' => $loanId], 'Rounding left on a repaid loan');
            $w = $this->ledger->write($t, $state, $plan, Auth::id());
            Audit::add($t, 'loan.sweep', 'loan_applications', $loanId, ['balance' => $left], ['balance' => 0, 'txId' => $w['txId']]);
            return $left;
        });
    }

    private static function needReason($reason)
    {
        if (trim((string)$reason) === '') {
            throw new FinanceException('invalid', 'A reason is required.');
        }
    }
}
