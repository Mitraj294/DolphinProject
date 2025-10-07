<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class UpdateSubscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily cleanup: Update the status of expired subscriptions for reporting and notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Performing daily subscription status cleanup...');

        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('subscription_end', '<', Carbon::now())
            ->get();

        $count = $expiredSubscriptions->count();
        
        if ($count > 0) {
            foreach ($expiredSubscriptions as $subscription) {
                $subscription->status = 'expired';
                $subscription->save();
                
                // Here you could add logic to send expiration emails, generate reports, etc.
                // Mail::to($subscription->customer_email)->send(new SubscriptionExpiredMail($subscription));
            }
            
            $this->info("Updated {$count} expired subscription(s).");
        } else {
            $this->info('No expired subscriptions found.');
        }

        $this->info('Daily cleanup completed.');
    }
}