<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeadAssessmentRegistrationNotification;
use App\Models\Lead;

class SendAssessmentController extends Controller
{
    public function send(Request $request)
    {
        Log::info('SendAssessmentController@send called', ['request' => $request->all()]);
        try {
            $validated = $request->validate([
                'to' => 'required|email',
                'subject' => 'required|string',
                'body' => 'required|string',
                'registration_link' => 'required|url',
                'name' => 'required|string',
                'lead_id' => 'nullable|integer',
            ]);
            Log::info('Validation passed', $validated);

            // Fetch lead if lead_id is provided
            $lead = null;
            if (!empty($validated['lead_id'])) {
                $lead = Lead::find($validated['lead_id']);
            } else {
                $lead = Lead::where('email', $validated['to'])->first();
            }

            // Build registration link with all lead fields as query params
            $registrationUrl = $validated['registration_link'];
            if ($lead) {
                $params = [
                    'first_name' => $lead->first_name,
                    'last_name' => $lead->last_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'organization_name' => $lead->org_name,
                    'organization_size' => $lead->org_size,
                    'organization_address' => $lead->address,
                    'organization_city' => $lead->city,
                    'organization_state' => $lead->state,
                    'organization_zip' => $lead->zip,
                    'country' => $lead->country,
                ];
                $query = http_build_query(array_filter($params, function($v) { return $v !== null && $v !== ''; }));
                $registrationUrl .= (parse_url($registrationUrl, PHP_URL_QUERY) ? '&' : '?') . $query;
            }

            $to = trim((string)$validated['to']);
            if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => 'Invalid recipient email'], 400);
            }

            Notification::route('mail', $to)
                ->notify(new LeadAssessmentRegistrationNotification(
                    $registrationUrl,
                    $validated['name'],
                    $validated['body'],
                    $validated['subject']
                ));
            Log::info('Notification sent', ['to' => $validated['to']]);

            return response()->json(['message' => 'Assessment email sent successfully.']);
        } catch (\Exception $e) {
            Log::error('SendAssessmentController@send error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
