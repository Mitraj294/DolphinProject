<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Lead;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Exception;

class SendAgreementController extends Controller
{
    /**
     * Sends an agreement email containing a Stripe payment link.
     */
    public function send(Request $request)
    {
        try {
            $validated = $request->validate([
                'to' => 'required|email',
                'subject' => 'required|string',
                'body' => 'required|string',
                'price_id' => 'nullable|string',
                'name' => 'nullable|string',
                'lead_id' => 'nullable|integer|exists:leads,id',
            ]);

            Log::info('SendAgreementController@send called', $validated);

            // Find lead by ID or email
            $lead = !empty($validated['lead_id'])
                ? Lead::find($validated['lead_id'])
                : Lead::where('email', $validated['to'])->first();

            // Create or find user
            $user = User::where('email', $validated['to'])->first();
            if (!$user) {
                $passwordPlain = Str::random(12);
                $userData = [
                    'email' => $validated['to'],
                    'first_name' => $lead->first_name ?? ($validated['name'] ?? null),
                    'last_name' => $lead->last_name ?? null,
                    'password' => Hash::make($passwordPlain),
                ];

                $user = User::create($userData);

                try {
                    if ($lead) {
                        $user->userDetails()->create([
                            'phone' => $lead->phone,
                            'country_id' => $lead->country_id ?? null,
                        ]);
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to create userDetails: ' . $e->getMessage());
                }

                try {
                    $role = Role::where('name', 'organizationadmin')->first();
                    if ($role) {
                        $user->roles()->attach($role->id);
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to attach role: ' . $e->getMessage());
                }
            }

            // Prepare Stripe Checkout
            $priceId = $validated['price_id'] ?? env('DEFAULT_PRICE_ID');
            if (!$priceId) {
                Log::warning('No price_id provided and DEFAULT_PRICE_ID not set');
            }

            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $frontend = env('FRONTEND_URL', 'http://localhost:8080');
                $session = StripeSession::create([
                    'payment_method_types' => ['card'],
                    'mode' => 'subscription',
                    'customer_email' => $user->email,
                    'line_items' => [[
                        'price' => $priceId,
                        'quantity' => 1,
                    ]],
                    'success_url' => $frontend . '/subscriptions/success?checkout_session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $frontend . '/subscriptions/plans',
                ]);
            } catch (Exception $e) {
                Log::error('Failed to create Stripe session: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to create payment session'], 500);
            }

            // Insert checkout URL into email body
            $checkoutUrl = $session->url ?? null;
            $htmlBody = $this->prepareEmailBody($validated, $checkoutUrl);

            $this->configureMailerForDevelopment();

            Mail::html($htmlBody, function ($message) use ($validated) {
                $message->to($validated['to'])->subject($validated['subject']);
            });

            return response()->json([
                'message' => 'Agreement email with payment link sent',
                'checkout' => [
                    'id' => $session->id ?? null,
                    'url' => $checkoutUrl ?? null,
                ],
                'mailer' => config('mail.default'),
            ], 200);

        } catch (Exception $e) {
            Log::error('SendAgreementController@send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Prepares the final HTML content with checkout link.
     */
    private function prepareEmailBody(array $validated, ?string $checkoutUrl = null): string
    {
        $htmlBody = $validated['body'];

        if ($checkoutUrl) {
            $htmlBody = str_replace(
                ['{{checkout_url}}', '{{checkoutUrl}}', '{{name}}'],
                [$checkoutUrl, $checkoutUrl, $validated['name'] ?? ''],
                $htmlBody
            );
        }

        if (stripos($htmlBody, '<html') === false) {
            $safeSubject = htmlspecialchars($validated['subject'], ENT_QUOTES, 'UTF-8');
            return "<!DOCTYPE html><html><head><title>{$safeSubject}</title></head><body>{$htmlBody}</body></html>";
        }

        return $htmlBody;
    }

    private function configureMailerForDevelopment(): void
    {
        if (config('mail.default') === 'log' && env('MAIL_FORCE_SMTP', false)) {
            Log::info('Overriding mailer to SMTP for development.');
            config(['mail.default' => 'smtp']);
        }
    }
}
