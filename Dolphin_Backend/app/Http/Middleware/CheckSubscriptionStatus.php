<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the authenticated user has an active subscription.
     * If not, it blocks access to the route. The decision of which routes
     * to protect is handled in the web/api routes files.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        // Skip checks for unauthenticated users, admin/sales roles, or users with an active subscription
        $skip = !Auth::check()
            || (method_exists($user, 'hasRole') && (
                $user->hasRole('superadmin') ||
                $user->hasRole('dolphinadmin') ||
                $user->hasRole('saleperson') ||
                $user->hasRole('user')
            ))
            || ($user && $user->subscriptions()->where('status', 'active')->exists());
        if ($skip) {
            return $next($request);
        }

        // --- If no active subscription is found, BLOCK access ---

        // Optionally, get the latest subscription to provide more context in the error message.
        $latestSubscription = $user->subscriptions()->orderByDesc('created_at')->first();
        $status = $latestSubscription ? $latestSubscription->status : 'none';
        $message = $status === 'expired'
            ? 'Your subscription has expired. Please renew your subscription to continue.'
            : 'You do not have an active subscription. Please subscribe to access this feature.';

        // For API requests, return a standardized JSON 403 error.
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'status' => $status,
                'subscription_end' => $latestSubscription?->subscription_end?->toDateTimeString(),
                'subscription_id' => $latestSubscription?->id,
                'redirect_url' => '/manage-subscription'
            ], 403);
        }
        
        // For standard web requests, redirect them to the subscription management page.
        return redirect('/manage-subscription')->with('error', $message);
    }
}