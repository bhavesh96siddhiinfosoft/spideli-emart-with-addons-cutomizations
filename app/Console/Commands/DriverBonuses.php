<?php

namespace App\Console\Commands;

use App\DriverBonus\Job;
use App\DriverBonus\ReadOnlyFirestore;
use App\Finance\Firestore;
use Illuminate\Console\Command;

/**
 * Point 104 - driver bonuses and levels (docs/APP-SPEC-DRIVER-BONUSES.md, section 6).
 * Pays the bonuses of finished weeks / months, sets levels, refreshes progress -
 * only in regions where Driver Bonuses > Bonus settings has "Pay bonuses" ticked.
 *
 *   php artisan drivers:bonuses                    the real run (scheduled twice a day)
 *   php artisan drivers:bonuses --dry-run          works everything out, writes nothing
 *   php artisan drivers:bonuses --dry-run --region=<id> --force-region --now="2026-11-09 10:00"
 *       a dry run for a region even if it is switched off, as at another time
 */
class DriverBonuses extends Command
{
    protected $signature = 'drivers:bonuses {--dry-run : Work everything out, write nothing}
                            {--region= : Only this region}
                            {--force-region : With --dry-run: work out --region even if it is switched off}
                            {--now= : With --dry-run: act as at this time (UTC), e.g. "2026-11-09 10:00"}';
    protected $description = 'Driver bonuses and levels: pay finished periods, set levels, refresh progress (point 104)';

    public function handle()
    {
        $dry = (bool)$this->option('dry-run');
        if (!$dry && ($this->option('force-region') || $this->option('now'))) {
            $this->error('--force-region and --now are for --dry-run only.');
            return 1;
        }
        $now = $this->option('now') ? new \DateTimeImmutable($this->option('now'), new \DateTimeZone('UTC')) : null;
        $db = $dry ? new ReadOnlyFirestore() : new Firestore();
        if ($dry) {
            $this->line('DRY RUN - nothing is written.');
        }
        $job = new Job($db, $now, $dry, $this->option('region'), (bool)$this->option('force-region'));
        foreach ($job->run() as $line) {
            $this->line($line);
        }
        return 0;
    }
}
