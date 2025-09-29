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
     * Handles the request to send an agreement/payment link email to a lead.
     */
    public function send(Request $request)
    {
        try {
            $validated = $request->validate([
                'to' => 'required|email',
                'subject' => 'required|string',
                'body' => 'required|string',
                'registration_link' => 'nullable|url',
                'price_id' => 'nullable|string',
                'name' => 'nullable|string',
                'lead_id' => 'nullable|integer|exists:leads,id',
            ]);

            Log::info('SendAgreementController@send called', $validated);

            // Find lead to pull additional fields if present
            $lead = null;
            if (!empty($validated['lead_id'])) {
                $lead = Lead::find($validated['lead_id']);
            } else {
                $lead = Lead::where('email', $validated['to'])->first();
            }

            // Create or find user for this email. We create a user with role 'organizationadmin'
            $user = User::where('email', $validated['to'])->first();
            if (!$user) {
                $passwordPlain = Str::random(12);
                $userData = [
                    'email' => $validated['to'],
                    'first_name' => $lead->first_name ?? ($validated['name'] ?? null),
                    'last_name' => $lead->last_name ?? null,
                    // Create account with a random password (user will reset it via email link)
                    'password' => Hash::make($passwordPlain),
                ];

                // Create user minimal record
                $user = User::create($userData);

                // Attach userDetails where possible
                try {
                    if ($lead) {
                        $user->userDetails()->create([
                            'phone' => $lead->phone,
                            'country_id' => $lead->country_id ?? null,
                        ]);
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to create userDetails for user: ' . $e->getMessage());
                }

                // Attach organizationadmin role if present
                try {
                    $role = Role::where('name', 'organizationadmin')->first();
                    if ($role) {
                        $user->roles()->attach($role->id);
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to attach role to user: ' . $e->getMessage());
                }
            }

            // Generate a password reset token and URL so the created user can set their password.
            try {
                /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
                $broker = \Illuminate\Support\Facades\Password::broker();
                $token = $broker->createToken($user);
                $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
            } catch (Exception $e) {
                Log::warning('Failed to create reset token for user: ' . $e->getMessage());
                $resetUrl = null;
            }

            // Prepare Stripe Checkout Session. Use provided price_id or fallback to env DEFAULT_PRICE_ID
            $priceId = $validated['price_id'] ?? env('DEFAULT_PRICE_ID');
            if (!$priceId) {
                Log::warning('No price_id provided and DEFAULT_PRICE_ID not set');
            }

            // Create Stripe Checkout session (subscription)
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

            // Insert checkout URL into template (use session->url) and include reset URL
            $checkoutUrl = $session->url ?? null;
            $htmlBody = $this->prepareEmailBody($validated, $resetUrl ?? $validated['registration_link'] ?? null);
            if ($checkoutUrl) {
                // Add a placeholder replacement for checkout_url
                $htmlBody = str_replace(['{{checkout_url}}', '{{checkoutUrl}}'], [$checkoutUrl, $checkoutUrl], $htmlBody);
            }

            $this->configureMailerForDevelopment();

            Mail::html($htmlBody, function ($message) use ($validated) {
                $message->to($validated['to'])->subject($validated['subject']);
            });

            return response()->json(['message' => 'Agreement email & checkout session created', 'checkout' => ['id' => $session->id ?? null, 'url' => $checkoutUrl ?? null], 'mailer' => config('mail.default')], 200);

        } catch (Exception $e) {
            Log::error('SendAgreementController@send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Prepares the final HTML content for the email.
     */
    private function prepareEmailBody(array $validated, ?string $registrationUrl = null): string
    {
        $htmlBody = $validated['body'];

        // Replace placeholders if a registration URL is provided
        if ($registrationUrl) {
            $placeholders = ['{{registrationUrl}}', '{{registration_link}}', '{{name}}'];
            $replacements = [$registrationUrl, $registrationUrl, $validated['name'] ?? ''];
            $htmlBody = str_replace($placeholders, $replacements, $htmlBody);
        }

        // Wrap in a basic HTML structure if not already present
        if (stripos($htmlBody, '<html') === false) {
            $safeSubject = htmlspecialchars($validated['subject'], ENT_QUOTES, 'UTF-8');
            return "<!DOCTYPE html><html><head><title>{$safeSubject}</title></head><body>{$htmlBody}</body></html>";
        }

        return $htmlBody;
    }

    /**
     * Overrides the default mailer to SMTP for development/testing if configured.
     */
    private function configureMailerForDevelopment(): void
    {
        if (config('mail.default') === 'log' && env('MAIL_FORCE_SMTP', false)) {
            Log::info('Overriding mailer to SMTP for development.');
            config(['mail.default' => 'smtp']);
        }
    }
}
