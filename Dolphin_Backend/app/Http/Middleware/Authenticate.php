<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * For API requests, this will return null, resulting in a JSON response
     * instead of a redirect.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request): ?string
    {
        // If the request expects a JSON response, don't redirect.
        // Laravel will automatically throw an AuthenticationException,
        // which is rendered as a 401 JSON response by the exception handler.
        return $request->expectsJson() ? null : route('login');
    }
}
