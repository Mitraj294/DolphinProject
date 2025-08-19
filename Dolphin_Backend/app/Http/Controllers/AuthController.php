<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:password',
            'phone' => 'nullable|string|max:32',
            'find_us' => 'nullable|string|max:255',
            'org_name' => 'nullable|string|max:255',
            'org_size' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:32',
        ]);

        if ($validator->fails()) {
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

        // Save all registration details to user_details table
        \App\Models\UserDetail::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'find_us' => $request->find_us,
            'org_name' => $request->org_name,
            'org_size' => $request->org_size,
            'address' => $request->address,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zip' => $request->zip,
        ]);

        // Assign default role in user_roles table
        $role = \App\Models\Role::where('name', 'user')->first();
        if ($role) {
            // Remove all roles and assign the new one (single role per user)
            $user->roles()->sync([$role->id]);
        }

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Issue Passport access token using password grant
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->accessToken;
        $expiresAt = $tokenResult->token->expires_at;

        // Eager load userDetails and roles for frontend
    $user = User::with(['userDetails.country', 'roles'])->find($user->id);
        // For convenience, also provide top-level role and userDetails fields
        $role = $user->roles->first()->name ?? 'user';
    $details = $user->userDetails;
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'expires_at' => $expiresAt,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $role,
                'roles' => $user->roles,
                'first_name' => $details->first_name ?? '',
                'last_name' => $details->last_name ?? '',
                'phone' => $details->phone ?? '',
                'country' => $details->country ?? '',
                'name' => trim(($details->first_name ?? '') . (($details->last_name ?? '') ? ' ' . $details->last_name : '')),
                'userDetails' => $details,
            ],

        ]);
        \Log::info('Login endpoint hit', [
            'email' => $request->input('email'),
            'time' => now()->toDateTimeString()
        ]);
        return response()->json([
            // ...existing code...
        ], 200);
    }


        public function profile(Request $request)
    {
        $user = $request->user();
        // Eager load userDetails and roles
        $user = User::with(['userDetails', 'roles'])->find($user->id);
        $role = $user->roles->first()->name ?? 'user';
        $details = $user->userDetails;
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $role,
            'roles' => $user->roles,
            'first_name' => $details->first_name ?? '',
            'last_name' => $details->last_name ?? '',
            'phone' => $details->phone ?? '',
            'country_id' => $details->country_id ?? null,
            'country' => $details->country ? $details->country->name : '',
            'name' => trim(($details->first_name ?? '') . (($details->last_name ?? '') ? ' ' . $details->last_name : '')),
            'userDetails' => $details,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'country' => 'nullable|string',
        ]);
        $user->update($data);
        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
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
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
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

        /**
     * Return the authenticated user's info with role from user_roles table
     */
    public function user(Request $request)
    {
        $user = $request->user();
        $user = User::with(['userDetails', 'roles'])->find($user->id);
        $role = $user->roles->first()->name ?? 'user';
        $details = $user->userDetails;
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $role,
            'roles' => $user->roles,
            'first_name' => $details->first_name ?? '',
            'last_name' => $details->last_name ?? '',
            'phone' => $details->phone ?? '',
            'country' => $details->country ?? '',
            'name' => trim(($details->first_name ?? '') . (($details->last_name ?? '') ? ' ' . $details->last_name : '')),
            'userDetails' => $details,
        ]);
    }
   
}