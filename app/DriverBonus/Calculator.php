<?php

namespace App\DriverBonus;

/**
 * Point 104 - driver bonuses and levels: the rules, without Firestore
 * (docs/APP-SPEC-DRIVER-BONUSES.md, sections 4 and 5). Everything is handed in,
 * so every rule can be tested on its own. Job.php reads and writes.
 *
 * A "trip" here is one order, normalised by trip():
 *   ['service', 'driverId', 'regionId', 'completed' (bool), 'at' (DateTimeImmutable, when completed),
 *    'value' (float), 'cancelledByDriver' (bool), 'cancelledAt' (DateTimeImmutable|null)]
 */
class Calculator
{
    public const COLLECTIONS = [
        'rides' => 'cab',
        'vendor_orders' => 'delivery',
        'parcel_orders' => 'parcel',
        'rental_orders' => 'rental',
    ];
    public const LEVELS = ['bronze' => 0, 'silver' => 1, 'gold' => 2];

    /* ------------------------------------------------------------ helpers -- */

    public static function num($v)
    {
        if ($v === null || $v === '' || is_bool($v) || is_array($v)) {
            return null;
        }
        $v = is_string($v) ? str_replace(',', '.', trim($v)) : $v;
        return is_numeric($v) ? (float)$v : null;
    }

