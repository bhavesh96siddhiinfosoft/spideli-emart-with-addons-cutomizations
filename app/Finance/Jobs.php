<?php

namespace App\Finance;

use Illuminate\Support\Facades\DB;

/**
 * Phase C scheduled work (spec 7): loan instalments due / overdue with the
 * late penalty / back to active, votes nobody closed, reminders and overdue
 * notices, savings targets reached, tontine invitations left unanswered.
 *
 * Demo records (demo: true) are never touched. Every notice is sent once
 * (finance_job_marks). Each change is its own transaction with its audit
 * entry; one failing record never stops the others. Returns a log of lines.
 */
class Jobs
{
    private $db;
    private $notifier;
    private $regions;
    private $log = [];
    private $now;

    public function __construct(Firestore $db = null, \DateTimeImmutable $now = null)
    {
        $this->db = $db ?: app(Firestore::class);
        $this->notifier = new Notifier($this->db);
        $this->regions = new Regions($this->db);
        $this->now = $now ?: new \DateTimeImmutable();
    }

    private function line($text)
    {
        $this->log[] = $text;
    }

    /** True the first time a mark is set (a notice not sent before). */
    private function once($mark)
    {
        return DB::table('finance_job_marks')->insertOrIgnore(['mark' => substr($mark, 0, 191), 'created_at' => now()]) === 1;
    }

    private function notifyOnce($mark, $uid, $type, array $values, array $data = [])
    {
        if ($uid && $this->once($mark)) {
            $this->line("  notify $type → $uid: " . $this->notifier->send($uid, $type, $values, $data));
        }
    }

    private static function demo(array $d)
    {
        return !empty($d['demo']);
    }

    private function date($ts)
    {
        return $ts instanceof \DateTimeInterface ? $ts->setTimezone(new \DateTimeZone(config('app.timezone')))->format('Y-m-d') : '';
    }

    private function settingsFor($module, array $record, $uid)
    {
        $u = $uid ? $this->db->get('users/' . $uid) : null;
        return $this->regions->moduleSettings($module, array_merge([(string)($record['regionId'] ?? '')], (array)($u['data']['regionIds'] ?? [])))[0];
    }

    public function daily()
    {
        foreach (['loanSchedules', 'tontineReminders', 'savingsReminders', 'savingsTargets', 'tontineInvitations'] as $job) {
            $this->line("[$job]");
            try {
                $this->$job();
            } catch (\Throwable $e) {
                report($e);
                $this->line('  failed: ' . $e->getMessage());
            }
        }
        return $this->log;
    }

    public function hourly()
    {
        $this->line('[closeVotes]');
        try {
            $this->closeVotes();
        } catch (\Throwable $e) {
            report($e);
            $this->line('  failed: ' . $e->getMessage());
        }
        return $this->log;
    }

    /* ================================================================ loans */

