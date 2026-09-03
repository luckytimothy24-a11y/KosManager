<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('tagihan:mark-overdue')->dailyAt('00:05')->withoutOverlapping();
        $schedule->command('booking:expire-old')->dailyAt('00:15')->withoutOverlapping();
        $schedule->command('kontrak:expire-old')->dailyAt('00:25')->withoutOverlapping();
        $schedule->command('tagihan:remind-due-soon')->dailyAt('07:00')->withoutOverlapping();

        if (config('backup.schedule_enabled', true)) {
            $schedule->command('backup:run')
                ->dailyAt(config('backup.schedule_at', '00:35'))
                ->withoutOverlapping()
                ->onOneServer();
        }

        $schedule->command('notification:cleanup')->monthly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
