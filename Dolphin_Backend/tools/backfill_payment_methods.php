<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load Laravel environment
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

// Set Stripe API key
\Stripe\Stripe::setApiKey(config('services.stripe.secret'));

echo "Backfilling payment method data for existing subscriptions...\n";

// Get all subscriptions that have Stripe subscription IDs but missing payment method details
$subscriptions = Subscription::whereNotNull('stripe_subscription_id')
    ->where(function($query) {
        $query->whereNull('payment_method_type')
              ->orWhereNull('payment_method_brand')
              ->orWhereNull('payment_method_last4');
    })
    ->get();

echo "Found " . $subscriptions->count() . " subscriptions to update.\n";

foreach ($subscriptions as $subscription) {
    echo "Processing subscription ID: {$subscription->id} (Stripe: {$subscription->stripe_subscription_id})\n";
    
    try {
        // Retrieve the Stripe subscription
        $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_subscription_id);
        
        // Get the default payment method
        $defaultPaymentMethodId = $stripeSubscription->default_payment_method;
        
        if ($defaultPaymentMethodId) {
            // Retrieve payment method details
            $paymentMethod = \Stripe\PaymentMethod::retrieve($defaultPaymentMethodId);
            
            $updateData = [
                'default_payment_method_id' => $paymentMethod->id,
                'payment_method_type' => $paymentMethod->type,
            ];
            
            if ($paymentMethod->type === 'card' && $paymentMethod->card) {
                $updateData['payment_method_brand'] = $paymentMethod->card->brand;
                $updateData['payment_method_last4'] = $paymentMethod->card->last4;
                
                // Also update the readable payment method field
                $updateData['payment_method'] = ucfirst($paymentMethod->card->brand) . ' ****' . $paymentMethod->card->last4;
            } else {
                $updateData['payment_method'] = ucfirst($paymentMethod->type);
            }
            
            // Update the subscription
            $subscription->update($updateData);
            
            echo "  ✓ Updated with {$paymentMethod->type}";
            if ($paymentMethod->type === 'card') {
                echo " ({$paymentMethod->card->brand} ****{$paymentMethod->card->last4})";
            }
            echo "\n";
            
        } else {
            echo "  ⚠ No default payment method found\n";
        }
        
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "Backfill complete!\n";
