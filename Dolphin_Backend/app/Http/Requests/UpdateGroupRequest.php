<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('organizationadmin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $groupId = $this->route('id');
        
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('groups', 'name')->ignore($groupId)->whereNull('deleted_at')
            ],
            'member_ids' => 'sometimes|array',
            'member_ids.*' => 'integer|exists:members,id',
        ];
    }
}