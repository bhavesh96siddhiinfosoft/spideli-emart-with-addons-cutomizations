<?php

namespace App\Finance;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Verifies a Firebase Authentication ID token (the signed-in customer's
 * token sent by the apps / website) as Google documents it: RS256 signed by
 * one of the securetoken public certificates, aud = the project id,
 * iss = https://securetoken.google.com/{project}, not expired, issued in the
 * past, with a uid (sub). Returns the claims or throws FinanceException with
 * reason invalid_id_token / id_token_expired.
 */
class IdTokens
{
    private const CERTS = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    private $projectId;
    private $certs;

    /** $certs: [kid => PEM certificate] for tests; normally fetched from Google and cached. */
    public function __construct($projectId, array $certs = null)
    {
        $this->projectId = (string)$projectId;
        $this->certs = $certs;
    }

    private function certs()
    {
        if ($this->certs !== null) {
            return $this->certs;
        }
        $cached = Cache::get('firebase_id_token_certs');
        if (is_array($cached) && $cached) {
            return $cached;
        }
        $res = Http::timeout(10)->get(self::CERTS);
        $certs = $res->successful() ? (array)$res->json() : [];
        if (!$certs) {
            throw new FinanceException('unavailable', 'Google certificates unavailable');
        }
        // Keep them as long as Google says (Cache-Control max-age), at most 6 hours.
        preg_match('/max-age=(\d+)/', (string)$res->header('Cache-Control'), $m);
        Cache::put('firebase_id_token_certs', $certs, min(21600, max(60, (int)($m[1] ?? 3600))));
        return $certs;
    }

    public function verify($idToken)
    {
        $idToken = (string)$idToken;
        if (substr_count($idToken, '.') !== 2) {
            throw new FinanceException('invalid_id_token', 'Not a Firebase ID token.');
        }
        $keys = [];
        foreach ($this->certs() as $kid => $pem) {
            $keys[$kid] = new Key($pem, 'RS256');
        }
        $previous = JWT::$leeway;
        JWT::$leeway = 60;          // clock skew between the device / Google and this server
        try {
            $claims = (array)JWT::decode($idToken, $keys);
        } catch (ExpiredException $e) {
            throw new FinanceException('id_token_expired', 'The ID token has expired.');
        } catch (\Throwable $e) {
            throw new FinanceException('invalid_id_token', 'The ID token is not valid.');
        } finally {
            JWT::$leeway = $previous;
        }
        $now = time();
        if (($claims['aud'] ?? null) !== $this->projectId
            || ($claims['iss'] ?? null) !== 'https://securetoken.google.com/' . $this->projectId
            || !is_string($claims['sub'] ?? null) || $claims['sub'] === '' || strlen($claims['sub']) > 128
            || (int)($claims['auth_time'] ?? PHP_INT_MAX) > $now + 60) {
            throw new FinanceException('invalid_id_token', 'The ID token is not for this project.');
        }
        return $claims;
    }
}
