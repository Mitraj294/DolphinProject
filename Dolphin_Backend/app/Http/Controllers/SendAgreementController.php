<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Lead;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
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
                'name' => 'required|string',
                'lead_id' => 'nullable|integer|exists:leads,id',
            ]);

            Log::info('SendAgreementController@send called', $validated);

            // Find lead by ID or email
            $lead = !empty($validated['lead_id'])
                ? Lead::find($validated['lead_id'])
                : Lead::where('email', $validated['to'])->first();

            // Create or find user. For lead-created users we won't require them to login
            // immediately — instead we generate a guest token that can be used to
            // access the subscription plans page and Stripe checkout flow.
            $user = User::where('email', $validated['to'])->first();
            // (no guest token persisted in this flow)
            if (!$user) {
                $passwordPlain = Str::random(12);
                $nameParts = $this->splitName($validated['name']);
                $userData = [
                    'email' => $validated['to'],
                    'first_name' => $lead->first_name ?? $nameParts['first_name'],
                    'last_name' => $lead->last_name ?? $nameParts['last_name'],
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

            $user->remember_token = Str::random(60);
            $user->save();

            // Prepare Stripe Checkout
            $priceId = $validated['price_id'] ?? env('DEFAULT_PRICE_ID');
            if (!$priceId) {
                Log::warning('No price_id provided and DEFAULT_PRICE_ID not set');
            }

            $session = null;
            $checkoutUrl = null;

            if ($priceId) {
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
                    $checkoutUrl = $session->url;
                } catch (Exception $e) {
                    Log::error('Failed to create Stripe session: ' . $e->getMessage());
                    return response()->json(['error' => 'Failed to create payment session'], 500);
                }
            } else {
                Log::info('Skipping Stripe session creation because no price_id/default price is available.');
            }

            // Insert checkout URL into email body — include identifying query
            // params so the frontend can accept them and prefill the plans page.
            $validated['checkout_url'] = $checkoutUrl;
            $htmlBody = $this->prepareEmailBody($validated, $checkoutUrl, $user->remember_token);

            $this->configureMailerForDevelopment();

            Mail::html($htmlBody, function ($message) use ($validated) {
                $message->to($validated['to'])->subject($validated['subject']);
            });

            $responsePayload = [
                'message' => 'Agreement email sent',
                'mailer' => config('mail.default'),
            ];

            if ($session) {
                $responsePayload['message'] = 'Agreement email with payment link sent';
                $responsePayload['checkout'] = [
                    'id' => $session->id,
                    'url' => $checkoutUrl,
                ];
            }

            return response()->json($responsePayload, 200);

        } catch (Exception $e) {
            Log::error('SendAgreementController@send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Splits a full name into first and last names.
     */
    private function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName));
        $lastName = array_pop($parts);
        $firstName = implode(' ', $parts);
        return [
            'first_name' => $firstName ?: $lastName,
            'last_name' => $firstName ? $lastName : null,
        ];
    }


    /**
     * Validate a guest token supplied by the frontend and return basic user data.
     */
    public function validateGuest(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return response()->json(['error' => 'Missing token'], 400);
        }

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json(['valid' => false], 200);
        }

        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ]
        ], 200);
    }

 

    private function configureMailerForDevelopment(): void
    {
        if (config('mail.default') === 'log' && env('MAIL_FORCE_SMTP', false)) {
            Log::info('Overriding mailer to SMTP for development.');
            config(['mail.default' => 'smtp']);
        }
    }

   

}