<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;

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
        // Add all members with tokens
        foreach ($tokens as $token) {
            $memberId = $token->member_id;
            $member = \DB::table('members')->where('id', $memberId)->first();
            $memberName = $member ? trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) : 'Unknown';
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
        // Only return assessments for the logged-in user or user_id from request
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
            ->select('id', 'name')
            ->get();
        \Log::info('[AssessmentController@show] assessments returned', ['count' => $assessments->count(), 'ids' => $assessments->pluck('id')]);
        return response()->json(['assessments' => $assessments]);
    }

    public function store(Request $request)
    {
       
        $assessment = Assessment::create([
            'name' => $request->input('name'),
            'user_id' => $request->user()->id ?? $request->input('user_id'),
            'organization_id' => $request->input('organization_id'),
        ]);
        // Attach questions if provided
        if ($request->has('question_ids')) {
            $assessment->questions()->attach($request->input('question_ids'));
        }
        return response()->json(['assessment' => $assessment->load('questions')], 201);
    }
}


