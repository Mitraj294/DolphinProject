<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the authenticated user has an active subscription.
     * It allows users with 'superadmin' or 'dolphinadmin' roles to bypass this check.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        // Assume the request will proceed unless a check fails.
        $response = $next($request);

        // The 'auth' middleware should handle unauthenticated users.
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Admins can bypass the subscription check. If the user is an admin,
        // the default response ($next($request)) will be returned.
        if (!$user->hasAnyRole('superadmin', 'dolphinadmin')) {
            $subscription = $user->activeSubscription;

            if (!$subscription) {
                $response = response()->json([
                    'message' => 'Your subscription has expired. Please renew to continue using the service.',
                    'subscription_status' => 'inactive'
                ], 403);
            } elseif ($subscription->ends_at && now()->greaterThan($subscription->ends_at)) {
                $response = response()->json([
                    'message' => 'Your subscription has expired on ' . $subscription->ends_at->toDateString() . '. Please renew to continue using the service.',
                    'subscription_status' => 'expired'
                ], 403);
            }
        }

        return $response;
    }
}

