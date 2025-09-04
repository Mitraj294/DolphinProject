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
 
        $schedule->command('assessment:send-scheduled-emails')->everyMinute();

        // Use the artisan command to dispatch pending announcements
        $schedule->command('announcements:dispatch-pending')->everyMinute()->description('Dispatch pending scheduled announcements');

        // Clean up expired tokens daily at 2 AM
        $schedule->command('tokens:cleanup --force')->dailyAt('02:00')->description('Clean up expired OAuth tokens');

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
    $this->load(__DIR__.'/Commands');

       require_once  base_path('routes/console.php');
    }
}
