<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Lead;
use App\Models\User;

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
                    'organization_name' => $lead->organization_name,
                    'organization_size' => $lead->organization_size,
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
            $responsePayload = null;
            $responseStatus = 200;
            if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $responsePayload = ['error' => 'Invalid recipient email'];
                $responseStatus = 400;
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

            if (is_null($responsePayload)) {
                try {
                    // Allow a safe development override: if MAIL_FORCE_SMTP=true or SMTP env vars are present,
                    // switch the mailer to smtp at runtime so messages are actually delivered (e.g., Mailtrap).
                    $activeMailer = config('mail.default');
                    $forceSmtp = env('MAIL_FORCE_SMTP', false);
                    $smtpHost = env('MAIL_HOST');
                    $smtpUser = env('MAIL_USERNAME');
                    $smtpPass = env('MAIL_PASSWORD');

                    if (($activeMailer === 'log') && ($forceSmtp || ($smtpHost && $smtpUser && $smtpPass))) {
                        Log::info('SendAssessmentController: overriding mailer to smtp for delivery (dev override)');
                        // Update runtime config for smtp
                        config(['mail.default' => 'smtp']);
                        config(['mail.mailers.smtp.host' => $smtpHost]);
                        config(['mail.mailers.smtp.port' => env('MAIL_PORT', 2525)]);
                        config(['mail.mailers.smtp.username' => $smtpUser]);
                        config(['mail.mailers.smtp.password' => $smtpPass]);
                        config(['mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', null)]);
                    }

                    Mail::html($htmlBody, function ($message) use ($to, $validated) {
                        $message->to($to)
                            ->subject($validated['subject'] ?: 'Complete Your Registration');
                    });
                    Log::info('Mail sent', ['to' => $validated['to']]);
                } catch (\Exception $mailException) {
                    // Log full exception details for troubleshooting
                    Log::error('SendAssessmentController: Mail send failed', [
                        'to' => $to,
                        'error' => $mailException->getMessage(),
                        'trace' => $mailException->getTraceAsString(),
                    ]);

                    $responsePayload = [
                        'error' => 'Failed to send email',
                        'details' => $mailException->getMessage(),
                    ];
                    $responseStatus = 500;
                }
            }

            // If we have a lead record, record that an assessment was sent.
            // Do not overwrite a lead that is already Registered.
            try {
                if ($lead) {
                    // Always record the timestamp that an assessment was sent
                    $lead->assessment_sent_at = now();

                    // Determine whether the lead is already registered.
                    $isRegistered = false;
                    if (!empty($lead->registered_at) || (!empty($lead->status) && strtolower($lead->status) === 'registered')) {
                        $isRegistered = true;
                    }

                    // Additionally, check the users table: if a user exists with the lead's email,
                    // treat the lead as Registered and record registered_at if missing.
                    try {
                        if (!empty($lead->email)) {
                            $matchedUser = User::where('email', $lead->email)->first();
                            if ($matchedUser) {
                                $isRegistered = true;
                                if (empty($lead->registered_at)) {
                                    $lead->registered_at = now();
                                }
                                // Ensure status reflects Registered
                                $lead->status = 'Registered';
                                Log::info('SendAssessmentController: Lead matched to user; marking Registered', ['lead_id' => $lead->id, 'user_id' => $matchedUser->id]);
                            }
                        }
                    } catch (\Exception $userCheckEx) {
                        Log::warning('SendAssessmentController: Failed checking users table for lead email', ['lead_id' => $lead->id, 'error' => $userCheckEx->getMessage()]);
                    }

                    if ($isRegistered) {
                        // Keep Registered status as-is; assessment_sent_at already recorded below
                        Log::info('SendAssessmentController: Lead already registered; skipping status overwrite', ['lead_id' => $lead->id]);
                    } else {
                        $lead->status = 'Assessment Sent';
                    }

                    $lead->save();
                }
            } catch (\Exception $e) {
                Log::error('Failed to update lead status after sending assessment', ['error' => $e->getMessage(), 'lead_id' => $lead ? $lead->id : null]);
            }

            if (is_null($responsePayload)) {
                $responsePayload = ['message' => 'Assessment email sent successfully.'];
                $responseStatus = 200;
            }

            // Add the active mailer to the response so callers know whether mails were actually sent or only logged.
            try {
                $activeMailer = config('mail.default');
                $responsePayload['mailer'] = $activeMailer;
                if ($activeMailer === 'log') {
                    $responsePayload['note'] = 'Emails are currently being logged (MAIL_MAILER=log). Configure SMTP or Mailtrap to deliver real messages.';
                }
            } catch (\Exception $cfgEx) {
                Log::warning('SendAssessmentController: Unable to read mailer config', ['error' => $cfgEx->getMessage()]);
            }

            return response()->json($responsePayload, $responseStatus);
        } catch (\Exception $e) {
            Log::error('SendAssessmentController@send error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
