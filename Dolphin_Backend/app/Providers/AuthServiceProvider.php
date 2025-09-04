<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
   public function boot()
    {
        $this->registerPolicies();

        // Enable password grant type (required in Passport >= 12)
        Passport::enablePasswordGrant();

        // Declare API scopes
        Passport::tokensCan([
            'impersonate' => 'Act as another user (impersonation)',
        ]);

        // Token expiry configuration
        Passport::tokensExpireIn(now()->addHours(8));         // Access token = 8 hours
        Passport::refreshTokensExpireIn(now()->addDays(7));   // Refresh token = 7 days

        // Optional: set personal access token expiry
        Passport::personalAccessTokensExpireIn(now()->addMonths(2));
    }
}
