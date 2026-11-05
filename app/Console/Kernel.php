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
        // Spideli Finance (Phase C): loans, tontines, savings - demo records are skipped.
        $schedule->command('finance:daily')->dailyAt('06:00')->withoutOverlapping();
        $schedule->command('finance:close-votes')->hourly()->withoutOverlapping();
        // Point 104: driver bonuses and levels - twice a day, so a region in another time zone has its
        // finished week / month closed within about 12 hours. Only regions switched on in Bonus settings.
        $schedule->command('drivers:bonuses')->twiceDaily(3, 15)->withoutOverlapping();
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
