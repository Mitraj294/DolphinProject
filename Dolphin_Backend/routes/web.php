<?php

use Illuminate\Support\Facades\Route;

$FRONTEND_URL = env('FRONTEND_URL');
Route::get('/', function () {
    return view('welcome');
});

// Password reset route for email link
Route::get('password/reset/{token}', function ($token) {
    $email = request()->query('email');
    // Build frontend URL from environment and redirect with URL-encoded params
    $frontend = env('FRONTEND_URL');
    $url = $frontend . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email);
    return redirect($url);
})->name('password.reset');

// Provide a simple named login route so calls to route('login') during
// authentication/exception handling don't throw a RouteNotFoundException.
// This redirects to the frontend login page.
Route::get('/login', function () {
    $frontend = env('FRONTEND_URL');
    return redirect($frontend . '/login');
})->name('login');


