<?php

namespace App\Http\Controllers;

use App\Finance\Fcm;
use App\Finance\Firestore;
use App\Finance\Value;

/**
 * Points 90 / 91 - can a driver's phone receive push notifications?
 *
 * status(): FCM checks the driver's saved token without delivering anything
 *   ("validate only"): working, no_token, expired (the app was reinstalled or
 *   its data cleared and the new token never saved), or unknown.
 * test():   sends one real "test notification" to the driver's phone, when the
 *   admin clicks the button.
 * An expired token is removed from the driver (users.fcmToken = "", with
 * fcmTokenRemovedAt), as the dispatch function does, so no order waits on a
 * phone that cannot be reached; the driver app saves a fresh one on its next start.
 */
class DriverPushController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function status($id)
    {
        return response()->json($this->check($id, false));
    }

    public function test($id)
    {
        return response()->json($this->check($id, true));
    }

    /** Overridden by the tests (an in-memory Firestore, a fake FCM). */
    protected function db()
    {
        return new Firestore();
    }

    protected function fcm($projectId)
    {
        return new Fcm($projectId);
    }

    public function check($id, $deliver)
    {
        try {
            $db = $this->db();
            $doc = $db->get('users/' . $id);
            if (!$doc || ($doc['data']['role'] ?? '') !== 'driver') {
                return ['status' => 'unknown', 'reason' => 'not_a_driver'];
            }
            $token = trim((string)($doc['data']['fcmToken'] ?? ''));
            if ($token === '') {
                return ['status' => 'no_token', 'removedAt' => $this->when($doc['data']['fcmTokenRemovedAt'] ?? null)];
            }
            $message = ['token' => $token];
            if ($deliver) {
                $message += [
                    'notification' => ['title' => trans('lang.driver_push_test_title'), 'body' => trans('lang.driver_push_test_body')],
                    'data' => ['type' => 'test'],
                    'android' => ['priority' => 'high', 'notification' => ['sound' => 'default']],
                ];
            } else {
                $message['notification'] = ['title' => 'check', 'body' => 'check'];
            }
            $res = $this->fcm($db->projectId())->send($message, !$deliver);
            if ($res['ok']) {
                return ['status' => 'working', 'sent' => $deliver];
            }
            if (($res['http'] ?? 0) == 404 || in_array($res['errorCode'] ?? '', ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                $db->batch([$db->write('update', 'users/' . $id, ['fcmToken' => '', 'fcmTokenRemovedAt' => Value::serverTimestamp()])]);
                return ['status' => 'expired', 'removed' => true];
            }
            return ['status' => 'unknown', 'reason' => (string)($res['status'] ?? '')];
        } catch (\Throwable $e) {
            report($e);
            return ['status' => 'unknown', 'reason' => 'error'];
        }
    }

    private function when($v)
    {
        return $v instanceof \DateTimeInterface ? $v->format('Y-m-d H:i') : null;
    }
}
