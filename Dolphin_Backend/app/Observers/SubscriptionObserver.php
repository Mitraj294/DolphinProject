<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Models\Organization;
use Exception;

class SubscriptionObserver
{
    protected function updateOrgsFromSubscription(Subscription $subscription)
    {
        if (empty($subscription->user_id)) {
            return;
        }
        try {
            $orgs = Organization::where('user_id', $subscription->user_id)->get();
            foreach ($orgs as $org) {
                $org->contract_start = $subscription->subscription_start;
                $org->contract_end = $subscription->subscription_end;
                $org->save();
            }
        } catch (Exception $e) {
            // swallow errors to avoid breaking subscription flow; log if you have a logger
        }
    }

    public function created(Subscription $subscription)
    {
        $this->updateOrgsFromSubscription($subscription);
    }

    public function updated(Subscription $subscription)
    {
        $this->updateOrgsFromSubscription($subscription);
    }

    public function restored(Subscription $subscription)
    {
        $this->updateOrgsFromSubscription($subscription);
    }
}
