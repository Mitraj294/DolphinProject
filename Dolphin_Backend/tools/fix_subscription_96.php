<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load Laravel environment
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Subscription;

// Set Stripe API key
\Stripe\Stripe::setApiKey(config('services.stripe.secret'));

echo "Fixing subscription ID 96 payment method data...\n";

try {
    // Get subscription 96
    $subscription = Subscription::find(96);
    if (!$subscription) {
        echo "Subscription not found!\n";
        exit(1);
    }
    
    echo "Current data: payment_method_type=" . ($subscription->payment_method_type ?? 'NULL') . "\n";
    
    // Get the Stripe subscription
    $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_subscription_id);
    
    if ($stripeSubscription->default_payment_method) {
        // Retrieve payment method details
        $paymentMethod = \Stripe\PaymentMethod::retrieve($stripeSubscription->default_payment_method);
        
        $updateData = [
            'default_payment_method_id' => $paymentMethod->id,
            'payment_method_type' => $paymentMethod->type,
        ];
        
        if ($paymentMethod->type === 'card' && $paymentMethod->card) {
            $updateData['payment_method_brand'] = $paymentMethod->card->brand;
            $updateData['payment_method_last4'] = $paymentMethod->card->last4;
            $updateData['payment_method'] = ucfirst($paymentMethod->card->brand) . ' ****' . $paymentMethod->card->last4;
        } else {
            $updateData['payment_method'] = ucfirst($paymentMethod->type);
        }
        
        // Update the subscription
        $subscription->update($updateData);
        
        echo "✓ Updated subscription 96 with {$paymentMethod->type}";
        if ($paymentMethod->type === 'card') {
            echo " ({$paymentMethod->card->brand} ****{$paymentMethod->card->last4})";
        }
        echo "\n";
        
    } else {
        echo "No default payment method found on subscription\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
