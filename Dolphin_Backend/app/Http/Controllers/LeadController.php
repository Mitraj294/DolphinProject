<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeadAssessmentRegistrationMail;

class LeadValidationRules
{
    public const REQUIRED_INTEGER = 'required|integer';
    public const REQUIRED_STRING = 'required|string';
    public const REQUIRED_EMAIL = 'required|email';
    public const OPTIONAL_INTEGER = 'nullable|integer';
    public const OPTIONAL_STRING = 'nullable|string';
    public const REQUIRED_BOOLEAN = 'required|boolean';
    public const REQUIRED_DATE = 'required|date';
}

class Message
{
    public const MESSAGE = 'Lead Not Found';
}

class LeadController extends Controller
{
    public function update(Request $request, $id)
    {
        $lead = Lead::find($id);
       
        if (!$lead) {
            return response()->json(['message' =>  Message::MESSAGE], 404);
        }
        // If this is a PATCH request with only 'notes' provided, validate and update just that field.
        // This avoids triggering full required-field validation for partial notes updates from the frontend.
        if ($request->isMethod('patch')) {
            $payloadKeys = array_keys($request->all());
            $onlyNotes = !empty($payloadKeys) && collect($payloadKeys)->every(function ($k) { return $k === 'notes'; });
            if ($request->has('notes') && $onlyNotes) {
                $data = $request->validate([
                    'notes' => LeadValidationRules::OPTIONAL_STRING,
                ]);
                $lead->update($data);
                return response()->json(['message' => 'Notes updated successfully', 'lead' => $lead]);
            }
        }
        $data = $request->validate([
            'first_name' =>  LeadValidationRules::REQUIRED_STRING,
            'last_name' => LeadValidationRules::REQUIRED_STRING,
            'email' => LeadValidationRules::REQUIRED_EMAIL,
            'phone' => 'required|regex:/^[6-9]\d{9}$/',
            'find_us' => LeadValidationRules::REQUIRED_STRING,
            'organization_name' => LeadValidationRules::REQUIRED_STRING.'|  max:500',
            'organization_size' => LeadValidationRules::REQUIRED_STRING,
            'notes' => LeadValidationRules::OPTIONAL_STRING,
            'address' => LeadValidationRules::REQUIRED_STRING.'|max:500',
            'country_id' => LeadValidationRules::REQUIRED_INTEGER . '|exists:countries,id',
            'state_id' => LeadValidationRules::REQUIRED_INTEGER . '|exists:states,id',
            'city_id' => LeadValidationRules::REQUIRED_INTEGER . '|exists:cities,id',
            'zip' => 'required|regex:/^[1-9][0-9]{5}$/',

        ]);
        $lead->update($data);
        return response()->json(['message' => 'Lead updated successfully', 'lead' => $lead]);
    }
    public function store(Request $request)
    {
                $data = $request->validate([
                        'first_name' => LeadValidationRules::REQUIRED_STRING,
                        'last_name' => LeadValidationRules::REQUIRED_STRING,
                    // Allow creating leads even if a user already exists with this email. We'll reconcile status below.
                    'email' => 'required|string|email|max:255',
            'phone' => 'required|regex:/^[6-9]\d{9}$/',
            'find_us' => LeadValidationRules::REQUIRED_STRING,
            'organization_name' => LeadValidationRules::REQUIRED_STRING.'|max:500',
            'organization_size' => LeadValidationRules::REQUIRED_STRING,
            'notes' => LeadValidationRules::OPTIONAL_STRING,
            'address' => LeadValidationRules::REQUIRED_STRING.'|max:500',
            'country_id' => LeadValidationRules::REQUIRED_INTEGER.'|exists:countries,id',
            'state_id' => LeadValidationRules::REQUIRED_INTEGER.'|exists:states,id',
            'city_id' => LeadValidationRules::REQUIRED_INTEGER.'|exists:cities,id',
            'zip' => 'required|regex:/^[1-9][0-9]{5}$/',
        ]);
        // If an authenticated user made this request, record them as the creator
        if ($request->user()) {
            $data['created_by'] = $request->user()->id;
        }
        $lead = Lead::create($data);

        // If a user already exists with this email, mark the lead as Registered and set registered_at if missing.
        try {
            $userModel = '\App\\Models\\User';
            $matchedUser = $userModel::where('email', $lead->email)->first();
            if ($matchedUser) {
                $lead->status = 'Registered';
                if (empty($lead->registered_at)) {
                    $lead->registered_at = $matchedUser->created_at ?? now();
                }
                // Optionally link the user id column if present on leads table
                if (property_exists($lead, 'user_id')) {
                    $lead->user_id = $matchedUser->id;
                }
                $lead->save();
                Log::info('LeadController: Created lead matched existing user; marked Registered', ['lead_id' => $lead->id, 'user_id' => $matchedUser->id]);
            }
        } catch (\Exception $e) {
            Log::warning('LeadController: Failed to check users table after lead create: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Lead saved successfully', 'lead' => $lead], 201);
    }

