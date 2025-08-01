<?php

namespace App\Http\Controllers;

use App\Models\Group;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role || $role->name !== 'organizationadmin') {
            return response()->json(['error' => 'Unauthorized. Only organizationadmin can access groups.'], 403);
        }
        $userId = $user->id;
        $groups = Group::with('members')->where('user_id', $userId)->get();
        return response()->json($groups);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->first();
        if (!$role || $role->name !== 'organizationadmin') {
            return response()->json(['error' => 'Unauthorized. Only organizationadmin can create groups.'], 403);
        }
        $userId = $user->id;
        $rules = [
            'name' => 'required|string|max:255',
            'member_ids' => 'array',
            'member_ids.*' => 'exists:members,id',
        ];
        $validated = $request->validate($rules);
        $validated['user_id'] = $userId;
        $memberIds = $request->input('member_ids', []);
        $group = Group::create($validated);
        if (!empty($memberIds)) {
            $group->members()->sync($memberIds);
        }
        return response()->json($group->load('members'), 201);
    }
}
