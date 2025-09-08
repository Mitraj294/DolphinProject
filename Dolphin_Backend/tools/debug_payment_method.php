<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load Laravel environment
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Log;

// Set Stripe API key
\Stripe\Stripe::setApiKey(config('services.stripe.secret'));

echo "Investigating payment method extraction for subscription: sub_1S4ytZPnfSZSgS1XbSpT27t8\n";

try {
    // Get the Stripe subscription
    $subscription = \Stripe\Subscription::retrieve('sub_1S4ytZPnfSZSgS1XbSpT27t8');
    echo "Subscription default_payment_method: " . ($subscription->default_payment_method ?? 'NULL') . "\n";
    
    // Get the customer
    $customer = \Stripe\Customer::retrieve($subscription->customer);
    echo "Customer default_source: " . ($customer->default_source ?? 'NULL') . "\n";
    echo "Customer invoice_settings.default_payment_method: " . ($customer->invoice_settings->default_payment_method ?? 'NULL') . "\n";
    
    // Get latest invoice
    $invoices = \Stripe\Invoice::all(['customer' => $subscription->customer, 'limit' => 1]);
    if (count($invoices->data) > 0) {
        $invoice = $invoices->data[0];
        echo "Latest invoice ID: " . $invoice->id . "\n";
        echo "Invoice payment_intent: " . ($invoice->payment_intent ?? 'NULL') . "\n";
        
        if ($invoice->payment_intent) {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);
            echo "PaymentIntent payment_method: " . ($paymentIntent->payment_method ?? 'NULL') . "\n";
            
            if ($paymentIntent->payment_method) {
                $pm = \Stripe\PaymentMethod::retrieve($paymentIntent->payment_method);
                echo "Payment method type: " . $pm->type . "\n";
                if ($pm->type === 'card') {
                    echo "Card brand: " . $pm->card->brand . "\n";
                    echo "Card last4: " . $pm->card->last4 . "\n";
                }
            }
        }
        
        // Check charges on payment intent
        if ($invoice->payment_intent) {
            $charges = \Stripe\Charge::all(['payment_intent' => $invoice->payment_intent]);
            if (count($charges->data) > 0) {
                $charge = $charges->data[0];
                echo "Charge payment_method: " . ($charge->payment_method ?? 'NULL') . "\n";
                if ($charge->payment_method_details && $charge->payment_method_details->card) {
                    echo "Charge card brand: " . $charge->payment_method_details->card->brand . "\n";
                    echo "Charge card last4: " . $charge->payment_method_details->card->last4 . "\n";
                }
            }
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
