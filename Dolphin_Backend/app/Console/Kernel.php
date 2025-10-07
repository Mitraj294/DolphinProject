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
 
        // Clean up expired tokens daily at 2 AM
        $schedule->command('tokens:cleanup --force')->dailyAt('02:00')->description('Clean up expired OAuth tokens');

        // Update subscription statuses daily for cleanup, reporting, and email notifications
        $schedule->command('subscriptions:update-status')->dailyAt('04:00')->description('Daily cleanup: mark expired subscriptions in DB for reporting and notifications');

    // Reconcile leads with users nightly to ensure lead.status reflects registration
    $schedule->command('leads:reconcile-with-users --dry-run')->dailyAt('03:00')->description('Dry-run reconcile leads with users (no updates)');


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

