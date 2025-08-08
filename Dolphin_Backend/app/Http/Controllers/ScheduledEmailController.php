<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledEmail;
use Illuminate\Support\Carbon;
use App\Models\AssessmentAnswerToken;
use App\Models\Member;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AssessmentAnswerLinkNotification;

class ScheduledEmailController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_email' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
            'send_at' => 'required|date',
            'assessment_id' => 'required|integer',
            'member_id' => 'required|integer',
            'group_id' => 'required|integer', // group_id is now required
        ]);

        // Parse send_at as UTC (frontend sends UTC ISO string)
        $sendAtUtc = Carbon::parse($validated['send_at'])->setTimezone('UTC');
        $scheduledEmail = ScheduledEmail::create([
            'recipient_email' => $validated['recipient_email'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'send_at' => $sendAtUtc,
        ]);

        // Generate unique token for answer link
        $token = bin2hex(random_bytes(16));
        $expiresAt = Carbon::now()->addDays(7);
        $answerToken = AssessmentAnswerToken::create([
            'assessment_id' => $validated['assessment_id'],
            'member_id' => $validated['member_id'],
            'group_id' => $validated['group_id'] ?? null,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);



        // Build the answer link URL
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:8080');
        $answerUrl = $frontendUrl . '/assessment/answer/' . $token;

        // Append the answer link to the email body
        $emailBody = $validated['body'] . "\n\n" . 'To answer the assessment, click the link below:' . "\n" . $answerUrl;

        // Queue the assessment email to be sent at the scheduled time
        try {
            \Mail::to($validated['recipient_email'])
                ->later($sendAtUtc, new \App\Mail\ScheduledAssessmentMail($validated['subject'], $emailBody));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to schedule email: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Email scheduled successfully', 'data' => $scheduledEmail], 201);
    }


    public function show(Request $request)
    {
        // Check for existing assessment schedule by assessment_id
        $assessmentId = $request->query('assessment_id');
        if ($assessmentId) {
            $schedule = \DB::table('assessment_schedules')->where('assessment_id', $assessmentId)->first();
            if ($schedule) {
                return response()->json(['scheduled' => true, 'data' => $schedule]);
            } else {
                return response()->json(['scheduled' => false]);
            }
        }
        // Fallback: check ScheduledEmail by recipient_email if provided
        $recipientEmail = $request->query('recipient_email');
        if ($recipientEmail) {
            $scheduled = ScheduledEmail::where('recipient_email', $recipientEmail)->first();
            if ($scheduled) {
                return response()->json(['scheduled' => true, 'data' => $scheduled]);
            } else {
                return response()->json(['scheduled' => false]);
            }
        }
        // If neither param is provided, return not scheduled
        return response()->json(['scheduled' => false]);
    }
}