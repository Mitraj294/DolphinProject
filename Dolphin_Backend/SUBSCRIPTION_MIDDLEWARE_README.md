# Subscription Status Middleware Implementation

## Overview
I've successfully implemented middleware to block access to most routes when a user's subscription is expired, while allowing access to subscription and billing related routes.

## Implementation Details

### 1. Created CheckSubscriptionStatus Middleware
- **File**: `app/Http/Middleware/CheckSubscriptionStatus.php`
- **Purpose**: Checks if authenticated user has an expired subscription
- **Behavior**: 
  - Allows access if user is not authenticated (passes through)
  - Allows access if user has no subscription (new users)
  - Blocks access with 403 error if subscription status is "expired"
  - Returns JSON response with subscription details when blocking

### 2. Registered Middleware
- **File**: `app/Http/Kernel.php`
- **Added**: `'subscription.check' => \App\Http\Middleware\CheckSubscriptionStatus::class`

### 3. Updated Route Structure
- **File**: `routes/api.php`
- **Changes**:
  - Separated routes that should remain accessible with expired subscriptions
  - Applied `subscription.check` middleware to routes that require active subscription

## Routes Always Accessible (Even with Expired Subscription)

```php
// Basic authentication routes
POST /api/logout
GET  /api/user
GET  /api/token/status

// Subscription and billing management routes
POST /api/stripe/checkout-session
POST /api/stripe/customer-portal
GET  /api/subscription
GET  /api/user/subscription
GET  /api/subscription/status
GET  /api/billing/current
GET  /api/billing/history
```

## Routes Blocked with Expired Subscription

All other authenticated routes including:
- Profile management
- Assessments
- Organizations 
- Groups and members
- Notifications
- Admin functions

## Response When Blocked

### API Requests
When a user with expired subscription tries to access blocked API routes:

```json
{
    "message": "Your subscription has expired. Please renew your subscription to continue using this service.",
    "status": "expired",
    "subscription_end": "2025-09-05 12:45:36",
    "subscription_id": 84,
    "redirect_url": "/manage-subscription"
}
```

### Web Requests
For web requests, users are automatically redirected to `/manage-subscription` with:
- Flash message: "Your subscription has expired. Please renew your subscription to continue using this service."
- Session data with subscription details

### Frontend Handling
The frontend should check for the `redirect_url` in 403 responses and redirect users to the subscription management page:

```javascript
// Example axios interceptor
axios.interceptors.response.use(response => response, error => {
  if (error.response?.status === 403 && error.response.data?.redirect_url) {
    window.location.href = error.response.data.redirect_url;
  }
  return Promise.reject(error);
});
```

## Testing

To test the functionality:

1. **Check subscription status**: `GET /api/subscription/status`
2. **If expired**: Try accessing any blocked route (e.g., `GET /api/profile`)
3. **Expected result**: 403 error with subscription details
4. **Verify access**: Try accessing allowed routes (e.g., `POST /api/stripe/checkout-session`)
5. **Expected result**: Normal access granted

## Next Steps

The implementation is complete and ready for testing. The middleware will:
- Automatically check subscription status on each request to protected routes
- Allow users to manage their subscriptions even when expired
- Provide clear feedback about expired subscription status
- Block access to all premium features until subscription is renewed