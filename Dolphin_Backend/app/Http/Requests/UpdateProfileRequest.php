<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    //Determine if the user is authorized to make this request.

    public function authorize(): bool
    {
        return true;
    }

    //Get the validation rules that apply to the request.

    //@return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'user.email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId . ',id,deleted_at,NULL',
            'user_details.first_name' => 'sometimes|nullable|string|max:255',
            'user_details.last_name' => 'sometimes|nullable|string|max:255',
            'user_details.phone' => 'sometimes|nullable|regex:/^[6-9]\d{9}$/',
            'user_details.country' => 'sometimes|nullable',
        ];
    }
}
