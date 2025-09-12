<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|same:password',
            'phone' => 'required|regex:/^[6-9]\d{9}$/',
            'find_us' => 'nullable|string|max:255',
            'organization_name' => 'nullable|string|max:255',
            'organization_size' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|integer|exists:countries,id',
            'state' => 'nullable|integer|exists:states,id',
            'city' => 'nullable|integer|exists:cities,id',
           'zip' => 'required|regex:/^[1-9][0-9]{5}$/',
        ];
    }
}