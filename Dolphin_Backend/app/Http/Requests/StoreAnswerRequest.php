<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAnswerRequest extends FormRequest
{

    //Determine if the user is authorized to make this request.
    //@return bool

    public function authorize(): bool
    {
        // Only authenticated users can submit answers.
        return Auth::check();
    }


    //Get the validation rules that apply to the request.
    //@return array

    public function rules(): array
    {
        return [
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'required|array',
        ];
    }
}
