<?php

namespace App\Finance;

use Google\Client as Google_Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * A customer's Firebase Authentication account, read with the panel's
 * service account (Identity Toolkit accounts:lookup): whether sign-in is
 * disabled, the two-step (second factor) enrolments, the sign-in methods
 * and the last sign-in (spec 6.3). Read only.
 */
class AuthAccounts
{
    private $db;

    public function __construct(Firestore $db)
    {
        $this->db = $db;
    }

    /**
     * ['disabled' => bool, 'twoStep' => [['kind', 'hint', 'enrolledAt']], 'providers' => [...],
     *  'lastSignIn' => DateTimeImmutable|null, 'created' => DateTimeImmutable|null],
     * 'missing' when there is no sign-in account, or null when Firebase could not be asked.
     */
    public function lookup($uid)
    {
        try {
            $res = Http::withToken($this->token())->timeout(15)->post(
                'https://identitytoolkit.googleapis.com/v1/projects/' . $this->db->projectId() . '/accounts:lookup',
                ['localId' => [(string)$uid]]
            );
            if (!$res->successful()) {
                return null;
            }
            $u = $res->json('users.0');
            if (!is_array($u)) {
                return 'missing';
            }
            $twoStep = [];
            foreach ((array)($u['mfaInfo'] ?? []) as $f) {
                $twoStep[] = [
                    'kind' => isset($f['phoneInfo']) ? 'sms' : (isset($f['totpInfo']) ? 'app' : 'other'),
                    'hint' => isset($f['phoneInfo']) ? self::maskPhone((string)$f['phoneInfo']) : (string)($f['displayName'] ?? ''),
                    'enrolledAt' => self::time($f['enrolledAt'] ?? null),
                ];
            }
            return [
                'disabled' => ($u['disabled'] ?? false) === true,
                'twoStep' => $twoStep,
                'providers' => array_values(array_unique(array_map(function ($p) { return (string)($p['providerId'] ?? ''); }, (array)($u['providerUserInfo'] ?? [])))),
                'lastSignIn' => self::millis($u['lastLoginAt'] ?? null),
                'created' => self::millis($u['createdAt'] ?? null),
            ];
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    private function token()
    {
        return Cache::remember('finance_auth_token', 3000, function () {
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/cloud-platform');
            return $client->fetchAccessTokenWithAssertion()['access_token'] ?? '';
        });
    }

    /** +237652365869 -> ••• 69 */
    private static function maskPhone($phone)
    {
        $digits = preg_replace('/\D+/', '', $phone);
        return '••• ' . substr($digits, -2);
    }

    private static function millis($v)
    {
        return is_numeric($v) ? (new \DateTimeImmutable('@' . intdiv((int)$v, 1000))) : null;
    }

    private static function time($v)
    {
        try {
            return $v ? new \DateTimeImmutable((string)$v) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
