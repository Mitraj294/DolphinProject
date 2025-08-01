<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StripeSubscriptionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password reset routes (now using controller)
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Send assessment email to lead (public route)
    Route::post('/leads/send-assessment', [\App\Http\Controllers\SendAssessmentController::class, 'send']);


    Route::post('/stripe/webhook', [StripeSubscriptionController::class, 'handleWebhook']);

    Route::get('/users', [UserController::class, 'index']);
// Public prefill endpoint for registration form (by lead_id or email)
    Route::get('/leads/prefill', [LeadController::class, 'prefill']);
    Route::middleware('auth:api')->group(function () {
    // Endpoint to get current authenticated user (for frontend role sync)
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
    Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);
    Route::patch('/users/{id}/soft-delete', [UserController::class, 'softDelete']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::get('/questions', [AnswerController::class, 'getQuestions']);

    Route::post('/answers', [AnswerController::class, 'store']);      
    Route::get('/answers', [AnswerController::class, 'getUserAnswers']);

    Route::get('/assessments', [AssessmentController::class, 'show']);
    Route::post('/assessments', [AssessmentController::class, 'store']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);
    Route::delete('/profile', [AuthController::class, 'deleteAccount']);

    Route::post('/leads', [LeadController::class, 'store']);
    Route::get('/leads', [LeadController::class, 'index']);

    Route::patch('/leads/{id}', [LeadController::class, 'update']);

    Route::post('/stripe/checkout-session', [StripeSubscriptionController::class, 'createCheckoutSession']);
    Route::post('/stripe/customer-portal', [StripeSubscriptionController::class, 'createCustomerPortal']);
    Route::get('/subscription', [SubscriptionController::class, 'getUserSubscription']);
    Route::get('/user/subscription', [SubscriptionController::class, 'getUserSubscription']);


// Organization management
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/members', [MemberController::class, 'index']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);


// Organization CRUD
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::get('/organizations/{id}', [OrganizationController::class, 'show']);
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::put('/organizations/{id}', [OrganizationController::class, 'update']);
    Route::delete('/organizations/{id}', [OrganizationController::class, 'destroy']);


// New endpoint for subscription status
    Route::get('/subscription/status', [SubscriptionController::class, 'subscriptionStatus']);

// Billing details endpoints for frontend
    Route::get('/billing/current', [SubscriptionController::class, 'getCurrentPlan']);
    Route::get('/billing/history', [SubscriptionController::class, 'getBillingHistory']);

// Impersonation route
// Only accessible by authenticated superadmins
    Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate'])
        ->middleware('can:impersonate,user');

    
});