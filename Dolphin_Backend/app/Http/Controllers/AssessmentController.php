<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\Answer;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    // Get current user's assessment and answers

    public function show()
    {
        $user = Auth::user();
        $answers = Answer::where('user_id', $user->id)->get();
        return response()->json(['answers' => $answers]);
    }

    // Store answers for both questions
    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*.question' => 'required|string',
            'answers.*.selected' => 'required|array',
        ]);

        // Remove old answers for this user
        Answer::where('user_id', $user->id)->delete();
        // Store new answers
        foreach ($data['answers'] as $ans) {
            Answer::create([
                'user_id' => $user->id,
                'question' => $ans['question'],
                'answer' => json_encode($ans['selected']),
            ]);
        }
        return response()->json(['message' => 'Assessment submitted successfully']);
    }
}

