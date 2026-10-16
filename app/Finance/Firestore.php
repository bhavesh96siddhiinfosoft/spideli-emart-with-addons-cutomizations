<?php

namespace App\Finance;

use Google\Client as Google_Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Firestore REST client for Spideli Finance, signed in with the panel's
 * service account (storage/app/firebase/credentials.json).
 *
 * Finance needs what FirestoreHelper cannot give: exact types (integers stay
 * integers, maps stay maps, timestamps stay timestamps), transactions,
 * increments and server timestamps. The service account bypasses the security
 * rules, so every caller validates before writing.
 *
 * Values: PHP int -> integerValue, float -> doubleValue, list -> arrayValue,
 * associative array -> mapValue. An empty PHP array is ambiguous: wrap it in
 * Value::map([]) to write an empty map, otherwise it is written as an empty
 * array. Value::serverTimestamp(), Value::increment(n) and Value::timestamp()
 * cover the rest.
 */
class Firestore
{
    private const CREDENTIALS = 'app/firebase/credentials.json';

    private $projectId;

    public function __construct()
    {
        $path = storage_path(self::CREDENTIALS);
        if (!is_file($path)) {
            throw new FinanceException('firebase_credentials_missing', 'The Firebase service account is not configured.');
        }
        $json = json_decode(file_get_contents($path), true);
        $this->projectId = $json['project_id'] ?? env('FIREBASE_PROJECT_ID');
    }

    public function projectId()
    {
        return $this->projectId;
    }

    /** projects/{p}/databases/(default)/documents */
    public function root()
    {
        return 'projects/' . $this->projectId . '/databases/(default)/documents';
    }

    public function name($path)
    {
        return $this->root() . '/' . ltrim($path, '/');
    }

    protected function token()
    {
        return Cache::remember('finance_firestore_token', 3000, function () {
            $client = new Google_Client();
            $client->setAuthConfig(storage_path(self::CREDENTIALS));
            $client->addScope('https://www.googleapis.com/auth/datastore');
            $token = $client->fetchAccessTokenWithAssertion();
            if (empty($token['access_token'])) {
                throw new FinanceException('firebase_auth_failed', 'Could not sign in to Firebase.');
            }
            return $token['access_token'];
        });
    }

    protected function call($method, $url, $body = null)
    {
        $request = Http::withToken($this->token())->timeout(30)->acceptJson();
        $full = 'https://firestore.googleapis.com/v1/' . $url;
        $response = $body === null ? $request->send($method, $full) : $request->send($method, $full, ['json' => $body]);

        if ($response->successful()) {
            return $response->json() ?? [];
        }
        $error = $response->json('error') ?? [];
        $status = $error['status'] ?? (string)$response->status();
        if ($method === 'GET' && $response->status() == 404) {
            return null;
        }
        throw new FirestoreError($status, $error['message'] ?? $response->body(), $response->status());
    }

    /* ------------------------------------------------------------------ reads */

    /**
     * One document as ['id' => .., 'path' => .., 'data' => [..], 'updateTime' => ..], or null.
     */
    public function get($path, $transaction = null)
    {
        $url = $this->name($path) . ($transaction ? '?transaction=' . urlencode($transaction) : '');
        $doc = $this->call('GET', $url);
        return $doc ? $this->decodeDocument($doc) : null;
    }

    /** Several documents by path: [path => document or null]. */
    public function getMany(array $paths)
    {
        $out = array_fill_keys($paths, null);
        foreach (array_chunk(array_values(array_unique($paths)), 100) as $chunk) {
            $rows = $this->call('POST', $this->root() . ':batchGet', [
                'documents' => array_map([$this, 'name'], $chunk),
            ]) ?? [];
            foreach ($rows as $row) {
                if (!empty($row['found'])) {
                    $doc = $this->decodeDocument($row['found']);
                    $out[$doc['path']] = $doc;
                }
            }
        }
        return $out;
    }

