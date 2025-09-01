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
    public function boot(): void
    {
        $this->registerPolicies();
    Passport::routes();
    // Set token lifetimes to 8 hours (was default 1 year)
    $eightHours = new \DateInterval('PT8H');
    Passport::tokensExpireIn($eightHours);
    Passport::refreshTokensExpireIn($eightHours);
    Passport::personalAccessTokensExpireIn($eightHours);
    }
}
