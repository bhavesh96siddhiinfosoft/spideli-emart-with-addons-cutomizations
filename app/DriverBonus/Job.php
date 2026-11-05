<?php

namespace App\DriverBonus;

use App\Finance\Audit;
use App\Finance\Fcm;
use App\Finance\Firestore;
use App\Finance\Notifier;
use App\Finance\Transaction;
use App\Finance\Value;

/**
 * Point 104 - the scheduled work behind driver bonuses and levels
 * (docs/APP-SPEC-DRIVER-BONUSES.md section 6). Run by `drivers:bonuses`.
 *
 * Only regions with settings/driverBonus.regions.<id>.enabled === true are
 * touched: everything else is left alone. For each such region:
 *   1. closes the last finished week / month of each published rule: a result
 *      per driver who had trips, and the bonus credited to the wallet in the
 *      same transaction as the result (never twice: the result's id is fixed);
 *   2. sets each driver's level from the last finished month, once a month;
 *   3. refreshes each driver's progress for the current week and month.
 * A paid bonus is pushed to the driver (dynamic_notification "driver_bonus_paid").
 *
 * Dry run: everything is worked out and reported, nothing is written.
 */
class Job
{
    private $db;
    private $now;
    private $dryRun;
    private $onlyRegion;
    private $forceRegion;
    private $lines = [];
    private $drivers = null;

    public function __construct(Firestore $db, \DateTimeImmutable $now = null, $dryRun = false, $onlyRegion = null, $forceRegion = false)
    {
        $this->db = $db;
        $this->now = $now ?: new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->dryRun = (bool)$dryRun;
        $this->onlyRegion = $onlyRegion ?: null;
        // A region not switched on is only ever worked out in a dry run.
        $this->forceRegion = $this->dryRun && (bool)$forceRegion;
    }

    private function line($text)
    {
        $this->lines[] = $text;
    }

    public function run()
    {
        $settings = $this->db->get('settings/driverBonus');
        $regionsSettings = $settings ? (array)($settings['data']['regions'] ?? []) : [];
        $regionIds = [];
        foreach ($regionsSettings as $id => $s) {
            if (($s['enabled'] ?? false) === true) {
                $regionIds[] = (string)$id;
            }
        }
        if ($this->onlyRegion !== null) {
            $regionIds = (in_array($this->onlyRegion, $regionIds, true) || $this->forceRegion) ? [$this->onlyRegion] : [];
        }
        if (!$regionIds) {
            $this->line('No region has driver bonuses switched on: nothing to do.');
            return $this->lines;
        }
        foreach ($regionIds as $regionId) {
            try {
                $this->region($regionId, (array)($regionsSettings[$regionId] ?? []));
            } catch (\Throwable $e) {
                report($e);
                $this->line("Region $regionId: stopped - " . $e->getMessage());
            }
        }
        return $this->lines;
    }

    private function region($regionId, array $settings)
    {
        $regionDoc = $this->db->get('regions/' . $regionId);
        if (!$regionDoc) {
            $this->line("Region $regionId: not found.");
            return;
        }
        $region = $regionDoc['data'];
        $tz = Calculator::timeZone($region);
        $local = $this->now->setTimezone($tz);
        $currency = !empty($region['currencyId']) ? $this->db->get('currencies/' . $region['currencyId']) : null;
        $decimals = $currency ? (int)($currency['data']['decimal_degits'] ?? 0) : 0;
        $symbol = $currency ? (string)($currency['data']['symbol'] ?? '') : '';
        $levels = (array)($settings['levels'] ?? []);

        $rules = array_values(array_filter(array_map(function ($d) {
            return ['id' => $d['id']] + $d['data'];
        }, $this->db->query('driver_bonus_rules', [['regionId', '==', $regionId]])), function ($r) {
            return ($r['publish'] ?? false) === true;
        }));

        $drivers = array_values(array_filter($this->allDrivers(), function ($d) use ($regionId) {
            return (string)($d['regionId'] ?? '') === $regionId;
        }));

        $trips = [];
        foreach (Calculator::COLLECTIONS as $collection => $service) {
            foreach ($this->db->query($collection, [['regionId', '==', $regionId]]) as $doc) {
                $t = Calculator::trip($collection, $doc['data']);
                if ($t) {
                    $trips[] = $t;
                }
            }
        }

        $this->line(sprintf('Region %s (%s, %s): %d published rule(s), %d driver(s), %d trip(s) with a driver.',
            $region['name'] ?? $regionId, $tz->getName(), $local->format('Y-m-d H:i'), count($rules), count($drivers), count($trips)));

        // 1. the last finished period of each rule
        foreach ($rules as $rule) {
            $this->closePeriod($regionId, $rule, $drivers, $trips, $local, $levels, $settings, $decimals, $symbol);
        }
        // 2. levels, from the last finished month (after paying, so a period is paid at the level the driver held)
        $this->levels($drivers, $trips, $local, $levels);
        // 3. progress for the current week and month
        $this->progress($regionId, $rules, $drivers, $trips, $local);
    }

