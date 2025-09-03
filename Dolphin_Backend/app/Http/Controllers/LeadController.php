<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeadAssessmentRegistrationMail;

class LeadController extends Controller
{
    public function update(Request $request, $id)
    {
        $lead = Lead::find($id);
       
        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }
        $data = $request->validate([
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string',
            'find_us' => 'sometimes|string',
            'org_name' => 'sometimes|string',
            'org_size' => 'sometimes|string',
            'notes' => 'sometimes|string',
            'address' => 'sometimes|string',
            'country_id' => 'sometimes|integer|exists:countries,id',
            'state_id' => 'sometimes|integer|exists:states,id',
            'city_id' => 'sometimes|integer|exists:cities,id',
            'zip' => 'sometimes|string',
        ]);
        $lead->update($data);
        return response()->json(['message' => 'Lead updated successfully', 'lead' => $lead]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
          'email' => 'required|string|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
            'phone' => 'required|string',
            'find_us' => 'required|string',
            'org_name' => 'required|string',
            'org_size' => 'required|string',
            'notes' => 'required|string',
            'address' => 'required|string',
            'country_id' => 'required|integer|exists:countries,id',
            'state_id' => 'required|integer|exists:states,id',
            'city_id' => 'required|integer|exists:cities,id',
            'zip' => 'required|string',
        ]);
        $lead = Lead::create($data);
        return response()->json(['message' => 'Lead saved successfully', 'lead' => $lead], 201);
    }

    public function index()
    {
        return response()->json(Lead::all());
    }
    /**
     * Return a single lead by id.
     */
    public function show($id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        // // Try to find an organization associated with this lead's email.
        // // Strategy: find a user with the same email, then check organizations.user_id.
        // $org = null;
        // $orgUser = null;
        // $orgUserDetails = null;
        
                                // Build a registration link that points to the frontend app so users land on the registration page.
                                // Prefer FRONTEND_URL (set in .env), fallback to a sensible local dev port.
                                $frontendBase = env('FRONTEND_URL', env('APP_URL', 'http://127.0.0.1:8080'));

                                // Prepare query params to prefill the registration form on the frontend.
                                $queryParams = [
                                        'email' => $lead->email,
                                        'first_name' => $lead->first_name ?? '',
                                        'last_name' => $lead->last_name ?? '',
                                        'phone' => $lead->phone ?? '',
                                        'organization_name' => $lead->org_name ?? '',
                                        'organization_size' => $lead->org_size ?? '',
                                        'organization_address' => $lead->address ?? '',
                                        'organization_city' => $lead->city_id ?? '',
                                        'organization_state' => $lead->state_id ?? '',
                                        'organization_zip' => $lead->zip ?? '',
                                        'country' => $lead->country_id ?? '',
                                        'find_us' => $lead->find_us ?? '',
                                        'lead_id' => $lead->id,
                                ];

                                $registration_link = rtrim($frontendBase, '/') . '/register?' . http_build_query($queryParams);
                                $safeLink = htmlspecialchars($registration_link, ENT_QUOTES, 'UTF-8');
                                $safeName = htmlspecialchars($lead->first_name ?? $lead->email, ENT_QUOTES, 'UTF-8');
                               $defaultTemplate = <<<HTML
<div style="width:100%; padding:40px 0; background-color:#f6f9fc; font-family: Arial, sans-serif;"><div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:6px; padding:30px; box-shadow:0 2px 4px rgba(0,0,0,0.05);"><div style="font-size:20px; font-weight:bold; color:#333333; margin-bottom:15px;">Hello {$safeName},</div><div style="font-size:16px; color:#555555; line-height:1.5; margin-bottom:25px;">You’ve been invited to complete your signup. Please click the button below to enter your details and activate your account.</div><div style="text-align:center;"><a href="{$safeLink}" style="display:inline-block; padding:10px 20px; background-color:#0164A5; color:#ffffff; text-decoration:none; border-radius:50px; font-weight:bold;">Complete Signup</a></div><div style="font-size:13px; color:#888888; text-align:center; margin-top:30px;">If you did not request this, you can safely ignore this email.</div><div style="margin-top:12px; text-align:center; word-break:break-all; color:#007bff;"><a href="{$safeLink}" style="color:#007bff;"></a></div></div></div>
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

    if ($org) {
        $resp['organization'] = $org;
        $resp['orgUser'] = $orgUser;
        $resp['orgUserDetails'] = $orgUserDetails;
    }

    return response()->json($resp);
}

        /**
         * Return an email template HTML for lead registration editor prefills.
         * Public endpoint used by the frontend editor to fetch a ready-made template.
         */
        public function leadRegistration(Request $request)
        {
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
    /**
     * Prefill endpoint for registration form: returns all lead data by lead_id or email
     */
    public function prefill(Request $request)
    {
        $lead = null;
        if ($request->has('lead_id')) {
            $lead = Lead::find($request->input('lead_id'));
        } elseif ($request->has('email')) {
            $lead = Lead::where('email', $request->input('email'))->first();
        }
        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
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
            'organization_name' => $lead->org_name ?? '',
            'organization_size' => $lead->org_size ?? '',
            'organization_address' => $lead->address ?? '',
            'organization_city_id' => $lead->city_id ?? '',
            'organization_state_id' => $lead->state_id ?? '',
            'organization_zip' => $lead->zip ?? '',
            'country_id' => $lead->country_id ?? '',
            'find_us' => $lead->find_us ?? '',
        ]]);
    }

    /**
     * Return distinct non-empty find_us values found in leads (public).
     */
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
