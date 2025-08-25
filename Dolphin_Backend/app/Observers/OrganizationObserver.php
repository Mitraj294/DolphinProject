<?php

namespace App\Observers;

use App\Models\Organization;
use App\Models\Subscription;
use Exception;

class OrganizationObserver
{
    protected function applyLatestSubscriptionToOrg(Organization $organization)
    {
        try {
            // find latest subscription for the owner user_id
            if (empty($organization->user_id)) return;
            $latest = Subscription::where('user_id', $organization->user_id)
                ->orderBy('subscription_start', 'desc')
                ->first();
            if ($latest) {
                // Only update and save if contract dates actually change to avoid recursive observer calls
                $changed = false;
                if ($organization->contract_start != $latest->subscription_start) {
                    $organization->contract_start = $latest->subscription_start;
                    $changed = true;
                }
                if ($organization->contract_end != $latest->subscription_end) {
                    $organization->contract_end = $latest->subscription_end;
                    $changed = true;
                }
                if ($changed) {
                    $organization->save();
                }
            }
        } catch (Exception $e) {
            // ignore
        }
    }

    public function created(Organization $organization)
    {
        $this->applyLatestSubscriptionToOrg($organization);
    }

    public function updated(Organization $organization)
    {
        // if organization user_id changed, re-sync
        $this->applyLatestSubscriptionToOrg($organization);
    }
}
