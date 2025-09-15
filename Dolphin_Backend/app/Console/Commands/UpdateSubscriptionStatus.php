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
    protected $signature = 'app:update-subscription-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of expired subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating subscription statuses...');

        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('subscription_end', '<', Carbon::now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->status = 'expired';
            $subscription->save();
        }

        $this->info('Done.');
    }
}
