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

            // Create or find user
            $user = User::where('email', $validated['to'])->first();
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

                // Attach user details from lead if available
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

                // Attach role
                try {
                    $role = Role::where('name', 'organizationadmin')->first();
                    if ($role) {
                        $user->roles()->attach($role->id);
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to attach role: ' . $e->getMessage());
                }
            }

            // Build plans URL with identifying query params
            $frontend = env('FRONTEND_URL', 'http://127.0.0.1:8080');
            $plansUrl = $frontend . '/subscriptions/plans';
            $qs = [];
            if (!empty($user->email)) {
                $qs['email'] = $user->email;
            }
            if (!empty($validated['lead_id'])) {
                $qs['lead_id'] = $validated['lead_id'];
            }
            if (!empty($validated['price_id'])) {
                $qs['price_id'] = $validated['price_id'];
            }

            // Create a short-lived personal access token for this user so the emailed link can auto-login
            try {
                $tokenResult = $user->createToken('GuestLink');
                $tokenResult->token->expires_at = now()->addHours(2);
                $tokenResult->token->save();
                $guestToken = $tokenResult->accessToken;
                if (!empty($guestToken)) {
                    $qs['guest_token'] = $guestToken;
                }
            } catch (Exception $e) {
                Log::warning('Failed to create guest token for email link: ' . $e->getMessage());
            }
            if (!empty($qs)) {
                $plansUrl .= '?' . http_build_query($qs);
            }

            // Insert plans URL into email body
            $validated['checkout_url'] = $plansUrl;
            $htmlBody = $this->prepareEmailBody($validated, $plansUrl);

            $this->configureMailerForDevelopment();

            // Log a short snippet and any plans-related hrefs from the final HTML
            try {
                $hrefs = [];
                libxml_use_internal_errors(true);
                $dom = new \DOMDocument();
                $wrapped = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . $htmlBody . '</body></html>';
                $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                foreach ($dom->getElementsByTagName('a') as $a) {
                    $href = $a->getAttribute('href');
                    if ($href) {
                        $hrefs[] = $href;
                    }
                }
                libxml_clear_errors();
                Log::info('SendAgreementController: final email HTML preview', [
                    'to' => $validated['to'] ?? null,
                    'checkout_url' => $validated['checkout_url'] ?? null,
                    'hrefs' => array_values(array_unique(array_filter($hrefs))),
                    'snippet' => substr(strip_tags($htmlBody), 0, 500),
                ]);
            } catch (Exception $e) {
                Log::warning('SendAgreementController: failed to parse final HTML for logging: ' . $e->getMessage());
            }

            Mail::html($htmlBody, function ($message) use ($validated) {
                $message->to($validated['to'])->subject($validated['subject']);
            });

            $responsePayload = [
                'message' => 'Agreement email sent',
                'mailer' => config('mail.default'),
            ];

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
     * Prepares the final HTML content for the send-agreement email.
     * Replaces placeholders like {{checkout_url}} and {{name}}.
     */
    private function prepareEmailBody(array $validated, string $checkoutUrl): string
    {
        $htmlBody = $validated['body'] ?? '';
        $placeholders = ['{{checkout_url}}', '{{checkoutUrl}}', '{{checkouturl}}', '{{name}}', '{{plans_url}}'];
        $replacements = [$checkoutUrl, $checkoutUrl, $checkoutUrl, $validated['name'] ?? '', $checkoutUrl];
        $htmlBody = str_replace($placeholders, $replacements, $htmlBody);

        // Replace any occurrence of the plans page URL in hrefs or plain text.
        $pattern = '/https?:\/\/[\w:\.\-@]+\/subscriptions\/plans(?:\?[^"\'\s<>]*)?/i';
        $htmlBody = preg_replace($pattern, $checkoutUrl, $htmlBody);

        $htmlBody = str_replace('/subscriptions/plans', $checkoutUrl, $htmlBody);

        $checkoutUrlHtmlAttr = htmlspecialchars($checkoutUrl, ENT_QUOTES, 'UTF-8');
        $hrefPattern = '/href=(["\'])(?:https?:\/\/[^"\']+)?\/subscriptions\/plans(?:\?[^"\']*)?\1/i';
        $htmlBody = preg_replace($hrefPattern, 'href=$1' . $checkoutUrlHtmlAttr . '$1', $htmlBody);

        // DOM parsing for anchor hrefs
        try {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $wrapped = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . $htmlBody . '</body></html>';
            $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $anchors = $dom->getElementsByTagName('a');
            foreach ($anchors as $a) {
                $href = $a->getAttribute('href');
                if ($href && str_contains($href, '/subscriptions/plans')) {
                    $a->setAttribute('href', $checkoutUrl);
                }
            }
            $body = $dom->getElementsByTagName('body')->item(0);
            $newHtml = '';
            foreach ($body->childNodes as $child) {
                $newHtml .= $dom->saveHTML($child);
            }
            if (!empty($newHtml)) {
                $htmlBody = $newHtml;
            }
            libxml_clear_errors();
        } catch (Exception $e) {
            Log::warning('prepareEmailBody DOM parsing failed: ' . $e->getMessage());
        }

        // Ensure the email includes a visible, copy/paste-friendly URL
        $safeUrl = htmlspecialchars($checkoutUrl, ENT_QUOTES, 'UTF-8');
        $plainLinkBlock = "<div style=\"margin-top:18px;text-align:center;font-size:13px;color:#666;\">" .
            "If the button above doesn't work, copy and paste this link into your browser:<br/>" .
            "<a href=\"{$safeUrl}\" target=\"_self\">{$safeUrl}</a>" .
            "</div>";

        try {
            Log::info('SendAgreementController: prepared checkout_url for email', [
                'checkout_url' => $checkoutUrl,
                'to' => $validated['to'] ?? null,
                'lead_id' => $validated['lead_id'] ?? null
            ]);
        } catch (Exception $e) {
            // Non-fatal if logging fails.
        }

        if (stripos($htmlBody, '<html') === false) {
            $safeSubject = htmlspecialchars($validated['subject'] ?? 'Agreement', ENT_QUOTES, 'UTF-8');
            return "<!DOCTYPE html><html><head><title>{$safeSubject}</title></head><body>" . $htmlBody . $plainLinkBlock . "</body></html>";
        }

        $pos = stripos($htmlBody, '</body>');
        if ($pos !== false) {
            $htmlBody = substr_replace($htmlBody, $plainLinkBlock, $pos, 0);
            return $htmlBody;
        }

        return $htmlBody . $plainLinkBlock;
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
     * Configure mailer for development.
     */
    private function configureMailerForDevelopment(): void
    {
        if (config('mail.default') === 'log' && env('MAIL_FORCE_SMTP', false)) {
            Log::info('Overriding mailer to SMTP for development.');
            config(['mail.default' => 'smtp']);
        }
    }
}