    private function allDrivers()
    {
        if ($this->drivers === null) {
            $this->drivers = array_map(function ($d) {
                return ['id' => $d['id']] + $d['data'];
            }, $this->db->query('users', [['role', '==', 'driver']]));
        }
        return $this->drivers;
    }

    private function closePeriod($regionId, array $rule, array $drivers, array $trips, \DateTimeImmutable $local, array $levels, array $settings, $decimals, $symbol)
    {
        $period = ($rule['period'] ?? 'weekly') === 'monthly' ? 'monthly' : 'weekly';
        [$start, $end, $key] = Calculator::previousPeriod($period, $local);
        $created = Calculator::time($rule['createdAt'] ?? null);
        if ($created && $created >= $end) {
            return;   // the rule did not exist during that period
        }
        $services = array_values(array_intersect(array_values(Calculator::COLLECTIONS), (array)($rule['services'] ?? [])));
        $max = Calculator::num($settings[$period === 'monthly' ? 'maxPerMonth' : 'maxPerWeek'] ?? null);

        $candidates = [];
        foreach ($drivers as $d) {
            $stats = Calculator::stats($trips, $d['id'], $services, $start, $end);
            if ($stats['trips'] > 0 || $stats['cancelled'] > 0) {
                $candidates[$d['id']] = [$d, $stats];
            }
        }
        if (!$candidates) {
            return;
        }
        $paths = array_map(function ($id) use ($rule, $key) {
            return 'driver_bonus_results/' . $id . '_' . $rule['id'] . '_' . $key;
        }, array_keys($candidates));
        $existing = $this->db->getMany($paths);

        foreach ($candidates as $driverId => [$driver, $stats]) {
            $path = 'driver_bonus_results/' . $driverId . '_' . $rule['id'] . '_' . $key;
            if (!empty($existing[$path])) {
                continue;   // already closed
            }
            $this->closeOne($path, $regionId, $rule, $period, $key, $start, $end, $driver, $stats, $levels, $max, $decimals, $symbol);
        }
    }

    private function closeOne($path, $regionId, array $rule, $period, $key, $start, $end, array $driver, array $stats, array $levels, $max, $decimals, $symbol)
    {
        $driverId = $driver['id'];
        $work = function ($t) use ($path, $regionId, $rule, $period, $key, $start, $end, $driver, $driverId, $stats, $levels, $max, $decimals) {
            /** @var Transaction|null $t */
            $read = function ($p) use ($t) { return $t ? $t->get($p) : $this->db->get($p); };
            if ($read($path)) {
                return null;   // closed meanwhile
            }
            $user = $read('users/' . $driverId);
            if (!$user) {
                return null;   // the driver's account is gone: nothing to credit
            }
            $u = $user['data'];
            $paidRows = $t ? $t->query('driver_bonus_results', [['driverId', '==', $driverId]])
                           : $this->db->query('driver_bonus_results', [['driverId', '==', $driverId]]);
            $alreadyPaid = 0.0;
            foreach ($paidRows as $row) {
                $r = $row['data'];
                if (($r['regionId'] ?? '') === $regionId && ($r['period'] ?? '') === $period && ($r['periodKey'] ?? '') === $key
                    && in_array($r['status'] ?? '', ['paid', 'capped'], true)) {
                    $alreadyPaid += (float)($r['bonus'] ?? 0);
                }
            }
            $level = Calculator::level($u);
            $rating = Calculator::rating($u);
            $res = Calculator::evaluate($rule, $stats, $rating, $level, $levels, $alreadyPaid, $max, $decimals);

            $walletId = $res['bonus'] > 0 ? Audit::newId() : null;
            $doc = [
                'driverId' => $driverId, 'ruleId' => (string)$rule['id'], 'ruleName' => (string)($rule['name'] ?? ''),
                'regionId' => $regionId, 'period' => $period, 'periodKey' => $key,
                'periodStart' => Value::timestamp($start), 'periodEnd' => Value::timestamp($end),
                'measure' => ($rule['measure'] ?? 'trips') === 'value' ? 'value' : 'trips',
                'walletId' => $walletId, 'createdAt' => Value::serverTimestamp(),
            ] + $res;
            if ($t) {
                $t->create($path, $doc);
                if ($res['bonus'] > 0) {
                    /* Read in this transaction and written as a number: an
                     * increment would overwrite a balance saved as text. */
                    $balance = Calculator::num($u['wallet_amount'] ?? null) ?? 0.0;
                    $t->update('users/' . $driverId, ['wallet_amount' => round($balance + $res['bonus'], 2)]);
                    $t->create('wallet/' . $walletId, [
                        'id' => $walletId, 'user_id' => $driverId, 'amount' => (float)$res['bonus'],
                        'date' => Value::serverTimestamp(), 'isTopUp' => true, 'order_id' => '',
                        'payment_method' => 'Bonus', 'payment_status' => 'success',
                        'transactionUser' => 'driver', 'serviceType' => '', 'regionId' => $regionId,
                        'note' => 'Bonus: ' . ($rule['name'] ?? '') . ' (' . $key . ')',
                    ]);
                }
            }
            return $res;
        };

        $res = $this->dryRun ? $work(null) : $this->db->transaction($work);
        if ($res === null) {
            return;
        }
        $name = trim(($driver['firstName'] ?? '') . ' ' . ($driver['lastName'] ?? '')) ?: $driverId;
        $this->line(sprintf('  %s %s | %s | %d trips, value %s | %s%s%s',
            $this->dryRun ? 'WOULD' : 'DONE', $key, $name, $res['trips'], $res['value'],
            $res['status'], $res['reason'] ? ' (' . $res['reason'] . ')' : '',
            $res['bonus'] > 0 ? ' -> bonus ' . $res['bonus'] . ' ' . $symbol : ''));

        if (!$this->dryRun && $res['bonus'] > 0) {
            $this->push($driverId, $res['bonus'], $symbol, (string)($rule['name'] ?? ''), $key);
        }
    }

