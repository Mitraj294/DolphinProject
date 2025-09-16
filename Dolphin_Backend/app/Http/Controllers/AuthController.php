<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Lead;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register a new user, their details, and organization.
     
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|regex:/^[6-9]\d{9}$/',
            'find_us' => 'required|string',
            'organization_name' => 'required|string|min:8|max:500',
            'organization_size' => 'required|string',
            'address' => 'required|string|min:10|max:500',
            'country' => 'required|integer|exists:countries,id',
            'state' => 'required|integer|exists:states,id',
            'city' => 'required|integer|exists:cities,id',
            'zip' => 'required|regex:/^[1-9][0-9]{5}$/',
        ]);

        if ($validator->fails()) {
            
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = $this->createUserAndDetails($request->all());
            $this->updateLeadStatus($user->email);

            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            Log::error('User registration failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An unexpected error occurred during registration.'], 500);
        }
    }

    //Authenticate a user and issue a Passport token.
     
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $tokenResponse = $this->issueToken($request);
        if ($tokenResponse->getStatusCode() >= 400) {
            return $tokenResponse;
        }

        $tokenData = json_decode($tokenResponse->getContent(), true);
        $userPayload = $this->buildUserPayload($user);

        return response()->json([
            'message'       => 'Login successful',
            'access_token'  => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'],
            'expires_in'    => $tokenData['expires_in'],
            'user'          => $userPayload,
        ]);
    }

    //Get the authenticated user's profile information.
     
    public function profile(Request $request)
    {
        $userPayload = $this->buildUserPayload($request->user());
        return response()->json($userPayload);
    }
    
    //Update the authenticated user's profile.
     
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'user.email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id . ',id,deleted_at,NULL',
            'user_details.first_name' => 'sometimes|nullable|string|max:255',
            'user_details.last_name' => 'sometimes|nullable|string|max:255',
            'user_details.phone' => 'sometimes|nullable|regex:/^[6-9]\d{9}$/',

            'user_details.country' => 'sometimes|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $this->updateUserRecord($user, $request->input('user', []), $request->input('user_details', []));
            $this->updateUserDetailsRecord($user, $request->input('user_details', []));

            DB::commit();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $this->buildUserPayload($user->fresh())
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update profile', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update profile'], 500);
        }
    }


    //Change the authenticated user's password.
     
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }

    /**
     * Send a password reset link to the user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
    
    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
    
    /**
     * Soft delete the authenticated user's account.
     */
    public function deleteAccount(Request $request)
    {
        $request->user()->delete();
        return response()->json(['message' => 'Account deleted successfully']);
    }

    /**
     * Get the currently authenticated user. Alias for profile().
     */
    public function user(Request $request)
    {
        return $this->profile($request);
    }
    
    /**
     * Log the user out (Revoke the token).
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    /**
     * Check the validity and expiration of the current token.
     */
    public function tokenStatus(Request $request)
    {
        $token = $request->user()->token();
        return response()->json([
            'valid' => true,
            'expires_at' => $token->expires_at,
        ]);
    }

    // --- Private Helper Methods ---

    /**
     * Create a user and their associated details in a transaction.
     */
    private function createUserAndDetails(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
            ]);

            UserDetail::create([
                'user_id' => $user->id,
                'find_us' => $data['find_us'],
                'address' => $data['address'],
                'country_id' => $data['country'],
                'state_id' => $data['state'],
                'city_id' => $data['city'],
                'zip' => $data['zip'],
            ]);

            Organization::create([
                'user_id' => $user->id,
                'organization_name' => $data['organization_name'],
                'organization_size' => $data['organization_size'],
            ]);

            $user->roles()->attach(Role::where('name', 'user')->first());

            return $user;
        });
    }

    /**
     * Update the lead status for a newly registered user.
     */
    private function updateLeadStatus(string $email): void
    {
        try {
            $lead = Lead::where('email', $email)->first();
            if ($lead) {
                $lead->update(['status' => 'Registered', 'registered_at' => now()]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update lead status after registration', ['email' => $email, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Build the standard user payload for API responses.
     */
    private function buildUserPayload(User $user): array
    {
        $user->loadMissing(['userDetails.country', 'roles']);
        $org = Organization::where('user_id', $user->id)->first();

        return [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->roles->first()->name ?? 'user',
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->userDetails->phone ?? null,
            'country' => $user->userDetails->country->name ?? null,
            'country_id' => $user->userDetails->country_id ?? null,
            'organization_id' => $org?->id,
            'organization_name' => $org?->organization_name,
        ];
    }

    /**
     * Update the main user record fields.
     */
    private function updateUserRecord(User $user, array $userData, array $detailsData): void
    {
        $user->fill([
            'email' => $userData['email'] ?? $detailsData['email'] ?? $user->email,
            'first_name' => $detailsData['first_name'] ?? $user->first_name,
            'last_name' => $detailsData['last_name'] ?? $user->last_name,
        ]);

        if ($user->isDirty()) {
            $user->save();
        }
    }

    /**
     * Update the user_details record.
     */
    private function updateUserDetailsRecord(User $user, array $detailsData): void
    {
        if (empty($detailsData)) {
            return;
        }

        $userDetail = UserDetail::firstOrNew(['user_id' => $user->id]);
        $userDetail->phone = $detailsData['phone'] ?? $userDetail->phone;
        
        if (isset($detailsData['country'])) {
            $userDetail->country_id = $this->resolveCountryId($detailsData['country']);
        }

        if ($userDetail->isDirty()) {
            $userDetail->save();
        }
    }
    
    /**
     * Resolve a country ID from various possible input formats.
     */
    private function resolveCountryId($countryInput): ?int
    {
        if (is_numeric($countryInput)) {
            return (int) $countryInput;
        }

        if (is_string($countryInput) && !empty(trim($countryInput))) {
            $country = Country::where('name', trim($countryInput))->orWhere('code', trim($countryInput))->first();
            return $country?->id;
        }
        
        return null;
    }
    
    /**
     * Issue a Passport token via an internal request.
     */
    private function issueToken(Request $request)
    {
        $proxy = Request::create('/oauth/token', 'POST', [
            'grant_type' => 'password',
            'client_id' => config('passport.password_client_id'),
            'client_secret' => config('passport.password_client_secret'),
            'username' => $request->email,
            'password' => $request->password,
            'scope' => '',
        ]);

        $response = app()->handle($proxy);

        if ($response->getStatusCode() >= 400) {
            Log::error('OAuth token dispatch failed.', [
                'status' => $response->getStatusCode(),
                'response' => $response->getContent()
            ]);
        }

        return $response;
    }
}