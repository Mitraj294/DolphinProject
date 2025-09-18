<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSchedule;
use App\Http\Requests\StoreAssessmentScheduleRequest;
use Illuminate\Support\Facades\Log;

class AssessmentScheduleController extends Controller
{
    public function store(StoreAssessmentScheduleRequest $request)
    {
        try {
            $schedule = AssessmentSchedule::create($request->validated());

            return response()->json([
                'message' => 'Assessment schedule created successfully.',
                'schedule' => $schedule
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating assessment schedule:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }
}
