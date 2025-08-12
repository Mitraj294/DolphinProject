<?php

namespace App\Http\Controllers;

use App\Mail\AssessmentAnswerLinkMail;
use App\Models\Assessment;
use App\Models\AssessmentAnswerToken;
use App\Models\AssessmentQuestion;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AssessmentAnswerLinkController extends Controller
{
    // Generate and send link
    public function sendLink(Request $request)
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'member_id' => 'required|exists:members,id',
            'email' => 'required|email',
        ]);

        $token = Str::random(40);
        $expiresAt = Carbon::now()->addDays(7);

        $assessment = Assessment::findOrFail($request->assessment_id);
        $member = Member::findOrFail($request->member_id);

        AssessmentAnswerToken::create([
            'assessment_id' => $assessment->id,
            'member_id' => $member->id,
            'group_id' => $request->input('group_id'),
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);


    // Generate frontend link for email (adjust domain as needed for production)
    $frontendBase = env('FRONTEND_URL', 'http://localhost:8080');
    $groupId = $request->input('group_id');
    $link = $frontendBase . '/assessment/answer/' . $token . '?group_id=' . $groupId . '&member_id=' . $member->id;
    Mail::to($request->email)->send(new AssessmentAnswerLinkMail($link, $assessment));

    return response()->json(['message' => 'Link sent successfully.']);
    }

    // Serve the answer page (API for frontend)
    public function getAssessmentByToken($token)
    {
        $tokenRow = AssessmentAnswerToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->firstOrFail();

        // Get assessment questions with assessment_question.id
        $assessment = Assessment::findOrFail($tokenRow->assessment_id);
        $assessmentQuestions = \App\Models\AssessmentQuestion::where('assessment_id', $assessment->id)
            ->get();

        $questions = $assessmentQuestions->map(function($aq) {
            $orgQuestion = \DB::table('organization_assessment_questions')->where('id', $aq->question_id)->first();
            $label = $orgQuestion ? $orgQuestion->text : null;
            return [
                'assessment_question_id' => $aq->id,
                'question_id' => $aq->question_id,
                'text' => $label
            ];
        });

        // Get group details if available
        $group = null;
        if ($tokenRow->group_id) {
            $group = \DB::table('groups')->where('id', $tokenRow->group_id)->first();
        }

        // Get member details
        $member = \App\Models\Member::find($tokenRow->member_id);

        // Get any previously submitted answers for this token/member/assessment
        $answers = \DB::table('assessment_question_answers')
            ->where('assessment_id', $assessment->id)
            ->where('member_id', $tokenRow->member_id)
            ->where('group_id', $tokenRow->group_id)
            ->get();

        return response()->json([
            'assessment' => [
                'id' => $assessment->id,
                'name' => $assessment->name,
                'questions' => $questions,
                'member' => $member,
                'group' => $group,
                'token' => $tokenRow->token,
                'answers' => $answers,
                 ],
            ]);
        }

    // Save answers
    public function submitAnswers(Request $request, $token)
    {
        \Log::info('submitAnswers called', [
            'token' => $token,
            'payload' => $request->all()
        ]);
        $tokenRow = AssessmentAnswerToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->firstOrFail();


        $request->validate([
            'answers' => 'required|array',
        ]);

        $assessmentQuestionIds = AssessmentQuestion::where('assessment_id', $tokenRow->assessment_id)
            ->pluck('id')->toArray();

        $invalid = [];
        foreach ($request->answers as $answer) {
            if (!in_array($answer['assessment_question_id'], $assessmentQuestionIds)) {
                $invalid[] = $answer['assessment_question_id'];
            }
        }

        if (count($invalid) > 0) {
            return response()->json([
                'message' => 'Some assessment_question_ids are invalid for this assessment.',
                'invalid_assessment_question_ids' => $invalid
            ], 422);
        }

        foreach ($request->answers as $answer) {
            // Get organization_assessment_question_id from assessment_question
            $assessmentQuestion = \App\Models\AssessmentQuestion::find($answer['assessment_question_id']);
            $organizationAssessmentQuestionId = $assessmentQuestion ? $assessmentQuestion->question_id : null;
            // Always use tokenRow group_id and member_id if not present in request
            $groupId = $tokenRow->group_id;
            $memberId = $tokenRow->member_id;
            if (is_null($groupId) || is_null($memberId)) {
                \Log::error('Missing group_id or member_id in tokenRow', [
                    'group_id' => $groupId,
                    'member_id' => $memberId,
                    'assessment_question_id' => $answer['assessment_question_id'],
                    'payload' => $request->all()
                ]);
                return response()->json([
                    'message' => 'group_id and member_id are required and missing for this answer.',
                    'assessment_question_id' => $answer['assessment_question_id']
                ], 422);
            }
            \DB::table('assessment_question_answers')->insert([
                'assessment_id' => $tokenRow->assessment_id,
                'organization_assessment_question_id' => $organizationAssessmentQuestionId,
                'assessment_question_id' => $answer['assessment_question_id'],
                'member_id' => $memberId,
                'group_id' => $groupId,
                'answer' => $answer['answer'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $tokenRow->used = true;
        $tokenRow->save();

        return response()->json([
            'message' => 'Answers submitted successfully.',
            'inserted' => count($request->answers)
        ]);
    }
}
