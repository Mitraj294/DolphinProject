<?php

namespace App\Services;

use App\Models\AssessmentAnswerToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AssessmentLinkService
{
    public function createAnswerToken(int $assessmentId, int $memberId, ?int $groupId): string
    {
        $token = Str::random(40);
        AssessmentAnswerToken::create([
            'assessment_id' => $assessmentId,
            'member_id' => $memberId,
            'group_id' => $groupId,
            'token' => $token,
            'expires_at' => Carbon::now()->addDays(7),
        ]);
        return $token;
    }

    public function generateFrontendLink(string $token, int $memberId, ?int $groupId): string
    {
        $frontendBase = rtrim(env('FRONTEND_URL', 'http://localhost:8080'), '/');
        $queryParams = http_build_query(array_filter(['group_id' => $groupId, 'member_id' => $memberId]));

        return "{$frontendBase}/assessment/answer/{$token}?{$queryParams}";
    }
}
