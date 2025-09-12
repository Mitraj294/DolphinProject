<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\UserDetail;


class UserController extends Controller
{

    public function softDelete(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'User soft deleted successfully.']);
        } catch (\Exception $e) {
            \Log::error('Error soft deleting user', [
                'userId' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error soft deleting user', 'error' => $e->getMessage()], 500);
        }
    }
    // Return all users except superadmin
    public function index()
    {
        $users = User::with(['userDetails', 'roles'])->get();
        $users = $users->map(function ($user) {
            $details = $user->userDetails;
            $role = $user->roles->first()->name ?? 'user';
            $firstName = $user->first_name ?? ($details->first_name ?? '');
            $lastName = $user->last_name ?? ($details->last_name ?? '');
            return [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $role,
                'roles' => $user->roles,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $details->phone ?? '',
                'country' => $details->country ?? '',
                'name' => trim($firstName . ($lastName ? ' ' . $lastName : '')),
                'userDetails' => $details,
            ];
        });
        return response()->json(['users' => $users]);
    }

    // Add a new user
    public function store(Request $request)
    {
        // Debug: Log incoming request data
        \Log::info('UserController::store - Request data:', $request->all());
        
        try {
            $validatedData = $request->validate([
                'email' => 'required|email|unique:users',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|regex:/^[6-9]\d{9}$/',
                'role' => 'required|string|in:user,organizationadmin,dolphinadmin,superadmin,salesperson',
            ]);

            // Generate a random password and hash it
            $plainPassword = \Illuminate\Support\Str::random(12);
            $hashedPassword = \Illuminate\Support\Facades\Hash::make($plainPassword);

            $user = User::create([
                'email' => $validatedData['email'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'password' => $hashedPassword,
            ]);

            // Create user details if phone is provided
            if (!empty($validatedData['phone'])) {
                $user->userDetails()->create([
                    'phone' => $validatedData['phone'],
                ]);
            }

            // Assign role - check if role exists, create if it doesn't
            $role = Role::firstOrCreate(['name' => $validatedData['role']]);
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);

          

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user->load('roles', 'userDetails'),
                'password' => $plainPassword,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Debug: Log validation errors
            \Log::error('UserController::store - Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    

    // Update a user's role (pivot table)
    public function updateRole(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $roleName = $request->input('role');
            $email = $request->input('email');
            $first_name = $request->input('first_name');
            $last_name = $request->input('last_name');
            $updates = [];
            if ($email){ $updates['email'] = $email;}
            if (!empty($updates)) {
                $user->update($updates);
            }
            // Update names on users table (user_details no longer stores first/last name)
            $userUpdates = [];
            if ($first_name !== null && $first_name !== $user->first_name) { $userUpdates['first_name'] = $first_name; }
            if ($last_name !== null && $last_name !== $user->last_name) { $userUpdates['last_name'] = $last_name; }
            if (!empty($userUpdates)) {
                $user->update($userUpdates);
            }
            // Keep organization_name in user_details and organizations as before
            $details = $user->userDetails;
            if ($details) {
                $detailsUpdates = [];
                if ($request->has('organization_name')) {
                    if (Schema::hasColumn('user_details', 'organization_name')) {
                        $detailsUpdates['organization_name'] = $request->input('organization_name');
                    } else {
                        \Log::warning('[UserController@updateRole] skipping write to user_details.organization_name because column missing', ['user_id' => $user->id]);
                    }
                }
                if (!empty($detailsUpdates)) {
                    $details->update($detailsUpdates);
                }
                if (isset($detailsUpdates['organization_name'])) {
                    \App\Models\Organization::updateOrCreate(
                        ['user_id' => $user->id],
                        ['organization_name' => $detailsUpdates['organization_name']]
                    );
                }
            }
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                \Log::error('Role not found in updateRole', ['roleName' => $roleName, 'userId' => $id]);
                return response()->json(['message' => 'Role not found'], 404);
            }
            // Remove all roles and assign the new one (single role per user)
            $user->roles()->sync([$role->id]);

            // Reload user with roles and userDetails to return updated info
            $user = User::with(['roles', 'userDetails'])->find($user->id);
            $details = $user->userDetails;
            $firstName = $user->first_name ?? ($details->first_name ?? '');
            $lastName = $user->last_name ?? ($details->last_name ?? '');
            $role = $user->roles->first()->name ?? 'user';
            return response()->json([
                'id' => $user->id,
                'email' => $user->email,
                'role' => $role,
                'roles' => $user->roles,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $details->phone ?? '',
                'country' => $details->country ?? '',
                'name' => trim($firstName . ($lastName ? ' ' . $lastName : '')),
                'userDetails' => $details,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating user', [
                'userId' => $id,
                'request' => $request->all(),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error updating user', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = $request->user();
        // If authenticated as superadmin, allow delete

        if ($currentUser && $currentUser->roles()->where('name', 'superadmin')->exists()) {
            $user->delete();
            return response()->json(['message' => 'User soft deleted by superadmin']);
        }
        // If authenticated as the user themself, allow delete
        if ($currentUser && $currentUser->id == $user->id) {
            $user->delete();
            return response()->json(['message' => 'User soft deleted their own account']);
        }
        return response()->json(['message' => 'Forbidden'], 403);
    }
    /**
     * Impersonate a user by generating a new Passport token for them.
     * Only superadmins can do this.
  
     */
    public function impersonate(Request $request, User $user)
    {
        $currentUser = $request->user();
        // Policy/gate already checked by middleware, but double-check for safety
        if (!$currentUser || !$currentUser->roles()->where('name', 'superadmin')->exists()) {
            \Log::warning('Unauthorized impersonation attempt.', [
                'super_admin_id' => $currentUser ? $currentUser->id : null,
                'target_user_id' => $user->id,
            ]);
            return response()->json(['message' => 'Unauthorized to impersonate this user.'], 403);
        }

        try {
            // Do NOT revoke the user's existing tokens; issue a separate impersonation token
            // Create a new, scoped, short-lived token for the impersonated user
            $tokenResult = $user->createToken('ImpersonationToken', ['impersonate']);
            $accessToken = $tokenResult->accessToken;
            $tokenModel = $tokenResult->token;
            // short-lived impersonation
            $tokenModel->expires_at = now()->addHours(3);
            $tokenModel->save();

            // Get the role of the impersonated user (first role or 'user')
            $impersonatedRole = $user->roles()->first()->name ?? 'user';

            // Build clear name fields for the frontend (first/last and a combined name)
            $impersonatedFirst = $user->first_name ?? '';
            $impersonatedLast = $user->last_name ?? '';
            $combinedName = trim(($impersonatedFirst ? $impersonatedFirst : '') . ' ' . ($impersonatedLast ? $impersonatedLast : ''));
            if (empty($combinedName)) {
                // Fall back to legacy name or email if first/last are not present
                $combinedName = $user->name ?? $user->email ?? '';
            }

            return response()->json([
                'message' => 'Impersonation successful.',
                'user_id' => $user->id,
                'impersonated_token' => $accessToken,
                'impersonated_role' => $impersonatedRole,
                'impersonated_first_name' => $impersonatedFirst,
                'impersonated_last_name' => $impersonatedLast,
                'impersonated_name' => $combinedName,
                'expires_at' => $tokenModel->expires_at ? $tokenModel->expires_at->toISOString() : null,
                'expires_in' => \Carbon\Carbon::now()->diffInSeconds($tokenModel->expires_at, false),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error during impersonation: ' . $e->getMessage(), [
                'exception' => $e,
                'super_admin_id' => $currentUser ? $currentUser->id : null,
                'target_user_id' => $user->id,
            ]);
            return response()->json(['message' => 'Failed to impersonate user.', 'error' => $e->getMessage()], 500);
        }
    }


        // Relationship: Get user details
    public function userDetails(User $user)
    {
        return $user->hasOne(UserDetail::class, 'user_id');
    }

    // Relationship: Get all subscriptions for the user
    public function subscriptions(User $user)
    {
        return $user->hasMany(Subscription::class, 'user_id');
    }

    // Relationship: Get roles for the user (many-to-many)
    public function roles(User $user)
    {
        return $user->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')->withTimestamps();
    }

    // Check if the user has a specific role
    public static function hasRole(User $user, string $roleName): bool
    {
        return $user->roles->contains('name', $roleName);
    }

    // Check if the user is a superadmin
    public static function isSuperAdmin(User $user): bool
    {
       return $user->role === 'super_admin';
    }
}
