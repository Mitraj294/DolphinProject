<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ValidationRules
{
    public const REQUIRED_INTEGER = 'required|integer';
    public const REQUIRED_STRING = 'required|string';
    public const REQUIRED_EMAIL = 'required|email';
    public const OPTIONAL_INTEGER = 'nullable|integer';
    public const REQUIRED_BOOLEAN = 'required|boolean';
    public const REQUIRED_DATE = 'required|date';
    public const NULLABLE_STRING = 'nullable|string|max:255';

}

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ValidationRules::REQUIRED_STRING,
            'last_name' => ValidationRules::REQUIRED_STRING,
            'email' => ValidationRules::REQUIRED_EMAIL . '|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:password',
            'phone' => 'required|regex:/^[6-9]\d{9}$/',
            'find_us' => ValidationRules::NULLABLE_STRING,
            'organization_name' => ValidationRules::NULLABLE_STRING,
            'organization_size' => ValidationRules::NULLABLE_STRING,
            'address' => ValidationRules::NULLABLE_STRING,
            // Accept numeric IDs for location fields coming from frontend
            'country' => ValidationRules::REQUIRED_INTEGER . '|exists:countries,id',
            'state' => ValidationRules::REQUIRED_INTEGER . '|exists:states,id',
            'city' => ValidationRules::REQUIRED_INTEGER . '|exists:cities,id',
            'zip' => ValidationRules::NULLABLE_STRING,
        ]);

        if ($validator->fails()) {
            // Log validation errors and payload (exclude passwords)
            Log::warning('Register validation failed', [
                'errors' => $validator->errors()->toArray(),
                'payload' => $request->except(['password', 'confirm_password']),
                'ip' => $request->ip(),
            ]);
            return response()->json($validator->errors(), 422);
        }

        // Save to users table (basic info)
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'country' => $request->country,
        ]);

        // Save all registration details to user_details table (names are stored on users table)
        // Persist registration details to user_details using *_id columns for locations
        \App\Models\UserDetail::create([
            'user_id' => $user->id,
            'email' => $request->email,
            'phone' => $request->phone,
            'find_us' => $request->find_us,
           
            'address' => $request->address,
            'country_id' => $request->country ?: null,
            'state_id' => $request->state ?: null,
            'city_id' => $request->city ?: null,
            'zip' => $request->zip,
        ]);

        \App\Models\Organization::create([
            'user_id' => $user->id,
            'organization_name' => $request->organization_name,
            'organization_size' => $request->organization_size,
        ]);

        // Assign default role in user_roles table
        $role = \App\Models\Role::where('name', 'user')->first();
        if ($role) {
            // Remove all roles and assign the new one (single role per user)
            $user->roles()->sync([$role->id]);
        }

        // Try to find a corresponding lead by this email and mark it as Registered
        try {
            $lead = \App\Models\Lead::where('email', $user->email)->first();
            if ($lead) {
                $lead->status = 'Registered';
                $lead->registered_at = now();
                $lead->save();
            }
        } catch (\Exception $e) {
            // Log but don't block registration success
            Log::warning('Failed to update lead status to Registered after user registration', ['email' => $user->email, 'error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }



    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        if ($validator->fails()  ) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Issue token via an internal kernel request to avoid self-HTTP deadlocks in dev server
        try {
            $tokenParams = [
                'grant_type'    => 'password',
                'client_id'     => config('passport.password_client_id'),
                'client_secret' => config('passport.password_client_secret'),
                'username'      => $request->email,
                'password'      => $request->password,
                'scope'         => '',
            ];
            
            // Send as form params so the framework populates the POST bag
            $server = [
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                'HTTP_ACCEPT'  => 'application/json',
            ];
            $tokenRequest = \Illuminate\Http\Request::create('/oauth/token', 'POST', $tokenParams, [], [], $server);
            $response = app()->handle($tokenRequest);
        } catch (\Throwable $e) {
            \Log::error('OAuth token dispatch failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Unable to generate token',
                'details' => 'Token dispatch failed.'
            ], 500);
        }

        if (method_exists($response, 'getStatusCode') ? $response->getStatusCode() >= 400 : false) {
            try {
                \Log::error('OAuth token error response', [
                    'status' => $response->getStatusCode(),
                    'body' => $response->getContent(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to log OAuth token error response', [
                    'email' => $request->input('email'),
                    'error' => $e->getMessage(),
                ]);
            }
            return response()->json([
                'error'   => 'Unable to generate token',
                'details' => json_decode($response->getContent(), true)
            ], $response->getStatusCode());
        }

        $tokenData = json_decode($response->getContent(), true);

        // Fix eager loading - remove the nested country relationship for now
        $user = User::with(['userDetails', 'roles'])->find($user->id);
        $role = $user->roles->first()->name ?? 'user';
        $details = $user->userDetails;

        $org = Organization::where('user_id', $user->id)->first();
        if ($org) {
            $org->last_contacted = now();
            $org->save();
        }

        Log::info('Login endpoint hit', [
            'email' => $request->input('email'),
            'time'  => now()->toDateTimeString(),
        ]);

        // Get country information safely
        $countryName = '';
        if ($details && $details->country_id) {
            $country = \App\Models\Country::find($details->country_id);
            $countryName = $country ? $country->name : '';
        }

        return response()->json([
            'message'       => 'Login successful',
            'access_token'  => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'expires_in'    => $tokenData['expires_in'],
        // Compatibility fields for older frontend code
        'token'         => $tokenData['access_token'],
        'expires_at'    => now()->addSeconds(intval($tokenData['expires_in'] ?? 0))->toISOString(),
            'user' => [
                'id'             => $user->id,
                'email'          => $user->email,
                'role'           => $role,
                'roles'          => $user->roles,
                'first_name'     => $user->first_name ?? '',
                'last_name'      => $user->last_name ?? '',
                'phone'          => $details->phone ?? '',
                'country'        => $countryName,
                'name'           => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'userDetails'    => $details,
                'organization_id'=> $org?->id,
            ],
        ]);
    }


    public function profile(Request $request)
    {
        $user = $request->user();
        // Eager load userDetails and roles
        $user = User::with(['userDetails', 'roles'])->find($user->id);
        $role = $user->roles->first()->name ?? 'user';
        $details = $user->userDetails;
        // Resolve organization id for the profile response
        $org = Organization::where('user_id', $user->id)->first();
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $role,
            'roles' => $user->roles,
                // Prefer names from users table
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'phone' => $details->phone ?? '',
                'country_id' => $details->country_id ?? null,
                'country' => $details->country ? $details->country->name : '',
                'name' => trim(($user->first_name ?? '') . (($user->last_name ?? '') ? ' ' . $user->last_name : '')),
                'userDetails' => $details,
                'organization_id' => $org ? $org->id : null,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        // Accept structured payloads from frontend. Possible shapes:
        // { user: { email }, user_details: { first_name, last_name, phone, country }, admin_email }
        $payload = $request->all();

        // Validate top-level user.email if provided
        $validator = Validator::make($payload, [
            // soft-deleted users will not be considered when checking uniqueness
            'user.email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id . ',id,deleted_at,NULL',
            'user_details.first_name' => 'sometimes|nullable|string|max:255',
            'user_details.last_name' => 'sometimes|nullable|string|max:255',
            'user_details.phone' => 'sometimes|regex:/^[6-9]\d{9}$/',
            'user_details.country' => 'sometimes|nullable|string',
            'admin_email' => 'sometimes|nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Update user table (email) and basic fields if provided
            if (isset($payload['user'])) {
                $u = $payload['user'];
                $shouldSaveUser = false;
                if (isset($u['email']) && $u['email'] !== $user->email) {
                    $user->email = $u['email'];
                    $shouldSaveUser = true;
                }
                // allow updating basic name/phone on users table as well
                if (isset($payload['user_details'])) {
                    $ud = $payload['user_details'];
                    if (isset($ud['first_name']) && $ud['first_name'] !== $user->first_name) {
                        $user->first_name = $ud['first_name'];
                        $shouldSaveUser = true;
                    }
                    if (isset($ud['last_name']) && $ud['last_name'] !== $user->last_name) {
                        $user->last_name = $ud['last_name'];
                        $shouldSaveUser = true;
                    }
                    // Do not store phone on users table anymore; keep phone in user_details
                }
                if ($shouldSaveUser) {
                    $user->save();
                }
            }

            // Update/insert user_details
            $detailsData = $payload['user_details'] ?? [];
            if (!empty($detailsData)) {
                $userDetail = UserDetail::firstOrNew(['user_id' => $user->id]);
                // Names are authoritative on users table; only persist other profile fields here
                if ((isset($detailsData['first_name'])) &&   ($detailsData['first_name'] !== $user->first_name)  ) {
                
                
                        $user->first_name = $detailsData['first_name'];
                        $user->save();
                    
                }
                if ((isset($detailsData['last_name']))  &&   ($detailsData['last_name'] !== $user->last_name) ) {
                  
                        $user->last_name = $detailsData['last_name'];
                        $user->save();
                    
                }
                if (isset($detailsData['phone'])) {
                    $userDetail->phone = $detailsData['phone'];
                }
                // Frontend sends country as string id; store in country_id if numeric-like
                if (isset($detailsData['country'])) {
                    $countryVal = $detailsData['country'];
                    // If frontend sent an object/array (e.g. { value, text, id, name }), try to extract a primitive
                    if (is_array($countryVal)) {
                        $countryVal = $countryVal['value'] ?? $countryVal['id'] ?? $countryVal['name'] ?? '';
                    } elseif (is_object($countryVal)) {
                        $countryVal = $countryVal->value ?? $countryVal->id ?? $countryVal->name ?? '';
                    }

                    if (is_numeric($countryVal)) {
                        $userDetail->country_id = intval($countryVal);
                    } elseif  (is_string($countryVal) && trim($countryVal) !== '') {
                        $countryVal = trim($countryVal);
                        // Try to resolve by name first (most common). Only attempt other columns if they exist.
                        $countryModel = \App\Models\Country::where('name', $countryVal)->first();
                        // Check optional columns safely
                        if (!$countryModel && \Illuminate\Support\Facades\Schema::hasColumn('countries', 'code')) {
                            $countryModel = \App\Models\Country::where('code', $countryVal)->first();
                        }
                        if (!$countryModel && \Illuminate\Support\Facades\Schema::hasColumn('countries', 'iso')) {
                            $countryModel = \App\Models\Country::where('iso', $countryVal)->first();
                        }
                        if ($countryModel) {
                            $userDetail->country_id = $countryModel->id;
                        } else {
                            // unknown string, null the country_id to avoid DB errors
                            $userDetail->country_id = null;
                        }
                    } else {
                        $userDetail->country_id = null;
                    }
                }
                // Email is authoritative on users table. If frontend provided an
                // email under user_details, use it to update the users.email.
                if ((isset($detailsData['email'])) &&  ($detailsData['email'] !== $user->email) ) {
                   
                        $user->email = $detailsData['email'];
                        $user->save();
                    
                }

                $userDetail->user_id = $user->id;
                $userDetail->save();
            }

            // organizations.admin_email column was removed in a migration. The
            // admin contact information is now sourced from the owning user's
            // `users` / `user_details` records. If an admin_email was provided
            // we'll log it so callers can optionally update their user record
            // through the dedicated user/profile endpoints.
            if (!empty($payload['admin_email'])) {
                \Log::info('updateProfile: received admin_email but organizations.admin_email has been removed; ignoring. admin_email=' . $payload['admin_email'], ['user_id' => $user->id]);
            }

            DB::commit();
            // reload user with details/roles
            $user = User::with(['userDetails', 'roles'])->find($user->id);
            return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update profile', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update profile', 'error' => $e->getMessage()], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }


    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ValidationRules::REQUIRED_EMAIL]);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => ValidationRules::REQUIRED_EMAIL,
            'password' => 'required|min:8|confirmed',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );
        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        // Only soft delete the user
        $user->delete();
        return response()->json(['message' => 'Account deleted successfully']);
    }

   
    public function user(Request $request)
    {
        $user = $request->user();
        $user = User::with(['userDetails', 'roles'])->find($user->id);
        $role = $user->roles->first()->name ?? 'user';
        $details = $user->userDetails;
 
        $org = Organization::where('user_id', $user->id)->first();
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $role,
            'roles' => $user->roles,
                // Prefer names from users table
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'phone' => $details->phone ?? '',
                'country' => $details->country ?? '',
                'name' => trim(($user->first_name ?? '') . (($user->last_name ?? '') ? ' ' . $user->last_name : '')),
                'userDetails' => $details,
        'organization_id' => $org ? $org->id : null,
        'organization_name' => $org ? $org->organization_name : null,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Check token status and return expiration info
     */
    public function tokenStatus(Request $request)
    {
        $user = $request->user();
        $token = $user->token();
        
        return response()->json([
            'valid' => true,
            'expires_at' => $token->expires_at,
            'expires_in_seconds' => \Carbon\Carbon::now()->diffInSeconds($token->expires_at, false),
            'user_id' => $user->id,
        ]);
    }

}