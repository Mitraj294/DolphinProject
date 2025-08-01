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
        $member = Member::where('user_id', $user->id)->findOrFail($id);
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
        $member = Member::where('user_id', $user->id)->findOrFail($id);
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
        $userId = $user->id;
        $members = Member::where('user_id', $userId)->get();
        return response()->json($members);
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
        $validated['user_id'] = $userId;
        $groupIds = $request->input('group_ids', []);
        $member = Member::create($validated);
        if (!empty($groupIds)) {
            $member->groups()->sync($groupIds);
        }
        return response()->json($member->load('groups'), 201);
    }
}
