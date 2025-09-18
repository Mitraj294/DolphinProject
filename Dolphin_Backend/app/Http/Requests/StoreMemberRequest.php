<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{

//Determine if the user is authorized to make this request.
//@return bool

    public function authorize(): bool
    {
        return $this->user()->hasRole('organizationadmin');
    }


//Get the validation rules that apply to the request.
//@return array<string, mixed>

    public function rules(): array
    {
        return [
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:members,email,NULL,id,deleted_at,NULL',
            'phone'         => 'required|regex:/^[6-9]\d{9}$/',
            'member_role'   => 'required',
            'group_ids'     => 'sometimes|array',
            'group_ids.*'   => 'exists:groups,id',
        ];
    }
}
