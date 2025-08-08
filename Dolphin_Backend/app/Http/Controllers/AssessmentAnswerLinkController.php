<?php

namespace App\Http\Controllers;

use App\Mail\AssessmentAnswerLinkMail;
use App\Models\Assessment;
use App\Models\AssessmentAnswerToken;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AssessmentAnswerLinkController extends Controller
{
    // Generate and send link
    public function sendLink(Request $request)
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'member_id' => 'required|exists:members,id',
            'email' => 'required|email',
        ]);

        $token = Str::random(40);
        $expiresAt = Carbon::now()->addDays(7);

        $assessment = Assessment::findOrFail($request->assessment_id);
        $member = Member::findOrFail($request->member_id);

        AssessmentAnswerToken::create([
            'assessment_id' => $assessment->id,
            'member_id' => $member->id,
            'group_id' => $request->input('group_id'),
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);


        // Generate frontend link for email (adjust domain as needed for production)
        $frontendBase = env('FRONTEND_URL', 'http://localhost:8080');
        $link = $frontendBase . '/assessment/answer/' . $token;
        Mail::to($request->email)->send(new AssessmentAnswerLinkMail($link, $assessment));

        return response()->json(['message' => 'Link sent successfully.']);
    }

    // Serve the answer page (API for frontend)
    public function getAssessmentByToken($token)
    {
        $tokenRow = AssessmentAnswerToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->firstOrFail();

        $assessment = Assessment::with('questions')->findOrFail($tokenRow->assessment_id);
        return response()->json([
            'assessment' => $assessment,
            'member_id' => $tokenRow->member_id,
        ]);
    }

    // Save answers
    public function submitAnswers(Request $request, $token)
    {
        $tokenRow = AssessmentAnswerToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->firstOrFail();

        $request->validate([
            'answers' => 'required|array',
        ]);

        \Log::info('Assessment answer submission', [
            'token' => $token,
            'tokenRow' => $tokenRow,
            'answers' => $request->answers
        ]);
        foreach ($request->answers as $answer) {
            \Log::info('Inserting answer', [
                'assessment_id' => $tokenRow->assessment_id,
                'question_id' => $answer['question_id'],
                'member_id' => $tokenRow->member_id,
                'group_id' => $tokenRow->group_id,
                'answer' => $answer['answer'],
            ]);
            \DB::table('assessment_question_answers')->insert([
                'assessment_id' => $tokenRow->assessment_id,
                'question_id' => $answer['question_id'],
                'member_id' => $tokenRow->member_id,
                'group_id' => $tokenRow->group_id,
                'answer' => $answer['answer'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $tokenRow->used = true;
        $tokenRow->save();

        return response()->json(['message' => 'Answers submitted successfully.']);
    }
}
