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
use App\Http\Controllers\OrganizationAssessmentQuestionController;
use App\Http\Controllers\AssessmentAnswerLinkController;
use App\Http\Controllers\ScheduledEmailController;
use App\Http\Controllers\NotificationController;

/*
 Public Routes
 These routes do not require any authentication.
*/
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);
Route::post('/stripe/webhook', [StripeSubscriptionController::class, 'handleWebhook']);

// Public assessment summary (allow unauthenticated access for diagnostics/front-end)
Route::get('/assessment/{id}/summary', [AssessmentController::class, 'summary']);

// Public endpoints for leads and assessments
Route::get('/leads/prefill', [LeadController::class, 'prefill']);
Route::get('/leads/find-us-options', [LeadController::class, 'findUsOptions']);
Route::post('/leads/send-assessment', [\App\Http\Controllers\SendAssessmentController::class, 'send']);
// Return rendered email templates for frontend editor prefills
Route::get('/email-template/lead-registration', [LeadController::class, 'leadRegistration']);

Route::post('/schedule-email', [\App\Http\Controllers\ScheduledEmailController::class, 'store']);
Route::get('/scheduled-email/show', [\App\Http\Controllers\ScheduledEmailController::class, 'show']);

// Public assessment answer links, specifically without Sanctum middleware
Route::post('/assessment/send-link', [AssessmentAnswerLinkController::class, 'sendLink']);
Route::get('/assessment/answer/{token}', [AssessmentAnswerLinkController::class, 'getAssessmentByToken']);
Route::post('/assessment/answer/{token}', [AssessmentAnswerLinkController::class, 'submitAnswers'])
    ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class]);

// Public location endpoints for dropdowns
Route::get('/countries', [\App\Http\Controllers\LocationController::class, 'countries']);
Route::get('/states', [\App\Http\Controllers\LocationController::class, 'states']);
Route::get('/cities', [\App\Http\Controllers\LocationController::class, 'cities']);
Route::get('/countries/{id}', [\App\Http\Controllers\LocationController::class, 'getCountryById']);
Route::get('/states/{id}', [\App\Http\Controllers\LocationController::class, 'getStateById']);
Route::get('/cities/{id}', [\App\Http\Controllers\LocationController::class, 'getCityById']);

/*
 Authenticated Routes (General)
 These routes require a valid access token but are accessible to all roles.
*/
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::delete('/profile', [AuthController::class, 'deleteAccount']);

    // Notifications for all users
    Route::get('/announcements/user', [NotificationController::class, 'userAnnouncements']);
    Route::get('/notifications/unread', [NotificationController::class, 'unreadAnnouncements']);
    // Returns notifications for the currently authenticated user
    Route::get('/notifications/user', [NotificationController::class, 'userNotifications']);
    Route::post('/announcements/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    // Allow authenticated users to create assessments
    Route::post('/assessments', [AssessmentController::class, 'store']);
    Route::get('/assessments', [AssessmentController::class, 'show'])->withoutMiddleware(['auth:api', 'auth']);
    Route::get('/questions', [AnswerController::class, 'getQuestions']);
    Route::post('/answers', [AnswerController::class, 'store']);
    Route::get('/answers', [AnswerController::class, 'getUserAnswers']);
    Route::get('/organization-assessment-questions', [OrganizationAssessmentQuestionController::class, 'index']);

    // Stripe endpoints: allow any authenticated user to create a checkout session or open customer portal
    Route::post('/stripe/checkout-session', [StripeSubscriptionController::class, 'createCheckoutSession']);
    Route::post('/stripe/customer-portal', [StripeSubscriptionController::class, 'createCustomerPortal']);

    // These routes are accessible to all roles, but the logic inside the controller may differ.
    Route::get('/subscription', [SubscriptionController::class, 'getUserSubscription']);
    Route::get('/user/subscription', [SubscriptionController::class, 'getUserSubscription']);
    Route::get('/subscription/status', [SubscriptionController::class, 'subscriptionStatus']);
    Route::get('/billing/current', [SubscriptionController::class, 'getCurrentPlan']);
    Route::get('/billing/history', [SubscriptionController::class, 'getBillingHistory']);

    /*
    
 Authenticated Routes (Role-based)
    
 These routes are protected by the new `auth.role` middleware.

    */

    // Superadmin only routes
    Route::middleware('auth.role:superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
        Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);
        Route::patch('/users/{id}/soft-delete', [UserController::class, 'softDelete']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate']);
        Route::get('/organizations', [OrganizationController::class, 'index']);
        Route::get('/organizations/{id}', [OrganizationController::class, 'show']);
        Route::post('/organizations', [OrganizationController::class, 'store']);
        Route::put('/organizations/{id}', [OrganizationController::class, 'update']);
        Route::delete('/organizations/{id}', [OrganizationController::class, 'destroy']);
        Route::get('/announcements', [NotificationController::class, 'allAnnouncements']);
    Route::get('/announcements/{id}', [NotificationController::class, 'showAnnouncement']);
        Route::post('/announcements/send', [NotificationController::class, 'send']);
        Route::get('/notifications', [NotificationController::class, 'allNotifications']);
    });

    // Dolphin Admin specific routes (per permissions.js)
    Route::middleware('auth.role:dolphinadmin,superadmin')->group(function () {
        Route::post('/leads', [LeadController::class, 'store']);
    Route::get('/leads/{id}', [LeadController::class, 'show']);
        Route::get('/leads', [LeadController::class, 'index']);
        Route::patch('/leads/{id}', [LeadController::class, 'update']);
        
    Route::post('/notifications/send', [NotificationController::class, 'send']);

        Route::post('/notifications/send', [NotificationController::class, 'send']);
    });

    // Organization Admin specific routes (per permissions.js)
    Route::middleware('auth.role:organizationadmin,superadmin')->group(function () {
        Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/groups/{id}', [GroupController::class, 'show']);
        Route::post('/groups', [GroupController::class, 'store']);
        Route::get('/members', [MemberController::class, 'index']);
    Route::get('/members/{id}', [MemberController::class, 'show']);
        Route::post('/members', [MemberController::class, 'store']);
        Route::put('/members/{id}', [MemberController::class, 'update']);
        Route::delete('/members/{id}', [MemberController::class, 'destroy']);
    });

    // Allow assessment scheduling for dolphin admins, organization admins, and superadmins
    Route::middleware('auth.role:dolphinadmin,organizationadmin,superadmin')->group(function () {
        Route::post('/assessment-schedules', [\App\Http\Controllers\AssessmentScheduleController::class, 'store']);
    });
});