    /**
     * A structured query on one collection.
     *
     * $where: [[field, op, value], ...] with op one of == != < <= > >= in array-contains array-contains-any not-in
     * $orderBy: [[field, 'asc'|'desc'], ...]
     */
    public function query($collection, array $where = [], array $orderBy = [], $limit = null, $transaction = null, $startAfter = null)
    {
        $ops = [
            '==' => 'EQUAL', '!=' => 'NOT_EQUAL', '<' => 'LESS_THAN', '<=' => 'LESS_THAN_OR_EQUAL',
            '>' => 'GREATER_THAN', '>=' => 'GREATER_THAN_OR_EQUAL', 'in' => 'IN', 'not-in' => 'NOT_IN',
            'array-contains' => 'ARRAY_CONTAINS', 'array-contains-any' => 'ARRAY_CONTAINS_ANY',
        ];
        // A subcollection ("insurance_claims/{id}/events") is queried under its parent document.
        $parent = '';
        if (strpos($collection, '/') !== false) {
            $parent = '/' . substr($collection, 0, strrpos($collection, '/'));
            $collection = substr($collection, strrpos($collection, '/') + 1);
        }
        $q = ['from' => [['collectionId' => $collection]]];

        $filters = [];
        foreach ($where as [$field, $op, $value]) {
            $filters[] = ['fieldFilter' => [
                'field' => ['fieldPath' => self::fieldPath(explode('.', $field))],
                'op' => $ops[$op],
                'value' => Value::encode($value),
            ]];
        }
        if (count($filters) == 1) {
            $q['where'] = $filters[0];
        } elseif ($filters) {
            $q['where'] = ['compositeFilter' => ['op' => 'AND', 'filters' => $filters]];
        }
        if ($orderBy) {
            $q['orderBy'] = array_map(function ($o) {
                return ['field' => ['fieldPath' => $o[0]], 'direction' => strtolower($o[1] ?? 'asc') == 'desc' ? 'DESCENDING' : 'ASCENDING'];
            }, $orderBy);
        }
        if ($startAfter !== null) {
            $q['startAt'] = ['values' => array_map([Value::class, 'encode'], $startAfter), 'before' => false];
        }
        if ($limit) {
            $q['limit'] = (int)$limit;
        }

        $body = ['structuredQuery' => $q];
        if ($transaction) {
            $body['transaction'] = $transaction;
        }
        $rows = $this->call('POST', $this->root() . $parent . ':runQuery', $body) ?? [];

        $docs = [];
        foreach ($rows as $row) {
            if (!empty($row['document'])) {
                $docs[] = $this->decodeDocument($row['document']);
            }
        }
        return $docs;
    }

    /** count() aggregation; null when the index or the query is refused. */
    public function count($collection, array $where = [])
    {
        $v = $this->aggregate($collection, $where, ['count' => (object)[]]);
        return $v === null ? null : (int)$v;
    }

    private function aggregate($collection, array $where, array $aggregation)
    {
        $ops = ['==' => 'EQUAL', '!=' => 'NOT_EQUAL', 'in' => 'IN', 'array-contains' => 'ARRAY_CONTAINS', 'array-contains-any' => 'ARRAY_CONTAINS_ANY'];
        $q = ['from' => [['collectionId' => $collection]]];
        $filters = [];
        foreach ($where as [$field, $op, $value]) {
            $filters[] = ['fieldFilter' => [
                'field' => ['fieldPath' => $field],
                'op' => $ops[$op],
                'value' => Value::encode($value),
            ]];
        }
        if (count($filters) == 1) {
            $q['where'] = $filters[0];
        } elseif ($filters) {
            $q['where'] = ['compositeFilter' => ['op' => 'AND', 'filters' => $filters]];
        }
        try {
            $rows = $this->call('POST', $this->root() . ':runAggregationQuery', [
                'structuredAggregationQuery' => ['structuredQuery' => $q, 'aggregations' => [['alias' => 'n'] + $aggregation]],
            ]);
        } catch (FirestoreError $e) {
            return null;
        }
        $f = $rows[0]['result']['aggregateFields']['n'] ?? null;
        if ($f === null) {
            return null;
        }
        return array_key_exists('integerValue', $f) ? (int)$f['integerValue'] : (array_key_exists('doubleValue', $f) ? (float)$f['doubleValue'] : (array_key_exists('nullValue', $f) ? 0 : null));
    }

    /* ----------------------------------------------------------------- writes */

    /**
     * Runs $work(Transaction $t) inside a read-write transaction and commits it.
     * Retried when Firestore aborts it because another write touched the same
     * documents (the whole callback runs again on fresh reads).
     */
    public function transaction(callable $work, $attempts = 5)
    {
        for ($i = 1; ; $i++) {
            $begin = $this->call('POST', $this->root() . ':beginTransaction', ['options' => ['readWrite' => (object)[]]]);
            $t = new Transaction($this, $begin['transaction']);
            try {
                $result = $work($t);
                $this->commit($t->writes(), $t->id());
                return $result;
            } catch (FirestoreError $e) {
                $this->rollback($t->id());
                if ($i < $attempts && $e->status === 'ABORTED') {
                    usleep(200000 * $i);
                    continue;
                }
                throw $e;
            } catch (\Throwable $e) {
                $this->rollback($t->id());
                throw $e;
            }
        }
    }

    /** Writes without a transaction, all or nothing. */
    public function batch(array $writes)
    {
        return $this->commit($writes, null);
    }

    public function commit(array $writes, $transaction = null)
    {
        if (!$writes) {
            return [];
        }
        $body = ['writes' => $writes];
        if ($transaction) {
            $body['transaction'] = $transaction;
        }
        return $this->call('POST', $this->root() . ':commit', $body);
    }

