<?php

namespace App\Finance;

use Google\Client as Google_Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Customer notifications for the panel's decisions (spec 5.4): the
 * dynamic_notification template of the event type, filled with its
 * placeholders, sent as an FCM push to users/{uid}.fcmToken and kept as a
 * Notification Center record users/{uid}/notifications/{id}.
 *
 * Best effort and after the decision is saved: a missing template, token or
 * a refused push never undoes the decision; the result says what happened.
 */
class Notifier
{
    /** Template type => preference category (financeNotificationPrefs); null = always sent. */
    private const CATEGORIES = [
        'kyc_approved' => null, 'kyc_rejected' => null, 'kyc_additional_info_required' => null,
        'wallet_withdrawal_completed' => 'payments', 'wallet_withdrawal_failed' => 'payments',
        'transfer_sent' => 'payments', 'transfer_reversed' => 'payments', 'transfer_failed' => 'payments',
        'bill_paid' => 'payments', 'bill_token' => 'payments', 'bill_reversed' => 'payments', 'bill_failed' => 'payments',
    ];

    private $db;

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

            $category = array_key_exists($type, self::CATEGORIES) ? self::CATEGORIES[$type]
                : (strpos($type, 'loan_') === 0 ? 'loans' : (preg_match('/^(savings_|tontine_)/', $type) ? 'groups'
                : (strpos($type, 'investment_') === 0 ? 'investments' : (strpos($type, 'insurance_') === 0 ? 'insurance' : 'payments'))));
            $pushAllowed = $category === null || (($u['financeNotificationPrefs']['push'][$category] ?? true) !== false);

            $data = ['type' => $type] + array_map('strval', $data);
            $this->db->batch([$this->db->write('create', 'users/' . $uid . '/notifications/' . Audit::newId(), [
                'type' => $type, 'title' => $title, 'body' => $body, 'category' => 'account', 'source' => 'finance',
                'read' => false, 'createdAt' => Value::serverTimestamp(), 'data' => $data ?: Value::map([]),
            ])]);

            if (!$pushAllowed) {
                return 'saved (push turned off by the customer)';
            }
            $token = (string)($u['fcmToken'] ?? '');
            if ($token === '') {
                return 'saved (no device to push to)';
            }
            return $this->push($token, $title, $body, $data) ? 'sent' : 'saved (push refused)';
        } catch (\Throwable $e) {
            report($e);
            return 'not sent';
        }
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
        $access = Cache::remember('finance_fcm_token', 3000, function () {
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            return $client->fetchAccessTokenWithAssertion()['access_token'] ?? '';
        });
        $res = Http::withToken($access)->timeout(15)->post(
            'https://fcm.googleapis.com/v1/projects/' . $this->db->projectId() . '/messages:send',
            ['message' => [
                'token' => $token,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => $data,
                'android' => ['priority' => 'high', 'notification' => ['channel_id' => 'high_importance_channel', 'sound' => 'default']],
                'apns' => ['headers' => ['apns-priority' => '10'], 'payload' => ['aps' => ['sound' => 'default', 'content-available' => 1]]],
            ]]
        );
        return $res->successful();
    }
}
