<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller
{
    public function getQuestions()
    {
        $questions = Question::all(['id', 'question', 'options']);
        return response()->json($questions);
    }
    public function store(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'required|array'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        foreach ($request->answers as $answerData) {
            $questionId = $answerData['question_id'];
            $selectedWords = $answerData['answer'];
            Answer::updateOrCreate(
                [
                    'user_id' => $userId,
                    'question' => $questionId,
                ],
                [
                    'answer' => json_encode($selectedWords)
                ]
            );
        }

        return response()->json([
            'message' => 'Assessment answers saved successfully'
        ], 201);
    }

    public function getUserAnswers()
    {
        $userId = Auth::id();
        $answers = Answer::where('user_id', $userId)->get();
        $result = $answers->map(function($ans) {
            return [
                'question_id' => $ans->question,
                'answer' => json_decode($ans->answer, true)
            ];
        });
        return response()->json($result);
    }
}
