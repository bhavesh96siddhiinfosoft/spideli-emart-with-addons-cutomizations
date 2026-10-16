<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;

/**
 * Phase D4 support tickets (spec 6.15): support_tickets/{id} with a
 * messages subcollection - staff-only (no customer rule exists), created by
 * support from a chat, a complaint or a failed operation. SLA targets by
 * priority come from config/finance.php ticket_sla_hours. The customer is
 * told when their ticket is resolved (chosen 16 Oct 2026).
 */
class Support
{
    public const CATEGORIES = ['payment', 'transfer', 'card', 'loan', 'tontine', 'savings', 'insurance', 'kyc', 'other'];
    public const PRIORITIES = ['urgent', 'high', 'normal', 'low'];
    public const STATUSES = ['open', 'in_progress', 'resolved', 'closed'];

    private $db;

    public function __construct(Firestore $db = null)
    {
        $this->db = $db ?: app(Firestore::class);
    }

    private function admin()
    {
        return 'admin:' . Auth::id();
    }

    /** The label of a priority (its own words: "High" alone already means a market high). */
    public static function priorityLabel($priority)
    {
        return ['urgent' => 'Urgent', 'high' => 'High priority', 'normal' => 'Normal priority', 'low' => 'Low priority'][$priority] ?? (string)$priority;
    }

    /** Hours to resolve a ticket of this priority. */
    public static function slaHours($priority)
    {
        $h = (array)config('finance.ticket_sla_hours', []);
        return (int)($h[$priority] ?? $h['normal'] ?? 72);
    }

    /**
     * The SLA state of a ticket: ['due' => DateTime, 'late' => bool,
     * 'left' => seconds left (negative when late), 'met' => for a resolved
     * ticket, resolved before its due time].
     */
    public static function sla(array $t, \DateTimeInterface $now)
    {
        $created = $t['createdAt'] ?? null;
        if (!$created instanceof \DateTimeInterface) {
            return null;
        }
        $due = \DateTimeImmutable::createFromInterface($created)->modify('+' . self::slaHours($t['priority'] ?? 'normal') . ' hours');
        $done = $t['resolvedAt'] ?? null;
        if (in_array($t['status'] ?? '', ['resolved', 'closed'], true) && $done instanceof \DateTimeInterface) {
            return ['due' => $due, 'late' => false, 'left' => null, 'met' => $done <= $due];
        }
        $left = $due->getTimestamp() - $now->getTimestamp();
        return ['due' => $due, 'late' => $left < 0, 'left' => $left, 'met' => null];
    }

    private static function check(array $in, $partial = false)
    {
        $out = [];
        if (!$partial || isset($in['category'])) {
            if (!in_array($in['category'] ?? '', self::CATEGORIES, true)) {
                throw new FinanceException('invalid', 'Choose the category.');
            }
            $out['category'] = $in['category'];
        }
        if (!$partial || isset($in['priority'])) {
            if (!in_array($in['priority'] ?? '', self::PRIORITIES, true)) {
                throw new FinanceException('invalid', 'Choose the priority.');
            }
            $out['priority'] = $in['priority'];
        }
        return $out;
    }

    /** Opens a ticket for a customer, with its first message (what happened). */
    public function open($uid, array $in, $assignedTo = null)
    {
        $subject = trim((string)($in['subject'] ?? ''));
        $text = trim((string)($in['message'] ?? ''));
        if ($subject === '' || mb_strlen($subject) > 200) {
            throw new FinanceException('invalid', 'The ticket needs a subject (at most 200 characters).');
        }
        if ($text === '') {
            throw new FinanceException('invalid', 'Describe the problem.');
        }
        $user = $this->db->get('users/' . $uid);
        if (!$user) {
            throw new FinanceException('missing', 'This customer does not exist.');
        }
        $t = self::check($in) + [
            'userId' => $uid,
            'subject' => $subject,
            'relatedRef' => trim((string)($in['relatedRef'] ?? '')),
            'status' => 'open',
            'assignedTo' => $assignedTo['id'] ?? '',
            'assignedToEmail' => $assignedTo['email'] ?? '',
            'source' => in_array($in['source'] ?? '', ['chat', 'complaint', 'operation', 'phone', 'other'], true) ? $in['source'] : 'other',
            'createdBy' => $this->admin(),
            'createdAt' => Value::serverTimestamp(),
            'updatedAt' => Value::serverTimestamp(),
        ];
        $id = Money::random(20);
        $this->db->batch([
            $this->db->write('create', 'support_tickets/' . $id, $t),
            $this->db->write('create', 'support_tickets/' . $id . '/messages/' . Money::random(20), ['authorId' => $this->admin(),
                'authorEmail' => (string)(Auth::user()->email ?? ''), 'authorType' => 'admin', 'text' => $text, 'createdAt' => Value::serverTimestamp()]),
            Audit::write($this->db, 'ticket.open', 'support_tickets', $id, null, $t),
        ]);
        return $id;
    }

