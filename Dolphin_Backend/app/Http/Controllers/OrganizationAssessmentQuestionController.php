<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganizationAssessmentQuestion;

class OrganizationAssessmentQuestionController extends Controller
{
    public function index()
    {
        $questions = OrganizationAssessmentQuestion::select('id', 'text')->get();
        return response()->json(['questions' => $questions]);
    }
}
