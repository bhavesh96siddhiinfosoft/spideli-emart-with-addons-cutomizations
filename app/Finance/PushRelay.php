<?php

namespace App\Finance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * The push relay (spec 1.6): the customer apps and website POST a push here
 * with the signed-in user's Firebase ID token instead of holding an FCM /
 * service-account key. The relay checks the caller and the message, limits
 * each account, and sends it with the panel's own key.
 *
 * handle() returns [httpStatus, body, headers]; every body is
 * { ok: true, messageId[, truncated] } or { ok: false, error, message }.
 */
class PushRelay
{
    public const MAX_BODY = 16384;
    private const MAX_DATA = 4096;
    private const PER_MINUTE = 60;
    private const PER_DAY = 3000;

    private $db;
    private $tokens;
    private $auth;
    private $fcm;

    public function __construct(Firestore $db, IdTokens $tokens = null, AuthAccounts $auth = null, Fcm $fcm = null)
    {
        $this->db = $db;
        $this->tokens = $tokens ?: new IdTokens($db->projectId());
        $this->auth = $auth ?: new AuthAccounts($db);
        $this->fcm = $fcm ?: new Fcm($db->projectId());
    }

    private static function fail($status, $error, $message, array $headers = [])
    {
        return [$status, ['ok' => false, 'error' => $error, 'message' => $message], $headers];
    }

