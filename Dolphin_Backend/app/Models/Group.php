<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'organization_id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    // Add relationship to get all users in the group
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_member', 'group_id', 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'group_member')->withTimestamps();
    }
}
