<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendAssessmentLinkRequest;
use App\Http\Requests\SubmitAssessmentAnswersRequest;
use App\Models\Assessment;
use App\Models\AssessmentAnswerToken;
use App\Models\AssessmentQuestion;
use App\Models\Group;
use App\Models\Member;
use App\Notifications\AssessmentInvitation;
use App\Services\AssessmentLinkService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssessmentAnswerLinkController extends Controller
{
    /**
     * Generate and send an assessment link to a member.
     *
     * @param  SendAssessmentLinkRequest  $request
     * @param  AssessmentLinkService  $linkService
     * @return JsonResponse
     */
    public function sendLink(SendAssessmentLinkRequest $request, AssessmentLinkService $linkService): JsonResponse
    {
        try {
            $validated = $request->validated();
            $assessment = Assessment::findOrFail($validated['assessment_id']);
            $member = Member::findOrFail($validated['member_id']);

            $token = $linkService->createAnswerToken($assessment->id, $member->id, $validated['group_id'] ?? null);
            $link = $linkService->generateFrontendLink($token, $member->id, $validated['group_id'] ?? null);

            $member->notify(new AssessmentInvitation($link, $assessment->name));

            return response()->json(['message' => 'Link sent successfully.']);
        } catch (\Exception $e) {
            Log::error('Failed to send assessment link.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An unexpected error occurred while sending the link.'], 500);
        }
    }

    /**
     * Retrieve assessment details using a valid token.
     *
     * @param  string  $token
     * @return JsonResponse
     */
    public function getAssessmentByToken(string $token): JsonResponse
    {
        $response_data = [];
        $status_code = 200;
        try {
            // Use firstOrFail to handle non-existent tokens immediately.
            $tokenRow = AssessmentAnswerToken::where('token', $token)->firstOrFail();

            // Check for invalid states first with early returns.
            if ($tokenRow->used) {
                $status_code = 409; // Conflict
                $response_data['message'] = 'This assessment has already been submitted.';
            } elseif (Carbon::now()->isAfter($tokenRow->expires_at)) {
                $status_code = 410; // Gone
                $response_data['message'] = 'This assessment link has expired.';
            } else {
                // Happy path: Token is valid, not used, and not expired.
                $assessment = Assessment::with('assessmentQuestions.question')->findOrFail($tokenRow->assessment_id);
                $responseData = $this->buildAssessmentResponse($assessment, $tokenRow);
                $status_code = 200;
                $response_data['assessment'] = $responseData;
            }
        } catch (ModelNotFoundException $e) {
            Log::warning('Attempt to access an invalid or missing assessment token.', ['token' => $token]);
            $status_code = 404;
            $response_data['message'] = 'Invalid token or assessment not found.';
        } catch (\Exception $e) {
            Log::error('Failed to get assessment by token.', ['token' => $token, 'error' => $e->getMessage()]);
            $status_code = 500;
            $response_data['message'] = 'An unexpected error occurred.';
        }
        return response()->json($response_data, $status_code);
    }

    /**
     * Submit answers for an assessment using a valid token.
     *
     * @param  SubmitAssessmentAnswersRequest  $request
     * @param  string  $token
     * @return JsonResponse
     */
    public function submitAnswers(SubmitAssessmentAnswersRequest $request, string $token): JsonResponse
    {
        $response_data = [];
        $status_code = 200;
        try {
            $tokenRow = AssessmentAnswerToken::where('token', $token)->firstOrFail();

            // Check if token has already been used
            if ($tokenRow->used) {
              $status_code = 409; // Conflict
              $response_data['message'] = 'This assessment has already been submitted.';
            }

            // Check if token has expired
            if (Carbon::now()->isAfter($tokenRow->expires_at)) {
                return response()->json(['message' => 'This assessment link has expired.'], 410);
            }

            $this->validateAnswersBelongToAssessment($request->answers, $tokenRow->assessment_id);

            DB::transaction(function () use ($request, $tokenRow) {
                $this->saveAnswers($request->answers, $tokenRow);
                $tokenRow->update(['used' => true]);
            });

            return response()->json([
                'message' => 'Answers submitted successfully.',
                'inserted' => count($request->answers),
                'redirect_url' => '/thanks',
                'success' => true,
            ]);
        } catch (\InvalidArgumentException $e) {
           $status_code = 400;
           $response_data['message'] = $e->getMessage();
        } catch (\Exception $e) {
            Log::error('Failed to submit answers.', ['token' => $token, 'error' => $e->getMessage()]);
            $status_code = 500;
            $response_data['message'] = 'An unexpected error occurred while submitting answers.';
        }
        return response()->json($response_data, $status_code);
    }

    // Private Helper Methods

    private function buildAssessmentResponse(Assessment $assessment, AssessmentAnswerToken $tokenRow): array
    {
        $questions = $assessment->assessmentQuestions->map(function ($aq) {
            return [
                'assessment_question_id' => $aq->id,
                'question_id' => $aq->question_id,
                'text' => $aq->question->text ?? null,
            ];
        });

        $answers = DB::table('assessment_question_answers')
            ->where('assessment_id', $assessment->id)
            ->where('member_id', $tokenRow->member_id)
            ->where('group_id', $tokenRow->group_id)
            ->get();

        return [
            'id' => $assessment->id,
            'name' => $assessment->name,
            'questions' => $questions,
            'member' => Member::withTrashed()->find($tokenRow->member_id),
            'group' => $tokenRow->group_id ? Group::find($tokenRow->group_id) : null,
            'token' => $tokenRow->token,
            'answers' => $answers,
        ];
    }

    private function validateAnswersBelongToAssessment(array $answers, int $assessmentId): void
    {
        $assessmentQuestionIds = AssessmentQuestion::where('assessment_id', $assessmentId)->pluck('id');
        $submittedQuestionIds = collect($answers)->pluck('assessment_question_id');

        $invalidIds = $submittedQuestionIds->diff($assessmentQuestionIds);

        if ($invalidIds->isNotEmpty()) {
            throw new \InvalidArgumentException('Some questions do not belong to this assessment.');
        }
    }

    private function saveAnswers(array $answers, AssessmentAnswerToken $tokenRow): void
    {
        $insertData = collect($answers)->map(function ($answer) use ($tokenRow) {
            $assessmentQuestion = AssessmentQuestion::find($answer['assessment_question_id']);
            return [
                'assessment_id' => $tokenRow->assessment_id,
                'organization_assessment_question_id' => $assessmentQuestion->question_id,
                'assessment_question_id' => $answer['assessment_question_id'],
                'member_id' => $tokenRow->member_id,
                'group_id' => $tokenRow->group_id,
                'answer' => $answer['answer'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        DB::table('assessment_question_answers')->insert($insertData);
    }
}
