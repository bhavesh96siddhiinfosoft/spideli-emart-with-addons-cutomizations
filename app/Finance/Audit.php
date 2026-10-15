<?php

namespace App\Finance;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;

/**
 * admin_audit_logs (spec 6.18): one entry for every write the panel makes,
 * written in the same commit as the write itself so neither exists without
 * the other. Append-only: the panel never updates or deletes an entry.
 */
class Audit
{
    public static function entry($action, $collection, $docId, $before, $after, $reason = '')
    {
        $user = Auth::user();
        $role = $user ? Role::find($user->role_id) : null;
        $request = request();

        return [
            'adminId' => $user ? (string)$user->id : '',
            'adminEmail' => $user ? (string)$user->email : '',
            'role' => $role ? (string)$role->role_name : '',
            'action' => $action,
            'collection' => $collection,
            'docId' => (string)$docId,
            'before' => self::snapshot($before),
            'after' => self::snapshot($after),
            'reason' => (string)$reason,
            'ip' => (string)($request ? $request->ip() : ''),
            'userAgent' => substr((string)($request ? $request->userAgent() : ''), 0, 500),
            'at' => Value::serverTimestamp(),
        ];
    }

    /** Adds the entry to a transaction. */
    public static function add(Transaction $t, $action, $collection, $docId, $before, $after, $reason = '')
    {
        $t->create('admin_audit_logs/' . self::newId(), self::entry($action, $collection, $docId, $before, $after, $reason));
    }

    /** The write of an entry, for a batch. */
    public static function write(Firestore $db, $action, $collection, $docId, $before, $after, $reason = '')
    {
        return $db->write('create', 'admin_audit_logs/' . self::newId(), self::entry($action, $collection, $docId, $before, $after, $reason));
    }

    /**
     * The fields that differ between two versions of a document, as
     * [before, after] - only what changed is logged.
     */
    public static function diff(array $before, array $after)
    {
        $b = [];
        $a = [];
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $k) {
            $old = array_key_exists($k, $before) ? self::canonical(Value::plain($before[$k])) : null;
            $new = array_key_exists($k, $after) ? self::canonical(Value::plain($after[$k])) : null;
            if ($old !== $new) {
                if (array_key_exists($k, $before)) {
                    $b[$k] = $before[$k];
                }
                if (array_key_exists($k, $after)) {
                    $a[$k] = $after[$k];
                }
            }
        }
        return [$b, $a];
    }

    /** Maps compared regardless of key order (Firestore does not keep it). */
    private static function canonical($v)
    {
        if (!is_array($v)) {
            return $v;
        }
        $v = array_map([self::class, 'canonical'], $v);
        if (Value::isMap($v)) {
            ksort($v);
        }
        return $v;
    }

    /** Server values cannot be logged as such; keep plain data, an explicit empty map when empty. */
    private static function snapshot($data)
    {
        if ($data === null) {
            return null;
        }
        if (!is_array($data)) {
            return $data;
        }
        $clean = self::stripServerValues($data);
        return $clean === [] ? Value::map([]) : $clean;
    }

    private static function stripServerValues(array $data)
    {
        $out = [];
        foreach ($data as $k => $v) {
            if ($v instanceof Value && $v->isTransform()) {
                $out[$k] = Value::plain($v);
            } elseif (is_array($v)) {
                $out[$k] = self::stripServerValues($v);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /** A Firestore-style 20-character auto id. */
    public static function newId()
    {
        return Money::random(20);
    }
}
