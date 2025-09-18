<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Member;
use App\Models\Organization;
use App\Http\Requests\StoreAssessmentRequest;
use App\Http\Requests\IndexAssessmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssessmentController extends Controller
{

    // Display a listing of the resource.
    // @param  IndexAssessmentRequest  $request
    // @return JsonResponse

    public function index(IndexAssessmentRequest $request): JsonResponse
    {
        return $this->show($request);
    }


    // Display the specified resource.
    // @param  IndexAssessmentRequest  $request
    // @return JsonResponse

    public function show(IndexAssessmentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $query = Assessment::select('id', 'name', 'organization_id');

            if (isset($validated['organization_id'])) {
                $query->where('organization_id', $validated['organization_id']);
            } elseif ($request->user()) {
                $query->where('user_id', $request->user()->id);
            } elseif (isset($validated['user_id'])) {
                $query->where('user_id', $validated['user_id']);
            } else {
                return response()->json(['assessments' => []]);
            }

            $assessments = $query->get();
            Log::info('[AssessmentController@show] Assessments returned', ['count' => $assessments->count()]);

            return response()->json(['assessments' => $assessments]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve assessments.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to retrieve assessments.'], 500);
        }
    }


    // Store a newly created resource in storage.
    // @param  StoreAssessmentRequest  $request
    // @return JsonResponse

    public function store(StoreAssessmentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $orgId = $this->resolveOrganizationId($request, $validated);

            $assessment = Assessment::create([
                'name' => $validated['name'],
                'user_id' => $request->user()->id,
                'organization_id' => $orgId,
            ]);

            if (!empty($validated['question_ids'])) {
                $assessment->questions()->attach($validated['question_ids']);
            }

            return response()->json(['assessment' => $assessment->load('questions')], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create assessment.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create assessment.'], 500);
        }
    }


    // Get a summary of an assessment's answers.
    // @param  int  $id
    // @return JsonResponse

    public function summary($id): JsonResponse
    {
        try {
            $assessment = Assessment::findOrFail($id);
            $tokens = DB::table('assessment_answer_tokens')->where('assessment_id', $id)->get();
            $answers = DB::table('assessment_question_answers')->where('assessment_id', $id)->get();

            $members = $this->getMembersFromTokens($tokens);
            $this->attachAnswersToMembers($members, $answers);

            $summaryCounts = $this->calculateSummaryCounts($tokens);

            return response()->json([
                'assessment' => [
                    'id' => $assessment->id,
                    'name' => $assessment->name,
                ],
                'members' => array_values($members),
                'summary' => $summaryCounts,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Assessment not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to generate assessment summary.', ['assessment_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate assessment summary.'], 500);
        }
    }


    // Get member details from assessment tokens.
    // @param  \Illuminate\Support\Collection  $tokens
    // @return array

    private function getMembersFromTokens($tokens): array
    {
        $members = [];
        foreach ($tokens as $token) {
            $member = Member::withTrashed()->find($token->member_id);
            $memberName = 'Unknown';
            if ($member) {
                $fullName = trim("{$member->first_name} {$member->last_name}");
                $memberName = !empty($fullName) ? $fullName : $member->email;
            }
            $members[$token->member_id] = [
                'member_id' => $token->member_id,
                'name' => $memberName,
                'answers' => [],
            ];
        }
        return $members;
    }


    // Attach answers to their respective members.
    // @param  array  $members
    // @param  \Illuminate\Support\Collection  $answers

    private function attachAnswersToMembers(array &$members, $answers): void
    {
        $questionIds = $answers->pluck('organization_assessment_question_id')->unique();
        $questions = DB::table('organization_assessment_questions')->whereIn('id', $questionIds)->pluck('text', 'id');

        foreach ($answers as $answer) {
            if (isset($members[$answer->member_id])) {
                $members[$answer->member_id]['answers'][] = [
                    'question' => $questions[$answer->organization_assessment_question_id] ?? '',
                    'answer' => $answer->answer,
                    'assessment_question_id' => $answer->assessment_question_id,
                    'organization_assessment_question_id' => $answer->organization_assessment_question_id,
                ];
            }
        }
    }


    // Calculate summary counts for an assessment.
    // @param  \Illuminate\Support\Collection  $tokens
    // @return array

    private function calculateSummaryCounts($tokens): array
    {
        return [
            'total_sent' => $tokens->count(),
            'submitted' => $tokens->where('used', 1)->count(),
            'pending' => $tokens->where('used', 0)->count(),
        ];
    }


    // Resolve the organization ID from the request.
    // @param  StoreAssessmentRequest  $request
    // @param  array  $validated
    // @return int|null

    private function resolveOrganizationId(StoreAssessmentRequest $request, array $validated): ?int
    {
        if (isset($validated['organization_id'])) {
            return $validated['organization_id'];
        }

        if ($request->user()) {
            $organization = Organization::where('user_id', $request->user()->id)->first();
            return $organization ? $organization->id : null;
        }

        return null;
    }
}
