<?php

namespace App\Http\Controllers;

use App\Models\Group;

use Illuminate\Http\Request;

class GroupController extends Controller
{
    // organizationadmin: only their groups; superadmin: all groups
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
        if ($role->name === 'superadmin') {
            $groups = Group::with('members')->get();
            return response()->json($groups);
        } elseif ($role->name === 'organizationadmin') {
            // organizationadmin: return groups belonging to the admin's organization
            // determine organization id with fallback to Organization.user_id
            $orgId = $user->organization_id;
            if (!$orgId) {
                $org = \App\Models\Organization::where('user_id', $user->id)->first();
                if ($org) {
                    $orgId = $org->id;
                }
            }
            if (!$orgId) {
                return response()->json(['error' => 'Organization not found for user.'], 400);
            }
            $groups = Group::with('members')->where('organization_id', $orgId)->get();
            return response()->json($groups);
        } else {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role || $role->name !== 'organizationadmin') {
            return response()->json(['error' => 'Unauthorized. Only organizationadmin can create groups.'], 403);
        }
        // determine organization id with fallback to Organization.user_id
        $orgId = $user->organization_id;
        if (!$orgId) {
            $org = \App\Models\Organization::where('user_id', $user->id)->first();
            if ($org) {
                $orgId = $org->id;
            }
        }
        if (!$orgId) {
            return response()->json(['error' => 'Organization not found for user.'], 400);
        }
        $rules = [
            'name' => 'required|string|max:255',
            'member_ids' => 'array',
            'member_ids.*' => 'exists:members,id',
        ];
        $validated = $request->validate($rules);
    $validated['organization_id'] = $orgId;
    // record which user (admin) created this group
    $validated['user_id'] = $user->id;
        $memberIds = $request->input('member_ids', []);
        $group = Group::create($validated);
        if (!empty($memberIds)) {
            $group->members()->sync($memberIds);
        }
        return response()->json($group->load('members'), 201);
    }

    // Return a single group with members
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($role->name === 'superadmin') {
            $group = Group::with('members')->findOrFail($id);
            return response()->json(['group' => $group, 'members' => $group->members]);
        } elseif ($role->name === 'organizationadmin') {
            $orgId = $user->organization_id;
            if (!$orgId) {
                $org = \App\Models\Organization::where('user_id', $user->id)->first();
                if ($org){ $orgId = $org->id;}
            }
            if (!$orgId) {
                return response()->json(['error' => 'Organization not found for user.'], 400);
            }
            $group = Group::with('members')->where('organization_id', $orgId)->findOrFail($id);
            return response()->json(['group' => $group, 'members' => $group->members]);
        }
        return response()->json(['error' => 'Unauthorized.'], 403);
    }
}
