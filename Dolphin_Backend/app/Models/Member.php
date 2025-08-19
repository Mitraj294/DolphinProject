<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory, \Illuminate\Notifications\Notifiable;
    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'member_role', 'organization_id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_member')->withTimestamps();
    }

    public function memberRoles()
    {
        return $this->belongsToMany(MemberRole::class, 'member_member_role');
    }
}
