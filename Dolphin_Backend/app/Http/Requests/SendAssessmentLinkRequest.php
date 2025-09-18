<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendAssessmentLinkRequest extends FormRequest
{

    // Determine if the user is authorized to make this request.
    // @return bool

    public function authorize()
    {
        // For now, allow any authenticated user to send a link.
        // You can add more specific authorization logic here if needed.
        return true;
    }


    // Get the validation rules that apply to the request.
    // @return array

    public function rules()
    {
        return [
            'assessment_id' => 'required|exists:assessments,id',
            'member_id' => 'required|exists:members,id',
            'email' => 'required|email',
            'group_id' => 'nullable|exists:groups,id',
        ];
    }
}
