<?php

namespace App\Http\Resources;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var \App\Models\Announcement
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'body' => $this->resource->body,
            'sender_id' => $this->resource->sender_id,
            'scheduled_at' => $this->resource->scheduled_at?->toIso8601String(),
            'sent_at' => $this->resource->sent_at?->toIso8601String(),
            'created_at' => $this->resource->created_at->toIso8601String(),
            'recipients' => [
                'users' => UserResource::collection($this->whenLoaded('users')),
                'groups' => GroupResource::collection($this->whenLoaded('groups')),
                'organizations' => OrganizationResource::collection($this->whenLoaded('organizations')),
            ],
        ];
    }
}

// NOTE: You would typically create separate, smaller resource files for related models.
// For simplicity, they are included here. In a real project, put these in their own files
// like `app/Http/Resources/UserResource.php`.

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
        ];
    }
}

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->organization_name,
        ];
    }
}
