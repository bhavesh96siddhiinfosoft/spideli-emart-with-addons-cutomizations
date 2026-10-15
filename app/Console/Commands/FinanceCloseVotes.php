<?php

namespace App\Console\Commands;

use App\Finance\Jobs;
use Illuminate\Console\Command;

/** Spideli Finance hourly work: closes loan votes past their end that nobody closed. */
class FinanceCloseVotes extends Command
{
    protected $signature = 'finance:close-votes';
    protected $description = 'Spideli Finance: close expired loan votes';

    public function handle()
    {
        foreach ((new Jobs())->hourly() as $line) {
            $this->line($line);
        }
        return 0;
    }
}
