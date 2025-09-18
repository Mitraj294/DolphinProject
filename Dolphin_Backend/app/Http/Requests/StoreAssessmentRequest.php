<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Assuming any authenticated user can create an assessment.
        // Adjust the logic here based on your authorization needs.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'question_ids' => 'nullable|array',
            'question_ids.*' => 'integer|distinct|min:1',

            'questions' => 'nullable|array',
            'questions.*.text' => 'required_with:questions|string|max:1000',
            'questions.*.type' => 'required_with:questions|string|in:multiple_choice,text,checkbox',

            'questions.*.options' => 'nullable|array',
            'questions.*.options.*' => 'string',
        ];
    }
}
