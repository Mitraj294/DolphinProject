<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentScheduleRequest extends FormRequest
{

    // Determine if the user is authorized to make this request.
    // @return bool

    public function authorize()
    {
        // Set to true to allow anyone to make this request.
        // You can add your own authorization logic here if needed.
        return true;
    }


    // Get the validation rules that apply to the request.
    // @return array

    public function rules()
    {
        return [
            'assessment_id' => 'required|exists:assessments,id',
            'date' => 'required|date',
            'time' => 'required',
            'send_at' => 'sometimes|date',
            'timezone' => 'sometimes|string',
            'group_ids' => 'sometimes|array',
            'group_ids.*' => 'exists:groups,id',
            'member_ids' => 'sometimes|array',
            'member_ids.*' => 'exists:members,id',
        ];
    }
}
