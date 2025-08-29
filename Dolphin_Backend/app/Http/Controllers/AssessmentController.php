<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Organization;

class AssessmentController extends Controller
{
    public function summary($id)
    {
        $assessment = \App\Models\Assessment::findOrFail($id);
        // Get all members who have answers for this assessment
        $answers = \App\Models\AssessmentQuestionAnswer::where('assessment_id', $id)->get();
        $tokens = \DB::table('assessment_answer_tokens')->where('assessment_id', $id)->get();

        // Group answers by member
        $members = [];
        // Add all members with tokens. Use Eloquent withTrashed() so soft-deleted
        // members are still resolved. If the member row is missing entirely then
        // the name will be set to 'Unknown'. If a member exists but has empty
        // first/last name, fall back to the member's email.
        foreach ($tokens as $token) {
            $memberId = $token->member_id;
            $member = \App\Models\Member::withTrashed()->find($memberId);
            $memberName = 'Unknown';
            if ($member) {
                $full = trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? ''));
                if (strlen($full) > 0) {
                    $memberName = $full;
                } elseif (!empty($member->email)) {
                    $memberName = $member->email;
                } else {
                    $memberName = 'Unknown';
                }
            }
            $members[$memberId] = [
                'member_id' => $memberId,
                'name' => $memberName,
                'answers' => []
            ];
        }
        // Add answers to members
        foreach ($answers as $answer) {
            $memberId = $answer->member_id;
            // Fetch question text directly from organization_assessment_questions table
            $orgQuestion = \DB::table('organization_assessment_questions')
                ->where('id', $answer->organization_assessment_question_id)
                ->first();
            $questionText = $orgQuestion ? $orgQuestion->text : '';
            $members[$memberId]['answers'][] = [
                'question' => $questionText,
                'answer' => $answer->answer,
                'assessment_question_id' => $answer->assessment_question_id,
                'organization_assessment_question_id' => $answer->organization_assessment_question_id,
            ];
        }

        // Calculate summary counts
        $totalSent = $tokens->count();
        $submitted = $tokens->where('used', 1)->count();
        $pending = $tokens->where('used', 0)->count();

        return response()->json([
            'assessment' => [
                'id' => $assessment->id,
                'name' => $assessment->name,
            ],
            'members' => array_values($members),
            'summary' => [
                'total_sent' => $totalSent,
                'submitted' => $submitted,
                'pending' => $pending,
            ],
        ]);
    }

    public function show(Request $request)
    {
        // Prefer organization_id if provided. Otherwise return assessments
        // for the logged-in user (or user_id from request).
        $orgId = $request->input('organization_id') ?: $request->query('organization_id');
        if ($orgId) {
            $assessments = Assessment::where('organization_id', $orgId)
                ->select('id', 'name', 'organization_id')
                ->get();
          
            return response()->json(['assessments' => $assessments]);
        }

        // Fall back to user-specific behavior
        $user = $request->user();
        $userId = null;
        if ($user) {
            $userId = $user->id;
            \Log::info('[AssessmentController@show] user info', [
                'user_id' => $userId,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'attributes' => $user->toArray()
            ]);
        } else {
            $userId = $request->input('user_id');
            if (!$userId) {
                $userId = $request->query('user_id');
            }
            // If user_id is encrypted, decode it
            if ($userId && strpos($userId, 'U2FsdGVkX1') === 0) {
                // Remove encryption prefix, use raw value
                $userId = str_replace('U2FsdGVkX1/', '', $userId);
            }
            \Log::warning('[AssessmentController@show] No user logged in, using user_id from request', ['user_id' => $userId]);
        }
        if (!$userId) {
            return response()->json(['assessments' => []]);
        }
        $assessments = Assessment::where('user_id', $userId)
            ->select('id', 'name', 'organization_id')
            ->get();
        \Log::info('[AssessmentController@show] assessments returned', ['count' => $assessments->count(), 'ids' => $assessments->pluck('id')]);
        return response()->json(['assessments' => $assessments]);
    }

    public function store(Request $request)
    {
        // Validate input
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'question_ids' => 'nullable|array',
        ]);

        // Prefer provided organization_id, otherwise try to use the
        // authenticated user's organization (if available).
        $orgId = $data['organization_id'] ?? null;
        if (!$orgId) {
            // First, check the authenticated User model for an organization_id
            if ($request->user() && isset($request->user()->organization_id)) {
                $orgId = $request->user()->organization_id;
            }
            // Next, try to resolve the organization via Organization relation
            if (!$orgId && $request->user()) {
                try {
                    $org = Organization::where('user_id', $request->user()->id)->first();
                    if ($org) $orgId = $org->id;
                } catch (\Exception $e) {
                    // ignore lookup errors
                }
            }
        }

        $assessment = Assessment::create([
            'name' => $data['name'],
            'user_id' => $request->user()->id ?? $request->input('user_id'),
            'organization_id' => $orgId,
        ]);
        // Attach questions if provided
        if (!empty($data['question_ids'])) {
            $assessment->questions()->attach($data['question_ids']);
        }
        return response()->json(['assessment' => $assessment->load('questions')], 201);
    }
}


