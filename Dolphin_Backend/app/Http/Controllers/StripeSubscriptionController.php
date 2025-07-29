<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Customer;
use App\Models\Subscription;
use App\Models\User;       
use Illuminate\Support\Facades\Log; 

class StripeSubscriptionController extends Controller
{

    public function createCheckoutSession(Request $request)
    {
        $user = Auth::user();
        $priceId = $request->input('price_id'); // Stripe Price ID from frontend
        if (!$priceId) {
            return response()->json(['error' => 'Missing price_id'], 400);
        }
        Stripe::setApiKey(config('services.stripe.secret'));
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'customer_email' => $user->email,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => 'http://127.0.0.1:8080/subscriptions/plans',
            'cancel_url' => 'http://127.0.0.1:8080/subscriptions/plans',
        ]);
        return response()->json(['id' => $session->id, 'url' => $session->url]);
    }

    public function createCustomerPortal(Request $request)
    {
        $user = Auth::user();
        Stripe::setApiKey(config('services.stripe.secret'));
        // You should store Stripe customer ID in your DB, here we fetch by email for demo
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

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret'); // Add this to your .env and config/services.php

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
       
            Log::info('Stripe Webhook Event Received:', ['type' => $event->type, 'id' => $event->id]);
        } catch (\UnexpectedValueException $e) {
        
            \Log::error('Stripe Webhook Invalid Payload: ' . $e->getMessage(), ['payload' => $payload]); 
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
         
            \Log::error('Stripe Webhook Invalid Signature: ' . $e->getMessage(), ['signature' => $sig_header, 'payload' => $payload]); 
            return response('Invalid signature', 400);
        }

         // Handle the event
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            // ADDED: Log full session payload
            Log::info('Checkout Session Completed Payload:', (array) $session); 

            // Retrieve subscription and customer details from Stripe
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $stripeSubscriptionId = $session->subscription;
            $stripeCustomerId = $session->customer;
            $plan = $session->display_items[0]->plan->id ?? null;
            $status = 'active';
            $paymentMethod = $session->payment_method_types[0] ?? null;
            // ADDED: Log payment method from checkout session types
            Log::info('Payment method from checkout session types (before DB save): ' . ($paymentMethod ?? 'NULL')); 
            $paymentDate = now();
            $subscriptionStart = null;
            $subscriptionEnd = null;
            $amount = $session->amount_total ? $session->amount_total / 100 : null;
            $receiptUrl = $session->payment_intent ? (\Stripe\PaymentIntent::retrieve($session->payment_intent)->charges->data[0]->receipt_url ?? null) : null;
            $invoiceNumber = null;
            $description = null;
            $customerName = null;
            $customerEmail = $session->customer_email ?? null;
            $customerCountry = null;

            // Try to get subscription period start/end from Stripe Subscription object
            if ($stripeSubscriptionId) {
                $stripeSub = \Stripe\Subscription::retrieve($stripeSubscriptionId);
                $subscriptionStart = $stripeSub->current_period_start ? date('Y-m-d H:i:s', $stripeSub->current_period_start) : null;
                $subscriptionEnd = $stripeSub->current_period_end ? date('Y-m-d H:i:s', $stripeSub->current_period_end) : null;
                $plan = $stripeSub->items->data[0]->plan->id ?? $plan;
                $status = $stripeSub->status ?? $status;
            }

            // Get customer details
            // If start/end are still null, set manually based on amount (monthly/annual)
            if (!$subscriptionStart) {
                $subscriptionStart = now();
            }
            if (!$subscriptionEnd) {
                if ($amount == 250) {
                    $subscriptionEnd = now()->addMonth();
                } elseif ($amount == 2500) {
                    $subscriptionEnd = now()->addYear();
                } else {
                    $subscriptionEnd = null;
                }
            }
            if ($stripeCustomerId) {
                $customer = \Stripe\Customer::retrieve($stripeCustomerId);
                $customerName = $customer->name ?? null;
                $customerEmail = $customer->email ?? $customerEmail;
                $customerCountry = $customer->address->country ?? null;
            }

            // Find user by email
            $user = User::where('email', $customerEmail)->first(); // Using imported User model
            if ($user) {
                // Update user with Stripe customer and subscription IDs
                $user->stripe_customer_id = $stripeCustomerId;
                $user->stripe_subscription_id = $stripeSubscriptionId;
                // Set role to organization admin if not already
                if ($user->role !== 'organizationadmin') {
                    $user->role = 'organizationadmin';
                }
                $user->save();

                // Update or create subscription record (try by subscription_id, then by customer_id+user_id)
                $sub = null;
                if ($stripeSubscriptionId) {
                    $sub = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)->first(); // Using imported Subscription model
                }
                if (!$sub && $stripeCustomerId) {
                    $sub = Subscription::where('stripe_customer_id', $stripeCustomerId)
                        ->where('user_id', $user->id)
                        ->first();
                }
                $subscriptionData = [
                    'user_id' => $user->id,
                    'stripe_subscription_id' => $stripeSubscriptionId,
                    'stripe_customer_id' => $stripeCustomerId,
                    'plan' => $plan,
                    'status' => $status,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $paymentDate,
                    'subscription_start' => $subscriptionStart,
                    'subscription_end' => $subscriptionEnd,
                    'amount' => $amount,
                    'receipt_url' => $receiptUrl,
                    'invoice_number' => $invoiceNumber,
                    'description' => $description,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'customer_country' => $customerCountry,
                ];
                if ($sub) {
                    $sub->update($subscriptionData);
                } else {
                    Subscription::create($subscriptionData);
                }
            }
        }

        // Handle invoice.paid event for more complete subscription/payment info
           elseif ($event->type === 'invoice.paid') { 
            $invoice = $event->data->object;
            
    
            Log::info('Invoice Paid Payload:', (array) $invoice);

            try {
                  \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $stripeSubscriptionId = $invoice->subscription;
                // Fallback: try to get subscription ID from invoice lines if missing
                if (empty($stripeSubscriptionId) && isset($invoice->lines->data) && is_array($invoice->lines->data)) {
                    foreach ($invoice->lines->data as $line) {
                        // Check for direct subscription field
                        if (isset($line->subscription) && !empty($line->subscription)) {
                            $stripeSubscriptionId = $line->subscription;
                            break;
                        }
                        // Check for nested parent->subscription_item_details->subscription
                        if (isset($line->parent->subscription_item_details->subscription) && !empty($line->parent->subscription_item_details->subscription)) {
                            $stripeSubscriptionId = $line->parent->subscription_item_details->subscription;
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Error processing invoice.paid event: ' . $e->getMessage(), ['exception' => $e]); // Using imported Log facade
                return response('Error processing invoice.paid event', 500);
            }

            $stripeCustomerId = $invoice->customer;
            $amount = $invoice->amount_paid ? $invoice->amount_paid / 100 : null;
            $receiptUrl = $invoice->hosted_invoice_url ?? null;
            $invoiceNumber = $invoice->number ?? null;
            $paymentDate = isset($invoice->status_transitions->paid_at) ? date('Y-m-d H:i:s', $invoice->status_transitions->paid_at) : (isset($invoice->created) ? date('Y-m-d H:i:s', $invoice->created) : null);
            $description = isset($invoice->lines->data[0]->description) ? $invoice->lines->data[0]->description : null;
            $subscriptionStart = null;
            $subscriptionEnd = null;
            $plan = null;
            $status = null;
            $customerName = null;
            $customerEmail = null;
            $customerCountry = null;
            $paymentMethod = null;

              // First attempt: Try to get payment method details from payment intent
            if ($invoice->payment_intent) {
       
                
                Log::info('Invoice has payment_intent: ' . $invoice->payment_intent); 
                try {
                    $paymentIntent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);
                    if ($paymentIntent->charges->data && count($paymentIntent->charges->data) > 0) {
                        $charge = $paymentIntent->charges->data[0];
                        if ($charge->payment_method_details) {
                            $type = $charge->payment_method_details->type;
                            if ($type === 'card' && $charge->payment_method_details->card) {
                                $paymentMethod = $charge->payment_method_details->card->brand . ' ****' . $charge->payment_method_details->card->last4;
                            } else {
                                $paymentMethod = $type;
                            }
                        }
                    }
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    \Log::error('Stripe API error retrieving PaymentIntent for invoice.paid (payment method): ' . $e->getMessage(), ['invoice_id' => $invoice->id]);
                }
         
                
                Log::info('Payment method after payment_intent attempt: ' . ($paymentMethod ?? 'NULL')); 
            }


            // Fallback: If payment_intent not found or didn't provide method, try customer's default payment method
                if (empty($paymentMethod) && $invoice->customer) {
                // ADDED: Log fallback attempt
                Log::info('Attempting fallback for payment method via customer default. Customer ID: ' . $invoice->customer); 
                try {
                    $customer = \Stripe\Customer::retrieve($invoice->customer);
                    if ($customer->invoice_settings && $customer->invoice_settings->default_payment_method) {
                        $defaultPaymentMethodId = $customer->invoice_settings->default_payment_method;
                        // ADDED: Log default payment method ID
                        Log::info('Customer default_payment_method ID: ' . $defaultPaymentMethodId); 
                        $pm = \Stripe\PaymentMethod::retrieve($defaultPaymentMethodId);
                        if ($pm->type === 'card' && $pm->card) {
                            $paymentMethod = $pm->card->brand . ' ****' . $pm->card->last4;
                        } else {
                            $paymentMethod = $pm->type;
                        }
                        // ADDED: Log final fallback payment method
                        Log::info('Payment method from customer default (before DB save): ' . ($paymentMethod ?? 'NULL')); 
                    } else {
                   
                        Log::warning('Customer has no default_payment_method set in invoice_settings for customer ID: ' . $invoice->customer); 
                    }
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    \Log::error('Stripe API error retrieving Customer default payment method for invoice.paid: ' . $e->getMessage(), ['customer_id' => $invoice->customer]);
                }
            }

       
            if ($stripeSubscriptionId) {
                $stripeSub = \Stripe\Subscription::retrieve($stripeSubscriptionId);
                $subscriptionStart = $stripeSub->current_period_start ? date('Y-m-d H:i:s', $stripeSub->current_period_start) : null;
                $subscriptionEnd = $stripeSub->current_period_end ? date('Y-m-d H:i:s', $stripeSub->current_period_end) : null;
                // If start/end are still null, set manually based on amount (monthly/annual)
                if (!$subscriptionStart) {
                    $subscriptionStart = now();
                }
                if (!$subscriptionEnd) {
                    if ($amount == 250) {
                        $subscriptionEnd = now()->addMonth();
                    } elseif ($amount == 2500) {
                        $subscriptionEnd = now()->addYear();
                    } else {
                        $subscriptionEnd = null;
                    }
                }
                $plan = $stripeSub->items->data[0]->plan->id ?? null;
                $status = $stripeSub->status ?? null;
            }
            // Get customer details
            if ($stripeCustomerId) {
                $customer = \Stripe\Customer::retrieve($stripeCustomerId);
                $customerName = $customer->name ?? null;
                $customerEmail = $customer->email ?? null;
                $customerCountry = $customer->address->country ?? null;
            }
            // Find user by Stripe customer ID
            $user = User::where('stripe_customer_id', $stripeCustomerId)->first(); // Using imported User model
            if ($user) {
                // Update user with Stripe subscription ID if missing or different
                if (empty($user->stripe_subscription_id) || $user->stripe_subscription_id !== $stripeSubscriptionId) {
                    $user->stripe_subscription_id = $stripeSubscriptionId;
                }
                // Set role to organizationadmin if not already
                if ($user->role !== 'organizationadmin') {
                    $user->role = 'organizationadmin';
                }
                $user->save();
                // Update or create subscription record (try by subscription_id, then by customer_id+user_id)
                $sub = null;
                if ($stripeSubscriptionId) {
                    $sub = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)->first(); // Using imported Subscription model
                }
                if (!$sub && $stripeCustomerId) {
                    $sub = Subscription::where('stripe_customer_id', $stripeCustomerId)
                        ->where('user_id', $user->id)
                        ->first();
                }
                $subscriptionData = [
                    'user_id' => $user->id,
                    'stripe_subscription_id' => $stripeSubscriptionId,
                    'stripe_customer_id' => $stripeCustomerId,
                    'plan' => $plan,
                    'status' => $status,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $paymentDate,
                    'subscription_start' => $subscriptionStart,
                    'subscription_end' => $subscriptionEnd,
                    'amount' => $amount,
                    'receipt_url' => $receiptUrl,
                    'invoice_number' => $invoiceNumber,
                    'description' => $description,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'customer_country' => $customerCountry,
                ];
                if ($sub) {
                    $sub->update($subscriptionData);
                } else {
                    Subscription::create($subscriptionData);
                }
            }
        }

        
  return response('Webhook handled', 200); 
    }
}