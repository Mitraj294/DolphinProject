<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        return response()->json(Group::with('members')->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $rules = [
            'name' => 'required|string|max:255',
            'member_ids' => 'array',
            'member_ids.*' => 'exists:members,id',
        ];
        // Only require organization_id if not an org admin
        if (!$user || $user->role !== 'organizationadmin') {
            $rules['organization_id'] = 'required|exists:organizations,id';
        }
        $validated = $request->validate($rules);
        // If org admin, set organization_id automatically
        if ($user && $user->role === 'organizationadmin') {
            $org = $user->organization();
            if ($org) {
                $validated['organization_id'] = $org->id;
            }
        }
        $memberIds = $request->input('member_ids', []);
        $group = Group::create($validated);
        if (!empty($memberIds)) {
            $group->members()->sync($memberIds);
        }
        return response()->json($group->load('members'), 201);
    }
}