    /** @param string|null $authorization the Authorization header; $raw the request body. */
    public function handle($authorization, $raw)
    {
        if (strlen((string)$raw) > self::MAX_BODY) {
            return self::fail(413, 'payload_too_large', 'The request is larger than 16 KB.');
        }

        // 1. the caller
        if (!preg_match('/^Bearer\s+(\S+)$/i', trim((string)$authorization), $m)) {
            return self::fail(401, 'unauthenticated', 'A Firebase ID token is required (Authorization: Bearer).');
        }
        try {
            $claims = $this->tokens->verify($m[1]);
        } catch (FinanceException $e) {
            if ($e->reason === 'unavailable') {
                return self::fail(503, 'unavailable', 'The token could not be checked now. Try again.');
            }
            return self::fail(401, $e->reason, $e->getMessage());
        }
        $uid = $claims['sub'];
        $provider = is_object($claims['firebase'] ?? null) ? ($claims['firebase']->sign_in_provider ?? '') : (($claims['firebase']['sign_in_provider'] ?? ''));
        if ($provider === 'anonymous') {
            return self::fail(403, 'user_not_found', 'Anonymous accounts cannot send pushes.');
        }
        $user = $this->db->get('users/' . $uid);
        if (!$user || !empty($user['data']['isDeleted'])) {
            return self::fail(403, 'user_not_found', 'No user profile for this account.');
        }
        $account = Cache::remember('push_relay_account_' . $uid, 60, function () use ($uid) {
            return $this->auth->lookup($uid);
        });
        if ($account === null) {
            Cache::forget('push_relay_account_' . $uid);
            return self::fail(503, 'unavailable', 'The account could not be checked now. Try again.');
        }
        if ($account === 'missing') {
            return self::fail(403, 'user_not_found', 'This account no longer exists.');
        }
        if (!empty($account['disabled'])) {
            return self::fail(403, 'user_disabled', 'This account is disabled.');
        }

        // 2. the message
        $req = json_decode((string)$raw, true);
        if (!is_array($req) || array_values($req) === $req && $req !== []) {
            return self::fail(400, 'invalid_argument', 'The body must be a JSON object.');
        }
        $token = $req['token'] ?? null;
        $topic = $req['topic'] ?? null;
        if (($token === null || $token === '') === ($topic === null || $topic === '')) {
            return self::fail(400, 'invalid_argument', 'Send exactly one of token or topic.');
        }
        if ($topic !== null && $topic !== '') {
            if (($user['data']['role'] ?? '') !== 'admin') {
                return self::fail(403, 'topic_forbidden', 'Only admins may send to a topic.');
            }
            if (!is_string($topic) || !preg_match('/^[A-Za-z0-9\-_.~%]{1,900}$/', $topic)) {
                return self::fail(400, 'invalid_argument', 'Invalid topic.');
            }
        } elseif (!is_string($token) || !preg_match('/^[A-Za-z0-9_\-:.]{32,4096}$/', $token)) {
            return self::fail(400, 'invalid_token', 'Invalid device token.');
        }
        foreach (['title', 'body', 'kind'] as $f) {
            if (isset($req[$f]) && !is_string($req[$f])) {
                return self::fail(400, 'invalid_argument', "$f must be a string.");
            }
        }
        $title = (string)($req['title'] ?? '');
        $body = (string)($req['body'] ?? '');
        $truncated = [];
        if (mb_strlen($title) > 200) {
            $title = mb_substr($title, 0, 200);
            $truncated[] = 'title';
        }
        if (mb_strlen($body) > 1000) {
            $body = mb_substr($body, 0, 1000);
            $truncated[] = 'body';
        }
        if (isset($req['data']) && !is_array($req['data'])) {
            return self::fail(400, 'invalid_argument', 'data must be an object.');
        }
        $data = [];
        foreach ((array)($req['data'] ?? []) as $k => $v) {
            $k = (string)$k;
            $lk = strtolower($k);
            if ($v === null || in_array($lk, ['from', 'notification', 'message_type', 'collapse_key'], true)
                || strpos($lk, 'google') === 0 || strpos($lk, 'gcm') === 0) {
                continue;
            }
            $data[$k] = is_array($v) || is_object($v) ? json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : (is_bool($v) ? ($v ? 'true' : 'false') : (string)$v);
        }
        if ($title === '' && $body === '' && !$data) {
            return self::fail(400, 'invalid_argument', 'Send a title, a body or data.');
        }
        if (strlen(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) > self::MAX_DATA) {
            return self::fail(413, 'payload_too_large', 'data is larger than 4 KB.');
        }
        $kind = substr((string)($req['kind'] ?? ($data['type'] ?? '')), 0, 100);
        $channel = (string)($req['android']['channelId'] ?? 'high_importance_channel');
        $androidSound = (string)($req['android']['sound'] ?? 'default');
        $apnsSound = (string)($req['apns']['sound'] ?? 'default');
        foreach ([$channel, $androidSound, $apnsSound] as $v) {
            if (!preg_match('/^[A-Za-z0-9_.\-]{1,100}$/', $v)) {
                return self::fail(400, 'invalid_argument', 'Invalid channel or sound.');
            }
        }

        // 3. the account's limits
        $wait = $this->rateLimit($uid);
        if ($wait > 0) {
            $this->log($uid, $kind, $token ?: $topic, $title, $body, $data, 'rate_limited');
            return self::fail(429, 'rate_limited', 'Too many pushes from this account. Try again later.', ['Retry-After' => (string)$wait]);
        }

        // 4. send
        $message = ($token ? ['token' => $token] : ['topic' => $topic]) + ($data ? ['data' => $data] : []);
        $hasNotification = $title !== '' || $body !== '';
        if ($hasNotification) {
            $message['notification'] = array_filter(['title' => $title, 'body' => $body], 'strlen');
            $message['android'] = ['priority' => 'high', 'notification' => ['channel_id' => $channel, 'sound' => $androidSound]];
            $message['apns'] = ['headers' => ['apns-priority' => '10'], 'payload' => ['aps' => ['sound' => $apnsSound, 'content-available' => 1]]];
        } else {
            // data only: delivered silently to the app
            $message['android'] = ['priority' => 'high'];
            $message['apns'] = ['headers' => ['apns-priority' => '5'], 'payload' => ['aps' => ['content-available' => 1]]];
        }
        $sent = $this->fcm->send($message);
        $result = $this->result($sent, $token);
        $this->log($uid, $kind, $token ?: $topic, $title, $body, $data, $sent['ok'] ? 'sent' : $result[1]['error']);
        if ($sent['ok']) {
            return [200, ['ok' => true, 'messageId' => $sent['messageId']] + ($truncated ? ['truncated' => $truncated] : []), []];
        }
        return $result;
    }