    /**
     * Instalments: upcoming → due on the due date; past due + grace and not
     * fully paid → overdue with the late penalty (once per instalment, on
     * what is unpaid); loan overdue ⇄ active.
     */
    public function loanSchedules()
    {
        $grace = (int)config('finance.loan_grace_days', 3);
        $before = (int)config('finance.reminder_days_before', 2);
        foreach ($this->db->query('loan_applications', [['status', 'in', ['active', 'overdue']]], [], 500) as $loan) {
            if (self::demo($loan['data'])) {
                continue;
            }
            $id = $loan['id'];
            $l = $loan['data'];
            $pct = (float)($this->settingsFor('lending', $l, $l['borrowerId'] ?? '')['latePenaltyPercent'] ?? 0);
            try {
                $result = $this->db->transaction(function (Transaction $t) use ($id, $grace, $pct) {
                    $loan = $t->get('loan_applications/' . $id);
                    $sched = $t->get('loan_schedules/' . $id);
                    if (!$loan || !$sched || !in_array($loan['data']['status'] ?? '', ['active', 'overdue'], true)) {
                        return null;
                    }
                    $items = (array)($sched['data']['installments'] ?? []);
                    $nowDue = [];
                    $nowOverdue = [];
                    $penalties = 0;
                    foreach ($items as $i => $it) {
                        $due = $it['dueDate'] ?? null;
                        if (!$due instanceof \DateTimeInterface) {
                            continue;
                        }
                        $status = $it['status'] ?? 'upcoming';
                        $amountDue = (int)($it['amountDue'] ?? $it['amount'] ?? 0);
                        $paid = (int)($it['paidAmount'] ?? 0);
                        if ($status === 'paid' || $paid >= $amountDue) {
                            continue;
                        }
                        $lateAfter = (new \DateTimeImmutable('@' . $due->getTimestamp()))->modify("+$grace days");
                        if ($this->now > $lateAfter && $status !== 'overdue') {
                            $penalty = 0;
                            if ((int)($it['penalty'] ?? 0) === 0 && $pct > 0) {
                                $penalty = (int)round(($amountDue - $paid) * $pct / 100);
                            }
                            $items[$i]['status'] = 'overdue';
                            $items[$i]['penalty'] = (int)($it['penalty'] ?? 0) + $penalty;
                            $items[$i]['amountDue'] = $amountDue + $penalty;
                            $penalties += $penalty;
                            $nowOverdue[] = $items[$i];
                        } elseif ($status === 'upcoming' && $due <= $this->now) {
                            $items[$i]['status'] = 'due';
                            $nowDue[] = $items[$i];
                        }
                    }
                    $anyOverdue = (bool)array_filter($items, function ($it) { return ($it['status'] ?? '') === 'overdue'; });
                    $loanStatus = $loan['data']['status'];
                    $newStatus = $anyOverdue ? 'overdue' : ((int)($loan['data']['outstandingAmount'] ?? 0) > 0 ? 'active' : $loanStatus);
                    if (!$nowDue && !$nowOverdue && $newStatus === $loanStatus) {
                        return null;
                    }
                    if ($nowDue || $nowOverdue) {
                        // The whole array is rewritten, same length (spec 6.9).
                        $t->update('loan_schedules/' . $id, ['installments' => array_values($items), 'updatedAt' => Value::serverTimestamp()]);
                    }
                    $changes = ['updatedAt' => Value::serverTimestamp()];
                    if ($newStatus !== $loanStatus) {
                        $changes['status'] = $newStatus;
                    }
                    if ($penalties > 0) {
                        $changes['outstandingAmount'] = Value::increment($penalties);
                    }
                    $t->update('loan_applications/' . $id, $changes);
                    Audit::add($t, 'job.loan_schedule', 'loan_applications', $id, ['status' => $loanStatus],
                        ['status' => $newStatus, 'due' => count($nowDue), 'overdue' => count($nowOverdue), 'penalties' => $penalties]);
                    return ['due' => $nowDue, 'overdue' => $nowOverdue, 'status' => $newStatus, 'from' => $loanStatus];
                });
            } catch (\Throwable $e) {
                report($e);
                $this->line("  loan $id failed: " . $e->getMessage());
                continue;
            }
            $cur = $l['currency'] ?? '';
            if ($result) {
                $this->line("  loan $id: {$result['from']} → {$result['status']}, " . count($result['due']) . ' due, ' . count($result['overdue']) . ' overdue');
                foreach ($result['due'] as $it) {
                    $this->notifyOnce("loan_due_{$id}_{$it['number']}", $l['borrowerId'] ?? '', 'loan_repayment_due',
                        ['amount' => Money::format((int)$it['amountDue'], $cur), 'dueDate' => $this->date($it['dueDate'])], ['loanId' => $id]);
                }
                foreach ($result['overdue'] as $it) {
                    $this->notifyOnce("loan_overdue_{$id}_{$it['number']}", $l['borrowerId'] ?? '', 'loan_repayment_overdue',
                        ['amount' => Money::format((int)$it['amountDue'] - (int)($it['paidAmount'] ?? 0), $cur)], ['loanId' => $id]);
                }
            }
            // Reminder a few days before the next instalment.
            $sched = $this->db->get('loan_schedules/' . $id);
            foreach ((array)($sched['data']['installments'] ?? []) as $it) {
                $due = $it['dueDate'] ?? null;
                if (($it['status'] ?? '') === 'upcoming' && $due instanceof \DateTimeInterface && $due > $this->now
                    && $due <= $this->now->modify("+$before days")) {
                    $this->notifyOnce("loan_due_{$id}_{$it['number']}", $l['borrowerId'] ?? '', 'loan_repayment_due',
                        ['amount' => Money::format((int)($it['amountDue'] ?? $it['amount'] ?? 0), $cur), 'dueDate' => $this->date($due)], ['loanId' => $id]);
                }
            }
        }
    }

