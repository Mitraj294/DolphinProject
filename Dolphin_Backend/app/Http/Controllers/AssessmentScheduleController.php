<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssessmentScheduleRequest;
use App\Models\Assessment;
use App\Models\Member;
use App\Notifications\AssessmentInvitation;
use App\Services\AssessmentLinkService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\AssessmentSchedule;
use Illuminate\Support\Facades\DB;

class AssessmentScheduleController extends Controller
{
    public function store(StoreAssessmentScheduleRequest $request, AssessmentLinkService $linkService)
    {
        try {
            $validated = $request->validated();
            $assessment = Assessment::findOrFail($validated['assessment_id']);

            $memberIds = collect($validated['member_ids'] ?? []);

            if (!empty($validated['group_ids'])) {
                $groupIds = $validated['group_ids'];
                // Get members from the provided groups.
                $membersFromGroups = Member::whereHas('groups', function ($query) use ($groupIds) {
                    $query->whereIn('groups.id', $groupIds);
                })->pluck('id');
                $memberIds = $memberIds->merge($membersFromGroups)->unique();
            }

            $recipients = Member::whereIn('id', $memberIds)->get();

            // Persist the assessment schedule so the frontend can query schedule details
            $scheduleData = [
                'assessment_id' => $assessment->id,
                'date' => $validated['date'] ?? null,
                'time' => $validated['time'] ?? null,
                'group_ids' => !empty($validated['group_ids']) ? $validated['group_ids'] : null,
                'member_ids' => !empty($validated['member_ids']) ? $validated['member_ids'] : null,
            ];

            // If frontend supplied a UTC ISO send_at, store it as well in scheduled_emails later; also include timezone for clarity
            $timezone = $validated['timezone'] ?? null;
            if (!empty($validated['send_at'])) {
                // store date/time pieces if available
                try {
                    $dt = Carbon::parse($validated['send_at'])->setTimezone($timezone ?: 'UTC');
                    $scheduleData['date'] = $dt->toDateString();
                    $scheduleData['time'] = $dt->toTimeString();
                } catch (\Exception $e) {
                    // ignore parse failure; fallback to provided date/time
                }
            }

            $assessmentSchedule = AssessmentSchedule::create($scheduleData);

            // Prefer send_at (UTC ISO) from frontend if provided. Otherwise parse date + time and assume frontend local timezone.
            if (!empty($validated['send_at'])) {
                // frontend sends send_at as UTC ISO string
                $sendAt = Carbon::parse($validated['send_at'])->setTimezone('UTC');
            } else {
                $sendAt = Carbon::parse($validated['date'] . ' ' . $validated['time']);
            }

            $recipientEmails = $recipients->pluck('email')->filter()->unique()->values()->all();

            // Log scheduling summary
            Log::info('Scheduling assessment', [
                'assessment_id' => $assessment->id,
                'assessment_name' => $assessment->name,
                'schedule' => [
                    'date' => $assessmentSchedule->date,
                    'time' => $assessmentSchedule->time,
                    'timezone' => $timezone,
                    'send_at' => $validated['send_at'] ?? null,
                ],
                'recipients_count' => $recipients->count(),
                'recipient_emails' => $recipientEmails,
            ]);

            // Create per-recipient token and queue the notification
            foreach ($recipients as $recipient) {
                $token = $linkService->createAnswerToken($assessment->id, $recipient->id, null);
                $link = $linkService->generateFrontendLink($token, $recipient->id, null);

                // Dispatch delayed notification (AssessmentInvitation handles link)
                try {
                    $recipient->notify((new AssessmentInvitation($link, $assessment->name, $assessment->id))->delay($sendAt));
                } catch (\Exception $e) {
                    Log::error('Failed to queue notification for recipient', ['recipient_id' => $recipient->id, 'error' => $e->getMessage()]);
                }
            }

            // Build the response payload expected by frontend
            // Reuse ScheduledEmailController::show logic by assembling similar data here
            $emails = DB::table('scheduled_emails')->where('assessment_id', $assessment->id)->get();

            // groups with members
            $groupsWithMembers = [];
            if (!empty($assessmentSchedule->group_ids)) {
                $groups = \App\Models\Group::whereIn('id', $assessmentSchedule->group_ids)
                    ->with(['members' => function($query) { $query->with('memberRoles'); }])
                    ->get();
                foreach ($groups as $group) {
                    $groupsWithMembers[] = [
                        'id' => $group->id,
                        'name' => $group->name,
                        'members' => $group->members->map(function($member) {
                            return [
                                'id' => $member->id,
                                'name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) ?: 'Unknown',
                                'email' => $member->email,
                                'member_roles' => $member->memberRoles->map(function($role) {
                                    return ['id' => $role->id, 'name' => $role->name];
                                })
                            ];
                        })
                    ];
                }
            }

            // members with details
            $membersWithDetails = [];
            if (!empty($assessmentSchedule->member_ids)) {
                $members = \App\Models\Member::whereIn('id', $assessmentSchedule->member_ids)
                    ->with(['memberRoles', 'groups'])
                    ->get();
                foreach ($members as $member) {
                    $membersWithDetails[] = [
                        'id' => $member->id,
                        'name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) ?: 'Unknown',
                        'email' => $member->email,
                        'groups' => $member->groups->map(function($g) { return ['id' => $g->id, 'name' => $g->name]; }),
                        'member_roles' => $member->memberRoles->map(function($r) { return ['id' => $r->id, 'name' => $r->name]; })
                    ];
                }
            }

            return response()->json([
                'message' => 'Assessment has been scheduled successfully.',
                'scheduled' => true,
                'schedule' => $assessmentSchedule,
                'assessment' => $assessment,
                'emails' => $emails,
                'groups_with_members' => $groupsWithMembers,
                'members_with_details' => $membersWithDetails,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error scheduling assessment:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An unexpected error occurred while scheduling the assessment.'], 500);
        }
    }
}