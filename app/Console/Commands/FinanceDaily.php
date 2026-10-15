<?php

namespace App\Console\Commands;

use App\Finance\Jobs;
use Illuminate\Console\Command;

/**
 * Spideli Finance daily work: loan instalments due / overdue (with the late
 * penalty) / back to active, contribution reminders and overdue notices,
 * savings targets reached, unanswered tontine invitations.
 */
class FinanceDaily extends Command
{
    protected $signature = 'finance:daily';
    protected $description = 'Spideli Finance daily jobs (loans, tontines, savings)';

    public function handle()
    {
        foreach ((new Jobs())->daily() as $line) {
            $this->line($line);
        }
        return 0;
    }
}
