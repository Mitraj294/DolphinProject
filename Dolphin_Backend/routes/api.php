<?php

use Illuminate\Support\Facades\Route;

// Controller Imports (organized alphabetically)
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
use App\Http\Controllers\SendAgreementController;
use App\Http\Controllers\SendAssessmentController;
use App\Http\Controllers\StripeSubscriptionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

// 1. PUBLIC API ROUTES
// These routes are open to the public and do not require authentication.
// They handle user registration, authentication, password resets, and
// public-facing resources like the Stripe webhook.


// Authentication & Password Reset

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::prefix('password')->group(function () {
    Route::post('/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/email', [AuthController::class, 'sendResetLinkEmail']);
    Route::post('/reset', [AuthController::class, 'resetPassword']);
});


// Stripe Webhook and Checkout

Route::post('/stripe/webhook', [StripeSubscriptionController::class, 'handleWebhook']);
Route::post('/stripe/checkout-session', [StripeSubscriptionController::class, 'createCheckoutSession']);
// Retrieve a checkout session and related subscription details (public)
Route::get('/stripe/session', [StripeSubscriptionController::class, 'getSessionDetails']);


// Public Assessments & Leads

Route::prefix('assessments')->group(function () {
    Route::get('/{id}/summary', [AssessmentController::class, 'summary']);
    Route::post('/send-link', [AssessmentAnswerLinkController::class, 'sendLink']);
    Route::get('/answer/{token}', [AssessmentAnswerLinkController::class, 'getAssessmentByToken']);
    Route::post('/answer/{token}', [AssessmentAnswerLinkController::class, 'submitAnswers'])
        // Exclude Sanctum middleware for external POST
        ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class]);
});

Route::prefix('leads')->group(function () {
    Route::get('/find-us-options', [LeadController::class, 'findUsOptions']);
    Route::get('/prefill', [LeadController::class, 'prefill']);
    Route::post('/send-assessment', [SendAssessmentController::class, 'send']);
    Route::post('/send-agreement', [SendAgreementController::class, 'send']);
    // Validate a guest token generated when sending an agreement email.
    Route::get('/guest-validate', [SendAgreementController::class, 'validateGuest']);
});

Route::prefix('email-template')->group(function () {
    Route::get('/lead-registration', [LeadController::class, 'leadRegistration']);
    Route::get('/lead-agreement', [LeadController::class, 'leadAgreement']);
});


// Scheduled Emails (Public)

Route::post('/schedule-email', [ScheduledEmailController::class, 'store']);
Route::get('/scheduled-email/show', [ScheduledEmailController::class, 'show']);


// Public Location Data

Route::prefix('countries')->group(function () {
    Route::get('/', [LocationController::class, 'countries']);
    Route::get('/{id}', [LocationController::class, 'getCountryById']);
});
Route::prefix('states')->group(function () {
    Route::get('/', [LocationController::class, 'states']);
    Route::get('/{id}', [LocationController::class, 'getStateById']);
});
Route::prefix('cities')->group(function () {
    Route::get('/', [LocationController::class, 'cities']);
    Route::get('/{id}', [LocationController::class, 'getCityById']);
});

