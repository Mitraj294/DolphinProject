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
            'email' => 'sometimes|email|unique:members,email,' . $id,
            'phone' => 'nullable|string',
            'member_role' => 'sometimes|string',
            'country' => 'nullable|string',
        ];
        $validated = $request->validate($rules);
        $member->update($validated);

        return response()->json($member);
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
        return response()->json(['message' => 'Member deleted successfully']);
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
        $members = Member::where('organization_id', $orgId)->with('groups')->get();
        // Return members with group_ids array for frontend selection logic
        $membersWithGroups = $members->map(function($member) {
            return [
                'id' => $member->id,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'group_ids' => $member->groups->pluck('id')->toArray(),
            ];
        });
        return response()->json($membersWithGroups);
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
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'phone' => 'nullable|string',
            'member_role' => 'required|string',
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
        $groupIds = $request->input('group_ids', []);
        $member = Member::create($validated);
        if (!empty($groupIds)) {
            $member->groups()->sync($groupIds);
        }
        return response()->json($member->load('groups'), 201);
    }
}
