<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;
use App\Models\Organization;
use App\Models\User;

class SubscriptionController extends Controller


{
    // Get the current user's active subscription (if any)
    public function getUserSubscription()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();
        if (!$subscription) {
            // No active subscription, allow resubscribe
            return response()->json(['plan_amount' => null, 'current_subscription' => null, 'history' => []]);
        }
        // Get billing history (all subscriptions for user)
        $history = Subscription::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();
        return response()->json([
            'plan_amount' => (float) $subscription->amount,
            'current_subscription' => $subscription,
            'history' => $history,
        ]);
    }

        /**
     * Update the user's role when subscription changes (upgrade/downgrade).
     * Call this after subscription is created or updated.
     * @param \App\Models\User $user
     * @param string $roleName (e.g. 'organizationadmin' or 'user')
     */
    public function updateUserRoleOnSubscription($user, $roleName)
    {
        $role = \App\Models\Role::where('name', $roleName)->first();
        if ($role) {
            // Remove all roles and assign the new one (single role per user)
            $user->roles()->sync([$role->id]);
        }
    }

    /**
     * Get the current (latest active) subscription for the authenticated user
     */
    public function getCurrentPlan(Request $request)
    {
        $user = $request->user();
        $orgId = $request->query('org_id') ?: $request->input('org_id');
        // If org_id provided and the current user is superadmin, resolve the owning user
        if ($orgId && $user && $user->hasRole('superadmin')) {
            $org = \App\Models\Organization::find($orgId);
            if ($org && $org->user_id) {
                $user = \App\Models\User::find($org->user_id);
            }
        }
        if (!$user) { return response()->json(null); }
        $current = $user->subscriptions()
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->first();
        if (!$current) {
            return response()->json(null);
        }
        $subEnd = $current->subscription_end ?? null;
        $nextBill = '';
        if ($subEnd) {
            try {
                $dt = \Carbon\Carbon::parse($subEnd);
                $nextBill = $dt->copy()->addDay()->toDateString();
            } catch (\Exception $e) {
                $nextBill = $subEnd;
            }
        }
        // Derive a friendly plan name and billing period for the frontend
        $planName = $current->plan ?? '';
        if (!empty($current->description)) {
            if (stripos($current->description, 'basic') !== false) {
                $planName = 'Basic';
            } elseif (stripos($current->description, 'standard') !== false) {
                $planName = 'Standard';
            }
        }
        // Determine period: check description first, then amount heuristic
        $period = '';
        if (!empty($current->description)) {
            if (stripos($current->description, 'month') !== false) { $period = 'Month'; }
            elseif (stripos($current->description, 'year') !== false) { $period = 'Year'; }
        }
        if (empty($period)) {
            $amt = floatval($current->amount ?? 0);
            $period = $amt >= 1000 ? 'Year' : 'Month';
        }

        return response()->json([
            'plan' => $current->plan ?? '',
            'plan_name' => $planName,
            'amount' => $current->amount ?? '',
            'period' => $period,
            'status' => $current->status ?? '',
            'payment_method' => $current->payment_method ?? '',
            'start' => $current->subscription_start ?? $current->created_at,
            'end' => $current->subscription_end ?? '',
            'nextBill' => $nextBill,
        ]);
    }
  


    /**
     * Get all subscriptions (history) for the authenticated user
     */
    public function getBillingHistory(Request $request)
    {
        $user = $request->user();
        $orgId = $request->query('org_id') ?: $request->input('org_id');
        // If org_id provided and user is superadmin, resolve org owner
        if ($orgId && $user && $user->hasRole('superadmin')) {
            $org = \App\Models\Organization::find($orgId);
            if ($org && $org->user_id) {
                $user = \App\Models\User::find($org->user_id);
            }
        }
        if (!$user) { return response()->json([]); }
        $subs = $user->subscriptions()->orderByDesc('created_at')->get();
        $history = $subs->map(function($sub) {
            // Use new specific payment method fields if available, fallback to old payment_method
            $paymentMethodType = '';
            $paymentMethod = '';
            $paymentEmail = '';
            $cardLast4 = '';
            
            // Check if we have detailed payment method information
            if ($sub->payment_method_type && $sub->payment_method_brand && $sub->payment_method_last4) {
                $paymentMethodType = $sub->payment_method_type;
                
                if ($sub->payment_method_type === 'card') {
                    $paymentMethod = ucfirst($sub->payment_method_brand) . ' ****' . $sub->payment_method_last4;
                    $cardLast4 = $sub->payment_method_last4;
                } else {
                    $paymentMethod = ucfirst($sub->payment_method_type);
                }
            } else {
                // Fallback to old payment_method field
                $paymentMethod = $sub->payment_method ?? '';
                
                if ($paymentMethod && stripos($paymentMethod, 'paypal') !== false) {
                    $paymentMethodType = 'paypal';
                    $paymentEmail = $sub->customer_email ?? '';
                } elseif ($paymentMethod && stripos($paymentMethod, 'card') !== false) {
                    $paymentMethodType = 'card';
                    // Try to get last4 from meta or description if available
                    if (!empty($sub->meta) && is_array($sub->meta) && isset($sub->meta['card_last4'])) {
                        $cardLast4 = $sub->meta['card_last4'];
                    } elseif (isset($sub->description) && preg_match('/\d{4}/', $sub->description, $m)) {
                        $cardLast4 = $m[0];
                    }
                }
            }
            
            return [
                'paymentMethodType' => $paymentMethodType,
                'paymentMethod' => $paymentMethod,
                'paymentEmail' => $paymentEmail,
                'cardLast4' => $cardLast4,
                'paymentDate' => $sub->payment_date ?? $sub->created_at,
                'subscriptionEnd' => $sub->subscription_end ?? '',
                'amount' => $sub->amount ?? '',
                'pdfUrl' => $sub->receipt_url ?? '',
                'description' => $sub->description ?? '',
                'invoiceNumber' => $sub->invoice_number ?? '',
            ];
        });
        return response()->json($history);
    }
        /**
     * Get the current user's subscription status (for /api/subscription/status)
     */
    public function subscriptionStatus(Request $request)
    {
        $user = $request->user();
        // If org_id provided and caller is superadmin or organizationadmin, resolve the organization's owner
        $orgId = $request->query('org_id') ?: $request->input('org_id');
        if ($orgId && $user && ($user->hasRole('superadmin') || $user->hasRole('organizationadmin'))) {
            $org = Organization::find($orgId);
            if ($org && $org->user_id) {
                $user = User::find($org->user_id) ?: $user;
            }
        }

        // Find the most recent subscription for the user, regardless of status.
        // The scheduled command `subscriptions:update-status` ensures the status is authoritative.
        $subscription = $user ? $user->subscriptions()->orderByDesc('created_at')->first() : null;

        $subscriptionEnd = null;
        if ($subscription && $subscription->subscription_end) {
            $subscriptionEnd = $subscription->subscription_end->toDateTimeString();
        }

        return response()->json([
            'status' => $subscription ? $subscription->status : 'none',
            'plan_amount' => $subscription ? $subscription->amount : null,
            'subscription_id' => $subscription ? $subscription->id : null,
            'subscription_end' => $subscriptionEnd,
        ]);
    }
}
