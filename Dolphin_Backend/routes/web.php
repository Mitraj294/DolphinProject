<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Password reset route for email link 
Route::get('password/reset/{token}', function ($token) {
    $email = request('email');
    // Redirect to frontend reset page with token and email as query params
    return redirect("http://127.0.0.1:8080/reset-password?token=$token&email=$email");
})->name('password.reset');
