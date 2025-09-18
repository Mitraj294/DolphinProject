<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\AssessmentAnswerToken;
use Carbon\Carbon;

class SubmitAssessmentAnswersRequest extends FormRequest
{

    //Determine if the user is authorized to make this request.

    //@return bool

    public function authorize()
    {
        $token = $this->route('token');

        // Check if the token is valid, not used, and not expired before proceeding.
        return AssessmentAnswerToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }



    //@return array

    public function rules()
    {
        return [
            'answers' => 'required|array',
            'answers.*.assessment_question_id' => 'required|integer|exists:assessment_question,id',
            'answers.*.answer' => 'required|string',
        ];
    }
}
