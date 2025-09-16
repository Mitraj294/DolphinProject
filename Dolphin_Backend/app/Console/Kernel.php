<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use function base_path;

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

        // Update subscription statuses hourly (mark active subscriptions past end as expired)
        $schedule->command('subscriptions:update-status')->everyMinute()->description('Mark expired subscriptions as expired in DB');


    }
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
    $this->load(__DIR__.'/Commands');

    // Console routes should be loaded via PSR-4 namespaces or Composer autoload;
    // avoid runtime require_once and prefer namespace imports or service providers.
    }
}

