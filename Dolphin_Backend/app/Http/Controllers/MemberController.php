<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        return response()->json(Member::with('group')->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'member_role' => 'required|string|max:100',
            'group_ids' => 'array',
            'group_ids.*' => 'exists:groups,id',
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
        $groupIds = $request->input('group_ids', []);
        $member = Member::create($validated);
        if (!empty($groupIds)) {
            $member->groups()->sync($groupIds);
        }
        return response()->json($member->load('groups'), 201);
    }
}
