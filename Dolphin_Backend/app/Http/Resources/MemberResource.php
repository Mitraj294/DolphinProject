<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{

    //Transform the resource into an array.
    //@param  \Illuminate\Http\Request  $request
    //@return array
     
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'group_ids' => $this->whenLoaded('groups', $this->groups->pluck('id')->toArray()),
            'member_role' => $this->member_role,
            'member_role_ids' => $this->whenLoaded('memberRoles', $this->memberRoles->pluck('id')->toArray()),
            'memberRoles' => $this->whenLoaded('memberRoles', $this->memberRoles->map->only(['id', 'name'])->toArray()),
            'member_role_names' => $this->whenLoaded('memberRoles', $this->memberRoles->pluck('name')->toArray()),
        ];
    }
}