// 2. AUTHENTICATED API ROUTES
// All routes in this group require API token authentication.
// Subdivided into routes always accessible and those requiring active subscription.
Route::middleware('auth:api')->group(function () {


    // 2.1 Routes Accessible to ALL Authenticated Users

    // Essential for account management, not blocked by expired subscription.
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/token/status', [AuthController::class, 'tokenStatus']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::prefix('profile')->group(function () {
        Route::get('/', [AuthController::class, 'profile']);
        Route::patch('/', [AuthController::class, 'updateProfile']);
        Route::delete('/', [AuthController::class, 'deleteAccount']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/user', [NotificationController::class, 'userNotifications']);
        Route::get('/unread', [NotificationController::class, 'unreadAnnouncements']);
    });

    // Allow marking notifications as read and marking all as read for authenticated users
    Route::prefix('announcements')->group(function () {
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    });
    Route::prefix('notifications')->group(function () {
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });

    // Subscription & Billing Management
    Route::prefix('stripe')->group(function () {
        Route::post('/customer-portal', [StripeSubscriptionController::class, 'createCustomerPortal']);
    });

    // Endpoint to refresh current authenticated user's roles (useful after subscription / webhook)
    Route::prefix('subscription')->group(function () {
        Route::post('/refresh-role', [StripeSubscriptionController::class, 'refreshRole']);
        Route::get('/', [SubscriptionController::class, 'getCurrentPlan']);
        Route::get('/status', [SubscriptionController::class, 'subscriptionStatus']);
    });

    Route::prefix('billing')->group(function () {
        Route::get('/current', [SubscriptionController::class, 'getCurrentPlan']);
        Route::get('/history', [SubscriptionController::class, 'getBillingHistory']);
    });


    // 2.2 Routes Requiring an ACTIVE Subscription

    // Protected by the `subscription.check` middleware.
    Route::middleware('subscription.check')->group(function () {

        
        // General Features for All Active Subscribers
        
        Route::apiResource('assessments', AssessmentController::class)->only(['index', 'store']);
        Route::get('/questions', [AnswerController::class, 'getQuestions']);
        Route::post('/answers', [AnswerController::class, 'store']);
        Route::get('/answers', [AnswerController::class, 'getUserAnswers']);
        Route::get('/organization-assessment-questions', [OrganizationAssessmentQuestionController::class, 'index']);

        
        // Notifications (admin endpoints remain in superadmin block below)

    
        // 2.2.1 Role-Based Routes (Active Subscription Required)
    

        
        // Superadmin Only (full management)
        
        Route::middleware('auth.role:superadmin')->group(function () {
            Route::apiResource('users', UserController::class);

            Route::prefix('users')->group(function () {
                Route::patch('/{user}/role', [UserController::class, 'updateRole']);
                Route::patch('/{user}/soft-delete', [UserController::class, 'softDelete']);
                Route::post('/{user}/impersonate', [UserController::class, 'impersonate']);
            });

            Route::prefix('organizations')->group(function () {
                // Superadmin-only endpoints for creating/updating/deleting organizations
                Route::post('/', [OrganizationController::class, 'store']);
                Route::patch('/{organization}', [OrganizationController::class, 'update']);
                Route::delete('/{organization}', [OrganizationController::class, 'destroy']);
            });

            Route::prefix('announcements')->group(function () {
                Route::get('/', [NotificationController::class, 'allAnnouncements']);
                Route::get('/{id}', [NotificationController::class, 'showAnnouncement']);
            });
            Route::get('/notifications', [NotificationController::class, 'allNotifications']);
        });

        
        // Dolphin Admin & Superadmin (manage all lead actions)
        
        Route::middleware('auth.role:dolphinadmin,superadmin')->group(function () {
            // Admins can perform all lead actions except index
            Route::apiResource('leads', LeadController::class)->except(['index']);
            Route::post('/notifications/send', [NotificationController::class, 'send']);
        });

        // Lead listing for allowed roles
        Route::get('/leads', [LeadController::class, 'index'])
            ->middleware('auth.role:dolphinadmin,superadmin,salesperson');

        
        // Organization Admin & Superadmin
        
        Route::middleware('auth.role:organizationadmin,superadmin')->group(function () {
            Route::prefix('organizations')->group(function () {
                Route::get('/', [OrganizationController::class, 'index']);
                Route::get('/{organization}', [OrganizationController::class, 'show']);
            });

            Route::prefix('groups')->group(function () {
                Route::get('/', [GroupController::class, 'index']);
                Route::get('/{group}', [GroupController::class, 'show']);
            });

            Route::apiResource('members', MemberController::class);
            Route::get('/member-roles', [MemberRoleController::class, 'index']);
        });

        
        // Organization Admin only for managing groups
        
        Route::middleware('auth.role:organizationadmin')->group(function () {
            Route::prefix('groups')->group(function () {
                Route::post('/', [GroupController::class, 'store']);
                Route::patch('/{group}', [GroupController::class, 'update']);
                Route::delete('/{group}', [GroupController::class, 'destroy']);
            });
        });

        
        // Multiple Admin Roles (Assessment Scheduling)
        
        Route::middleware('auth.role:dolphinadmin,organizationadmin,superadmin')->group(function () {
            Route::post('/assessment-schedules', [AssessmentScheduleController::class, 'store']);
        });
    });
});
