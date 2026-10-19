<?php

namespace App\Finance;

use Google\Client as Google_Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Sends one FCM v1 message with the panel's service account (the key never
 * leaves the server). Used by the Notifier (the panel's own notices) and by
 * the push relay (spec 1.6).
 */
class Fcm
{
    private $projectId;

    public function __construct($projectId)
    {
        $this->projectId = (string)$projectId;
    }

    private function accessToken()
    {
        return Cache::remember('finance_fcm_token', 3000, function () {
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            return $client->fetchAccessTokenWithAssertion()['access_token'] ?? '';
        });
    }

    /**
     * ['ok' => true, 'messageId' => 'projects/../messages/..'] or
     * ['ok' => false, 'http' => status, 'status' => "NOT_FOUND"..., 'errorCode' => "UNREGISTERED"...,
     *  'message' => text] ('http' 0 = FCM could not be reached).
     */
    public function send(array $message)
    {
        try {
            $res = Http::withToken($this->accessToken())->timeout(15)->post(
                'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send', ['message' => $message]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'http' => 0, 'status' => 'UNAVAILABLE', 'errorCode' => '', 'message' => 'FCM unreachable'];
        }
        if ($res->successful()) {
            return ['ok' => true, 'messageId' => (string)$res->json('name')];
        }
        $code = '';
        foreach ((array)$res->json('error.details') as $d) {
            if (!empty($d['errorCode'])) {
                $code = (string)$d['errorCode'];
            }
        }
        return ['ok' => false, 'http' => $res->status(), 'status' => (string)$res->json('error.status'),
                'errorCode' => $code, 'message' => (string)$res->json('error.message')];
    }
}
