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


        // Try to find an existing token for this assessment/member/group
        $answerToken = AssessmentAnswerToken::where('assessment_id', $validated['assessment_id'])
            ->where('member_id', $validated['member_id'])
            ->where('group_id', $validated['group_id'] ?? null)
            ->first();
        if (!$answerToken) {
            $token = bin2hex(random_bytes(16));
            $expiresAt = Carbon::now()->addDays(7);
            $answerToken = AssessmentAnswerToken::create([
                'assessment_id' => $validated['assessment_id'],
                'member_id' => $validated['member_id'],
                'group_id' => $validated['group_id'] ?? null,
                'token' => $token,
                'expires_at' => $expiresAt,
            ]);
        }
        $token = $answerToken->token;
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:8080');
        $answerUrl = $frontendUrl . '/assessment/answer/' . $token;
        $emailBody = $validated['body'] . "\n\n" . 'To answer the assessment, click the link below:' . "\n" . $answerUrl;

        $scheduledEmail = ScheduledEmail::create([
            'recipient_email' => $validated['recipient_email'],
            'subject' => $validated['subject'],
            'body' => $emailBody, // Save full body with link
            'send_at' => $sendAtUtc,
            'assessment_id' => $validated['assessment_id'],
        ]);

        // Queue the assessment email to be sent at the scheduled time
        try {
            \App\Jobs\SendScheduledAssessmentEmail::dispatch($scheduledEmail->id)->delay($sendAtUtc);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to schedule email: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Email scheduled successfully', 'data' => $scheduledEmail], 201);
    }


    public function show(Request $request)
    {
        $assessmentId = $request->query('assessment_id');
        if ($assessmentId) {
            $schedule = \DB::table('assessment_schedules')->where('assessment_id', $assessmentId)->first();
            $assessment = \DB::table('assessments')->where('id', $assessmentId)->first();
            $emails = [];
            if ($schedule) {
                // Optionally, filter by send_at or member_ids
                $emails = \DB::table('scheduled_emails')
                    ->whereDate('send_at', $schedule->date)
                    ->get();
            }
            return response()->json([
                'scheduled' => (bool)$schedule,
                'schedule' => $schedule,
                'assessment' => $assessment,
                'emails' => $emails,
            ]);
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