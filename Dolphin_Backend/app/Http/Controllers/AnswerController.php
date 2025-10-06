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
            // The questions table stores the question text in the 'question' column.
            $questions = Question::all(['id', 'question', 'options'])->map(function ($q) {
                return [
                    'id' => $q->id,
                    // Return 'question' key to match frontend expectations.
                    'question' => $q->question,
                    'options' => $q->options,
                ];
            });

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
                    // The answers table stores the question as a string column named 'question'.
                    // The incoming payload validates 'answers.*.question_id' (exists on questions.id),
                    // so load the question text from the questions table and save by question text.
                    $question = null;
                    if (isset($answerData['question_id'])) {
                        $q = \App\Models\Question::find($answerData['question_id']);
                        $question = $q ? $q->text : null;
                    }

                    // If we couldn't resolve question text, fall back to any provided 'question' string in payload.
                    if (!$question && isset($answerData['question'])) {
                        $question = $answerData['question'];
                    }

                    if (!$question) {
                        // Skip saving malformed entry instead of causing DB errors.
                        continue;
                    }

                    Answer::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'question' => $question,
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

            // Transform the data for a consistent API response. The answers table stores the
            // question as text in the 'question' column. Resolve the question's id when possible
            // so the frontend can correlate by question_id.
            $formattedAnswers = $answers->map(function ($answer) {
                $questionId = null;
                if (!empty($answer->question)) {
                    $questionId = Question::where('text', $answer->question)->value('id');
                }

                return [
                    'question_id' => $questionId,
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
