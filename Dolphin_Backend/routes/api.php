<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AssessmentAnswerLinkController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentScheduleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberRoleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationAssessmentQuestionController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ScheduledEmailController;
use App\Http\Controllers\StripeSubscriptionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SendAssessmentController;

/*
 1. Public API Routes
 These routes are open to the public and do not require authentication.
 They handle user registration, authentication, password resets, and
 public-facing resources like the Stripe webhook.
*/

// Authentication & Password Reset
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Stripe Webhook
Route::post('/stripe/webhook', [StripeSubscriptionController::class, 'handleWebhook']);

// Public Assessments & Leads
Route::get('/assessment/{id}/summary', [AssessmentController::class, 'summary']);
Route::post('/assessment/send-link', [AssessmentAnswerLinkController::class, 'sendLink']);
Route::get('/assessment/answer/{token}', [AssessmentAnswerLinkController::class, 'getAssessmentByToken']);
Route::post('/assessment/answer/{token}', [AssessmentAnswerLinkController::class, 'submitAnswers'])
    ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class]);

Route::get('/leads/find-us-options', [LeadController::class, 'findUsOptions']);
Route::get('/email-template/lead-registration', [LeadController::class, 'leadRegistration']);
Route::post('/leads/send-assessment', [SendAssessmentController::class, 'send']);
Route::post('/schedule-email', [ScheduledEmailController::class, 'store']);
Route::get('/scheduled-email/show', [ScheduledEmailController::class, 'show']);

// Public Location Data
Route::get('/countries', [LocationController::class, 'countries']);
Route::get('/states', [LocationController::class, 'states']);
Route::get('/cities', [LocationController::class, 'cities']);
Route::get('/countries/{id}', [LocationController::class, 'getCountryById']);
Route::get('/states/{id}', [LocationController::class, 'getStateById']);
Route::get('/cities/{id}', [LocationController::class, 'getCityById']);


/*
 2. Authenticated API Routes
 All routes in this group require API token authentication. They are
 further divided into routes that are always accessible and those
 that require an active subscription.
*/

Route::middleware('auth:api')->group(function () {

    /*
      2.1 Routes Accessible to ALL Authenticated Users
      These are essential for account management and are not blocked by
      an expired subscription.
     */
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/token/status', [AuthController::class, 'tokenStatus']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::prefix('profile')->group(function () {
        Route::get('/', [AuthController::class, 'profile']);
        Route::patch('/', [AuthController::class, 'updateProfile']);
        Route::delete('/', [AuthController::class, 'deleteAccount']);
    });

    // Subscription & Billing Management
    Route::prefix('stripe')->group(function () {
        Route::post('/checkout-session', [StripeSubscriptionController::class, 'createCheckoutSession']);
        Route::post('/customer-portal', [StripeSubscriptionController::class, 'createCustomerPortal']);
    });

    Route::get('/subscription', [SubscriptionController::class, 'getUserSubscription']);
    Route::get('/subscription/status', [SubscriptionController::class, 'subscriptionStatus']);
    Route::get('/billing/current', [SubscriptionController::class, 'getCurrentPlan']);
    Route::get('/billing/history', [SubscriptionController::class, 'getBillingHistory']);


    /*
      2.2 Routes Requiring an ACTIVE Subscription
      This entire group is protected by the `subscription.check` middleware.
     */
    Route::middleware('subscription.check')->group(function () {

        // General Features for All Active Subscribers
        Route::apiResource('assessments', AssessmentController::class)->only(['index', 'store']);
        Route::get('/questions', [AnswerController::class, 'getQuestions']);
        Route::post('/answers', [AnswerController::class, 'store']);
        Route::get('/answers', [AnswerController::class, 'getUserAnswers']);
        Route::get('/organization-assessment-questions', [OrganizationAssessmentQuestionController::class, 'index']);

        // Notifications
        Route::get('/announcements/user', [NotificationController::class, 'userAnnouncements']);
        Route::post('/announcements/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::get('/notifications/user', [NotificationController::class, 'userNotifications']);
    Route::get('/notifications/unread', [NotificationController::class, 'unreadAnnouncements']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    // Manual create notification (useful for testing/debug)
    Route::post('/notifications/create', [NotificationController::class, 'createNotification']);

        /*
         2.2.1 Role-Based Routes (Active Subscription Required)
        */

        // Superadmin Only
        Route::middleware('auth.role:superadmin')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);
            Route::patch('/users/{id}/soft-delete', [UserController::class, 'softDelete']);
            Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate']);
            
            Route::apiResource('organizations', OrganizationController::class);

            Route::get('/announcements', [NotificationController::class, 'allAnnouncements']);
            Route::get('/announcements/{id}', [NotificationController::class, 'showAnnouncement']);
            Route::get('/notifications', [NotificationController::class, 'allNotifications']);
        });

        // Dolphin Admin & Superadmin
        Route::middleware('auth.role:dolphinadmin,superadmin')->group(function () {
            Route::apiResource('leads', LeadController::class);
            Route::post('/notifications/send', [NotificationController::class, 'send']);
        });

        // Organization Admin & Superadmin
        Route::middleware('auth.role:organizationadmin,superadmin')->group(function () {
            Route::apiResource('groups', GroupController::class);
            Route::apiResource('members', MemberController::class);
            Route::get('/member-roles', [MemberRoleController::class, 'index']);
        });
        
        // Multiple Admin Roles
        Route::middleware('auth.role:dolphinadmin,organizationadmin,superadmin')->group(function () {
            Route::post('/assessment-schedules', [AssessmentScheduleController::class, 'store']);
        });
    });
});