    /** A Firestore time (DateTimeImmutable from the client) or an ISO string, else null. */
    public static function time($v)
    {
        if ($v instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($v);
        }
        if (is_string($v) && trim($v) !== '') {
            try {
                return new \DateTimeImmutable($v);
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }

    /** The region's time zone, from its country (regions keep none); UTC when unknown. */
    public static function timeZone(array $region)
    {
        $code = strtoupper(trim((string)($region['countryCode'] ?? '')));
        if (strlen($code) === 2) {
            try {
                $ids = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $code);
                if ($ids) {
                    return new \DateTimeZone($ids[0]);
                }
            } catch (\Throwable $e) {
            }
        }
        return new \DateTimeZone('UTC');
    }

    /* ------------------------------------------------------------ periods -- */

    /** The period containing $local: [start, end, key]. Weekly = Monday 00:00 to next Monday; monthly = calendar month. */
    public static function periodOf($period, \DateTimeImmutable $local)
    {
        if ($period === 'monthly') {
            $start = $local->setDate((int)$local->format('Y'), (int)$local->format('n'), 1)->setTime(0, 0);
            return [$start, $start->modify('+1 month'), $start->format('Y-m')];
        }
        $start = $local->setTime(0, 0)->modify('-' . ((int)$local->format('N') - 1) . ' days');
        return [$start, $start->modify('+7 days'), $start->format('o-\WW')];
    }

    /** The last period that has fully ended before $local. */
    public static function previousPeriod($period, \DateTimeImmutable $local)
    {
        [$start] = self::periodOf($period, $local);
        return self::periodOf($period, $start->modify('-1 second'));
    }

    /* -------------------------------------------------------------- trips -- */

    /** One order as a trip, or null when it has no driver. */
    public static function trip($collection, array $o)
    {
        $driverId = (string)($o['driverId'] ?? ($o['driverID'] ?? ''));
        if ($driverId === '' || !isset(self::COLLECTIONS[$collection])) {
            return null;
        }
        $service = self::COLLECTIONS[$collection];
        $status = (string)($o['status'] ?? '');
        if ($service === 'delivery') {
            $value = self::num($o['deliveryCharge'] ?? null);
        } elseif ($service === 'parcel') {
            $value = self::num($o['priceBreakdown']['carrierPrice'] ?? null);
            if ($value === null) {
                $value = self::num($o['subTotal'] ?? null);
            }
        } else {
            $value = self::num($o['subTotal'] ?? null);
        }
        return [
            'service' => $service,
            'driverId' => $driverId,
            'regionId' => (string)($o['regionId'] ?? ''),
            'completed' => $status === 'Order Completed',
            'at' => self::time($o['completedAt'] ?? null) ?: self::time($o['createdAt'] ?? null),
            'value' => max(0.0, (float)($value ?? 0)),
            // P4: only a driver cancelling a trip they had accepted; declining is not a cancellation.
            'cancelledByDriver' => ($o['cancelledBy'] ?? '') === 'driver',
            'cancelledAt' => self::time($o['cancelledAt'] ?? null) ?: self::time($o['createdAt'] ?? null),
        ];
    }

    private static function inside($at, \DateTimeImmutable $start, \DateTimeImmutable $end)
    {
        return $at instanceof \DateTimeInterface && $at >= $start && $at < $end;
    }

    /** A driver's numbers for a period: trips, value, cancellations, cancellation rate (%). */
    public static function stats(array $trips, $driverId, array $services, \DateTimeImmutable $start, \DateTimeImmutable $end)
    {
        $count = 0;
        $value = 0.0;
        $cancelled = 0;
        foreach ($trips as $t) {
            if ($t['driverId'] !== $driverId || !in_array($t['service'], $services, true)) {
                continue;
            }
            if ($t['completed'] && self::inside($t['at'], $start, $end)) {
                $count++;
                $value += $t['value'];
            } elseif ($t['cancelledByDriver'] && self::inside($t['cancelledAt'], $start, $end)) {
                $cancelled++;
            }
        }
        $rate = ($count + $cancelled) > 0 ? round($cancelled / ($count + $cancelled) * 100, 2) : 0.0;
        return ['trips' => $count, 'value' => round($value, 2), 'cancelled' => $cancelled, 'cancellationRate' => $rate];
    }

    /** The lifetime rating, or null when the driver has no review yet. */
    public static function rating(array $driver)
    {
        $count = (int)($driver['reviewsCount'] ?? 0);
        $sum = self::num($driver['reviewsSum'] ?? null);
        return ($count > 0 && $sum !== null) ? round($sum / $count, 2) : null;
    }

    /** A store's own delivery man (the store / admin "Delivery man" screens set vendorID). Client, 5 Nov: no bonus. */
    public static function isStoreDriver(array $driver)
    {
        return trim((string)($driver['vendorID'] ?? '')) !== '';
    }

    /** The company owner a driver works for (users.ownerId), or ''. Client, 5 Nov: the company gets the bonus. */
    public static function companyOwnerId(array $driver)
    {
        return trim((string)($driver['ownerId'] ?? ''));
    }

    public static function level(array $driver)
    {
        $l = (string)($driver['driverLevel'] ?? '');
        return isset(self::LEVELS[$l]) ? $l : 'bronze';
    }

    /* -------------------------------------------------------------- rules -- */

    /** Why a driver cannot earn under a rule (rating, cancellation, level), or null. */
    public static function ineligibleReason(array $rule, $rating, $cancellationRate, $level)
    {
        $minRating = self::num($rule['minRating'] ?? null);
        // P3: a driver with no review yet passes the minimum rating.
        if ($minRating !== null && $rating !== null && $rating < $minRating) {
            return 'rating';
        }
        $maxCancel = self::num($rule['maxCancellationRate'] ?? null);
        if ($maxCancel !== null && $cancellationRate > $maxCancel) {
            return 'cancellation';
        }
        $minLevel = (string)($rule['minLevel'] ?? '');
        if (isset(self::LEVELS[$minLevel]) && self::LEVELS[$level] < self::LEVELS[$minLevel]) {
            return 'level';
        }
        return null;
    }

    /** The rule's steps, ascending, valid ones only. */
    public static function tiers(array $rule)
    {
        $out = [];
        foreach ((array)($rule['tiers'] ?? []) as $t) {
            $target = self::num($t['target'] ?? null);
            $bonus = self::num($t['bonus'] ?? null);
            if ($target !== null && $target > 0 && $bonus !== null && $bonus > 0) {
                $out[] = ['target' => $target, 'bonus' => $bonus];
            }
        }
        usort($out, function ($a, $b) { return $a['target'] <=> $b['target']; });
        return $out;
    }

    /** The highest step reached by $done, or null. */
    public static function tierReached(array $tiers, $done)
    {
        $reached = null;
        foreach ($tiers as $t) {
            if ($done >= $t['target']) {
                $reached = $t;
            }
        }
        return $reached;
    }

    /** The next step above $done, or null when the highest is reached. */
    public static function nextTier(array $tiers, $done)
    {
        foreach ($tiers as $t) {
            if ($done < $t['target']) {
                return $t;
            }
        }
        return null;
    }

    /**
     * A closed period's result for one driver and rule.
     * $levels: the region's settings levels; $alreadyPaid: what the driver has received for the same
     * period type and key (all rules); $max: maxPerWeek / maxPerMonth or null; $decimals: the currency's.
     */
    public static function evaluate(array $rule, array $stats, $rating, $level, array $levels, $alreadyPaid, $max, $decimals)
    {
        $done = ($rule['measure'] ?? 'trips') === 'value' ? $stats['value'] : $stats['trips'];
        $tier = self::tierReached(self::tiers($rule), $done);
        $out = [
            'trips' => $stats['trips'], 'value' => $stats['value'], 'rating' => $rating,
            'cancellationRate' => $stats['cancellationRate'], 'level' => $level,
            'tierTarget' => $tier ? $tier['target'] : null, 'tierBonus' => $tier ? $tier['bonus'] : 0,
            'boostPercent' => 0, 'cappedBy' => 0, 'bonus' => 0, 'status' => 'not_reached', 'reason' => null,
        ];
        if (!$tier) {
            return $out;
        }
        $reason = self::ineligibleReason($rule, $rating, $stats['cancellationRate'], $level);
        if ($reason !== null) {
            $out['status'] = 'not_eligible';
            $out['reason'] = $reason;
            return $out;
        }
        $boost = self::num($levels[$level]['bonusBoost'] ?? null) ?? 0.0;
        $bonus = round($tier['bonus'] * (1 + max(0.0, $boost) / 100), (int)$decimals);
        $out['boostPercent'] = $boost;
        $out['status'] = 'paid';
        if ($max !== null) {
            $left = max(0.0, round($max - $alreadyPaid, (int)$decimals));
            if ($bonus > $left) {
                $out['cappedBy'] = round($bonus - $left, (int)$decimals);
                $bonus = $left;
                $out['status'] = 'capped';
            }
        }
        $out['bonus'] = $bonus;
        return $out;
    }

    /**
     * The level earned in a month (section 5): Gold if all of Gold's set thresholds are met, else Silver's,
     * else Bronze. A level with no threshold set is not used (nobody reaches it by default).
     */
    public static function levelFor(array $monthStats, $rating, array $levels)
    {
        foreach (['gold', 'silver'] as $level) {
            $l = $levels[$level] ?? [];
            $minTrips = self::num($l['minTrips'] ?? null);
            $minRating = self::num($l['minRating'] ?? null);
            $maxCancel = self::num($l['maxCancellationRate'] ?? null);
            if ($minTrips === null && $minRating === null && $maxCancel === null) {
                continue;
            }
            if ($minTrips !== null && $monthStats['trips'] < $minTrips) {
                continue;
            }
            if ($minRating !== null && $rating !== null && $rating < $minRating) {
                continue;
            }
            if ($maxCancel !== null && $monthStats['cancellationRate'] > $maxCancel) {
                continue;
            }
            return $level;
        }
        return 'bronze';
    }

    /** One rule's progress in the current period, for the driver app and the admin's driver card. */
    public static function progress(array $rule, array $stats, $rating, $level, $periodKey)
    {
        $byValue = ($rule['measure'] ?? 'trips') === 'value';
        $done = $byValue ? $stats['value'] : $stats['trips'];
        $tiers = self::tiers($rule);
        $next = self::nextTier($tiers, $done);
        $reason = self::ineligibleReason($rule, $rating, $stats['cancellationRate'], $level);
        return [
            'ruleId' => (string)($rule['id'] ?? ''), 'name' => (string)($rule['name'] ?? ''),
            'period' => ($rule['period'] ?? 'weekly') === 'monthly' ? 'monthly' : 'weekly',
            'periodKey' => $periodKey, 'measure' => $byValue ? 'value' : 'trips',
            'done' => $done, 'nextTarget' => $next ? $next['target'] : null, 'nextBonus' => $next ? $next['bonus'] : null,
            'tiers' => $tiers, 'eligible' => $reason === null, 'reason' => $reason,
        ];
    }
}
