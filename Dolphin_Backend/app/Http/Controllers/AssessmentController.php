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
        // Return all assessments created by this user, with their questions
        $assessments = \App\Models\Assessment::with('questions')->where('user_id', $user->id)->orderByDesc('id')->get();
        return response()->json(['assessments' => $assessments]);
    }

    // Store answers for both questions (legacy)
    public function store(Request $request)
    {
        $user = Auth::user();
        // If this is a create assessment request
        if ($request->has('name') && $request->has('questions')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'questions' => 'required|array|min:1',
                'questions.*.text' => 'required|string|max:1000',
            ]);
            $assessment = \App\Models\Assessment::create([
                'name' => $request->name,
                'user_id' => $user->id,
            ]);
            foreach ($request->questions as $q) {
                $assessment->questions()->create(['text' => $q['text']]);
            }
            return response()->json(['success' => true, 'assessment' => $assessment->load('questions')], 201);
        }

        // Otherwise, treat as answer submission (legacy)
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

