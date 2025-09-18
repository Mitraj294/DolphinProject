<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{

    // Determine if the user is authorized to make this request.
    // @return bool

    public function authorize(): bool
    {
        // Only an organization admin can create new groups.
        return $this->user()->hasRole('organizationadmin');
    }


    // Get the validation rules that apply to the request.
    // @return array

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:groups,name,NULL,id,deleted_at,NULL',
            'member_ids' => 'sometimes|array',
            'member_ids.*' => 'integer|exists:members,id',
        ];
    }
}
