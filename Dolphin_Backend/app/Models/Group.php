<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['name', 'organization_id', 'user_id'];

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
