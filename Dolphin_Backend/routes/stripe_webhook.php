<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeSubscriptionController;

// ...existing routes...

// Stripe webhook endpoint (no auth middleware)
Route::post('/stripe/webhook', [StripeSubscriptionController::class, 'handleWebhook']);
