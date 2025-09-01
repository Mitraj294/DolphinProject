<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Subscription;
use App\Models\Organization;
use App\Models\User;
use App\Observers\SubscriptionObserver;
use App\Observers\OrganizationObserver;
use App\Observers\UserObserver;

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
    }
}