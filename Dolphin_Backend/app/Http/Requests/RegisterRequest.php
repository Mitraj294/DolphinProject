<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{

    // Determine if the user is authorized to make this request.

    public function authorize(): bool
    {
        return true;
    }


    // Get the validation rules that apply to the request.
    // @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6|same:password',
            'phone' => 'required|regex:/^[6-9]\d{9}$/',
            'find_us' => 'required|string',
            'organization_name' => 'required|string|max:500',
            'organization_size' => 'required|string',
            'address' => 'required|string|max:500',
            'country' => 'required|integer|exists:countries,id',
            'state' => 'required|integer|exists:states,id',
            'city' => 'required|integer|exists:cities,id',
            'zip' => 'required|regex:/^[1-9][0-9]{5}$/',
        ];
    }
}
