<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssessmentScheduleRequest;
use App\Models\Assessment;
use App\Models\Member;
use App\Notifications\AssessmentInvitation;
use App\Services\AssessmentLinkService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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

            $sendAt = Carbon::parse($validated['date'] . ' ' . $validated['time']);

            foreach ($recipients as $recipient) {
                // For scheduled assessments, we may not have a single group context if multiple groups are selected.
                // We will pass null for the group_id. The token and link generation can handle this.
                $token = $linkService->createAnswerToken($assessment->id, $recipient->id, null);
                $link = $linkService->generateFrontendLink($token, $recipient->id, null);

                $recipient->notify((new AssessmentInvitation($link, $assessment->name))->delay($sendAt));
            }

            return response()->json(['message' => 'Assessment has been scheduled successfully.'], 200);

        } catch (\Exception $e) {
            Log::error('Error scheduling assessment:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An unexpected error occurred while scheduling the assessment.'], 500);
        }
    }
}