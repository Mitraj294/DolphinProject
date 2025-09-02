<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

            // Send email
            $htmlBody = $validated['body'] ?? '';

           
            $search = ['{{registrationUrl}}', '{{registration_link}}', '{{registration_url}}', '{{name}}'];
            $replace = [$registrationUrl, $registrationUrl, $registrationUrl, $validated['name'] ?? ''];
            $htmlBody = str_replace($search, $replace, $htmlBody);

          
            if (stripos($htmlBody, '<html') === false) {
                $safeSubject = htmlspecialchars($validated['subject'] ?: 'Complete Your Registration', ENT_QUOTES, 'UTF-8');
                $wrapper = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>' . $safeSubject . '</title>'
                    . '<style>body{background:#f4f4f4;font-family:Arial,sans-serif;margin:0;padding:0}.email-container{max-width:600px;margin:36px auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.05);padding:28px 24px;color:#333}</style></head><body>'
                    . $htmlBody
                    . '</body></html>';
                $htmlBody = $wrapper;
            }

            Mail::html($htmlBody, function ($message) use ($to, $validated) {
                $message->to($to)
                    ->subject($validated['subject'] ?: 'Complete Your Registration');
            });
            Log::info('Mail sent', ['to' => $validated['to']]);

            // If we have a lead record, mark it as Assessment Sent
            try {
                if ($lead) {
                    $lead->status = 'Assessment Sent';
                    $lead->assessment_sent_at = now();
                    $lead->save();
                }
            } catch (\Exception $e) {
                
                Log::error('Failed to update lead status after sending assessment', ['error' => $e->getMessage(), 'lead_id' => $lead ? $lead->id : null]);
            }

            return response()->json(['message' => 'Assessment email sent successfully.']);
        } catch (\Exception $e) {
            Log::error('SendAssessmentController@send error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
