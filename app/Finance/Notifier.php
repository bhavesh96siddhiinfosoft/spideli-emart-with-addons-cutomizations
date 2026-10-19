<?php

namespace App\Finance;

use App\Mail\FinanceNotice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * Customer notifications for the panel's decisions (spec 5.4, 6.14): the
 * dynamic_notification template of the event type, filled with its
 * placeholders, kept as a Notification Center record
 * users/{uid}/notifications/{id} and sent
 *   - as an FCM push to users/{uid}.fcmToken,
 *   - by SMS (the panel's SMS gateway, settings/SMSGateway) for the critical
 *     types, and
 *   - by e-mail (the panel's mail settings) for receipts and alerts,
 * each channel only when the customer's financeNotificationPrefs allow the
 * type's category (security notices always go). The channels of each type
 * are in NotificationTypes.
 *
 * Best effort and after the decision is saved: a missing template, token,
 * phone or a refused send never undoes the decision; the result says what
 * happened.
 */
class Notifier
{
    private $db;
    private $sms = false;

    public function __construct(Firestore $db)
    {
        $this->db = $db;
    }

    /**
     * Sends one event to one customer. $values fill the {placeholders};
     * $data goes with the push (type + ids). Returns a short status string.
     */
    public function send($uid, $type, array $values, array $data = [])
    {
        try {
            $template = $this->db->query('dynamic_notification', [['type', '==', $type]], [], 1);
            if (!$template) {
                return "no \"$type\" template";
            }
            $t = $template[0]['data'];
            $title = self::fill((string)($t['subject'] ?? $t['title'] ?? ''), $values);
            $body = self::fill((string)($t['message'] ?? ''), $values);

            $user = $this->db->get('users/' . $uid);
            if (!$user) {
                return 'customer not found';
            }
            $u = $user['data'];
            $category = NotificationTypes::category($type);
            $kind = NotificationTypes::get($type) ?: ['sms' => false, 'email' => false];

            $data = ['type' => $type] + array_map('strval', $data);
            $this->db->batch([$this->db->write('create', 'users/' . $uid . '/notifications/' . Audit::newId(), [
                'type' => $type, 'title' => $title, 'body' => $body, 'category' => 'account', 'source' => 'finance',
                'read' => false, 'createdAt' => Value::serverTimestamp(), 'data' => $data ?: Value::map([]),
            ])]);

            $result = [$this->pushStatus($u, $category, $title, $body, $data)];
            if ($kind['sms'] && config('finance.notify_sms', true) && NotificationTypes::allowed($u, 'sms', $category)) {
                $result[] = $this->smsStatus($u, $title, $body);
            }
            if ($kind['email'] && config('finance.notify_email', true) && NotificationTypes::allowed($u, 'email', $category)) {
                $result[] = $this->emailStatus($u, $title, $body);
            }
            $left = NotificationTypes::placeholders($title . ' ' . $body);
            if ($left) {
                $result[] = 'the template uses {' . implode('}, {', $left) . '}, which the panel does not fill';
            }
            return implode('; ', $result);
        } catch (\Throwable $e) {
            report($e);
            return 'not sent';
        }
    }

    private function pushStatus(array $u, $category, $title, $body, array $data)
    {
        if (!NotificationTypes::allowed($u, 'push', $category)) {
            return 'saved (push turned off by the customer)';
        }
        $token = (string)($u['fcmToken'] ?? '');
        if ($token === '') {
            return 'saved (no device to push to)';
        }
        try {
            return $this->push($token, $title, $body, $data) ? 'sent' : 'saved (push refused)';
        } catch (\Throwable $e) {
            report($e);
            return 'saved (push refused)';
        }
    }

    private function smsStatus(array $u, $title, $body)
    {
        $settings = $this->smsSettings();
        if (!$settings) {
            return 'no SMS (the SMS gateway is off)';
        }
        $to = self::phone($u);
        if ($to === '') {
            return 'no SMS (no phone number)';
        }
        $text = trim($title) === '' ? $body : $title . ': ' . $body;
        try {
            $res = Http::timeout(15)->get(rtrim(trim((string)($settings['apiUrl'] ?? '')) ?: 'https://obitsms.com/api/v2', '/') . '/bulksms', [
                'key_api' => $settings['apiKey'],
                'sender' => $settings['senderId'] ?? '',
                'destination' => $to,
                'message' => mb_substr($text, 0, 320),
            ]);
            $json = $res->json();
            return is_array($json) && ($json['success'] ?? false) === true ? 'SMS sent' : 'SMS refused by the gateway';
        } catch (\Throwable $e) {
            report($e);
            return 'SMS not sent (gateway unreachable)';
        }
    }

    private function emailStatus(array $u, $title, $body)
    {
        $email = trim((string)($u['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'no e-mail (no address)';
        }
        try {
            Mail::to($email)->send(new FinanceNotice($title, $body));
            return 'e-mail sent';
        } catch (\Throwable $e) {
            report($e);
            return 'e-mail not sent';
        }
    }

    /** settings/SMSGateway when it is switched on and has a key, else null (read once). */
    private function smsSettings()
    {
        if ($this->sms === false) {
            $doc = $this->db->get('settings/SMSGateway');
            $s = $doc ? $doc['data'] : [];
            $this->sms = (($s['isEnabled'] ?? false) === true && trim((string)($s['apiKey'] ?? '')) !== '') ? $s : null;
        }
        return $this->sms;
    }

    /**
     * The customer's number as the gateway wants it: digits only, with the
     * country code (countryCode "+237" + phoneNumber "652365869" =
     * 237652365869). A number saved with its own "+" is used as it is.
     */
    public static function phone(array $u)
    {
        $phone = trim((string)($u['phoneNumber'] ?? ''));
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) < 6) {
            return '';
        }
        if (strpos($phone, '+') === 0) {
            return $digits;
        }
        if (strpos($digits, '00') === 0) {
            return substr($digits, 2);
        }
        $cc = preg_replace('/\D+/', '', (string)($u['countryCode'] ?? ''));
        return $cc === '' ? '' : $cc . ltrim($digits, '0');
    }

    public static function fill($text, array $values)
    {
        foreach ($values as $k => $v) {
            $text = str_replace('{' . $k . '}', (string)$v, $text);
        }
        return $text;
    }

    private function push($token, $title, $body, array $data)
    {
        return (new Fcm($this->db->projectId()))->send([
            'token' => $token,
            'notification' => ['title' => $title, 'body' => $body],
            'data' => $data,
            'android' => ['priority' => 'high', 'notification' => ['channel_id' => 'high_importance_channel', 'sound' => 'default']],
            'apns' => ['headers' => ['apns-priority' => '10'], 'payload' => ['aps' => ['sound' => 'default', 'content-available' => 1]]],
        ])['ok'];
    }
}
