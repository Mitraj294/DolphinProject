<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Organization;
use App\Models\Subscription;
use App\Http\Controllers\SubscriptionController;

class CheckSubscriptionStatus
{
    /**
     * The subscription controller instance.
     *
     * @var SubscriptionController
     */
    protected SubscriptionController $subscriptionController;

    /**
     * Create a new middleware instance.
     */
    public function __construct(SubscriptionController $subscriptionController)
    {
        $this->subscriptionController = $subscriptionController;
    }

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
    // when true, an organization-admin without an active org subscription must be blocked
    $forceBlock = false;
        $blockContext = [
            'latest' => null,
            'status' => 'none',
            'message' => 'You have not selected any plans yet.',
        ];

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
                    // Check if the organization's subscription has expired in real-time
                    if ($this->subscriptionController->hasExpired($active)) {
                        // Update status for consistency
                        $active->update(['status' => 'expired']);
                        $forceBlock = true;
                        $blockContext['latest'] = $active;
                        $blockContext['status'] = 'expired';
                        $blockContext['message'] = 'Your organization\'s subscription has expired. Please renew your subscription to continue.';
                    } else {
                        $allow = true;
                    }
                } else {
                    // No active subscription — we must block this organization admin (do not fall through to other exemptions)
                    $forceBlock = true;
                    $latest = Subscription::where('user_id', $organization->user_id)->orderByDesc('created_at')->first();
                    $blockContext['latest'] = $latest;
                    $blockContext['status'] = $latest?->status ?? 'none';
                    $blockContext['message'] = $blockContext['status'] === 'expired'
                        ? 'Your organization\'s subscription has expired. Please renew your subscription to continue.'
                        : 'You have not selected any plans yet.';
                }
            }
            // If there is no organization resolved, fall through to normal checks.
        }

        // If the request is unauthenticated, allow to proceed (assume auth middleware handles access)
        if (! Auth::check()) {
            $allow = true;
        }

        // If a forceBlock condition was set (organization admin with no active org subscription),
        // skip further exemptions and prepare to block below.
        if (! $forceBlock) {
            // If user has any exempt role, allow
            if (! $allow && $this->userHasAnyExemptRole($user)) {
                $allow = true;
            }

            // If user has an active subscription, allow
            if (! $allow && $this->userHasActiveSubscription($user)) {
                $allow = true;
            }
        }

        // If allowed, pass the request onward (single return for allowed path)
        if ($allow) {
            return $next($request);
        }

        // Prepare block payload: prefer organization-level context if forceBlock, otherwise use user-level latest
        if ($forceBlock) {
            $latest = $blockContext['latest'];
            $status = $blockContext['status'];
            $message = $blockContext['message'];
        } else {
            $latest = $user?->subscriptions()->orderByDesc('created_at')->first();
            $status = $latest?->status ?? 'none';
            $message = $status === 'expired'
                ? 'Your subscription has expired. Please renew your subscription to continue.'
                : 'You have not selected any plans yet.';
        }

        // API / JSON responses (single return for blocked path)
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
            // Get the latest subscription and check if it's truly active (not expired)
            $subscription = $user->subscriptions()
                ->where('status', 'active')
                ->orderByDesc('created_at')
                ->first();
            
            if ($subscription) {
                // Check if subscription has expired in real-time using controller
                if ($this->subscriptionController->hasExpired($subscription)) {
                    // Optionally update the status in database for consistency
                    $subscription->update(['status' => 'expired']);
                    return false;
                }
                return true;
            }
        }

        return false;
    }
}