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

            // Generate a guest token for the user so they can login via magic link
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
     * Prepares the final HTML content with checkout link.
     */
    private function prepareEmailBody(array $validated, ?string $checkoutUrl = null, ?string $token = null): string
    {
        $htmlBody = $validated['body'];

        // Build a magic link URL that includes the guest token so the recipient can
        // be logged in automatically.
        $frontend = env('FRONTEND_URL', 'http://127.0.0.1:8080');

        $queryParams = array_filter([
            'token' => $token,
            'email' => $validated['to'] ?? null,
            'lead_id' => $validated['lead_id'] ?? null,
            'price_id' => $validated['price_id'] ?? null,
        ], static fn($value) => $value !== null && $value !== '');

        $magicLink = $frontend . '/magic-login-and-redirect';
        if (!empty($queryParams)) {
            $magicLink .= '?' . http_build_query($queryParams);
        }

        $replacements = [
            '{{magic_link}}' => $magicLink,
            '{{plans_url}}' => $magicLink,
            '{{plansUrl}}' => $magicLink,
            '{{name}}' => $validated['name'] ?? '',
        ];

        if ($checkoutUrl) {
            $replacements['{{checkout_url}}'] = $checkoutUrl;
            $replacements['{{checkoutUrl}}'] = $checkoutUrl;
            $replacements['{{stripe_checkout_url}}'] = $checkoutUrl;
            $replacements['{{stripeCheckoutUrl}}'] = $checkoutUrl;
        } else {
            $replacements['{{checkout_url}}'] = $magicLink;
            $replacements['{{checkoutUrl}}'] = $magicLink;
        }

        $htmlBody = str_replace(array_keys($replacements), array_values($replacements), $htmlBody);

        if (stripos($htmlBody, '<html') === false) {
            $safeSubject = htmlspecialchars($validated['subject'], ENT_QUOTES, 'UTF-8');
            return "<!DOCTYPE html><html><head><title>{$safeSubject}</title></head><body>{$htmlBody}</body></html>";
        }

        return $htmlBody;
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

    /**
     * Exchange a one-time guest token for an API access token so the lead
     * can be automatically logged in from the emailed magic link.
     */
    public function magicLogin(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $tokenValue = $validated['token'];

        $user = User::with(['roles', 'userDetails'])->where('remember_token', $tokenValue)->first();
        if (!$user) {
            Log::warning('Magic login attempted with invalid token', ['token' => $tokenValue]);
            return response()->json(['error' => 'Invalid or expired link'], 410);
        }

        try {
            $personalToken = $user->createToken('magic-link');
            $tokenModel = $personalToken->token;

            if (!$tokenModel->expires_at) {
                $tokenModel->expires_at = Carbon::now()->addHours(12);
                $tokenModel->save();
            }

            $expiresAt = Carbon::parse($tokenModel->expires_at);
            $expiresIn = max($expiresAt->diffInSeconds(Carbon::now()), 0);

            $organization = Organization::where('user_id', $user->id)->first();
            $roleName = optional($user->roles->first())->name ?? 'user';

            $userPayload = [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $roleName,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'organization_id' => $organization?->id,
                'organization_name' => $organization?->organization_name,
            ];

            // Invalidate the token so it cannot be reused.
            $user->remember_token = null;
            $user->save();

            Log::info('Magic login token consumed successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Magic login successful',
                'access_token' => $personalToken->accessToken,
                'refresh_token' => null,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
                'expires_at' => $expiresAt->toDateTimeString(),
                'user' => $userPayload,
            ]);
        } catch (Exception $exception) {
            Log::error('Magic login processing failed', [
                'token' => $tokenValue,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['error' => 'Unable to complete login'], 500);
        }
    }

    private function configureMailerForDevelopment(): void
    {
        if (config('mail.default') === 'log' && env('MAIL_FORCE_SMTP', false)) {
            Log::info('Overriding mailer to SMTP for development.');
            config(['mail.default' => 'smtp']);
        }
    }
}