    /**
     * Adds a message: the admin's own note, or what the customer said (in the
     * support chat, on the phone) recorded by the admin.
     */
    public function message($id, $text, $fromCustomer = false)
    {
        $text = trim((string)$text);
        if ($text === '') {
            throw new FinanceException('invalid', 'Write the message.');
        }
        return $this->db->transaction(function (Transaction $t) use ($id, $text, $fromCustomer) {
            $snap = $t->get('support_tickets/' . $id);
            if (!$snap) {
                throw new FinanceException('missing', 'This ticket no longer exists.');
            }
            if (($snap['data']['status'] ?? '') === 'closed') {
                throw new FinanceException('state_changed', 'This ticket is closed.');
            }
            $t->create('support_tickets/' . $id . '/messages/' . Money::random(20), ['authorId' => $fromCustomer ? ($snap['data']['userId'] ?? '') : $this->admin(),
                'authorEmail' => $fromCustomer ? '' : (string)(Auth::user()->email ?? ''), 'recordedBy' => $this->admin(),
                'authorType' => $fromCustomer ? 'customer' : 'admin', 'text' => $text, 'createdAt' => Value::serverTimestamp()]);
            $changes = ['updatedAt' => Value::serverTimestamp()];
            if (($snap['data']['status'] ?? '') === 'open' && !$fromCustomer) {
                $changes['status'] = 'in_progress';
            }
            $t->update('support_tickets/' . $id, $changes);
            Audit::add($t, 'ticket.message', 'support_tickets', $id, null, ['authorType' => $fromCustomer ? 'customer' : 'admin'] + array_diff_key($changes, ['updatedAt' => 1]));
        });
    }

    /** Changes the priority, category or assignee. */
    public function update($id, array $in, $assignee = null)
    {
        $changes = self::check(array_filter($in, function ($v) { return $v !== null && $v !== ''; }), true);
        if ($assignee !== null) {
            $changes['assignedTo'] = $assignee['id'] ?? '';
            $changes['assignedToEmail'] = $assignee['email'] ?? '';
        }
        if (!$changes) {
            return;
        }
        $this->db->transaction(function (Transaction $t) use ($id, $changes) {
            $snap = $t->get('support_tickets/' . $id);
            if (!$snap) {
                throw new FinanceException('missing', 'This ticket no longer exists.');
            }
            $t->update('support_tickets/' . $id, $changes + ['updatedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'ticket.update', 'support_tickets', $id, array_intersect_key($snap['data'], $changes), $changes);
        });
    }

    /**
     * Moves a ticket: resolve (a note for the customer, required), close,
     * or reopen. Returns the ticket.
     */
    public function move($id, $to, $note = '')
    {
        $from = ['resolved' => ['open', 'in_progress'], 'closed' => ['open', 'in_progress', 'resolved'], 'in_progress' => ['resolved', 'closed']];
        if (!isset($from[$to])) {
            throw new FinanceException('invalid', 'Unknown action.');
        }
        $note = trim((string)$note);
        if ($to === 'resolved' && $note === '') {
            throw new FinanceException('invalid', 'Write what was done: the customer receives it.');
        }
        return $this->db->transaction(function (Transaction $t) use ($id, $to, $note, $from) {
            $snap = $t->get('support_tickets/' . $id);
            if (!$snap) {
                throw new FinanceException('missing', 'This ticket no longer exists.');
            }
            $d = $snap['data'];
            if (!in_array($d['status'] ?? '', $from[$to], true)) {
                throw new FinanceException('state_changed', 'This ticket is now "' . ($d['status'] ?? '?') . '".');
            }
            $changes = ['status' => $to, 'updatedAt' => Value::serverTimestamp()];
            if ($to === 'resolved') {
                $changes += ['resolvedAt' => Value::serverTimestamp(), 'resolution' => $note, 'resolvedBy' => $this->admin()];
            } elseif ($to === 'closed') {
                $changes['closedAt'] = Value::serverTimestamp();
                if (empty($d['resolvedAt'])) {
                    $changes['resolvedAt'] = Value::serverTimestamp();
                }
            } else {
                $changes += ['resolvedAt' => Value::delete(), 'reopenedAt' => Value::serverTimestamp()];
            }
            if ($note !== '') {
                $t->create('support_tickets/' . $id . '/messages/' . Money::random(20), ['authorId' => $this->admin(), 'authorEmail' => (string)(Auth::user()->email ?? ''),
                    'authorType' => 'admin', 'text' => ($to === 'resolved' ? '[' . __('Resolved') . '] ' : '') . $note, 'createdAt' => Value::serverTimestamp()]);
            }
            $t->update('support_tickets/' . $id, $changes);
            Audit::add($t, 'ticket.' . $to, 'support_tickets', $id, ['status' => $d['status']], ['status' => $to], $note);
            return array_merge($d, ['status' => $to]);
        });
    }
}
