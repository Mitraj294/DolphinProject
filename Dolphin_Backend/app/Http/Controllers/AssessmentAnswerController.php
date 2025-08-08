<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssessmentAnswerLink;
use App\Models\Assessment;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class AssessmentAnswerController extends Controller
{
    // Show the answer page for a given token
    public function show($token)
    {
        try {
            $link = AssessmentAnswerLink::where('token', $token)->firstOrFail();
            $assessment = Assessment::findOrFail($link->assessment_id);
            $member = Member::findOrFail($link->member_id);
            // Fix ambiguous column error by specifying table for id
            $questions = $assessment->questions()->get(['organization_assessment_questions.id', 'organization_assessment_questions.text']);
            // Return data for frontend (questions, member info, etc)
            return response()->json([
                'assessment' => [
                    'id' => $assessment->id,
                    'name' => $assessment->name,
                    'questions' => $questions,
                ],
                'member' => $member,
                'link' => $link,
            ]);
        } catch (\Exception $e) {
            \Log::error('AssessmentAnswerController@show error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid token or assessment not found.'], 404);
        }
    }

    // Save submitted answers
    public function submit(Request $request, $token)
    {
        try {
            $link = AssessmentAnswerLink::where('token', $token)->firstOrFail();
            if ($link->completed) {
                return response()->json(['error' => 'Already submitted'], 400);
            }
            $answers = $request->input('answers', []);
            $assessmentId = $link->assessment_id;
            $memberId = $link->member_id;
            $groupId = $link->group_id ?? null;
            if (empty($answers)) {
                \Log::error('No answers provided', ['token' => $token, 'payload' => $request->all()]);
                return response()->json(['error' => 'No answers provided'], 422);
            }
            DB::transaction(function () use ($answers, $assessmentId, $memberId, $groupId) {
                foreach ($answers as $questionId => $answerText) {
                    try {
                        \App\Models\AssessmentQuestionAnswer::create([
                            'assessment_id' => $assessmentId,
                            'member_id' => $memberId,
                            'group_id' => $groupId,
                            'question_id' => $questionId,
                            'answer' => $answerText,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to save answer', [
                            'assessment_id' => $assessmentId,
                            'member_id' => $memberId,
                            'group_id' => $groupId,
                            'question_id' => $questionId,
                            'answer' => $answerText,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                }
            });
            $link->completed = true;
            $link->completed_at = now();
            $link->save();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('AssessmentAnswerController@submit error', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);
            return response()->json(['error' => 'Internal Server Error', 'details' => $e->getMessage()], 500);
        }
    }
}
