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
use Illuminate\Support\Facades\Schema;
class ValidationRules
{
    public const REQUIRED_INTEGER = 'required|integer';
    public const REQUIRED_STRING = 'required|string';
    public const REQUIRED_EMAIL = 'required|email';
    public const OPTIONAL_INTEGER = 'nullable|integer';
    public const REQUIRED_BOOLEAN = 'required|boolean';
    public const REQUIRED_DATE = 'required|date';
    public const NULLABLE_STRING = 'nullable|string|max:255';
    public const NULLABLE_DATE = 'nullable|date';

}

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
        // prefer organization-level main_contact, then org name, then user/user_details
        $mainContact = $org->main_contact ?? $org->organization_name ?? null;
            if (!$mainContact && $user) {
                $mainContact = trim((($user->first_name ?? '') . ' ' . ($user->last_name ?? '')));
                if (trim($mainContact) === '') {
            $mainContact = $org->organization_name ?? ($details->organization_name ?? $user->email ?? '');
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
                // If find() returns a collection, get the first item
                if ($c instanceof \Illuminate\Database\Eloquent\Collection) {
                    $c = $c->first();
                }
                $countryName = $c ? $c->name : null;
            }
            if ($stateId) {
                $s = State::find($stateId);
                // If find() returns a collection, get the first item
                if ($s instanceof \Illuminate\Database\Eloquent\Collection) {
                    $s = $s->first();
                }
                $stateName = $s ? $s->name : null;
            }
            if ($cityId) {
                $ci = City::find($cityId);
                // If find() returns a collection, get the first item
                if ($ci instanceof \Illuminate\Database\Eloquent\Collection) {
                    $ci = $ci->first();
                }
                $cityName = $ci ? $ci->name : null;
            }

            // resolve salesperson display name if available
            $salesPersonUser = null;
            if (!empty($org->sales_person_id)) {
                $salesPersonUser = User::find($org->sales_person_id);
            }

            $salesPersonDisplay = null;
            if ($salesPersonUser) {
                $salesPersonDisplay = trim((($salesPersonUser->first_name ?? '') . ' ' . ($salesPersonUser->last_name ?? '')));
                if ($salesPersonDisplay === '') {
                    $salesPersonDisplay = $salesPersonUser->email ?? null;
                }
            }

            return [
                'id' => $org->id,
                // prefer the explicit organizations table values; fall back to user_details
                'organization_name' => $org->organization_name ?? ($details->organization_name ?? null),
                'organization_size' => $org->organization_size ?? ($details->organization_size ?? null),
                'find_us' => $org->find_us ?? ($details->find_us ?? null),
                'main_contact' => $mainContact,
                'contract_start' => $subscription ? $subscription->subscription_start : $org->contract_start,
                'contract_end' => $subscription ? $subscription->subscription_end : $org->contract_end,
                'last_contacted' => $org->last_contacted,
                'address' => $org->address1 ?? ($details->address ?? null),
            
                'zip' => $org->zip ?? $details->zip ?? null,
                'country' => $org->country ?? $details->country ?? null,
                // explicit lookup names from location tables (if available)
                'country' => $countryName ?? ($org->country ?? $details->country ?? null),
                'state_name' => $stateName ?? ($org->state ?? $details->state ?? null),
                'city_name' => $cityName ?? ($org->city ?? $details->city ?? null),
                'admin_email' => $org->admin_email ?? ($user->email ?? null),
                'admin_phone' => $org->admin_phone ?? ($details->phone ?? null),
                
                'sales_person_id' => $org->sales_person_id ?? null,
                'sales_person' => $salesPersonDisplay,
                // compute certified staff by scanning groups -> members -> memberRoles
                'certified_staff' => (function() use ($org) {
                    try {
                        $groups = \App\Models\Group::where('organization_id', $org->id)->with(['members.memberRoles'])->get();
                        $qualified = [];
                        $wanted = ['owner', 'manager', 'ceo'];
                        foreach ($groups as $g) {
                            foreach ($g->members as $m) {
                                // ensure we only count each member once
                                if (isset($qualified[$m->id])) {
                                    continue;
                                }
                                $roles = $m->memberRoles->pluck('name')->map(function($n){ return strtolower(trim($n)); })->toArray();
                                foreach ($roles as $r) {
                                    if (in_array($r, $wanted, true)) {
                                        $qualified[$m->id] = true;
                                        break;
                                    }
                                }
                            }
                        }
                        return count($qualified);
                    } catch (\Exception $e) {
                        // on error, fall back to stored value if present
                        return $org->certified_staff ?? 0;
                    }
                })(),
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
        $mainContact = $org->main_contact ?? $org->organization_name ?? null;
        if (!$mainContact && $user) {
            $mainContact = trim((($user->first_name ?? '') . ' ' . ($user->last_name ?? '')));
            if (trim($mainContact) === '') {
                $mainContact = $org->organization_name ?? ($details->organization_name ?? $user->email ?? '');
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
            // If find() returns a collection, get the first item
            if ($c instanceof \Illuminate\Database\Eloquent\Collection) {
                $c = $c->first();
            }
            $countryName = $c ? $c->name : null;
        }
        if ($stateId) {
            $s = State::find($stateId);
            // If find() returns a collection, get the first item
            if ($s instanceof \Illuminate\Database\Eloquent\Collection) {
                $s = $s->first();
            }
            $stateName = $s ? $s->name : null;
        }
        if ($cityId) {
            $ci = City::find($cityId);
            // If find() returns a collection, get the first item
            if ($ci instanceof \Illuminate\Database\Eloquent\Collection) {
                $ci = $ci->first();
            }
            $cityName = $ci ? $ci->name : null;
        }

        return response()->json([
            'id' => $org->id,
            'organization_name' => $org->organization_name ?? ($details->organization_name ?? null),
            'organization_size' => $org->organization_size ?? ($details->organization_size ?? null),
            'find_us' => $org->find_us ?? ($details->find_us ?? null),
            'main_contact' => $mainContact,
            'contract_start' => $subscription ? $subscription->subscription_start : $org->contract_start,
            'contract_end' => $subscription ? $subscription->subscription_end : $org->contract_end,
            'last_contacted' => $org->last_contacted,
            'address' => $org->address1 ?? ($details->address ?? null),
          
            'zip' => $org->zip ?? $details->zip ?? null,
            'country' => $org->country ?? $details->country ?? null,
            'country' => $countryName ?? ($org->country ?? $details->country ?? null),
            'state_name' => $stateName ?? ($org->state ?? $details->state ?? null),
            'city_name' => $cityName ?? ($org->city ?? $details->city ?? null),
            'admin_email' => $org->admin_email ?? ($user->email ?? null),
            'admin_phone' => $org->admin_phone ?? ($details->phone ?? null),
    
            'sales_person_id' => $org->sales_person_id ?? null,
            'sales_person' => (function() use ($org) {
                if (empty($org->sales_person_id)) { return null; }
                $u = User::find($org->sales_person_id);
                if (!$u) { return null; }
                $name = trim((($u->first_name ?? '') . ' ' . ($u->last_name ?? '')));
                return $name !== '' ? $name : ($u->email ?? null);
            })(),
            // compute certified staff same as index: count unique group members whose roles include owner/manager/ceo
            'certified_staff' => (function() use ($org) {
                try {
                    $groups = \App\Models\Group::where('organization_id', $org->id)->with(['members.memberRoles'])->get();
                    $qualified = [];
                    $wanted = ['owner', 'manager', 'ceo'];
                    foreach ($groups as $g) {
                        foreach ($g->members as $m) {
                            if (isset($qualified[$m->id])) {
                                continue;
                            }
                            $roles = $m->memberRoles->pluck('name')->map(function($n){ return strtolower(trim($n)); })->toArray();
                            foreach ($roles as $r) {
                                if (in_array($r, $wanted, true)) {
                                    $qualified[$m->id] = true;
                                    break;
                                }
                            }
                        }
                    }
                    return count($qualified);
                } catch (\Exception $e) {
                    return $org->certified_staff ?? 0;
                }
            })(),
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

            'sales_person_id' => ValidationRules::REQUIRED_INTEGER . '|exists:users,id',
            'last_contacted' => ValidationRules::NULLABLE_DATE,
            'certified_staff' => ValidationRules::REQUIRED_INTEGER,
            'user_id' => ValidationRules::OPTIONAL_INTEGER . '|exists:users,id',
            'organization_name' => ValidationRules::REQUIRED_STRING,
            'organization_size' => ValidationRules::REQUIRED_STRING,
        ]);
        // Create organization record
            $orgFields = array_filter($validated, function ($k) {
                return in_array($k, ['contract_start', 'contract_end', 'sales_person_id', 'last_contacted', 'certified_staff', 'user_id', 'organization_name', 'organization_size']);
        }, ARRAY_FILTER_USE_KEY);
        $org = Organization::create($orgFields);

        // If organization_name was provided and a user is attached, also populate user_details.organization_name
    if (!empty($org->user_id) && (!empty($orgFields['organization_name']) || !empty($orgFields['organization_size']))) {
            try {
                $user = User::find($org->user_id);
                if ($user) {
                        $details = $user->userDetails ?: new \App\Models\UserDetail();
                        $details->user_id = $user->id;
                        // Only set columns if the user_details table still contains them.
                        if (Schema::hasColumn('user_details', 'organization_name') && !empty($orgFields['organization_name'])) {
                            $details->organization_name = $orgFields['organization_name'];
                        } elseif (!Schema::hasColumn('user_details', 'organization_name') && !empty($orgFields['organization_name'])) {
                            \Log::warning('[Organization@store] user_details.organization_name column missing; skipping backfill', ['org_id' => $org->id]);
                        }
                        if (Schema::hasColumn('user_details', 'organization_size') && !empty($orgFields['organization_size'])) {
                            $details->organization_size = $orgFields['organization_size'];
                        } elseif (!Schema::hasColumn('user_details', 'organization_size') && !empty($orgFields['organization_size'])) {
                            \Log::warning('[Organization@store] user_details.organization_size column missing; skipping backfill', ['org_id' => $org->id]);
                        }
                        $details->save();
                }
            } catch (\Exception $e) {
                \Log::warning('[Organization@store] failed to backfill user_details organization_name', ['org_id' => $org->id, 'error' => $e->getMessage()]);
            }
        }
        return response()->json($org, 201);
    }

    public function update(Request $request, $id)
    {
        $org = Organization::with(['user', 'user.userDetails'])->findOrFail($id);

    $validated = $request->validate([
            // organization table fields
           
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'sales_person_id' => ValidationRules::REQUIRED_INTEGER . '|exists:users,id',
            'last_contacted' => ValidationRules::NULLABLE_DATE,
            'certified_staff' => ValidationRules::REQUIRED_INTEGER,
            'first_name' => ValidationRules::REQUIRED_STRING,
            'last_name' => ValidationRules::REQUIRED_STRING,
            'admin_email' => ValidationRules::REQUIRED_EMAIL,
            'admin_phone' =>  ValidationRules::REQUIRED_STRING . '|regex:/^[6-9]\d{9}$/',
            'organization_name' => ValidationRules::REQUIRED_STRING,
            'organization_size' => ValidationRules::REQUIRED_STRING,
            'source' => ValidationRules::REQUIRED_STRING,
            'address' => ValidationRules::REQUIRED_STRING,
            'country_id' => ValidationRules::REQUIRED_INTEGER . '|exists:countries,id',
            'state_id' => ValidationRules::REQUIRED_INTEGER . '|exists:states,id',
            'city_id' => ValidationRules::REQUIRED_INTEGER . '|exists:cities,id',
            'zip' => 'required|regex:/^[1-9][0-9]{5}$/',
        ]);

        DB::beginTransaction();
        try {
            // Update allowed organization columns (include organization_name)
            $orgFields = array_filter($validated, function ($k) {
                return in_array($k, ['contract_start', 'contract_end',  'sales_person_id', 'last_contacted', 'certified_staff', 'organization_name', 'organization_size']);
            }, ARRAY_FILTER_USE_KEY);
            if (!empty($orgFields)) {
                $org->update($orgFields);
            }

            // Update related user and user_details where applicable
            $user = $org->user;
            if ($user) {
                $userUpdated = false;
                if (array_key_exists('admin_email', $validated) && $validated['admin_email'] !== $user->email) {
                    // Ensure new admin_email does not conflict with existing (non-trashed) users
                    $exists = \App\Models\User::where('email', $validated['admin_email'])->whereNull('deleted_at')->first();
                    if ($exists) {
                        // keep existing email and log conflict; frontend should handle this error ideally
                        \Log::warning('Organization update attempted to set admin_email that already exists for active user', ['admin_email' => $validated['admin_email'], 'org_id' => $org->id]);
                    } else {
                        $user->email = $validated['admin_email'];
                        $userUpdated = true;
                    }
                }
                if (array_key_exists('first_name', $validated) && $validated['first_name'] !== $user->first_name) {
                    $user->first_name = $validated['first_name'];
                    $userUpdated = true;
                }
                if (array_key_exists('last_name', $validated) && $validated['last_name'] !== $user->last_name) {
                    $user->last_name = $validated['last_name'];
                    $userUpdated = true;
                }
                if ($userUpdated) { $user->save(); }

                $details = $user->userDetails;
                if (!$details) {
                    $details = new UserDetail();
                    $details->user_id = $user->id;
                }
                $detailUpdated = false;
                if (array_key_exists('admin_phone', $validated)) { $details->phone = $validated['admin_phone']; $detailUpdated = true; }
                if (array_key_exists('organization_name', $validated)) {
                    if (Schema::hasColumn('user_details', 'organization_name')) {
                        $details->organization_name = $validated['organization_name']; $detailUpdated = true;
                    } else {
                        \Log::warning('[Organization@update] skipping write to user_details.organization_name because column missing', ['org_id' => $org->id]);
                    }
                }
                if (array_key_exists('organization_size', $validated)) {
                    if (Schema::hasColumn('user_details', 'organization_size')) {
                        $details->organization_size = $validated['organization_size']; $detailUpdated = true;
                    } else {
                        \Log::warning('[Organization@update] skipping write to user_details.organization_size because column missing', ['org_id' => $org->id]);
                    }
                }
                if (array_key_exists('source', $validated)) { $details->find_us = $validated['source']; $detailUpdated = true; }
                if (array_key_exists('address', $validated)) { $details->address = $validated['address']; $detailUpdated = true; }
                if (array_key_exists('country_id', $validated)) { $details->country_id = $validated['country_id']; $detailUpdated = true; }
                if (array_key_exists('state_id', $validated)) { $details->state_id = $validated['state_id']; $detailUpdated = true; }
                if (array_key_exists('city_id', $validated)) { $details->city_id = $validated['city_id']; $detailUpdated = true; }
                if (array_key_exists('zip', $validated)) { $details->zip = $validated['zip']; $detailUpdated = true; }
                if ($detailUpdated) { $details->save(); }
                // Keep organizations.organization_name and user_details.organization_name in sync
                try {
                    if (array_key_exists('organization_name', $validated) && isset($validated['organization_name'])) {
                        // ensure organization record mirrors the supplied organization_name
                        $org->organization_name = $validated['organization_name'];
                        $org->save();
                    }
                    if (array_key_exists('organization_size', $validated) && isset($validated['organization_size'])) {
                        // ensure organization record mirrors the supplied organization_size
                        $org->organization_size = $validated['organization_size'];
                        $org->save();
                    }
                } catch (\Exception $e) {
                    \Log::warning('[Organization@update] failed to sync organization_name to organization', ['org_id' => $org->id, 'error' => $e->getMessage()]);
                }
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
    return response()->json(['message' => 'Organization soft deleted']);
    }
}

