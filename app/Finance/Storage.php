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
