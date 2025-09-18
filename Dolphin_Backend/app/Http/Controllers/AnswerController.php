<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnswerRequest;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnswerController extends Controller
{

    // Retrieve all questions for the assessment.
    // @return \Illuminate\Http\JsonResponse

    public function getQuestions(): JsonResponse
    {
        try {
            $questions = Question::all(['id', 'question', 'options']);
            return response()->json($questions);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve questions.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Could not retrieve questions.'], 500);
        }
    }


    // Store the user's answers.
    // @param  \App\Http\Requests\StoreAnswerRequest  $request
    // @return \Illuminate\Http\JsonResponse

    public function store(StoreAnswerRequest $request): JsonResponse
    {
        try {
            $answers = $request->validated()['answers'];
            $userId = Auth::id();

            // Use a database transaction to ensure all answers are saved together.
            DB::transaction(function () use ($answers, $userId) {
                foreach ($answers as $answerData) {
                    Answer::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'question_id' => $answerData['question_id'],
                        ],
                        [
                            'answer' => json_encode($answerData['answer']),
                        ]
                    );
                }
            });

            return response()->json(['message' => 'Assessment answers saved successfully'], 201);
        } catch (\Exception $e) {
            Log::error('Failed to store answers.', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'An error occurred while saving answers.'], 500);
        }
    }


    // Retrieve the authenticated user's saved answers.
    // @return \Illuminate\Http\JsonResponse

    public function getUserAnswers(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $answers = Answer::where('user_id', $userId)->get();

            // Transform the data for a consistent API response.
            $formattedAnswers = $answers->map(function ($answer) {
                return [
                    'question_id' => $answer->question_id,
                    'answer' => json_decode($answer->answer, true),
                ];
            });

            return response()->json($formattedAnswers);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user answers.', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Could not retrieve user answers.'], 500);
        }
    }
}
