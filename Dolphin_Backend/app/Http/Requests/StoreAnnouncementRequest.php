<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Assuming only authenticated users with specific roles can send announcements.
        // Adjust the role check as per your application's requirements.
        return $this->user() && $this->user()->hasAnyRole('superadmin', 'dolphinadmin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => 'required|string|min:1',
            'organization_ids' => 'nullable|array',
            'organization_ids.*' => 'required|integer|exists:organizations,id',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'required|integer|exists:groups,id',
            'user_ids' => 'nullable|array', // Renamed from admin_ids for clarity
            'user_ids.*' => 'required|integer|exists:users,id',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'body.required' => 'The announcement message body cannot be empty.',
            'scheduled_at.after_or_equal' => 'The scheduled time cannot be in the past.',
        ];
    }
}
