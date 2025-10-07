<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    
    /**
     * Check if a subscription has expired.
     *
     * @param Subscription $subscription
     * @return bool
     */
    public function hasExpired(Subscription $subscription): bool
    {
        // If no end date is set, consider it as never expiring
        if (!$subscription->subscription_end) {
            return false;
        }
        
        // Compare the end date with the current time
        return $subscription->subscription_end->isPast();
    }

    /**
     * Check if a subscription is currently active (not expired).
     *
     * @param Subscription $subscription
     * @return bool
     */
    public function isActive(Subscription $subscription): bool
    {
        return $subscription->status === 'active' && !$this->hasExpired($subscription);
    }

    /**
     * Update subscription status if it has expired and return the status.
     *
     * @param Subscription $subscription
     * @return bool True if active, false if expired
     */
    public function checkAndUpdateStatus(Subscription $subscription): bool
    {
        if ($this->hasExpired($subscription) && $subscription->status === 'active') {
            $subscription->update(['status' => 'expired']);
            return false;
        }
        
        return $subscription->status === 'active';
    }
    
    //Get the current active subscription plan for the relevant user.
    //Accessible by the user or by a superadmin viewing a specific organization.
     
    public function getCurrentPlan(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json(null);
        }

        $currentSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->latest('created_at')
            ->first();

        if (!$currentSubscription) {
            return response()->json(null);
        }

        // The formatting logic is now handled by the Subscription model's accessors.
        return response()->json($this->formatPlanPayload($currentSubscription));
    }

    //Get the entire billing history for the relevant user.
     
    public function getBillingHistory(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([]);
        }

        $history = $user->subscriptions()
            ->latest('created_at')
            ->get()
            ->map(fn($subscription) => $this->formatHistoryPayload($subscription));

        return response()->json($history);
    }

    //Get a simple subscription status for the relevant user.
    
    public function subscriptionStatus(Request $request)
    {
        $user = $this->resolveUser($request);

        $subscription = $user?->subscriptions()->latest('created_at')->first();

        return response()->json([
            'status' => $subscription?->status ?? 'none',
            'plan_amount' => $subscription?->amount,
            'period' => $subscription?->amount == 2500 ? 'Annual' : 'Monthly',
            'plan_name' => $subscription?->amount == 2500 ? 'Dolphin Standard Annual Plan' : 'Dolphin Basic Monthly Plan',
            'subscription_id' => $subscription?->id,
            'subscription_end' => $subscription?->subscription_end?->toDateTimeString(),
        ]);
    }

    /*

    | Helper & Formatting Methods

    */

    /**
     * Resolve the user for the request.
     * If the requester is a superadmin and an org_id is provided, it will
     * return the organization's owner. Otherwise, it returns the authenticated user.
     */
    private function resolveUser(Request $request): ?User
    {
        $authenticatedUser = $request->user();
        $orgId = $request->query('org_id') ?: $request->input('org_id');

        if ($orgId && $authenticatedUser->hasRole('superadmin')) {
            $organization = Organization::find($orgId);
            return $organization?->user;
        }

        return $authenticatedUser;
    }

    /**
     * Format the payload for the current plan response.
     */
    private function formatPlanPayload(Subscription $subscription): array
    {
        return [
            'plan' => $subscription->plan,
            'plan_name' => $subscription->plan_name,
            'amount' => $subscription->amount,
            'period' => $subscription->amount == 2500 ? 'Annual' : 'Monthly',
            'status' => $subscription->status,
            'payment_method' => $subscription->payment_method_type . ' ' . $subscription->payment_method ,
            'start' => $subscription->subscription_start,
            'end' => $subscription->subscription_end,
            'nextBill' => $subscription->subscription_end?->addDay()->toDateTimeString(),
        ];
    }

    /**
     * Format the payload for a billing history item.
     */
    private function formatHistoryPayload(Subscription $subscription): array
    {
        // Ensure payment details array exists
        $paymentDetails = $subscription->payment_method_details;
        if (!is_array($paymentDetails)) {
            $paymentDetails = [];
        }

        return [
           
            'payment_method' => $subscription->payment_method_type . ' ' . $subscription->payment_method,
            'paymentEmail' => $subscription->customer_email,
            'paymentDate' => $subscription->payment_date ?? $subscription->created_at,
            'subscriptionEnd' => $subscription->subscription_end,
            'amount' => $subscription->amount,
            'pdfUrl' => $subscription->receipt_url,
            'description' => $subscription->amount == 2500 ? 'DOLPHIN Standard Subscription (at $2500.00 / year)' : 'DOLPHIN Basic Subscription (at $250.00 / month)',
         
            'invoiceNumber' => $subscription->invoice_number,
        ];
    }
}