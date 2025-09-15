<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check subscription status for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Get the user's most recent subscription
        $subscription = $user->subscriptions()->orderByDesc('created_at')->first();
        
        // Check if subscription exists and is expired
        if ($subscription && $subscription->status === 'expired') {
            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Your subscription has expired. Please renew your subscription to continue using this service.',
                    'status' => 'expired',
                    'subscription_end' => $subscription->subscription_end ? $subscription->subscription_end->toDateTimeString() : null,
                    'subscription_id' => $subscription->id,
                    'redirect_url' => '/manage-subscription'
                ], 403);
            }
            
            // For web requests, redirect to manage-subscription page
            return redirect('/manage-subscription')
                ->with('error', 'Your subscription has expired. Please renew your subscription to continue using this service.')
                ->with('subscription_data', [
                    'status' => 'expired',
                    'subscription_end' => $subscription->subscription_end ? $subscription->subscription_end->toDateTimeString() : null,
                    'subscription_id' => $subscription->id
                ]);
        }

        // Allow access if no subscription exists (new users) or subscription is not expired
        return $next($request);
    }
}
