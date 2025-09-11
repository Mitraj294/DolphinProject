<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberRole;

class MemberRoleController extends Controller
{
    /**
     * Return all member roles for the organization (or global list).
     */
    public function index(Request $request)
    {
        // For now return all member roles. If later we need org-scoped roles,
        // add filtering by organization_id.
        $roles = MemberRole::orderBy('id')->get(['id', 'name']);
        return response()->json($roles);
    }
}
