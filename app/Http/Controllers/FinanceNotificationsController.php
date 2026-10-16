<?php

namespace App\Http\Controllers;

use App\Finance\Audit;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\NotificationTypes;
use App\Finance\Notifier;
use App\Finance\Value;
use App\Mail\FinanceNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * Finance notifications (spec 6.14): every notification type with who sends
 * it, its channels and whether its dynamic_notification template exists;
 * "Create missing templates" with the default English text; a test send to
 * a phone / e-mail address the admin types.
 */
class FinanceNotificationsController extends Controller
{
    /** The service of the existing finance templates (the template list filters by it). */
    private const SERVICE_TYPE = 'Multivendor Delivery Service';

    public function __construct()
    {
        $this->middleware('auth');
    }

    private function db()
    {
        return app(Firestore::class);
    }

    /** The live templates by type (the first one of a type, as the notifier uses). */
    private function templates()
    {
        $out = [];
        foreach ($this->db()->query('dynamic_notification', [], [], 1000) as $d) {
            $type = (string)($d['data']['type'] ?? '');
            if ($type !== '' && !isset($out[$type])) {
                $out[$type] = $d;
            }
        }
        return $out;
    }

    private function smsReady()
    {
        $doc = $this->db()->get('settings/SMSGateway');
        $s = $doc ? $doc['data'] : [];
        return ($s['isEnabled'] ?? false) === true && trim((string)($s['apiKey'] ?? '')) !== '';
    }

    public function index()
    {
        $templates = $this->templates();
        $rows = [];
        $missing = 0;
        foreach (NotificationTypes::all() as $type => $k) {
            $t = $templates[$type] ?? null;
            $unknown = [];
            if ($t && $k['by'] !== 'app') {
                $unknown = array_values(array_diff(
                    NotificationTypes::placeholders(($t['data']['subject'] ?? '') . ' ' . ($t['data']['message'] ?? '')),
                    NotificationTypes::placeholders($k['subject'] . ' ' . $k['message'])
                ));
            }
            $missing += $t ? 0 : 1;
            $rows[$k['module']][$type] = $k + [
                'template' => $t ? ['id' => basename($t['path']), 'subject' => $t['data']['subject'] ?? '', 'message' => $t['data']['message'] ?? ''] : null,
                'unknown' => $unknown,
                'fills' => NotificationTypes::placeholders($k['subject'] . ' ' . $k['message']),
            ];
        }
        $perms = json_decode(session('user_permissions') ?? '[]', true) ?: [];
        return view('finance.notifications', [
            'rows' => $rows, 'missing' => $missing,
            'smsReady' => $this->smsReady(),
            'mailReady' => trim((string)config('mail.mailers.smtp.host')) !== '' && config('mail.default') !== 'log',
            'smsOn' => (bool)config('finance.notify_sms', true), 'emailOn' => (bool)config('finance.notify_email', true),
            'canManage' => in_array('finance.notifications.manage', $perms, true),
        ]);
    }

    /** Creates the template of every type that has none, with the default English text. */
    public function createMissing()
    {
        try {
            $db = $this->db();
            $templates = $this->templates();
            $writes = [];
            $created = [];
            foreach (NotificationTypes::all() as $type => $k) {
                if (isset($templates[$type])) {
                    continue;
                }
                $id = Audit::newId();
                $writes[] = $db->write('create', 'dynamic_notification/' . $id, [
                    'id' => $id, 'type' => $type, 'subject' => $k['subject'], 'message' => $k['message'],
                    'service_type' => self::SERVICE_TYPE, 'createdAt' => Value::serverTimestamp(),
                ]);
                $created[] = $type;
            }
            if (!$created) {
                return back()->with('success', __('Every finance template already exists.'));
            }
            $writes[] = Audit::write($db, 'notification_templates.create_missing', 'dynamic_notification', '', [], ['types' => $created]);
            $db->batch($writes);
            return back()->with('success', __('Templates created:') . ' ' . implode(', ', $created) . '. ' . __('Translate or edit them in Dynamic Notification.'));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $e instanceof FirestoreError ? 'Firebase refused the request (' . $e->status . '). Nothing was changed.' : 'Something went wrong. Nothing was changed.');
        }
    }

    /**
     * Sends one type, filled with sample values, to a phone number and / or
     * an e-mail address the admin types. No customer record is written.
     */
    public function test(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|max:80',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
        ]);
        $kind = NotificationTypes::get($data['type']);
        if (!$kind || (trim((string)($data['phone'] ?? '')) === '' && trim((string)($data['email'] ?? '')) === '')) {
            return back()->withInput()->with('error', __('Choose a type and enter a phone number or an e-mail address.'));
        }
        $t = $this->templates()[$data['type']] ?? null;
        $subject = $t ? (string)($t['data']['subject'] ?? '') : $kind['subject'];
        $message = $t ? (string)($t['data']['message'] ?? '') : $kind['message'];
        $samples = [];
        foreach (NotificationTypes::placeholders($subject . ' ' . $message) as $p) {
            $samples[$p] = self::sample($p);
        }
        $title = Notifier::fill($subject, $samples);
        $body = Notifier::fill($message, $samples);

        $done = [];
        if (trim((string)($data['phone'] ?? '')) !== '') {
            $doc = $this->db()->get('settings/SMSGateway');
            $s = $doc ? $doc['data'] : [];
            $to = preg_replace('/\D+/', '', $data['phone']);
            if (!$this->smsReady()) {
                $done[] = __('SMS: the SMS gateway is off (Settings > SMS gateway).');
            } else {
                try {
                    $res = Http::timeout(15)->get(rtrim(trim((string)($s['apiUrl'] ?? '')) ?: 'https://obitsms.com/api/v2', '/') . '/bulksms', [
                        'key_api' => $s['apiKey'], 'sender' => $s['senderId'] ?? '', 'destination' => $to,
                        'message' => mb_substr(trim($title) === '' ? $body : $title . ': ' . $body, 0, 320),
                    ]);
                    $json = $res->json();
                    $done[] = is_array($json) && ($json['success'] ?? false) === true ? __('SMS sent to') . ' ' . $to . '.'
                        : __('SMS refused by the gateway:') . ' ' . (is_array($json) ? ($json['message'] ?? '') : mb_substr($res->body(), 0, 120));
                } catch (\Throwable $e) {
                    $done[] = __('SMS: the gateway could not be reached.');
                }
            }
        }
        if (trim((string)($data['email'] ?? '')) !== '') {
            try {
                Mail::to($data['email'])->send(new FinanceNotice($title, $body));
                $done[] = __('E-mail sent to') . ' ' . $data['email'] . '.';
            } catch (\Throwable $e) {
                report($e);
                $done[] = __('E-mail not sent: check the mail settings of the panel.');
            }
        }
        return back()->withInput()->with('success', implode(' ', $done));
    }

    private static function sample($placeholder)
    {
        $samples = ['amount' => '10 000 XAF', 'reason' => 'test reason', 'last4' => '4242', 'token' => '1234-5678-9012', 'units' => '12.5',
            'navPerUnit' => '1 000 XAF', 'score' => '80', 'level' => 'full'];
        if (isset($samples[$placeholder])) {
            return $samples[$placeholder];
        }
        if (preg_match('/(Date|At|Until|Start|End|expiry|time)$/i', $placeholder)) {
            return (new \DateTimeImmutable('+7 days'))->format('d/m/Y');
        }
        return '[' . $placeholder . ']';
    }
}
