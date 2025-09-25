<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Customer;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;
use Throwable;
use Carbon\Carbon;

class StripeSubscriptionController extends Controller
{

    public function createCheckoutSession(Request $request)
    {
        $user = Auth::user();
        $priceId = $request->input('price_id');
        if (!$priceId) {
            return response()->json(['error' => 'Missing price_id'], 400);
        }
        Stripe::setApiKey(config('services.stripe.secret'));
        $frontend = env('FRONTEND_URL', 'http://localhost:8080');
        // include the Checkout session id in the success URL so the frontend
        // can pick it up and poll subscription status immediately after redirect
        // Stripe replaces the placeholder {CHECKOUT_SESSION_ID} with the actual id
        // when redirecting back to the success_url.
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'customer_email' => $user->email,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => $frontend . '/subscriptions/plans?checkout_session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $frontend . '/subscriptions/plans',
        ]);
        return response()->json(['id' => $session->id, 'url' => $session->url]);
    }

    /**
     * Refresh the authenticated user's roles and return them.
     * This endpoint is intended to be called by the frontend after a
     * successful subscription checkout so the UI can immediately reflect
     * the new 'organizationadmin' role.
     */
    public function refreshRole(Request $request)
    {
        $response = null;
        $status = 200;

        $user = Auth::user();
        if (!$user) {
            $response = ['error' => 'Unauthenticated'];
            $status = 401;
        } else {
            try {
                $fresh = User::with('roles')->find($user->id);

                if ($fresh) {
                    Auth::guard()->setUser($fresh);

                    // Optionally regenerate session in stateful setups
                    $request->session()?->regenerate();

                    $response = ['roles' => $fresh->roles->pluck('name')];
                } else {
                    $response = ['roles' => []];
                }
            } catch (Throwable $e) {
                Log::warning(
                    'Failed to refresh user roles: ' . $e->getMessage(),
                    ['user_id' => $user->id]
                );
                $response = ['error' => 'Could not refresh roles'];
                $status = 500;
            }
        }

        return response()->json($response, $status);
    }

    

    public function createCustomerPortal(Request $request)
    {
        $user = Auth::user();
        Stripe::setApiKey(config('services.stripe.secret'));
        $customers = Customer::all(['email' => $user->email, 'limit' => 1]);
        if (count($customers->data) === 0) {
            return response()->json(['error' => 'No Stripe customer found'], 404);
        }
        $customer = $customers->data[0];
        $session = \Stripe\BillingPortal\Session::create([
            'customer' => $customer->id,
            'return_url' => URL::to('/dashboard'),
        ]);
        return response()->json(['url' => $session->url]);
    }

    /**
     * Handles incoming Stripe webhooks with robust error handling and delegation.
     */
 public function handleWebhook(Request $request)
{
    $responseMessage = 'Webhook handled';
    $responseCode = 200;
    $event = null;

    try {
        $event = $this->verifyAndConstructEvent($request);
        Log::info('Stripe Webhook Event Received:', [
            'type' => $event->type,
            'id' => $event->id
        ]);
    } catch (UnexpectedValueException $e) {
        Log::error('Stripe Webhook Invalid Payload', [
            'error' => $e->getMessage(),
            'payload' => $request->getContent()
        ]);
        $responseMessage = 'Invalid payload';
        $responseCode = 400;
    } catch (SignatureVerificationException $e) {
        Log::error('Stripe Webhook Invalid Signature', [
            'error' => $e->getMessage(),
            'signature' => $request->header('Stripe-Signature')
        ]);
        $responseMessage = 'Invalid signature';
        $responseCode = 400;
    }

    if ($event) {
        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event->data->object),
                'invoice.paid' => $this->handleInvoicePaid($event->data->object),
                default => Log::info('Unhandled event type', ['type' => $event->type]),
            };
        } catch (Throwable $e) {
            Log::error('Error processing webhook event', [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'exception' => $e
            ]);
            $responseMessage = 'Webhook handled with internal error';
        }
    }

    return response($responseMessage, $responseCode);
}


    /**
     * Verifies webhook signature and constructs the Stripe Event object.
     */
    private function verifyAndConstructEvent(Request $request): Event
    {
        return \Stripe\Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook_secret')
        );
    }

    /**
     * Processes the 'checkout.session.completed' event.
     */
    private function handleCheckoutSessionCompleted(StripeSession $session): void
    {
        Log::info('Checkout Session Completed Payload:', (array) $session);
        Stripe::setApiKey(config('services.stripe.secret'));

        $user = User::where('email', $session->customer_email)->first();
        if (!$user) {
            Log::warning('User not found for checkout session', ['email' => $session->customer_email]);
            return;
        }

        $stripeSub = \Stripe\Subscription::retrieve($session->subscription);
        $customer = Customer::retrieve($session->customer);

        // Try to fetch the latest invoice for this subscription (if available)
        $latestInvoice = null;
        try {
            if (!empty($stripeSub->latest_invoice)) {
                $latestInvoice = \Stripe\Invoice::retrieve($stripeSub->latest_invoice);
            }
        } catch (Throwable $e) {
            Log::warning('Unable to retrieve latest invoice for subscription: ' . $e->getMessage(), ['sub_id' => $stripeSub->id]);
            $latestInvoice = null;
        }

        // Normalize start/end using Carbon. If Stripe provides equal/missing values,
        // compute end based on the payment amount (monthly/yearly heuristic).
        $start = $stripeSub->current_period_start ? Carbon::createFromTimestamp($stripeSub->current_period_start) : Carbon::now();
        $end = $stripeSub->current_period_end ? Carbon::createFromTimestamp($stripeSub->current_period_end) : null;

        $amount = $session->amount_total ? $session->amount_total / 100 : null;
        if (is_null($end) || $start->equalTo($end)) {
            if ($amount == 2500) {
                $end = $start->copy()->addYear();
            } else {
                $end = $start->copy()->addMonth();
            }
        }

        $subscriptionData = [
            'user_id' => $user->id,
            'stripe_subscription_id' => $session->subscription,
            'stripe_customer_id' => $session->customer,
            'plan' => $stripeSub->items->data[0]->plan->id ?? null,
            'status' => $stripeSub->status ?? 'active',
            'payment_date' => now(),
            'subscription_start' => $start instanceof Carbon ? $start->toDateTimeString() : (string)$start,
            'subscription_end' => $end instanceof Carbon ? $end->toDateTimeString() : (string)$end,
            'amount' => $amount,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_country' => $customer->address->country ?? null,
            // Invoice-related fields may not be present on the session; populate from latest invoice when possible
            'receipt_url' => $latestInvoice->hosted_invoice_url ?? null,
            'invoice_number' => $latestInvoice->number ?? null,
            'description' => $latestInvoice->lines->data[0]->description ?? null,
        ];

        $paymentMethodDetails = $this->getPaymentMethodDetailsFromSession($session, $stripeSub);
        $this->updateOrCreateSubscription(array_merge($subscriptionData, $paymentMethodDetails));
    }

    /**
     * Processes the 'invoice.paid' event with fallback logic.
     */
    private function handleInvoicePaid(Invoice $invoice): void
    {
        Log::info('Invoice Paid Payload:', (array) $invoice);
        Stripe::setApiKey(config('services.stripe.secret'));

        $stripeSubscriptionId = $this->getSubscriptionIdFromInvoice($invoice);
        if (!$stripeSubscriptionId) {
            Log::info('Invoice.paid event without a subscription ID, skipping.', ['invoice_id' => $invoice->id]);
            return;
        }

        $customer = Customer::retrieve($invoice->customer);
        $user = User::where('email', $customer->email)->first();
        if (!$user) {
            Log::warning('User not found for invoice', ['email' => $customer->email]);
            return;
        }

        $stripeSub = \Stripe\Subscription::retrieve($stripeSubscriptionId);
        $amount = $invoice->amount_paid / 100;
        
        // Fallback for subscription period if not available on the subscription object
        // Use Carbon to compute start/end consistently. If Stripe didn't provide
        // a period end (or start and end are equal), compute end based on plan.
        $start = $stripeSub->current_period_start ? Carbon::createFromTimestamp($stripeSub->current_period_start) : Carbon::now();
        $end = $stripeSub->current_period_end ? Carbon::createFromTimestamp($stripeSub->current_period_end) : null;

        if (is_null($end) || $start->equalTo($end)) {
            // Determine period from amount: 250 -> monthly, 2500 -> yearly. Fallback to monthly.
            if ($amount == 2500) {
                $end = $start->copy()->addYear();
            } else {
                $end = $start->copy()->addMonth();
            }
        }

        $subscriptionData = [
            'user_id' => $user->id,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'stripe_customer_id' => $invoice->customer,
            'plan' => $stripeSub->items->data[0]->plan->id ?? null,
            'status' => $stripeSub->status ?? null,
            'payment_date' => date('Y-m-d H:i:s', $invoice->status_transitions->paid_at),
            'subscription_start' => $start instanceof Carbon ? $start->toDateTimeString() : (string)$start,
            'subscription_end' => $end instanceof Carbon ? $end->toDateTimeString() : (string)$end,
            'amount' => $amount,
            'receipt_url' => $invoice->hosted_invoice_url,
            'invoice_number' => $invoice->number,
            'description' => $invoice->lines->data[0]->description ?? null,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_country' => $customer->address->country ?? null,
        ];

        $paymentMethodDetails = $this->getPaymentMethodDetailsFromInvoice($invoice, $customer, $stripeSub);
        $this->updateOrCreateSubscription(array_merge($subscriptionData, $paymentMethodDetails));
    }

    /**
     * Finds subscription ID from invoice, checking lines as a fallback.
     */
    private function getSubscriptionIdFromInvoice(Invoice $invoice): ?string
    {
        if (!empty($invoice->subscription)) {
            return $invoice->subscription;
        }

        Log::info('Subscription ID missing from invoice top-level, checking line items.', ['invoice_id' => $invoice->id]);
        if (isset($invoice->lines->data) && is_array($invoice->lines->data)) {
            foreach ($invoice->lines->data as $line) {
                if (isset($line->subscription) && !empty($line->subscription)) {
                    return $line->subscription;
                }
            }
        }
        return null;
    }

    /**
     * Retrieves payment method details from a checkout session or its related subscription.
     */
    private function getPaymentMethodDetailsFromSession(StripeSession $session, \Stripe\Subscription $stripeSub): array
    {
        try {
            if ($session->payment_intent) {
                $intent = PaymentIntent::retrieve($session->payment_intent);
                if ($intent->payment_method) {
                    $pm = PaymentMethod::retrieve($intent->payment_method);
                    return $this->formatPaymentMethodDetails($pm);
                }
            }
    
            if ($stripeSub->default_payment_method) {
                Log::info('Payment method extracted from subscription default.', ['sub_id' => $stripeSub->id]);
                $pm = PaymentMethod::retrieve($stripeSub->default_payment_method);
                return $this->formatPaymentMethodDetails($pm);
            }
        } catch (Throwable $e) {
            Log::error('Stripe API error retrieving payment method for checkout session: ' . $e->getMessage(), ['session_id' => $session->id]);
        }
        return [];
    }

    /**
     * Retrieves payment method details from an invoice, customer, or subscription.
     */
  private function getPaymentMethodDetailsFromInvoice(
    Invoice $invoice,
    Customer $customer,
    \Stripe\Subscription $stripeSub
): array {
    $pm = null;

    try {
        if ($invoice->payment_intent) {
            $intent = PaymentIntent::retrieve($invoice->payment_intent);
            if ($intent->payment_method) {
                $pm = PaymentMethod::retrieve($intent->payment_method);
            }
        }

        if (!$pm && $customer->invoice_settings->default_payment_method) {
            Log::info('Attempting fallback for payment method via customer default.', [
                'customer_id' => $customer->id
            ]);
            $pm = PaymentMethod::retrieve($customer->invoice_settings->default_payment_method);
        }

        if (!$pm && $stripeSub->default_payment_method) {
            Log::info('Attempting fallback for payment method via subscription default.', [
                'sub_id' => $stripeSub->id
            ]);
            $pm = PaymentMethod::retrieve($stripeSub->default_payment_method);
        }

    } catch (Throwable $e) {
        Log::error('Stripe API error retrieving payment method for invoice: ' . $e->getMessage(), [
            'invoice_id' => $invoice->id
        ]);
    }

    return $pm ? $this->formatPaymentMethodDetails($pm) : [];
}


    /**
     * Formats a PaymentMethod object into a structured array for the database.
     */
    private function formatPaymentMethodDetails(PaymentMethod $pm): array
    {
        $details = [
            'default_payment_method_id' => $pm->id,
            'payment_method_type' => $pm->type,
            'payment_method_brand' => null,
            'payment_method_last4' => null,
            'payment_method' => ucfirst($pm->type),
        ];

        if ($pm->type === 'card' && $pm->card) {
            $details['payment_method_brand'] = $pm->card->brand;
            $details['payment_method_last4'] = $pm->card->last4;
            $details['payment_method'] = ucfirst($pm->card->brand) . ' ****' . $pm->card->last4;
        }

        Log::info('Formatted payment method details', ['payment_method' => $details['payment_method']]);
        return $details;
    }

    /**
     * Creates/updates a subscription, assigns roles safely, and ensures an organization exists.
     */
    private function updateOrCreateSubscription(array $data): void
    {
        $user = User::find($data['user_id']);
        if (!$user) {
            return;
        }

    // Ensure the user has only the 'organizationadmin' role after subscription.
    // This replaces any previous roles so the latest role is the single active one.
    $orgAdminRole = Role::firstOrCreate(['name' => 'organizationadmin']);
    $user->roles()->sync([$orgAdminRole->id]);

        // If this is the currently authenticated user, reload their roles into the session
        try {
            if (Auth::check() && Auth::id() === $user->id) {
                // Reload fresh user instance from DB and replace the authenticated user
                $fresh = User::with('roles')->find($user->id);
                if ($fresh) {
                    // For Laravel's default auth guard, set the user instance on the guard
                    Auth::guard()->setUser($fresh);
                    // Regenerate session to ensure updated auth payload (optional but helps some setups)
                    request()->session()?->regenerate();
                    Log::info('Refreshed authenticated user after role update', ['user_id' => $user->id]);
                }
            }
        } catch (Throwable $e) {
            Log::warning('Failed to refresh auth user after role update: ' . $e->getMessage(), ['user_id' => $user->id]);
        }

        // Create an organization for the user if one doesn't exist
        $organization = Organization::where('user_id', $user->id)->first();
        if (!$organization) {
            Log::info('Creating organization for user subscription', ['user_id' => $user->id]);
            $organization = Organization::create(['user_id' => $user->id]);
            Log::info('Organization created', ['organization_id' => $organization->id, 'user_id' => $user->id]);
        }
        
        // Update or create the subscription
        Subscription::updateOrCreate(
            ['stripe_subscription_id' => $data['stripe_subscription_id']],
            $data
        );

        Log::info('Subscription record updated/created successfully.', ['subscription_id' => $data['stripe_subscription_id']]);
    }
}