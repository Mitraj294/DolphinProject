<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

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
            return [
                'id' => $org->id,
                'org_name' => $org->org_name,
                'size' => $org->size,
                'source' => $org->source,
                'admin' => $org->main_contact ?? ($org->user ? ($org->user->userDetails->first_name ?? $org->user->email) : ''),
                'main_contact' => $org->main_contact,
                'contract_start' => $subscription ? $subscription->subscription_start : $org->contract_start,
                'contract_end' => $subscription ? $subscription->subscription_end : $org->contract_end,
                'last_login' => $org->last_contacted,
                'address1' => $org->address1,
                'address2' => $org->address2,
                'city' => $org->city,
                'state' => $org->state,
                'zip' => $org->zip,
                'country' => $org->country,
                'admin_email' => $org->admin_email,
                'admin_phone' => $org->admin_phone,
                'sales_person' => $org->sales_person,
                'certified_staff' => $org->certified_staff,
            ];
        });
        return response()->json($result);
    }

    public function show($id)
    {
        $org = Organization::findOrFail($id);
        return response()->json($org);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'org_name' => 'required|string|max:255',
            'size' => 'nullable|string',
            'source' => 'nullable|string',
            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
            'country' => 'nullable|string',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'main_contact' => 'nullable|string',
            'admin_email' => 'nullable|email',
            'admin_phone' => 'nullable|string',
            'sales_person' => 'nullable|string',
            'last_contacted' => 'nullable|date',
            'certified_staff' => 'nullable|integer',
        ]);
        $org = Organization::create($validated);
        return response()->json($org, 201);
    }

    public function update(Request $request, $id)
    {
        $org = Organization::findOrFail($id);
        $validated = $request->validate([
            'org_name' => 'sometimes|required|string|max:255',
            'size' => 'nullable|string',
            'source' => 'nullable|string',
            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
            'country' => 'nullable|string',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'main_contact' => 'nullable|string',
            'admin_email' => 'nullable|email',
            'admin_phone' => 'nullable|string',
            'sales_person' => 'nullable|string',
            'last_contacted' => 'nullable|date',
            'certified_staff' => 'nullable|integer',
        ]);
        $org->update($validated);
        return response()->json($org);
    }

    public function destroy($id)
    {
        $org = Organization::findOrFail($id);
        $org->delete();
        return response()->json(['message' => 'Organization deleted']);
    }
}
