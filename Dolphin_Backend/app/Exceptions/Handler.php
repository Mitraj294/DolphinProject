<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Accept Throwable parameter; empty-parameter closures cause a runtime error in Laravel 11
        $this->reportable(function (Throwable $e) {
            // No-op: keep closure signature valid for Laravel 11 and mark parameter as used
            $tmp = $e; unset($tmp);
            // You can add conditional reporting or logging here if needed
        });
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}

