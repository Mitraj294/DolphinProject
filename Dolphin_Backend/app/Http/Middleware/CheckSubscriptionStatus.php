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
     * Roles that are exempt from subscription checks.
     *
     * @var string[]
     */
    protected array $exemptRoles = [
        'superadmin',
        'dolphinadmin',
        'saleperson',
        'user',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();
        $allow = false;

        // If the request is unauthenticated, allow to proceed (assume auth middleware handles access)
        if (! Auth::check()) {
            $allow = true;
        }

        // If user has any exempt role, allow
        if (! $allow && $this->userHasAnyExemptRole($user)) {
            $allow = true;
        }

        // If user has an active subscription, allow
        if (! $allow && $this->userHasActiveSubscription($user)) {
            $allow = true;
        }

        if ($allow) {
            return $next($request);
        }

        // Otherwise, block access and provide context about the latest subscription (if any)
        $latest = $user->subscriptions()->orderByDesc('created_at')->first();
        $status = $latest?->status ?? 'none';

        $message = $status === 'expired'
            ? 'Your subscription has expired. Please renew your subscription to continue.'
            : 'You do not have an active subscription. Please subscribe to access this feature.';

        // API / JSON responses
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'status' => $status,
                'subscription_end' => $latest?->subscription_end?->toDateTimeString(),
                'subscription_id' => $latest?->id,
                'redirect_url' => url('/manage-subscription'),
            ], 403);
        }

        // Web responses: redirect to subscription management with a flash message
        return redirect('/manage-subscription')->with('error', $message);
    }

    /**
     * Determine if the user has any role that exempts them from subscription checks.
     */
    protected function userHasAnyExemptRole(?User $user): bool
    {
        $has = false;

        if (! $user) {
            return $has;
        }

        if (method_exists($user, 'hasAnyRole')) {
            $has = (bool) $user->hasAnyRole(...$this->exemptRoles);
        } elseif (method_exists($user, 'hasRole')) {
            foreach ($this->exemptRoles as $role) {
                if ($user->hasRole($role)) {
                    $has = true;
                    break;
                }
            }
        }

        return $has;
    }

    /**
     * Check whether the user has at least one active subscription record.
     */
    protected function userHasActiveSubscription(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'subscriptions')) {
            return $user->subscriptions()->where('status', 'active')->exists();
        }

        return false;
    }
}