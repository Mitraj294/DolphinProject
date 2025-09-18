<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitAssessmentAnswerRequest;
use App\Models\AssessmentAnswerLink;
use App\Models\AssessmentQuestionAnswer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssessmentAnswerController extends Controller
{

    // Display the assessment questions for a given token.
    // @param  string  $token
    // @return \Illuminate\Http\JsonResponse

    public function show(string $token): JsonResponse
    {
        $response_data = [];
        $status_code = 200;

        try {
            $link = AssessmentAnswerLink::with(['assessment.questions', 'member'])
                ->where('token', $token)
                ->firstOrFail();

            if ($link->completed) {
                $status_code = 410; // Gone
                $response_data['error'] = 'This assessment has already been submitted.';
            } else {
                $response_data = [
                    'assessment' => [
                        'id' => $link->assessment->id,
                        'name' => $link->assessment->name,
                        'questions' => $link->assessment->questions->map->only(['id', 'text']),
                    ],
                    'member' => $link->member,
                    'link' => $link->only(['token', 'group_id']),
                ];
            }
        } catch (ModelNotFoundException $e) {
            Log::warning('Attempt to access an invalid or missing assessment token.', ['token' => $token]);
            $status_code = 404;
            $response_data['error'] = 'Invalid token or assessment not found.';
        } catch (\Exception $e) {
            Log::error('Error retrieving assessment for answering.', ['token' => $token, 'error' => $e->getMessage()]);
            $status_code = 500;
            $response_data['error'] = 'An unexpected error occurred.';
        }

        return response()->json($response_data, $status_code);
    }


    // Store the submitted answers for an assessment.
    // @param  \App\Http\Requests\SubmitAssessmentAnswerRequest  $request
    // @param  string  $token
    // @return \Illuminate\Http\JsonResponse

    public function submit(SubmitAssessmentAnswerRequest $request, string $token): JsonResponse
    {
        $link = $request->assessment_answer_link;
        $answers = $request->validated()['answers'];

        try {
            DB::transaction(function () use ($answers, $link) {
                $this->saveAnswers($answers, $link);

                $link->update([
                    'completed' => true,
                    'completed_at' => now(),
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Answers submitted successfully.']);
        } catch (\Exception $e) {
            Log::error('Failed to submit assessment answers.', [
                'token' => $token,
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);
            return response()->json(['error' => 'An internal server error occurred while saving answers.'], 500);
        }
    }


    // Bulk insert assessment answers.
    // @param  array  $answers
    // @param  \App\Models\AssessmentAnswerLink  $link
    // @return void

    private function saveAnswers(array $answers, AssessmentAnswerLink $link): void
    {
        $answerData = collect($answers)->map(function ($answerText, $questionId) use ($link) {
            return [
                'assessment_id' => $link->assessment_id,
                'member_id' => $link->member_id,
                'group_id' => $link->group_id,
                'question_id' => $questionId,
                'answer' => $answerText,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->values()->all();

        if (!empty($answerData)) {
            AssessmentQuestionAnswer::insert($answerData);
        }
    }
}
