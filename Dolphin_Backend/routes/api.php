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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password reset routes (now using controller)
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Laravel notifications endpoints
// Announcement endpoints
Route::middleware('auth:api')->group(function () {
    Route::get('/announcements', [\App\Http\Controllers\NotificationController::class, 'allAnnouncements']);
    Route::post('/announcements/send', [\App\Http\Controllers\NotificationController::class, 'send']);
    Route::get('/announcements/user', [\App\Http\Controllers\NotificationController::class, 'userAnnouncements']);
    Route::get('/announcements/unread', [\App\Http\Controllers\NotificationController::class, 'unreadAnnouncements']);
    Route::post('/announcements/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/announcements/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead']);
});
// Schedule an email (public route)
Route::post('/schedule-email', [\App\Http\Controllers\ScheduledEmailController::class, 'store']);
// Scheduled email status/details endpoint for frontend modal
Route::get('/scheduled-email/show', [\App\Http\Controllers\ScheduledEmailController::class, 'show']);

// Send assessment email to lead (public route)
Route::post('/leads/send-assessment', [\App\Http\Controllers\SendAssessmentController::class, 'send']);

// Assessment answer link routes
Route::post('/assessment/send-link', [AssessmentAnswerLinkController::class, 'sendLink']);
Route::get('/assessment/answer/{token}', [AssessmentAnswerLinkController::class, 'getAssessmentByToken']);

Route::post('/assessment/answer/{token}', [AssessmentAnswerLinkController::class, 'submitAnswers'])
    ->withoutMiddleware([
        'auth:api', 'auth',
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class
    ]);


    Route::post('/stripe/webhook', [StripeSubscriptionController::class, 'handleWebhook']);

    Route::get('/users', [UserController::class, 'index']);
// Public prefill endpoint for registration form (by lead_id or email)
    Route::get('/leads/prefill', [LeadController::class, 'prefill']);
    // Organization assessment questions
    Route::get('/organization-assessment-questions', [OrganizationAssessmentQuestionController::class, 'index']);

// Public answer endpoints (no auth, no Sanctum)
Route::get('/answer/{token}', [\App\Http\Controllers\AssessmentAnswerController::class, 'show'])
    ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class]);
Route::post('/answer/{token}', [\App\Http\Controllers\AssessmentAnswerController::class, 'submit'])
    ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class]);

Route::get('/assessment/{id}/summary', [AssessmentController::class, 'summary']);
// Location endpoints for country/state/city dropdowns (public)
Route::get('/countries', [\App\Http\Controllers\LocationController::class, 'countries']);
Route::get('/countries/{id}', [\App\Http\Controllers\LocationController::class, 'getCountryById']);
Route::get('/states', [\App\Http\Controllers\LocationController::class, 'states']);
Route::get('/states/{id}', [\App\Http\Controllers\LocationController::class, 'getStateById']);
Route::get('/cities', [\App\Http\Controllers\LocationController::class, 'cities']);
Route::get('/cities/{id}', [\App\Http\Controllers\LocationController::class, 'getCityById']);

Route::middleware('auth:api')->group(function () {

    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::get('/notifications/unread', [NotificationController::class, 'unreadAnnouncements']);
    Route::get('/notifications/user', [NotificationController::class, 'userNotifications']);
    Route::get('/notifications', [NotificationController::class, 'allNotifications']);
    // Endpoint to get current authenticated user (for frontend role sync)
    Route::get('/user', [\App\Http\Controllers\AuthController::class, 'user']);

// Public endpoint for frontend to fetch all notifications

    Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);
    Route::patch('/users/{id}/soft-delete', [UserController::class, 'softDelete']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::get('/questions', [AnswerController::class, 'getQuestions']);

    Route::post('/answers', [AnswerController::class, 'store']);      
    Route::get('/answers', [AnswerController::class, 'getUserAnswers']);

    Route::get('/assessments', [AssessmentController::class, 'show'])->withoutMiddleware(['auth:api', 'auth']);
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
    // For notifications page: superadmin can fetch all groups, orgadmin only their own
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);

    Route::get('/members', [MemberController::class, 'index']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);

    // Assessment scheduling
    Route::post('/assessment-schedules', [\App\Http\Controllers\AssessmentScheduleController::class, 'store']);


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