    private function rollback($transaction)
    {
        try {
            $this->call('POST', $this->root() . ':rollback', ['transaction' => $transaction]);
        } catch (\Throwable $e) {
            // Firestore drops an unused transaction on its own.
        }
    }

    /**
     * Write builders. $mode: 'set' overwrites the document, 'create' fails if
     * it exists, 'update' changes only the given fields of an existing
     * document, 'merge' changes only the given fields and creates if missing.
     * For update / merge the keys of $data are field paths ("a.b").
     */
    public function write($mode, $path, array $data)
    {
        $transforms = [];
        if ($mode === 'update' || $mode === 'merge') {
            $fields = [];
            $mask = [];
            foreach ($data as $dotted => $value) {
                $segments = explode('.', $dotted);
                if ($value instanceof Value && $value->isTransform()) {
                    $transforms[] = $value->transform(self::fieldPath($segments));
                    continue;
                }
                if (is_array($value) && Value::isMap($value) && self::hasTransform($value)) {
                    // A map holding a server value: written leaf by leaf (as the
                    // Firestore client libraries do), the server values as transforms.
                    self::leaves($value, $segments, $fields, $mask, $transforms);
                    continue;
                }
                $mask[] = self::fieldPath($segments);
                if ($value instanceof Value && $value->isDelete()) {
                    continue;   // in the mask but not in the fields = deleted
                }
                self::setNested($fields, $segments, $value);
            }
            $write = ['update' => ['name' => $this->name($path), 'fields' => Value::encodeFields($fields)]];
            $write['updateMask'] = ['fieldPaths' => $mask];
            if ($mode === 'update') {
                $write['currentDocument'] = ['exists' => true];
            }
        } else {
            $fields = self::extractTransforms($data, [], $transforms);
            $write = ['update' => ['name' => $this->name($path), 'fields' => Value::encodeFields($fields)]];
            if ($mode === 'create') {
                $write['currentDocument'] = ['exists' => false];
            }
        }
        if ($transforms) {
            $write['updateTransforms'] = $transforms;
        }
        return $write;
    }

    /** Takes the transform values (server timestamp, increment) out of a full document. */
    private static function extractTransforms(array $data, array $prefix, array &$transforms)
    {
        $out = [];
        foreach ($data as $key => $value) {
            $segments = array_merge($prefix, [(string)$key]);
            if ($value instanceof Value && $value->isTransform()) {
                $transforms[] = $value->transform(self::fieldPath($segments));
            } elseif (is_array($value) && Value::isMap($value)) {
                $out[$key] = self::extractTransforms($value, $segments, $transforms);
                if ($out[$key] === []) {
                    $out[$key] = Value::map([]);
                }
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    private static function hasTransform(array $value)
    {
        foreach ($value as $v) {
            if (($v instanceof Value && $v->isTransform()) || (is_array($v) && Value::isMap($v) && self::hasTransform($v))) {
                return true;
            }
        }
        return false;
    }

    private static function leaves(array $map, array $prefix, array &$fields, array &$mask, array &$transforms)
    {
        foreach ($map as $k => $v) {
            $segments = array_merge($prefix, [(string)$k]);
            if ($v instanceof Value && $v->isTransform()) {
                $transforms[] = $v->transform(self::fieldPath($segments));
            } elseif (is_array($v) && Value::isMap($v) && self::hasTransform($v)) {
                self::leaves($v, $segments, $fields, $mask, $transforms);
            } else {
                $mask[] = self::fieldPath($segments);
                self::setNested($fields, $segments, $v);
            }
        }
    }

    private static function setNested(array &$fields, array $segments, $value)
    {
        $ref = &$fields;
        foreach (array_slice($segments, 0, -1) as $s) {
            if (!isset($ref[$s]) || !is_array($ref[$s])) {
                $ref[$s] = [];
            }
            $ref = &$ref[$s];
        }
        $ref[end($segments)] = $value;
    }

    public static function fieldPath(array $segments)
    {
        return implode('.', array_map(function ($s) {
            return preg_match('/^[A-Za-z_][A-Za-z_0-9]*$/', $s) ? $s : '`' . str_replace(['\\', '`'], ['\\\\', '\\`'], $s) . '`';
        }, $segments));
    }

    /* ---------------------------------------------------------------- decoding */

    public function decodeDocument(array $doc)
    {
        $prefix = $this->root() . '/';
        $path = strpos($doc['name'], $prefix) === 0 ? substr($doc['name'], strlen($prefix)) : $doc['name'];
        $parts = explode('/', $path);
        return [
            'id' => end($parts),
            'path' => $path,
            'data' => Value::decodeFields($doc['fields'] ?? []),
            'updateTime' => $doc['updateTime'] ?? null,
        ];
    }
}
