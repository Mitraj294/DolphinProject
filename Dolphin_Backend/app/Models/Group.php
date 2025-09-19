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

    // Get users through the members relationship
    public function users()
    {
        return $this->members()->join('users', 'members.user_id', '=', 'users.id')
                   ->select('users.*');
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'group_member')->withTimestamps();
    }
}
