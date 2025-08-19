<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request)
    {
        // For API requests we don't want to attempt a web redirect (route('login'))
        // because the named route may not exist. Let the exception handler
        // return a JSON 401 instead by returning null here for API calls.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // For web requests, fall back to a simple path instead of calling route()
        // to avoid RouteNotFoundException during early boot situations.
        return '/login';
    }
}
