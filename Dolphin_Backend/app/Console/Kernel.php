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
    \Log::info('[Scheduler] register start');
        $schedule->command('assessment:send-scheduled-emails')->everyMinute();

    // Use the artisan command to dispatch pending announcements
    $schedule->command('announcements:dispatch-pending')->everyMinute()->description('Dispatch pending scheduled announcements');
    \Log::info('[Scheduler] register end');
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
