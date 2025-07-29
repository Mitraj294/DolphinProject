<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;

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
        $users = User::with('roles')->get();
   
        return response()->json(['users' => $users]);
    }

    // Update a user's role (pivot table)
    public function updateRole(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $roleName = $request->input('role');
            $name = $request->input('name');
            $email = $request->input('email');
            $updates = [];
            if ($name) $updates['name'] = $name;
            if ($email) $updates['email'] = $email;
            if (!empty($updates)) {
                $user->update($updates);
            }
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                \Log::error('Role not found in updateRole', ['roleName' => $roleName, 'userId' => $id]);
                return response()->json(['message' => 'Role not found'], 404);
            }
            // Remove all roles and assign the new one (single role per user)
            $user->roles()->sync([$role->id]);
            // Also update the 'role' column in users table to keep in sync
            $user->role = $role->name;
            $user->save();
            // Reload user with roles to return updated info
            $user = User::with('roles')->find($user->id);
            return response()->json([
                'message' => 'User updated',
                'user' => $user
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
            // Revoke existing tokens for the user to be impersonated (optional)
            if (method_exists($user, 'tokens')) {
                foreach ($user->tokens as $token) {
                    $token->delete();
                }
            }

            // Create a new token for the impersonated user
            $token = $user->createToken('ImpersonationToken')->accessToken;

            // Get the role of the impersonated user (first role or 'user')
            $impersonatedRole = $user->roles()->first()->name ?? 'user';

            return response()->json([
                'message' => 'Impersonation successful.',
                'user_id' => $user->id,
                'impersonated_token' => $token,
                'impersonated_role' => $impersonatedRole,
                'impersonated_name' => $user->name,
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
}
