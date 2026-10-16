<?php

namespace App\Finance;

/**
 * Cloud Storage signed links for private customer files (KYC documents,
 * spec 6.4): a V4 signed GET URL, valid at most 5 minutes, signed with the
 * panel's service account. The file is never downloaded to the panel's disk.
 */
class Storage
{
    private const CREDENTIALS = 'app/firebase/credentials.json';

    public static function bucket()
    {
        return env('FIREBASE_STORAGE_BUCKET') ?: (json_decode(file_get_contents(storage_path(self::CREDENTIALS)), true)['project_id'] . '.appspot.com');
    }

    /**
     * Uploads a file the admin chose to gs://{bucket}/{path} (fund documents,
     * customer reports). Private: customers read it through the Storage rules,
     * the panel through signed URLs. Returns the stored path.
     */
    public static function upload($localPath, $path, $contentType = 'application/pdf')
    {
        $token = \Illuminate\Support\Facades\Cache::remember('finance_storage_token', 3000, function () {
            $client = new \Google\Client();
            $client->setAuthConfig(storage_path(self::CREDENTIALS));
            $client->addScope('https://www.googleapis.com/auth/devstorage.read_write');
            return $client->fetchAccessTokenWithAssertion()['access_token'] ?? '';
        });
        $url = 'https://storage.googleapis.com/upload/storage/v1/b/' . rawurlencode(self::bucket()) . '/o?uploadType=media&name=' . rawurlencode($path);
        $res = \Illuminate\Support\Facades\Http::withToken($token)->timeout(120)
            ->withBody(file_get_contents($localPath), $contentType)->post($url);
        if (!$res->successful()) {
            throw new FinanceException('upload_failed', 'The file could not be stored (' . $res->status() . ').');
        }
        return $path;
    }

    /** A file name safe for a Storage path: letters, digits, dot, dash, underscore. */
    public static function safeName($name)
    {
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string)$name);
        return trim($name, '-.') ?: 'file.pdf';
    }

    /** A signed URL for gs://{bucket}/{path}, valid $seconds (capped at 300). */
    public static function signedUrl($path, $seconds = 300)
    {
        $key = json_decode(file_get_contents(storage_path(self::CREDENTIALS)), true);
        $seconds = max(1, min(300, (int)$seconds));
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $date = $now->format('Ymd');
        $stamp = $now->format('Ymd\THis\Z');
        $scope = $date . '/auto/storage/goog4_request';
        $host = 'storage.googleapis.com';

        $object = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));
        $resource = '/' . self::bucket() . '/' . $object;

        $query = [
            'X-Goog-Algorithm' => 'GOOG4-RSA-SHA256',
            'X-Goog-Credential' => $key['client_email'] . '/' . $scope,
            'X-Goog-Date' => $stamp,
            'X-Goog-Expires' => (string)$seconds,
            'X-Goog-SignedHeaders' => 'host',
        ];
        ksort($query);
        $canonicalQuery = implode('&', array_map(function ($k, $v) {
            return rawurlencode($k) . '=' . rawurlencode($v);
        }, array_keys($query), $query));

        $canonicalRequest = implode("\n", ['GET', $resource, $canonicalQuery, 'host:' . $host, '', 'host', 'UNSIGNED-PAYLOAD']);
        $stringToSign = implode("\n", ['GOOG4-RSA-SHA256', $stamp, $scope, hash('sha256', $canonicalRequest)]);

        $signature = '';
        if (!openssl_sign($stringToSign, $signature, $key['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new FinanceException('sign_failed', 'Could not sign the file link.');
        }
        return 'https://' . $host . $resource . '?' . $canonicalQuery . '&X-Goog-Signature=' . bin2hex($signature);
    }
}