    public function index()
    {
        // Return only leads created by the currently authenticated user.
        // If no authenticated user is present, return a 401 Unauthorized response.
        $user = request()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

    // Superadmin may view all leads (use model helper which checks many-to-many roles)
    if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $leads = Lead::all();
            try {
                $ids = $leads->pluck('id')->values()->all();
                Log::info('LeadController@index superadmin fetch', ['user_id' => $user->id, 'count' => $leads->count(), 'ids' => $ids]);
                // Also write a lightweight debug file in case main logging is not capturing info-level logs in this environment.
                try {
                    $debugPath = storage_path('logs/leads_debug.log');
                    $payload = [
                        'time' => now()->toDateTimeString(),
                        'user_id' => $user->id,
                        'count' => $leads->count(),
                        'ids' => $ids,
                    ];
                    file_put_contents($debugPath, json_encode($payload, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND | LOCK_EX);
                } catch (\Exception $e) {
                    // Avoid breaking the request flow for debugging failures
                    Log::warning('LeadController@index file debug write failed: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                Log::warning('LeadController@index logging failed: ' . $e->getMessage());
            }
            return response()->json($leads);
        }

        $leads = Lead::where('created_by', $user->id)->get();
        return response()->json($leads);
    }

    public function show($id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['message' =>  Message::MESSAGE,], 404);
        }

        // // Try to find an organization associated with this lead's email.
        // // Strategy: find a user with the same email, then check organizations.user_id.
        // $org = null;
        // $orgUser = null;
        // $orgUserDetails = null;
        
                                // Build a registration link that points to the frontend app so users land on the registration page.
                                // Prefer FRONTEND_URL (set in .env), fallback to APP_URL and finally a sensible local dev port.
                                $frontendBase = env('FRONTEND_URL', env('APP_URL', 'http://127.0.0.1:8080'));

                                // Prepare query params to prefill the registration form on the frontend.
                                $queryParams = [
                                        'email' => $lead->email,
                                        'first_name' => $lead->first_name ?? '',
                                        'last_name' => $lead->last_name ?? '',
                                        'phone' => $lead->phone ?? '',
                                        'organization_name' => $lead->organization_name ?? '',
                                        'organization_size' => $lead->organization_size ?? '',
                                        'organization_address' => $lead->address ?? '',
                                        'organization_city' => (string)($lead->city_id ?? ''),
                                        'organization_state' => (string)($lead->state_id ?? ''),
                                        'organization_zip' => $lead->zip ?? '',
                                        'country' => (string)($lead->country_id ?? ''),
                                        'find_us' => $lead->find_us ?? '',
                                        'lead_id' => $lead->id,
                                ];

                                $registration_link = rtrim($frontendBase, '/') . '/register?' . http_build_query($queryParams);
                                Log::info('LeadController: prepared registration_link', ['registration_link' => $registration_link, 'lead_id' => $lead->id]);
                                $safeLink = htmlspecialchars($registration_link, ENT_QUOTES, 'UTF-8');
                                $safeName = htmlspecialchars((string)($lead->first_name ?? $lead->email), ENT_QUOTES, 'UTF-8');
                               
                                $defaultTemplate = <<<HTML
                                    <h2>Hello {$safeName},</h2>
                                    <p>You've been invited to complete your signup. Please click the button below to enter your details and activate your account.</p>
                                    <p style="text-align: center;">
                                        <a href="{$safeLink}" style="display: inline-block; padding: 12px 24px; background-color: #0164A5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Complete Signup</a>
                                    </p>
                                    <p style="font-size: 13px; color: #888888; text-align: center;">If you did not request this, you can safely ignore this email.</p>
                                    HTML;


            // ensure variables are defined so we don't hit "Undefined variable" when no organization/user is found
            $org = null;
            $orgUser = null;
            $orgUserDetails = null;

            try {
                    $userModel = '\App\\Models\\User';
                    $user = $userModel::where('email', $lead->email)->first();
                    if ($user) {
                        $orgModel = '\App\\Models\\Organization';
                        $org = $orgModel::where('user_id', $user->id)->first();
                        if ($org) {
                            $orgUser = $user;
                            // try to load user details if the relation or model exists
                            $detailsModel = '\App\\Models\\UserDetail';
                            if (class_exists($detailsModel)) {
                                $orgUserDetails = $detailsModel::where('user_id', $user->id)->first();
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('LeadController::show organization lookup failed: ' . $e->getMessage());
                }

                // Compose response: include lead and, if found, organization and primary user details
                $resp = ['lead' => $lead];
                $resp['defaultTemplate'] = $defaultTemplate;

                // Resolve sales person (if any) from lead or organization and include their name/id in the response.
                try {
                    $salesPersonId = null;
                    // Prefer explicit sales_person_id on the lead, fall back to organization.sales_person_id if present
                    if (isset($lead->sales_person_id) && $lead->sales_person_id) {
                        $salesPersonId = $lead->sales_person_id;
                    } elseif (isset($org) && isset($org->sales_person_id) && $org->sales_person_id) {
                        $salesPersonId = $org->sales_person_id;
                    }

                    if ($salesPersonId) {
                        $userModel = '\\App\\Models\\User';
                        if (class_exists($userModel)) {
                            $salesUser = $userModel::find($salesPersonId);
                            if ($salesUser) {
                                // add a gentle, compact representation
                                $resp['sales_person'] = [
                                    'id' => $salesUser->id,
                                    'first_name' => $salesUser->first_name ?? null,
                                    'last_name' => $salesUser->last_name ?? null,
                                    'full_name' => trim(($salesUser->first_name ?? '') . ' ' . ($salesUser->last_name ?? '')),
                                    'email' => $salesUser->email ?? null,
                                ];
                                // also set a convenient property on the lead object for backward compatibility
                                $lead->sales_person = $resp['sales_person']['full_name'];
                                $lead->sales_person_id = $salesUser->id;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('LeadController::show sales person lookup failed: ' . $e->getMessage());
                }

            if ($org) {
                $resp['organization'] = $org;
                $resp['orgUser'] = $orgUser;
                $resp['orgUserDetails'] = $orgUserDetails;
            }

            return response()->json($resp);
    }

    /**
     * Soft-delete a lead by id.
     * Route: DELETE /api/leads/{id}
     */
    public function destroy(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['message' => Message::MESSAGE], 404);
        }

        try {
            // uses SoftDeletes on the model
            $lead->delete();
            Log::info('LeadController@destroy soft-deleted lead', ['lead_id' => $id, 'deleted_by' => $request->user()->id ?? null]);
            return response()->json(['message' => 'Lead soft-deleted', 'id' => $id]);
        } catch (\Exception $e) {
            Log::error('LeadController@destroy failed to delete lead: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete lead'], 500);
        }
    }

   
    public function leadRegistration(Request $request)
    {
                // Prefer FRONTEND_URL, fallback to APP_URL, then localhost dev URL
                $registration_link = $request->query('registration_link', rtrim(env('FRONTEND_URL', env('APP_URL', 'http://127.0.0.1:8080')), '/') . '/register');
                $name = $request->query('name', '');

                $safeLink = htmlspecialchars($registration_link, ENT_QUOTES, 'UTF-8');
                $safeName = htmlspecialchars($name ?: 'User', ENT_QUOTES, 'UTF-8');

                $html = <<<HTML
                <!doctype html>
                <html>
                <head>
                    <meta charset="utf-8" />
                    <meta name="viewport" content="width=device-width,initial-scale=1" />
                    <title>Registration Invite</title>
                </head>
                <body>
                    <div class="email-container">
                        <div style="width:100%; padding:40px 0; background-color:#f6f9fc; font-family: Arial, sans-serif;">
                            <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:6px; padding:30px; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                                <div style="font-size:20px; font-weight:bold; color:#333333; margin-bottom:15px;">Hello {$safeName},</div>
                                <div style="font-size:16px; color:#555555; line-height:1.5; margin-bottom:25px;">You’ve been invited to complete your signup. Please click the button below to enter your details and activate your account.</div>
                                <div style="text-align:center;">
                                    <a href="{$safeLink}" style="display:inline-block; padding:10px 20px; background-color:#0164A5; color:#ffffff; text-decoration:none; border-radius:50px; font-weight:bold;">Complete Signup</a>
                                </div>
                                <div style="font-size:13px; color:#888888; text-align:center; margin-top:30px;">If you did not request this, you can safely ignore this email.</div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
                HTML;

                return response($html, 200)->header('Content-Type', 'text/html');
    }
   
    public function leadAgreement(Request $request)
    {
    // Prefer provided checkout_url; if missing, point to frontend plans page.
    $checkout_url = $request->query('checkout_url', rtrim(env('FRONTEND_URL', env('APP_URL', 'http://127.0.0.1:8080')), '/') . '/subscriptions/plans');
        $name = $request->query('name', '');

        $safeLink = htmlspecialchars($checkout_url, ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($name ?: 'User', ENT_QUOTES, 'UTF-8');

    Log::info('LeadController: prepared leadAgreement checkout_url', ['checkout_url' => $checkout_url, 'name' => $name]);

    $html = <<<HTML
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width,initial-scale=1" />
            <title>Agreement and Payment</title>
        </head>
        <body>
            <div class="email-container">
                <div style="width:100%; padding:40px 0; background-color:#f6f9fc; font-family: Arial, sans-serif;">
                    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:6px; padding:30px; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                        <div style="font-size:20px; font-weight:bold; color:#333333; margin-bottom:15px;">Hello {$safeName},</div>
                        <div style="font-size:16px; color:#555555; line-height:1.5; margin-bottom:25px;">Please find your agreement and payment link below. Click the button to proceed with the subscription.</div>
                        <div style="text-align:center;">
                            <a href="{$safeLink}" style="display:inline-block; padding:10px 20px; background-color:#0164A5; color:#ffffff; text-decoration:none; border-radius:50px; font-weight:bold;">Proceed to Payment</a>
                        </div>
                        <div style="font-size:13px; color:#888888; text-align:center; margin-top:30px;">If you did not request this, you can safely ignore this email.</div>
                        
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;

        return response($html, 200)->header('Content-Type', 'text/html');
    }

    public function prefill(Request $request)
    {
        $lead = null;
        if ($request->has('lead_id')) {
            $lead = Lead::find($request->input('lead_id'));
        } elseif ($request->has('email')) {
            $lead = Lead::where('email', $request->input('email'))->first();
        }
        if (!$lead) {
            return response()->json(['message' =>  Message::MESSAGE,], 404);
        }
        // Log find_us presence/absence for debugging
        if (empty($lead->find_us)) {
            Log::info("Lead prefill: lead_id={$lead->id} find_us is empty");
        } else {
            Log::info("Lead prefill: lead_id={$lead->id} find_us={$lead->find_us}");
        }

        return response()->json(['lead' => [
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'organization_name' => $lead->organization_name ?? '',
            'organization_size' => $lead->organization_size ?? '',
            'organization_address' => $lead->address ?? '',
            'organization_city_id' => $lead->city_id ?? '',
            'organization_state_id' => $lead->state_id ?? '',
            'organization_zip' => $lead->zip ?? '',
            'country_id' => $lead->country_id ?? '',
            'find_us' => $lead->find_us ?? '',
        ]]);
    }

   
    public function findUsOptions()
    {
        $values = Lead::whereNotNull('find_us')
            ->where('find_us', '!=', '')
            ->distinct()
            ->pluck('find_us')
            ->filter()
            ->values();

        if ($values->isEmpty()) {
            Log::info('Lead find_us options: none found');
        } else {
            Log::info('Lead find_us options: ' . $values->join(', '));
        }

        return response()->json(['options' => $values]);
    }
}
