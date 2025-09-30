<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Lead;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use App\Models\GuestLink;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
                    // Create a short guest code and persist to guest_links so the emailed link is small
                    $guestCode = Str::upper(Str::random(10));
                    $guestLink = GuestLink::create([
                        'code' => $guestCode,
                        'user_id' => $user->id,
                        'lead_id' => $validated['lead_id'] ?? null,
                        'meta' => ['price_id' => $validated['price_id'] ?? null],
                        'expires_at' => now()->addHours(2),
                    ]);
                    if ($guestLink) {
                        $qs['guest_code'] = $guestCode;
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
        $responseData = ['valid' => false];
        $status = 200;

        // Prefer short guest codes (safer to email). If present, redeem the
        // guest code by creating a short-lived personal access token and
        // returning it to the frontend for immediate use.
        $guestCode = $request->query('guest_code');
        if ($guestCode) {
            try {
                $guestLink = GuestLink::where('code', $guestCode)->first();
                if (! $guestLink) {
                    $responseData['message'] = 'Invalid guest code';
                    // fall through to final return
                } else {
                    if ($guestLink->used_at) {
                        $responseData['message'] = 'Guest code already used';
                    } elseif ($guestLink->expires_at && Carbon::parse($guestLink->expires_at)->lt(Carbon::now())) {
                        $responseData['message'] = 'Guest code expired';
                    } else {
                        $user = User::find($guestLink->user_id);
                        if (! $user) {
                            $responseData['message'] = 'User not found';
                        } else {
                            try {
                                $tokenResult = $user->createToken('GuestLink');
                                if (isset($tokenResult->token)) {
                                    $tokenResult->token->expires_at = Carbon::now()->addHours(2);
                                    $tokenResult->token->save();
                                }
                                $plainToken = $tokenResult->accessToken ?? null;

                                // mark the guest link used
                                $guestLink->used_at = Carbon::now();
                                $guestLink->save();

                                $responseData = [
                                    'valid' => true,
                                    'token' => $plainToken,
                                    'expires_at' => $tokenResult->token->expires_at ?? null,
                                    'user' => [
                                        'id' => $user->id,
                                        'email' => $user->email,
                                        'first_name' => $user->first_name,
                                        'last_name' => $user->last_name,
                                    ],
                                ];
                            } catch (\Exception $e) {
                                Log::warning('Failed to mint guest token: ' . $e->getMessage());
                                $responseData['message'] = 'Failed to mint token';
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('validateGuest guest_code flow failed: ' . $e->getMessage());
                $responseData['message'] = 'Guest flow failed';
            }
        } else {
            // Backwards-compatible: accept a full token string in `token` query
            // parameter and attempt to resolve it against Passport's storage.
            $token = $request->query('token');
            if (! $token) {
                $responseData = ['error' => 'Missing token or guest_code'];
                $status = 400;
            } else {
                $isValid = false;
                $foundUser = null;
                try {
                    $accessTokenModel = null;

                    if (strpos($token, '|') !== false) {
                        [$idPart] = explode('|', $token, 2);
                        $accessTokenModel = DB::table('oauth_access_tokens')->where('id', $idPart)->first();
                    }

                    if (!$accessTokenModel && substr_count($token, '.') === 2) {
                        try {
                            $parts = explode('.', $token);
                            $payloadB64 = $parts[1] ?? null;
                            $payloadJson = $payloadB64 ? base64_decode(strtr($payloadB64, '-_', '+/')) : null;
                            $payload = json_decode($payloadJson, true);
                            if (is_array($payload) && !empty($payload['jti'])) {
                                $accessTokenModel = DB::table('oauth_access_tokens')->where('id', $payload['jti'])->first();
                            }
                        } catch (\Exception $e) {
                            // ignore malformed JWTs here and continue to fallback
                        }
                    }

                    if (!$accessTokenModel) {
                        $accessTokenModel = DB::table('oauth_access_tokens')->where('id', $token)->first();
                    }

                    if ($accessTokenModel) {
                        $now = Carbon::now();
                        $expiresAt = isset($accessTokenModel->expires_at) ? Carbon::parse($accessTokenModel->expires_at) : null;
                        if (empty($accessTokenModel->revoked) && (! $expiresAt || $expiresAt->gt($now))) {
                            $foundUser = \App\Models\User::find($accessTokenModel->user_id);
                            if ($foundUser) {
                                $isValid = true;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('validateGuest token check failed: ' . $e->getMessage());
                }

                if (! $isValid) {
                    try {
                        $user = User::where('remember_token', $token)->first();
                        if ($user) {
                            $isValid = true;
                            $foundUser = $user;
                        }
                    } catch (\Exception $e) {
                        Log::warning('validateGuest remember_token fallback failed: ' . $e->getMessage());
                    }
                }

                if ($isValid && $foundUser) {
                    $responseData = [
                        'valid' => true,
                        'user' => [
                            'id' => $foundUser->id,
                            'email' => $foundUser->email,
                            'first_name' => $foundUser->first_name,
                            'last_name' => $foundUser->last_name,
                        ],
                    ];
                }
            }
        }

        return response()->json($responseData, $status);
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
    $hrefPattern = "/href=(['\"])(?:https?:\\/\\/[^\"']+)?\\/subscriptions\\/plans(?:\\?[^\"']*)?\\1/i";
        $htmlBody = preg_replace($hrefPattern, 'href=$1' . $checkoutUrlHtmlAttr . '$1', $htmlBody);

        // Ensure anchor hrefs point to the final URL
        try {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $wrapped = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . $htmlBody . '</body></html>';
            $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            foreach ($dom->getElementsByTagName('a') as $a) {
                $href = $a->getAttribute('href');
                if ($href && preg_match('/\/subscriptions\/plans/i', $href)) {
                    $a->setAttribute('href', $checkoutUrl);
                }
            }
            libxml_clear_errors();
            $htmlBody = '';
            foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $child) {
                $htmlBody .= $dom->saveHTML($child);
            }
        } catch (\Exception $e) {
            // If DOM parsing fails, fallback to the replaced raw HTML
            Log::warning('prepareEmailBody DOM parsing failed: ' . $e->getMessage());
        }

        return $htmlBody;
    }
}