<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeadAssessmentRegistrationMail;

class LeadController extends Controller
{
    public function update(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }
        $data = $request->validate([
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'phone' => 'nullable|string',
            'find_us' => 'nullable|string',
            'org_name' => 'nullable|string',
            'org_size' => 'nullable|string',
            'notes' => 'nullable|string',
            'address' => 'nullable|string',
            'country_id' => 'nullable|integer|exists:countries,id',
            'state_id' => 'nullable|integer|exists:states,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'zip' => 'nullable|string',
        ]);
        $lead->update($data);
        return response()->json(['message' => 'Lead updated successfully', 'lead' => $lead]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'find_us' => 'nullable|string',
            'org_name' => 'nullable|string',
            'org_size' => 'nullable|string',
            'notes' => 'nullable|string',
            'address' => 'nullable|string',
            'country_id' => 'nullable|integer|exists:countries,id',
            'state_id' => 'nullable|integer|exists:states,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'zip' => 'nullable|string',
        ]);
        $lead = Lead::create($data);
        return response()->json(['message' => 'Lead saved successfully', 'lead' => $lead], 201);
    }

    public function index()
    {
        return response()->json(Lead::all());
    }
    /**
     * Prefill endpoint for registration form: returns all lead data by lead_id or email
     */
    public function prefill(Request $request)
    {
        $lead = null;
        if ($request->has('lead_id')) {
            $lead = Lead::find($request->input('lead_id'));
        } elseif ($request->has('email')) {
            $lead = Lead::where('email', $request->input('email'))->first();
        }
        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }
        // Log find_us presence/absence for debugging
        if (empty($lead->find_us)) {
            Log::info("Lead prefill: lead_id={$lead->id} find_us is empty");
        } else {
            Log::info("Lead prefill: lead_id={$lead->id} find_us={$lead->find_us}");
        }

        return response()->json(['lead' => [
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'organization_name' => $lead->org_name ?? '',
            'organization_size' => $lead->org_size ?? '',
            'organization_address' => $lead->address ?? '',
            'organization_city_id' => $lead->city_id ?? '',
            'organization_state_id' => $lead->state_id ?? '',
            'organization_zip' => $lead->zip ?? '',
            'country_id' => $lead->country_id ?? '',
            'find_us' => $lead->find_us ?? '',
        ]]);
    }

    /**
     * Return distinct non-empty find_us values found in leads (public).
     */
    public function findUsOptions()
    {
        $values = Lead::whereNotNull('find_us')
            ->where('find_us', '!=', '')
            ->distinct()
            ->pluck('find_us')
            ->filter()
            ->values();

        if ($values->isEmpty()) {
            Log::info('Lead find_us options: none found');
        } else {
            Log::info('Lead find_us options: ' . $values->join(', '));
        }

        return response()->json(['options' => $values]);
    }
}
