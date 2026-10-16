<?php

namespace App\Http\Controllers;

use App\Finance\FinanceException;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\Notifier;
use App\Finance\Reports;
use App\Finance\Scope;
use App\Finance\Support;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Spideli Finance, admin Phase D4 (spec 6.15, 6.16): support tickets (staff
 * only, with SLA timers) and the reports page with CSV export.
 */
class FinanceSupportController extends Controller
{
    public const TABS = [
        'active' => ['open', 'in_progress'],
        'resolved' => ['resolved'],
        'closed' => ['closed'],
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

    /** Panel admins a ticket can be assigned to: "admin:{id}" => e-mail. */
    private static function admins()
    {
        $out = [];
        foreach (User::query()->orderBy('email')->get(['id', 'email', 'name']) as $u) {
            $out['admin:' . $u->id] = $u->email ?: $u->name;
        }
        return $out;
    }

    private static function assignee($value)
    {
        $value = (string)$value;
        if ($value === '') {
            return ['id' => '', 'email' => ''];
        }
        $admins = self::admins();
        if (!isset($admins[$value])) {
            throw new FinanceException('invalid', 'Choose an admin of the panel.');
        }
        return ['id' => $value, 'email' => $admins[$value]];
    }

    /** A customer by uid, e-mail or phone; null when none. */
    private function findCustomer($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $db = $this->db();
        if (strpos($value, '@') !== false) {
            $found = $db->query('users', [['email', '==', $value]], [], 1);
        } elseif (preg_match('/^\+?[0-9 ]{6,}$/', $value)) {
            $phone = str_replace(' ', '', $value);
            $found = $db->query('users', [['phoneNumber', '==', $phone]], [], 1) ?: $db->query('users', [['phoneNumber', '==', '+' . ltrim($phone, '+')]], [], 1);
        } else {
            $doc = $db->get('users/' . $value);
            $found = $doc ? [$doc] : [];
        }
        return $found ? $found[0] : null;
    }

    /* ============================================================ tickets */

    public function tickets(Request $request)
    {
        $db = $this->db();
        $tab = $request->input('tab', 'active');
        $mine = $request->boolean('mine');
        abort_unless(isset(self::TABS[$tab]), 404);
        $rows = Scope::filter($db, $db->query('support_tickets', [['status', 'in', self::TABS[$tab]]], [], 2000), 'userId');
        if ($mine) {
            $rows = array_values(array_filter($rows, function ($r) { return ($r['data']['assignedTo'] ?? '') === 'admin:' . auth()->id(); }));
        }
        $now = new \DateTimeImmutable();
        usort($rows, function ($a, $b) use ($now) {
            $x = Support::sla($a['data'], $now);
            $y = Support::sla($b['data'], $now);
            return ($x['due'] ?? $now) <=> ($y['due'] ?? $now);
        });
        return view('finance.support.tickets', [
            'tab' => $tab, 'mine' => $mine, 'rows' => $rows, 'now' => $now,
            'names' => $this->names(array_map(function ($r) { return $r['data']['userId'] ?? null; }, $rows)),
            'canManage' => $this->can('finance.tickets.manage'),
        ]);
    }

    public function ticketForm(Request $request)
    {
        $customer = $this->findCustomer($request->input('userId'));
        return view('finance.support.ticket_new', ['customer' => $customer, 'admins' => self::admins(),
            'prefill' => ['user' => $customer['id'] ?? $request->input('userId'), 'relatedRef' => $request->input('relatedRef'), 'category' => $request->input('category')]]);
    }

    public function ticketStore(Request $request)
    {
        $db = $this->db();
        try {
            $customer = $this->findCustomer($request->input('user'));
            if (!$customer || !Scope::allows($db, ['id' => $customer['id'], 'data' => ['userId' => $customer['id']]], 'userId')) {
                throw new FinanceException('invalid', 'No customer matches: enter their id, e-mail or phone.');
            }
            $id = (new Support($db))->open($customer['id'], $request->all(), $request->filled('assignedTo') ? self::assignee($request->input('assignedTo')) : null);
            return redirect()->route('finance.tickets.show', $id)->with('success', __('Ticket opened.'));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    public function ticket($id)
    {
        $db = $this->db();
        $doc = $db->get('support_tickets/' . $id);
        abort_unless($doc && Scope::allows($db, $doc, 'userId'), 404);
        $messages = $db->query('support_tickets/' . $id . '/messages', [], [], 500);
        usort($messages, function ($a, $b) {
            $x = $a['data']['createdAt'] ?? null;
            $y = $b['data']['createdAt'] ?? null;
            return ($x instanceof \DateTimeInterface ? $x->getTimestamp() : 0) <=> ($y instanceof \DateTimeInterface ? $y->getTimestamp() : 0);
        });
        $uid = $doc['data']['userId'] ?? '';
        return view('finance.support.ticket', [
            'id' => $id, 't' => $doc['data'], 'messages' => $messages, 'sla' => Support::sla($doc['data'], new \DateTimeImmutable()),
            'names' => $this->names([$uid]), 'customer' => $db->get('users/' . $uid), 'admins' => self::admins(),
            'canManage' => $this->can('finance.tickets.manage'),
        ]);
    }

    public function ticketAction(Request $request, $id, $action)
    {
        $db = $this->db();
        $support = new Support($db);
        try {
            $doc = $db->get('support_tickets/' . $id);
            abort_unless($doc && Scope::allows($db, $doc, 'userId'), 404);
            $text = trim((string)$request->input('text'));
            switch ($action) {
                case 'message':
                    $support->message($id, $text, $request->input('author') === 'customer');
                    return back()->with('success', __('Message added.'));
                case 'update':
                    $support->update($id, ['priority' => $request->input('priority'), 'category' => $request->input('category')],
                        $request->has('assignedTo') ? self::assignee($request->input('assignedTo')) : null);
                    return back()->with('success', __('Ticket updated.'));
                case 'resolve':
                    $t = $support->move($id, 'resolved', $text);
                    $note = (new Notifier($db))->send($t['userId'] ?? '', 'support_ticket_resolved', ['subject' => $t['subject'] ?? '', 'resolution' => $text], ['ticketId' => $id]);
                    return back()->with('success', __('Ticket resolved.') . ' ' . __('Notification:') . ' ' . $note);
                case 'close':
                    $support->move($id, 'closed', $text);
                    return back()->with('success', __('Ticket closed.'));
                case 'reopen':
                    $support->move($id, 'in_progress', $text);
                    return back()->with('success', __('Ticket reopened.'));
            }
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    /* ============================================================ reports */

    /** The period of the form: [from 00:00, to 24:00) in the panel's timezone; the last 30 days by default. */
    private static function period(Request $request)
    {
        $tz = new \DateTimeZone(config('app.timezone'));
        $to = \DateTimeImmutable::createFromFormat('!Y-m-d', (string)$request->input('to'), $tz) ?: new \DateTimeImmutable('today', $tz);
        $from = \DateTimeImmutable::createFromFormat('!Y-m-d', (string)$request->input('from'), $tz) ?: $to->modify('-29 days');
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        return [$from, $to->modify('+1 day')];
    }

    public function reports(Request $request)
    {
        [$from, $to] = self::period($request);
        $r = new Reports($this->db(), $from, $to);
        $reports = [];
        foreach (Reports::SECTIONS as $s) {
            try {
                $reports[$s] = $r->build($s);
            } catch (\Throwable $e) {
                report($e);
                $reports[$s] = ['title' => $s, 'columns' => [], 'rows' => [], 'note' => $this->failure($e)];
            }
        }
        return view('finance.support.reports', ['reports' => $reports, 'from' => $from, 'to' => $to->modify('-1 day'), 'truncated' => $r->truncated]);
    }

    public function reportExport(Request $request, $section)
    {
        abort_unless(in_array($section, Reports::SECTIONS, true), 404);
        [$from, $to] = self::period($request);
        $report = (new Reports($this->db(), $from, $to))->build($section);
        $name = 'spideli-' . $section . '-' . $from->format('Ymd') . '-' . $to->modify('-1 day')->format('Ymd') . '.csv';
        return response(Reports::csv($report), 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="' . $name . '"']);
    }
}