    /** FCM's answer as the relay's response; a dead token is cleared from the profiles holding it. */
    private function result(array $sent, $token)
    {
        if ($sent['ok']) {
            return [200, [], []];
        }
        $code = $sent['errorCode'] ?: $sent['status'];
        if ($code === 'UNREGISTERED' || ($sent['http'] === 404 && $token)) {
            if ($token) {
                $this->clearToken($token);
            }
            return self::fail(404, 'unregistered', 'This device token is no longer registered.');
        }
        if ($code === 'SENDER_ID_MISMATCH' || ($code === 'INVALID_ARGUMENT' && stripos($sent['message'], 'token') !== false)) {
            return self::fail(400, 'invalid_token', 'Invalid device token.');
        }
        if ($code === 'INVALID_ARGUMENT') {
            return self::fail(400, 'invalid_argument', 'FCM refused the message.');
        }
        if (in_array($code, ['UNAVAILABLE', 'QUOTA_EXCEEDED'], true) || $sent['http'] === 0 || $sent['http'] === 503 || $sent['http'] === 429) {
            return self::fail(503, 'unavailable', 'FCM is unavailable. Try again once.', ['Retry-After' => '30']);
        }
        return self::fail(500, 'internal', 'The push could not be sent.');
    }

    /** fcmToken "" on every profile still holding a dead token. */
    private function clearToken($token)
    {
        try {
            $writes = [];
            foreach ($this->db->query('users', [['fcmToken', '==', $token]], [], 20) as $u) {
                $writes[] = $this->db->write('update', 'users/' . $u['id'], ['fcmToken' => '']);
            }
            if ($writes) {
                $this->db->batch($writes);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * push_rate_limits/{uid} (server only): the send times of the last minute
     * and the count of the current UTC day. Returns 0 when the push may go,
     * else the seconds to wait.
     */
    private function rateLimit($uid)
    {
        return $this->db->transaction(function (Transaction $t) use ($uid) {
            $nowMs = (int)round(microtime(true) * 1000);
            $day = gmdate('Y-m-d', intdiv($nowMs, 1000));
            $doc = $t->get('push_rate_limits/' . $uid);
            $d = $doc ? $doc['data'] : [];
            $recent = array_values(array_filter(array_map('intval', (array)($d['minute'] ?? [])), function ($ms) use ($nowMs) { return $ms > $nowMs - 60000; }));
            $dayCount = ($d['day'] ?? '') === $day ? (int)($d['dayCount'] ?? 0) : 0;
            if ($dayCount >= self::PER_DAY) {
                return max(1, strtotime($day . ' 00:00:00 UTC +1 day') - intdiv($nowMs, 1000));
            }
            if (count($recent) >= self::PER_MINUTE) {
                sort($recent);
                return max(1, (int)ceil(($recent[0] + 60000 - $nowMs) / 1000));
            }
            $recent[] = $nowMs;
            $t->set('push_rate_limits/' . $uid, ['minute' => $recent, 'day' => $day, 'dayCount' => $dayCount + 1, 'updatedAt' => Value::serverTimestamp()]);
            return 0;
        });
    }

    /** Spec 1.6 logging: the uid, kind, a short hash of the token, lengths and key names - never a value. */
    private function log($uid, $kind, $target, $title, $body, array $data, $result)
    {
        Log::info('push_relay', ['uid' => $uid, 'kind' => $kind, 'target' => substr(hash('sha256', (string)$target), 0, 10),
            'titleLength' => mb_strlen($title), 'bodyLength' => mb_strlen($body), 'dataKeys' => array_keys($data), 'result' => $result]);
    }
}
