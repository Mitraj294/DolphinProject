<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use App\Notifications\NewUserInvitation;

class UserController extends Controller
{
    //Display a listing of all users.

    public function index()
    {
        $users = User::with(['userDetails.country', 'roles'])->get()->map(function ($user) {
            return $this->formatUserPayload($user);
        });

        return response()->json(['users' => $users]);
    }

    //Store a new user in the database.

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email|unique:users,email',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|regex:/^[6-9]\d{9}$/',
                'country_id' => 'required|integer|exists:countries,id',
                'role' => ['required', Rule::in(['user', 'organizationadmin', 'dolphinadmin', 'superadmin', 'salesperson'])],
                'organization_name' => 'nullable|string|max:255|required_if:role,organizationadmin',
                'organization_size' => 'nullable|string|max:255|required_if:role,organizationadmin',
            ]);

            $plainPassword = Str::random(12);
            $validatedData['password'] = Hash::make($plainPassword);

            $user = $this->createUserWithRelations($validatedData);

            // Generate a password reset token so the user can set their own password securely.
            try {
                // Help static analyzers (intelephense) infer the broker type so createToken() is recognized
                /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
                $broker = Password::broker();
                $token = $broker->createToken($user);
                // Use named web route to redirect to frontend reset page (web.php redirects to frontend)
                $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
            } catch (\Exception $e) {
                // Fallback: no reset link available
                $resetUrl = null;
            }

            // Send email notification with the temporary password and reset link (if available)
            try {
                $user->notify(new NewUserInvitation($plainPassword, $resetUrl));
            } catch (\Exception $e) {
                Log::warning('Failed to send new user invitation email', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }

            return response()->json([
                'message' => 'User created successfully',
                'user' => $this->formatUserPayload($user->load('roles', 'userDetails')),
                'password' => $plainPassword,
            ], 201);
        } catch (ValidationException $e) {
            Log::error('User creation validation failed:', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating user:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred while creating the user.'], 500);
        }
    }

    //Update the specified user's role and basic information.

    public function updateRole(Request $request, User $user)
    {
        // Build rules dynamically so we only apply the unique rule when the
        // provided email is different from the current one. This avoids a
        // validation failure when the client resubmits the same email.
        $rules = [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'role' => ['required', 'string', Rule::in(['user', 'organizationadmin', 'dolphinadmin', 'superadmin', 'salesperson'])],
            'organization_name' => 'nullable|string|max:255',
        ];

        if ($request->has('email')) {
            $incomingEmail = $request->input('email');
            if ($incomingEmail && $incomingEmail !== $user->email) {
                $rules['email'] = ['sometimes', 'email', Rule::unique('users', 'email')];
            } else {
                // If email is the same as current, only validate format.
                $rules['email'] = ['sometimes', 'email'];
            }
        }

        $validatedData = $request->validate($rules);

        try {
            DB::transaction(function () use ($user, $validatedData, $request) {
                $user->update($validatedData);

                $role = Role::where('name', $validatedData['role'])->firstOrFail();
                $user->roles()->sync([$role->id]);

                if ($request->has('organization_name')) {
                    Organization::updateOrCreate(
                        ['user_id' => $user->id],
                        ['organization_name' => $validatedData['organization_name']]
                    );
                }
            });

            return response()->json($this->formatUserPayload($user->fresh()));
        } catch (\Exception $e) {
            Log::error('Error updating user role', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred while updating the user.'], 500);
        }
    }

    //Soft delete a user.
    public function softDelete(User $user)
    {
        try {
            $user->delete();
            // Ensure deleted_at is populated; sometimes DB-level issues can prevent Eloquent from setting it
            if (is_null($user->deleted_at)) {
                $user->deleted_at = now();
                $user->save();
            }
            return response()->json(['message' => 'User soft deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Error soft deleting user', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error soft deleting user'], 500);
        }
    }

    // Impersonate another user (superadmin only)
    public function impersonate(Request $request, User $user)
    {
        // A policy should handle this authorization check
        if ($request->user()->cannot('impersonate', $user)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $tokenResult = $user->createToken('ImpersonationToken', ['impersonate']);
            $tokenResult->token->expires_at = now()->addHours(1);
            $tokenResult->token->save();

            return response()->json([
                'message' => "Successfully impersonating {$user->first_name}.",
                'impersonated_token' => $tokenResult->accessToken,
                'user' => $this->formatUserPayload($user),
                'expires_at' => $tokenResult->token->expires_at->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Impersonation failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to impersonate user.'], 500);
        }
    }

    // Private Helper Methods

    // Create a user and their related models within a database transaction.

    private function createUserWithRelations(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);

            $user->userDetails()->create([
                'phone' => $data['phone'],
                'country_id' => $data['country_id'],
            ]);

            if ($data['role'] === 'organizationadmin') {
                $org = Organization::create([
                    'user_id' => $user->id,
                    'organization_name' => $data['organization_name'],
                    'organization_size' => $data['organization_size'],
                    'country_id' => $data['country_id'],
                ]);

                // Persist organization_id back to user
                $user->organization_id = $org->id;
                $user->save();
            }

            $role = Role::where('name', $data['role'])->first();
            $user->roles()->attach($role);

            return $user;
        });
    }

    //Format the user data into a consistent payload for API responses.

    private function formatUserPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'name' => $user->full_name,
            'role' => $user->roles->first()->name ?? 'user',
            'phone' => $user->userDetails->phone ?? null,
            'country' => $user->userDetails->country->name ?? null,
        ];
    }
}
