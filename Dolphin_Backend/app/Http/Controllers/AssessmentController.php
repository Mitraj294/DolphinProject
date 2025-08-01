<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;

class AssessmentController extends Controller
{
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


