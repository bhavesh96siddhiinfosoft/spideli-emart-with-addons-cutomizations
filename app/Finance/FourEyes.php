<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Four-eyes rule (spec 5.3): a reversal or manual adjustment of at least the
 * threshold waits for a second admin. Thresholds are set per region, in the
 * region's currency; a currency without a threshold always needs the second
 * admin.
 */
class FourEyes
{
    /** [regionId => [CUR => minor amount]] from the panel database. */
    public static function thresholds()
    {
        $out = [];
        foreach (DB::table('finance_panel_settings')->where('key', 'four_eyes_threshold')->get() as $row) {
            $value = json_decode($row->value, true);
            $out[$row->region_id] = is_array($value) ? $value : [];
        }
        return $out;
    }

    public static function saveThreshold($regionId, array $minorByCurrency)
    {
        DB::table('finance_panel_settings')->updateOrInsert(
            ['region_id' => $regionId, 'key' => 'four_eyes_threshold'],
            ['value' => json_encode((object)$minorByCurrency), 'updated_at' => now(), 'created_at' => now()]
        );
    }

    /**
     * Whether an amount needs a second admin. With $regionIds, only those
     * regions' thresholds count; otherwise (a posting, which names no region)
     * every region with a threshold in that currency counts and the strictest
     * (lowest) wins. No threshold at all for the currency = always.
     */
    public static function needsSecondAdmin($currency, $amount, array $regionIds = [])
    {
        $limits = [];
        foreach (self::thresholds() as $regionId => $map) {
            if ($regionIds && !in_array($regionId, $regionIds, true)) {
                continue;
            }
            if (isset($map[$currency])) {
                $limits[] = (int)$map[$currency];
            }
        }
        return !$limits || $amount >= min($limits);
    }

    public static function request($action, array $payload, $currency, $amount, $reason, $regionId = null)
    {
        $user = Auth::user();
        return DB::table('finance_approvals')->insertGetId([
            'action' => $action,
            'region_id' => $regionId,
            'payload' => json_encode($payload),
            'currency' => $currency,
            'amount' => $amount,
            'reason' => $reason,
            'status' => 'pending',
            'requested_by' => $user->id,
            'requested_by_email' => (string)$user->email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Takes a pending request for the current admin to decide on. Only one
     * admin can win it (the status update is the lock), and never the one who
     * asked for it.
     */
    public static function claim($id)
    {
        $row = DB::table('finance_approvals')->where('id', $id)->first();
        if (!$row || $row->status !== 'pending') {
            throw new FinanceException('approval_closed', 'This request has already been decided.');
        }
        if ((int)$row->requested_by === (int)Auth::id()) {
            throw new FinanceException('self_approval', 'A second admin must approve your own request.');
        }
        $won = DB::table('finance_approvals')->where('id', $id)->where('status', 'pending')
            ->update(['status' => 'executing', 'updated_at' => now()]);
        if (!$won) {
            throw new FinanceException('approval_closed', 'This request has already been decided.');
        }
        return $row;
    }

    public static function finish($id, $status, $result = null, $note = null)
    {
        $user = Auth::user();
        DB::table('finance_approvals')->where('id', $id)->update([
            'status' => $status,
            'decided_by' => $user->id,
            'decided_by_email' => (string)$user->email,
            'decided_at' => now(),
            'decision_note' => $note,
            'result' => $result === null ? null : json_encode($result),
            'updated_at' => now(),
        ]);
    }

    public static function pendingCount()
    {
        try {
            return DB::table('finance_approvals')->where('status', 'pending')->count();
        } catch (\Throwable $e) {
            return 0;   // before the migration has run
        }
    }
}
