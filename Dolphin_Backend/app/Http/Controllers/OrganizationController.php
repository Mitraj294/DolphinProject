<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the organizations.
     */
    public function index(Request $request)
    {
        $user = $request->user()->load('roles');
        $query = Organization::with([
            'user.roles',
            'user.userDetails.country',
            'user.userDetails.state',
            'user.userDetails.city',
            'salesPerson', 
            'activeSubscription'
        ]);

        if ($user->hasRole('organizationadmin')) {
            $query->where('user_id', $user->id);
        } elseif (!$user->hasRole('superadmin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $organizations = $query->get()->map(fn($org) => $this->formatOrganizationPayload($org));

        return response()->json($organizations);
    }

    /**
     * Display the specified organization.
     */
    public function show(Request $request, Organization $organization)
    {
        $user = $request->user();
        if (!($user->hasRole('superadmin') || ($user->hasRole('organizationadmin') && $organization->user_id === $user->id))) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $organization->load([
            'user.roles',
            'user.userDetails.country',
            'user.userDetails.state',
            'user.userDetails.city',
            'salesPerson', // Eager load the salesperson relationship
            'activeSubscription'
        ]);
        
        return response()->json($this->formatOrganizationPayload($organization));
    }

    /**
     * Store a newly created organization in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_size' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'sales_person_id' => 'nullable|integer|exists:users,id',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'last_contacted' => 'nullable|date',
        ]);

        $organization = Organization::create($validated);

        return response()->json($this->formatOrganizationPayload($organization), 201);
    }

    /**
     * Update the specified organization in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        // Using a policy for authorization is recommended here
        // $this->authorize('update', $organization);

        $validated = $request->validate([
            'organization_name' => 'sometimes|string|max:255',
            'organization_size' => 'sometimes|string',
            'sales_person_id' => 'nullable|integer|exists:users,id',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'last_contacted' => 'nullable|date',
            'admin_email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($organization->user_id)],
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'admin_phone' => 'sometimes|string|regex:/^[6-9]\d{9}$/',
            'address' => 'sometimes|string',
            'country_id' => 'sometimes|integer|exists:countries,id',
            'state_id' => 'sometimes|integer|exists:states,id',
            'city_id' => 'sometimes|integer|exists:cities,id',
            'zip' => 'sometimes|string|regex:/^[1-9][0-9]{5}$/',
        ]);

        try {
            DB::transaction(function () use ($organization, $validated) {
                $organization->update($validated);
                if ($organization->user) {
                    $organization->user->update($validated);
                    $organization->user->userDetails()->updateOrCreate(['user_id' => $organization->user_id], $validated);
                }
            });

            return response()->json($this->formatOrganizationPayload($organization->fresh()));
        } catch (\Exception $e) {
            Log::error('Failed to update organization', ['id' => $organization->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update organization.'], 500);
        }
    }

    /**
     * Remove the specified organization from storage.
     */
    public function destroy(Organization $organization)
    {
        // Using a policy for authorization is recommended here
        // $this->authorize('delete', $organization);
        
        $organization->delete();
        
        return response()->json(null, 204);
    }

   
    private function formatOrganizationPayload(Organization $org): array
    {
        $user = $org->user;
        $details = $user?->userDetails;
        $subscription = $org->activeSubscription;
        
        // This is the ideal way to get the full name, assuming an accessor in your User model
        $salesPersonName = $org->salesPerson?->full_name;

        // If you don't have a full_name accessor, this is the direct fix:
        if ($org->salesPerson) {
             $salesPersonName = trim($org->salesPerson->first_name . ' ' . $org->salesPerson->last_name);
        } else {
            $salesPersonName = null;
        }

        return [
            'id' => $org->id,
            'organization_name' => $org->organization_name,
            'organization_size' => $org->organization_size,
            'main_contact' => $user?->first_name . ' ' . $user?->last_name,
            'admin_email' => $user?->email,
            'admin_phone' => $details?->phone,
            'address' => $details?->address,
            'city' => $details?->city?->name,
            'state' => $details?->state?->name,
            'country' => $details?->country?->name,
            'zip' => $details?->zip,
            'contract_start' => $subscription?->subscription_start?->toDateString() ?? $org->contract_start,
            'contract_end' => $subscription?->subscription_end?->toDateString() ?? $org->contract_end,
            'source' => $details?->find_us,
            'last_contacted' => $org->last_contacted,
            'sales_person_id' => $org->sales_person_id,
            'sales_person' => $salesPersonName,
            'certified_staff' => $org->certified_staff,
        ];
    }
}