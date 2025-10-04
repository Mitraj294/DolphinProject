<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SubscriptionReceiptNotification;

class TestNotificationController extends Controller
{
    /**
     * Trigger a test subscription receipt notification.
     * Example: GET /debug/send-receipt?email=you@example.com
     */
    public function sendReceipt(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return response()->json(['error' => 'email query param required'], 400);
        }

        $payload = [
            'plan' => 'Pro',
            'amount' => 99.99,
            'invoice_number' => 'TEST-0001',
            'payment_date' => now()->toDateTimeString(),
            'next_billing' => now()->addMonth()->toDateTimeString(),
            'receipt_url' => url('/receipts/test-0001'),
            'customer_name' => 'Test User',
        ];

        Notification::route('mail', $email)->notify(new SubscriptionReceiptNotification($payload));
        Log::info('Dispatched test subscription receipt notification', ['email' => $email]);

        return response()->json(['status' => 'queued', 'email' => $email]);
    }
}
