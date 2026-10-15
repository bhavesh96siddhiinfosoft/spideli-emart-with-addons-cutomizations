<?php

namespace App\Finance;

/**
 * Region scope for an admin tied to regions (admin_user_regions): a record is
 * visible when its regionId (written by the app once it applies the region
 * spec) or else its customer's regionIds meet the admin's regions. Unbound
 * admins see everything.
 */
class Scope
{
    /**
     * $userField may be a field holding one uid (ownerId, borrowerId) or a list
     * of uids (memberIds): any of them in the admin's regions is enough.
     */
    public static function filter(Firestore $db, array $rows, $userField)
    {
        $allowed = Regions::allowedIds();
        if (!$allowed || !$rows) {
            return $rows;
        }
        $uids = [];
        foreach ($rows as $r) {
            foreach ((array)($r['data'][$userField] ?? []) as $u) {
                if (is_string($u)) {
                    $uids[$u] = true;
                }
            }
        }
        $users = $uids ? $db->getMany(array_map(function ($u) { return 'users/' . $u; }, array_keys($uids))) : [];
        return array_values(array_filter($rows, function ($r) use ($allowed, $users, $userField) {
            if (!empty($r['data']['regionId'])) {
                return in_array($r['data']['regionId'], $allowed, true);
            }
            foreach ((array)($r['data'][$userField] ?? []) as $u) {
                $user = $users['users/' . $u] ?? null;
                if ($user && array_intersect((array)($user['data']['regionIds'] ?? []), $allowed)) {
                    return true;
                }
            }
            return false;
        }));
    }

    /** One record: visible to this admin? */
    public static function allows(Firestore $db, array $doc, $userField)
    {
        return (bool)self::filter($db, [$doc], $userField);
    }
}