    private function levels(array $drivers, array $trips, \DateTimeImmutable $local, array $levels)
    {
        [$start, $end, $month] = Calculator::previousPeriod('monthly', $local);
        $all = array_values(Calculator::COLLECTIONS);
        $changed = 0;
        foreach ($drivers as $d) {
            if (($d['driverLevelMonth'] ?? '') === $month) {
                continue;   // already set for that month
            }
            $level = Calculator::levelFor(Calculator::stats($trips, $d['id'], $all, $start, $end), Calculator::rating($d), $levels);
            if (!$this->dryRun) {
                $this->db->batch([$this->db->write('update', 'users/' . $d['id'], [
                    'driverLevel' => $level, 'driverLevelMonth' => $month, 'driverLevelAt' => Value::serverTimestamp(),
                ])]);
            }
            if ($level !== Calculator::level($d)) {
                $changed++;
                $name = trim(($d['firstName'] ?? '') . ' ' . ($d['lastName'] ?? '')) ?: $d['id'];
                $this->line(sprintf('  %s level %s: %s -> %s', $this->dryRun ? 'WOULD' : 'DONE', $name, Calculator::level($d), $level));
            }
        }
        $this->line(sprintf('  Levels for %s: %d change(s).', $month, $changed));
    }

    private function progress($regionId, array $rules, array $drivers, array $trips, \DateTimeImmutable $local)
    {
        if (!$rules) {
            return;
        }
        $writes = [];
        foreach ($drivers as $d) {
            $rows = [];
            foreach ($rules as $rule) {
                $period = ($rule['period'] ?? 'weekly') === 'monthly' ? 'monthly' : 'weekly';
                [$start, $end, $key] = Calculator::periodOf($period, $local);
                $services = array_values(array_intersect(array_values(Calculator::COLLECTIONS), (array)($rule['services'] ?? [])));
                $stats = Calculator::stats($trips, $d['id'], $services, $start, $end);
                $rows[] = Calculator::progress($rule, $stats, Calculator::rating($d), Calculator::level($d), $key);
            }
            $writes[] = $this->db->write('set', 'driver_bonus_progress/' . $d['id'], [
                'driverId' => $d['id'], 'regionId' => $regionId, 'rules' => $rows, 'updatedAt' => Value::serverTimestamp(),
            ]);
        }
        if (!$this->dryRun) {
            foreach (array_chunk($writes, 400) as $chunk) {
                $this->db->batch($chunk);
            }
        }
        $this->line(sprintf('  Progress: %s for %d driver(s).', $this->dryRun ? 'would refresh' : 'refreshed', count($writes)));
    }

    /** Best effort: a missing template or token never undoes the payment. */
    private function push($driverId, $bonus, $symbol, $ruleName, $periodKey)
    {
        try {
            $user = $this->db->get('users/' . $driverId);
            $token = $user ? (string)($user['data']['fcmToken'] ?? '') : '';
            if ($token === '') {
                return;
            }
            $amount = $bonus . ' ' . $symbol;
            $values = ['amount' => trim($amount), 'rule' => $ruleName, 'period' => $periodKey];
            $template = $this->db->query('dynamic_notification', [['type', '==', 'driver_bonus_paid']], [], 1);
            $t = $template ? $template[0]['data'] : [];
            $title = Notifier::fill((string)($t['subject'] ?? $t['title'] ?? 'Bonus received'), $values);
            $body = Notifier::fill((string)($t['message'] ?? 'You received a bonus of {amount} for {rule} ({period}).'), $values);
            (new Fcm($this->db->projectId()))->send([
                'token' => $token,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => ['type' => 'driver_bonus_paid', 'periodKey' => (string)$periodKey],
                'android' => ['priority' => 'high', 'notification' => ['channel_id' => 'high_importance_channel', 'sound' => 'default']],
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
