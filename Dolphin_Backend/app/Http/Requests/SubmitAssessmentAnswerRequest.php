<?php

namespace App\Http\Requests;

use App\Models\AssessmentAnswerLink;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAssessmentAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $link = AssessmentAnswerLink::where('token', $this->route('token'))->first();

        if (!$link || $link->completed) {
            return false;
        }

        // Attach the link model to the request object so we don't have to query for it again in the controller.
        $this->merge(['assessment_answer_link' => $link]);

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'answers' => 'required|array|min:1',
            'answers.*' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'answers.required' => 'No answers were provided.',
            'answers.array' => 'The answers must be in the correct format.',
            'answers.min' => 'At least one answer must be submitted.',
        ];
    }
}
