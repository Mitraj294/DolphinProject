<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        if ($role->name === 'superadmin') {
            $orgs = Organization::with(['user', 'user.userDetails', 'user.subscriptions' => function($q) {
                $q->where('status', 'active')->orderByDesc('id');
            }])->get();
        } elseif ($role->name === 'organizationadmin') {
            $orgs = Organization::with(['user', 'user.userDetails', 'user.subscriptions' => function($q) {
                $q->where('status', 'active')->orderByDesc('id');
            }])->where('user_id', $user->id)->get();
        } else {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        $result = $orgs->map(function ($org) {
            $subscription = $org->user && $org->user->subscriptions ? $org->user->subscriptions->first() : null;
            $user = $org->user;
            $details = $user && $user->userDetails ? $user->userDetails : null;
            $mainContact = $org->main_contact ?? null;
            if (!$mainContact && $user) {
                $mainContact = trim((($user->first_name ?? '') . ' ' . ($user->last_name ?? '')));
                if (trim($mainContact) === '') {
                    $mainContact = $details->org_name ?? $user->email ?? '';
                }
            }
            // derive location names from ids when available so frontend can display them
            $countryId = $org->country_id ?? $details->country_id ?? null;
            $stateId = $org->state_id ?? $details->state_id ?? null;
            $cityId = $org->city_id ?? $details->city_id ?? null;

            $countryName = null;
            $stateName = null;
            $cityName = null;

            if ($countryId) {
                $c = Country::find($countryId);
                $countryName = $c ? $c->name : null;
            }
            if ($stateId) {
                $s = State::find($stateId);
                $stateName = $s ? $s->name : null;
            }
            if ($cityId) {
                $ci = City::find($cityId);
                $cityName = $ci ? $ci->name : null;
            }

            return [
                'id' => $org->id,
                'org_name' => $org->org_name,
                'org_size' => $details->org_size ?? $org->org_size ?? null,
                'find_us' => $details->find_us ?? $org->find_us ?? null,
                'main_contact' => $mainContact,
                'contract_start' => $subscription ? $subscription->subscription_start : $org->contract_start,
                'contract_end' => $subscription ? $subscription->subscription_end : $org->contract_end,
                'last_contacted' => $org->last_contacted,
                'address' => $org->address1 ?? $details->address ?? null,
                // string fields (legacy) - may be null if IDs are used
                'city' => $org->city ?? $details->city ?? null,
                'state' => $org->state ?? $details->state ?? null,
                'zip' => $org->zip ?? $details->zip ?? null,
                'country_id' => $org->country_id ?? $details->country_id ?? null,
                // explicit lookup names from location tables (if available)
                'country' => $countryName ?? ($org->country ?? $details->country ?? null),
                'state_name' => $stateName ?? ($org->state ?? $details->state ?? null),
                'city_name' => $cityName ?? ($org->city ?? $details->city ?? null),
                'admin_email' => $org->admin_email ?? ($user->email ?? null),
                'admin_phone' => $org->admin_phone ?? ($details->phone ?? null),
                'sales_person' => $org->sales_person,
                'certified_staff' => $org->certified_staff,
            ];
        });
        return response()->json($result);
    }

    public function show($id)
    {
        $org = Organization::with(['user', 'user.userDetails', 'user.subscriptions' => function($q) {
            $q->where('status', 'active')->orderByDesc('id');
        }])->findOrFail($id);

        $subscription = $org->user && $org->user->subscriptions ? $org->user->subscriptions->first() : null;
        $user = $org->user;
        $details = $user && $user->userDetails ? $user->userDetails : null;
        $mainContact = $org->main_contact ?? null;
        if (!$mainContact && $user) {
            $mainContact = trim((($user->first_name ?? '') . ' ' . ($user->last_name ?? '')));
            if (trim($mainContact) === '') {
                $mainContact = $details->org_name ?? $user->email ?? '';
            }
        }

        $countryId = $org->country_id ?? $details->country_id ?? null;
        $stateId = $org->state_id ?? $details->state_id ?? null;
        $cityId = $org->city_id ?? $details->city_id ?? null;

        $countryName = null;
        $stateName = null;
        $cityName = null;

        if ($countryId) {
            $c = Country::find($countryId);
            $countryName = $c ? $c->name : null;
        }
        if ($stateId) {
            $s = State::find($stateId);
            $stateName = $s ? $s->name : null;
        }
        if ($cityId) {
            $ci = City::find($cityId);
            $cityName = $ci ? $ci->name : null;
        }

        return response()->json([
            'id' => $org->id,
            'org_name' => $org->org_name,
            'org_size' => $details->org_size ?? $org->org_size ?? null,
            'find_us' => $details->find_us ?? $org->find_us ?? null,
            'main_contact' => $mainContact,
            'contract_start' => $subscription ? $subscription->subscription_start : $org->contract_start,
            'contract_end' => $subscription ? $subscription->subscription_end : $org->contract_end,
            'last_contacted' => $org->last_contacted,
            'address' => $org->address1 ?? $details->address ?? null,
            'city' => $org->city ?? $details->city ?? null,
            'state' => $org->state ?? $details->state ?? null,
            'zip' => $org->zip ?? $details->zip ?? null,
            'country_id' => $org->country_id ?? $details->country_id ?? null,
            'country' => $countryName ?? ($org->country ?? $details->country ?? null),
            'state_name' => $stateName ?? ($org->state ?? $details->state ?? null),
            'city_name' => $cityName ?? ($org->city ?? $details->city ?? null),
            'admin_email' => $org->admin_email ?? ($user->email ?? null),
            'admin_phone' => $org->admin_phone ?? ($details->phone ?? null),
            'sales_person' => $org->sales_person,
            'certified_staff' => $org->certified_staff,
            // include nested user and details for frontend that relies on them
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        // Only accept fields that remain on organizations table. Other
        // organization-looking fields should be set on the owning user's
        // user_details or users table via the user endpoints.
        $validated = $request->validate([
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'sales_person' => 'nullable|string',
            'last_contacted' => 'nullable|date',
            'certified_staff' => 'nullable|integer',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);
        $org = Organization::create($validated);
        return response()->json($org, 201);
    }

    public function update(Request $request, $id)
    {
        $org = Organization::with(['user', 'user.userDetails'])->findOrFail($id);

        $validated = $request->validate([
            // organization table fields
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'sales_person' => 'nullable|string',
            'last_contacted' => 'nullable|date',
            'certified_staff' => 'nullable|integer',
            // user / user_details fields (frontend sends these)
            'first_name' => 'sometimes|nullable|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'admin_email' => 'sometimes|nullable|email|max:255',
            'admin_phone' => 'sometimes|nullable|string|max:64',
            'org_name' => 'sometimes|nullable|string|max:255',
            'org_size' => 'sometimes|nullable|string|max:255',
            'source' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string|max:1024',
            'city_id' => 'sometimes|nullable|integer|exists:cities,id',
            'state_id' => 'sometimes|nullable|integer|exists:states,id',
            'country_id' => 'sometimes|nullable|integer|exists:countries,id',
            'zip' => 'sometimes|nullable|string|max:64',
        ]);

        DB::beginTransaction();
        try {
            // Update allowed organization columns
            $orgFields = array_filter($validated, function ($v, $k) {
                return in_array($k, ['contract_start', 'contract_end', 'sales_person', 'last_contacted', 'certified_staff']);
            }, ARRAY_FILTER_USE_BOTH);
            if (!empty($orgFields)) {
                $org->update($orgFields);
            }

            // Update related user and user_details where applicable
            $user = $org->user;
            if ($user) {
                $userUpdated = false;
                if (array_key_exists('admin_email', $validated) && $validated['admin_email'] !== $user->email) {
                    $user->email = $validated['admin_email'];
                    $userUpdated = true;
                }
                if (array_key_exists('first_name', $validated) && $validated['first_name'] !== $user->first_name) {
                    $user->first_name = $validated['first_name'];
                    $userUpdated = true;
                }
                if (array_key_exists('last_name', $validated) && $validated['last_name'] !== $user->last_name) {
                    $user->last_name = $validated['last_name'];
                    $userUpdated = true;
                }
                if ($userUpdated) $user->save();

                $details = $user->userDetails;
                if (!$details) {
                    $details = new UserDetail();
                    $details->user_id = $user->id;
                }
                $detailUpdated = false;
                if (array_key_exists('admin_phone', $validated)) { $details->phone = $validated['admin_phone']; $detailUpdated = true; }
                if (array_key_exists('org_name', $validated)) { $details->org_name = $validated['org_name']; $detailUpdated = true; }
                if (array_key_exists('org_size', $validated)) { $details->org_size = $validated['org_size']; $detailUpdated = true; }
                if (array_key_exists('source', $validated)) { $details->find_us = $validated['source']; $detailUpdated = true; }
                if (array_key_exists('address', $validated)) { $details->address = $validated['address']; $detailUpdated = true; }
                if (array_key_exists('country_id', $validated)) { $details->country_id = $validated['country_id']; $detailUpdated = true; }
                if (array_key_exists('state_id', $validated)) { $details->state_id = $validated['state_id']; $detailUpdated = true; }
                if (array_key_exists('city_id', $validated)) { $details->city_id = $validated['city_id']; $detailUpdated = true; }
                if (array_key_exists('zip', $validated)) { $details->zip = $validated['zip']; $detailUpdated = true; }
                if ($detailUpdated) $details->save();
            }

            DB::commit();

            // Return refreshed organization for frontend
            $org = Organization::with(['user', 'user.userDetails', 'user.subscriptions' => function($q) {
                $q->where('status', 'active')->orderByDesc('id');
            }])->findOrFail($id);
            return response()->json($org);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update organization', ['id' => $id, 'error' => $e->getMessage(), 'request' => $request->all()]);
            return response()->json(['message' => 'Failed to update organization', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $org = Organization::findOrFail($id);
        $org->delete();
        return response()->json(['message' => 'Organization deleted']);
    }
}
