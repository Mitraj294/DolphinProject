<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSchedule;
use Illuminate\Http\Request;

class AssessmentScheduleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'date' => 'required|date',
            'time' => 'required',
            'group_ids' => 'array',
            'group_ids.*' => 'exists:groups,id',
            'member_ids' => 'array',
            'member_ids.*' => 'exists:members,id',
        ]);
        $schedule = AssessmentSchedule::create([
            'assessment_id' => $validated['assessment_id'],
            'date' => $validated['date'],
            'time' => $validated['time'],
            'group_ids' => $validated['group_ids'] ?? [],
            'member_ids' => $validated['member_ids'] ?? [],
        ]);
        return response()->json(['schedule' => $schedule], 201);
    }
}
