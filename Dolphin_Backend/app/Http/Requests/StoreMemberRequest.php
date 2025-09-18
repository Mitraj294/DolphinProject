<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Only allow users with the 'organizationadmin' role to create members.
        return $this->user()->hasRole('organizationadmin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:members,email,NULL,id,deleted_at,NULL',
            'phone'         => 'required|string|regex:/^[6-9]\d{9}$/',
            'member_role'   => 'required|array|min:1',
            'member_role.*' => 'integer|exists:member_roles,id',
            'group_ids'     => 'sometimes|array',
            'group_ids.*'   => 'integer|exists:groups,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already registered with another member.',
            'phone.unique' => 'This phone number is already registered with another member.',
            'phone.regex' => 'Phone number must be a valid Indian mobile number (10 digits starting with 6-9).',
            'member_role.required' => 'At least one role must be selected for the member.',
            'member_role.*.exists' => 'One or more selected roles are invalid.',
            'group_ids.*.exists' => 'One or more selected groups are invalid.',
        ];
    }
}

