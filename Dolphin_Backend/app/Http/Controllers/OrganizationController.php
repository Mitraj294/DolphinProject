<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        return response()->json(Organization::all());
    }

    public function show($id)
    {
        $org = Organization::findOrFail($id);
        return response()->json($org);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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
            'name' => 'sometimes|required|string|max:255',
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