    /** Votes past votingEndsAt that nobody closed (hourly). */
    public function closeVotes()
    {
        $loans = new Loans($this->db);
        foreach ($this->db->query('loan_applications', [['status', '==', 'voting']], [], 300) as $loan) {
            $l = $loan['data'];
            $ends = $l['votingEndsAt'] ?? null;
            if (self::demo($l) || !$ends instanceof \DateTimeInterface || $ends > $this->now) {
                continue;
            }
            try {
                $r = $loans->closeVote($loan['id'], $this->settingsFor('lending', $l, $l['borrowerId'] ?? ''));
            } catch (\Throwable $e) {
                $this->line("  loan {$loan['id']} failed: " . $e->getMessage());
                continue;
            }
            $this->line("  loan {$loan['id']}: vote closed → {$r['status']}");
            Notices::loanVoteClosed($this->notifier, $l + ['id' => $loan['id']], $r['status']);
        }
    }

    /* ============================================================= tontines */

    /** Due soon → tontine_contribution_due; past due → tontine_contribution_overdue (notices only). */
    public function tontineReminders()
    {
        $before = (int)config('finance.reminder_days_before', 2);
        foreach ($this->db->query('tontines', [['status', '==', 'active']], [], 300) as $tn) {
            $d = $tn['data'];
            if (self::demo($d)) {
                continue;
            }
            $cycle = $this->db->get('tontine_cycles/' . $tn['id'] . '_' . (int)($d['currentCycle'] ?? 0));
            if (!$cycle || ($cycle['data']['status'] ?? '') !== 'collecting') {
                continue;
            }
            $due = $cycle['data']['dueDate'] ?? null;
            if (!$due instanceof \DateTimeInterface) {
                continue;
            }
            $paid = [];
            foreach ($this->db->query('tontine_contributions', [['cycleId', '==', $cycle['id']]]) as $c) {
                $paid[$c['data']['userId'] ?? ''] = true;
            }
            $values = ['amount' => Money::format((int)($d['contributionAmount'] ?? 0), $d['currency'] ?? ''), 'tontineName' => $d['name'] ?? '', 'dueDate' => $this->date($due)];
            foreach ((array)($d['memberIds'] ?? []) as $uid) {
                if (isset($paid[$uid])) {
                    continue;
                }
                if ($due < $this->now) {
                    $this->notifyOnce("tontine_overdue_{$cycle['id']}_$uid", $uid, 'tontine_contribution_overdue', $values, ['tontineId' => $tn['id'], 'cycleId' => $cycle['id']]);
                } elseif ($due <= $this->now->modify("+$before days")) {
                    $this->notifyOnce("tontine_due_{$cycle['id']}_$uid", $uid, 'tontine_contribution_due', $values, ['tontineId' => $tn['id'], 'cycleId' => $cycle['id']]);
                }
            }
        }
    }

