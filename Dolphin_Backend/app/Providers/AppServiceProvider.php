<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Subscription;
use App\Models\Organization;
use App\Models\User;
use App\Observers\SubscriptionObserver;
use App\Observers\OrganizationObserver;
use App\Observers\UserObserver;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    // Register model observers so organization contract dates are kept in sync
    Subscription::observe(SubscriptionObserver::class);
    Organization::observe(OrganizationObserver::class);
    User::observe(UserObserver::class);

    // Ensure Passport password grant is enabled (Passport >= 12)
    Passport::enablePasswordGrant();

    // Set Passport token expirations (applies at issue-time for NEW tokens)
    // Access tokens: 8 hours; Refresh tokens: 7 days; Personal access tokens: 2 months
    \Laravel\Passport\Passport::tokensExpireIn(now()->addHours(8));
    \Laravel\Passport\Passport::refreshTokensExpireIn(now()->addDays(7));
    \Laravel\Passport\Passport::personalAccessTokensExpireIn(now()->addMonths(2));
    }
}