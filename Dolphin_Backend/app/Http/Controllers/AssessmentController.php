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

        return response()->json([
            'assessment' => [
                'id' => $assessment->id,
                'name' => $assessment->name,
            ],
            'members' => array_values($members),
        ]);
    }

    public function show()
    {
        // Return all assessments with their questions (dummy for now)
        $assessments = Assessment::with('questions')->get();
        return response()->json(['assessments' => $assessments]);
    }

    public function store(Request $request)
    {
        // Dummy create logic
        $assessment = Assessment::create([
            'name' => $request->input('name'),
        ]);
        // Attach questions if provided
        if ($request->has('question_ids')) {
            $assessment->questions()->attach($request->input('question_ids'));
        }
        return response()->json(['assessment' => $assessment->load('questions')], 201);
    }
}