    /** Invitations still "invited" after the tontine started → removed. */
    public function tontineInvitations()
    {
        foreach ($this->db->query('tontine_members', [['status', '==', 'invited']], [], 500) as $m) {
            if (self::demo($m['data'])) {
                continue;
            }
            $tn = $this->db->get('tontines/' . ($m['data']['tontineId'] ?? ''));
            if (!$tn || !in_array($tn['data']['status'] ?? '', ['active', 'completed'], true)) {
                continue;
            }
            try {
                $this->db->transaction(function (Transaction $t) use ($m) {
                    $now = $t->get('tontine_members/' . $m['id']);
                    if (!$now || ($now['data']['status'] ?? '') !== 'invited') {
                        return;
                    }
                    $t->update('tontine_members/' . $m['id'], ['status' => 'removed']);
                    Audit::add($t, 'job.tontine_invitation_removed', 'tontine_members', $m['id'], ['status' => 'invited'], ['status' => 'removed']);
                });
                $this->line("  invitation {$m['id']} removed (tontine started)");
            } catch (\Throwable $e) {
                $this->line("  invitation {$m['id']} failed: " . $e->getMessage());
            }
        }
    }

    /* ============================================================== savings */

    /** Goals with a schedule: a reminder once per period to each member who has not paid in it. */
    public function savingsReminders()
    {
        $days = ['weekly' => 7, 'biweekly' => 14, 'monthly' => 30];
        foreach ($this->db->query('savings_goals', [['status', '==', 'active']], [], 500) as $g) {
            $d = $g['data'];
            $period = $days[$d['schedule']['frequency'] ?? ''] ?? 0;
            $start = $d['createdAt'] ?? null;
            if (self::demo($d) || !$period || !$start instanceof \DateTimeInterface || empty($d['schedule']['amount'])) {
                continue;
            }
            $index = (int)floor(($this->now->getTimestamp() - $start->getTimestamp()) / ($period * 86400));
            $dueDate = (new \DateTimeImmutable('@' . $start->getTimestamp()))->modify('+' . (($index + 1) * $period) . ' days');
            $periodStart = (new \DateTimeImmutable('@' . $start->getTimestamp()))->modify('+' . ($index * $period) . ' days');
            if ($dueDate > $this->now->modify('+' . (int)config('finance.reminder_days_before', 2) . ' days')) {
                continue;   // not yet close to this period's end
            }
            $paidThisPeriod = [];
            foreach ($this->db->query('savings_contributions', [['goalId', '==', $g['id']]]) as $c) {
                $at = $c['data']['createdAt'] ?? null;
                if ($at instanceof \DateTimeInterface && $at >= $periodStart && ($c['data']['status'] ?? '') === 'succeeded') {
                    $paidThisPeriod[$c['data']['userId'] ?? ''] = true;
                }
            }
            foreach ((array)($d['memberIds'] ?? []) as $uid) {
                if (!isset($paidThisPeriod[$uid])) {
                    $this->notifyOnce("savings_due_{$g['id']}_{$uid}_$index", $uid, 'savings_contribution_due',
                        ['amount' => Money::format((int)$d['schedule']['amount'], $d['currency'] ?? ''), 'goalName' => $d['name'] ?? '', 'dueDate' => $this->date($dueDate)], ['goalId' => $g['id']]);
                }
            }
        }
    }

    /** Target reached: a notice once to every member (the goal stays active, spec 6.7). */
    public function savingsTargets()
    {
        foreach ($this->db->query('savings_goals', [['status', '==', 'active']], [], 500) as $g) {
            $d = $g['data'];
            if (self::demo($d) || (int)($d['targetAmount'] ?? 0) <= 0 || (int)($d['savedAmount'] ?? 0) < (int)$d['targetAmount']) {
                continue;
            }
            foreach ((array)($d['memberIds'] ?? []) as $uid) {
                $this->notifyOnce("savings_target_{$g['id']}_$uid", $uid, 'savings_goal_completed', ['goalName' => $d['name'] ?? ''], ['goalId' => $g['id']]);
            }
        }
    }
}
