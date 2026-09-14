<?php

namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('app:multivendor-order-auto-cancel')->everyMinute();
        $schedule->command('app:multivendor-scheduled-order-notification')->everyMinute();
        $schedule->command('app:parcel-order-auto-cancel')->everyFiveMinutes();
        $schedule->command('app:rental-order-auto-cancel')->everyFiveMinutes();
        $schedule->command('app:ondemand-order-auto-cancel')->daily();
        $schedule->command('app:cab-schedule-ride')->everyMinute();
    }
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
