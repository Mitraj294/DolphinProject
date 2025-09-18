<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
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
        $memberId = $this->route('member');

        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            'email'      => 'sometimes|email|unique:members,email,' . $memberId . ',id,deleted_at,NULL',
            'phone'      => 'nullable|string',
            'member_role'=> 'sometimes',
            'country'    => 'nullable|string',
        ];
    }
}
