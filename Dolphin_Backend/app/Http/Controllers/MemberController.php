<?php
namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role || $role->name !== 'organizationadmin') {
            return response()->json(['error' => 'Unauthorized. Only organizationadmin can update members.'], 403);
        }
        // determine organization id: prefer $user->organization_id, otherwise find org owned by this user
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

        $member = Member::where('organization_id', $orgId)->findOrFail($id);
        $rules = [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            // unique excluding trashed rows and excluding this member id
            'email' => 'sometimes|email|unique:members,email,' . $id . ',id,deleted_at,NULL',
            'phone' => 'nullable|string',
            // accept string/array for roles
            'member_role' => 'sometimes',
            'country' => 'nullable|string',
        ];
        $validated = $request->validate($rules);

        // extract role input so it doesn't try to fill a non-existent column
        $roleInput = null;
        if (array_key_exists('member_role', $validated)) {
            $roleInput = $validated['member_role'];
            unset($validated['member_role']);
        }

        $member->update($validated);

        if ($roleInput !== null) {
            // use Member model mutator to sync roles
            $member->member_role = $roleInput;
        }

        return response()->json($member->load('memberRoles'));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role || $role->name !== 'organizationadmin') {
            return response()->json(['error' => 'Unauthorized. Only organizationadmin can delete members.'], 403);
        }
        // determine organization id with fallback
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
        $member = Member::where('organization_id', $orgId)->findOrFail($id);
    $member->delete();
    return response()->json(['message' => 'Member soft deleted successfully']);
    }
    
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role || $role->name !== 'organizationadmin') {
            return response()->json(['error' => 'Unauthorized. Only organizationadmin can access members.'], 403);
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
        $members = Member::where('organization_id', $orgId)->with(['groups', 'memberRoles'])->get();
        // Return members with group_ids array and role objects/names for frontend
        $membersWithGroups = $members->map(function($member) {
            return [
                'id' => $member->id,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'phone' => $member->phone,
                'group_ids' => $member->groups->pluck('id')->toArray(),
                'member_role' => $member->member_role, // primary role name (compat)
                'member_role_ids' => $member->memberRoles->pluck('id')->toArray(),
                'memberRoles' => $member->memberRoles->map(function($r){ return ['id' => $r->id, 'name' => $r->name]; })->toArray(),
                'member_role_names' => $member->memberRoles->pluck('name')->toArray(),
            ];
        });
        return response()->json($membersWithGroups);
    }

    /**
     * Show a single member with related groups and roles.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role || $role->name !== 'organizationadmin') {
            return response()->json(['error' => 'Unauthorized. Only organizationadmin can access members.'], 403);
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

        $member = Member::where('organization_id', $orgId)->with(['groups', 'memberRoles'])->findOrFail($id);

        $mapped = [
            'id' => $member->id,
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'email' => $member->email,
            'phone' => $member->phone,
            'group_ids' => $member->groups->pluck('id')->toArray(),
            'member_role' => $member->member_role,
            'member_role_ids' => $member->memberRoles->pluck('id')->toArray(),
            'memberRoles' => $member->memberRoles->map(function($r){ return ['id' => $r->id, 'name' => $r->name]; })->toArray(),
            'member_role_names' => $member->memberRoles->pluck('name')->toArray(),
        ];

        return response()->json($mapped);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role || $role->name !== 'organizationadmin') {
            return response()->json(['error' => 'Unauthorized. Only organizationadmin can create members.'], 403);
        }
        $userId = $user->id;
        $rules = [
            'first_name' => 'required|string|max:255',
            // require email to be unique among non-deleted members only
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,NULL,id,deleted_at,NULL',
            'phone' => 'required|regex:/^[6-9]\d{9}$/',
            'member_role' => 'required',
            'group_ids' => 'array',
            'group_ids.*' => 'exists:groups,id',
        ];
        $validated = $request->validate($rules);
        // ensure organization_id is set from the authenticated user's organization (with fallback)
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
        $validated['organization_id'] = $orgId;
        // record which user (admin) created this member
        $validated['user_id'] = $userId;

        $roleInput = null;
        if (array_key_exists('member_role', $validated)) {
            $roleInput = $validated['member_role'];
            unset($validated['member_role']);
        }

        $groupIds = $request->input('group_ids', []);
    // If a soft-deleted member exists with this email, preserve it (do not force-delete).
    // We rely on a composite unique index on (email, deleted_at) so an active row (deleted_at=NULL)
    // can coexist with trashed rows. Ensure the migration to change the unique index has been run.
    // (Previously the code force-deleted trashed rows; preserving history is safer.)

        $member = Member::create($validated);
        if (!empty($groupIds)) {
            $member->groups()->sync($groupIds);
        }

        if ($roleInput !== null) {
            $member->member_role = $roleInput;
        }

        return response()->json($member->load('groups', 'memberRoles'), 201);
    }
}
