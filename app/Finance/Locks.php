<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Work locks for queue items (spec 8): opening an item takes it for the
 * current admin; another admin sees who has it. A lock lapses after 15
 * minutes without a decision, and is released when the item is decided.
 */
class Locks
{
    public const MINUTES = 15;

    /**
     * Takes (or keeps) the lock on $resource for the current admin.
     * Returns null when the admin holds it, else the holder's e-mail.
     */
    public static function take($resource)
    {
        $me = Auth::user();
        return DB::transaction(function () use ($resource, $me) {
            $row = DB::table('finance_locks')->where('resource', $resource)->lockForUpdate()->first();
            $fresh = $row && $row->locked_at && now()->diffInMinutes($row->locked_at) < self::MINUTES;
            if ($row && $fresh && (int)$row->locked_by !== (int)$me->id) {
                return $row->locked_by_email ?: ('#' . $row->locked_by);
            }
            DB::table('finance_locks')->updateOrInsert(['resource' => $resource],
                ['locked_by' => $me->id, 'locked_by_email' => (string)$me->email, 'locked_at' => now()]);
            return null;
        });
    }

    /** Refuses a decision on an item another admin holds. */
    public static function assertMine($resource)
    {
        $holder = self::take($resource);
        if ($holder !== null) {
            throw new FinanceException('locked', "Another admin ($holder) is working on this item.");
        }
    }

    public static function release($resource)
    {
        DB::table('finance_locks')->where('resource', $resource)->where('locked_by', Auth::id())->delete();
    }

    /** resource => holder e-mail, for the queue lists (fresh locks only). */
    public static function held(array $resources)
    {
        if (!$resources) {
            return [];
        }
        $out = [];
        foreach (DB::table('finance_locks')->whereIn('resource', $resources)->get() as $row) {
            if ($row->locked_at && now()->diffInMinutes($row->locked_at) < self::MINUTES) {
                $out[$row->resource] = $row->locked_by_email;
            }
        }
        return $out;
    }
}
