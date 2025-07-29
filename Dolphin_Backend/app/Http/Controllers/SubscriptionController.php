<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;

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
     * Get the current (latest active) subscription for the authenticated user
     */
    public function getCurrentPlan(Request $request)
    {
        $user = $request->user();
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
        return response()->json([
            'plan' => $current->plan ?? '',
            'amount' => $current->amount ?? '',
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
        $subs = $user->subscriptions()->orderByDesc('created_at')->get();
        $history = $subs->map(function($sub) {
            return [
                'paymentMethod' => $sub->payment_method ?? '',
                'paymentDate' => $sub->payment_date ?? $sub->created_at,
                'subscriptionEnd' => $sub->subscription_end ?? '',
                'amount' => $sub->amount ?? '',
                'pdfUrl' => $sub->receipt_url ?? '',
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
        $subscription = $user->subscriptions()->where('status', 'active')->orderByDesc('created_at')->first();
        return response()->json([
            'status' => $subscription ? $subscription->status : 'none',
            'plan_amount' => $subscription ? $subscription->amount : null,
            'subscription_id' => $subscription ? $subscription->id : null,
        ]);
    }
}
