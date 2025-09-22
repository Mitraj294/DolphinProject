<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Organization;
use App\Models\Subscription;

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
        'salesperson',
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

        // If the user is an organization admin, first check the organization's
        // subscription status. Organization admins should be allowed only when
        // their organization (owner) has an active subscription. If the org has
        // no subscription at all, we block and show a "You have not selected any plans yet." message.
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('organizationadmin')) {
            $organization = null;
            if (! empty($user->organization_id)) {
                $organization = Organization::find($user->organization_id);
            }

            if (! $organization && method_exists($user, 'organization')) {
                $organization = $user->organization()->first();
            }

            if ($organization) {
                // activeSubscription is a hasOne constrained to status='active'
                $active = $organization->activeSubscription()->first();
                if ($active) {
                    return $next($request);
                }

                // No active subscription — inspect latest subscription to craft the message
                $latest = Subscription::where('user_id', $organization->user_id)->orderByDesc('created_at')->first();
                $status = $latest?->status ?? 'none';

                $message = $status === 'expired'
                    ? 'Your subscription has expired. Please renew your subscription to continue.'
                    : 'You have not selected any plans yet.';

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => $message,
                        'status' => $status,
                        'subscription_end' => $latest?->subscription_end?->toDateTimeString(),
                        'subscription_id' => $latest?->id,
                        'redirect_url' => url('/manage-subscription'),
                    ], 403);
                }

                return redirect('/manage-subscription')->with('error', $message);
            }
            // If there is no organization resolved, fall through to normal checks.
        }

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

        // Treat 'none' (no subscription) the same as blocked/expired for feature access.
        $message = $status === 'expired'
            ? 'Your subscription has expired. Please renew your subscription to continue.'
            : 'You have not selected any plans yet.';

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