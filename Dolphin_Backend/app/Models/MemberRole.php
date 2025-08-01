<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
      
    ];

    public function members()
    {
        return $this->belongsToMany(Member::class, 'member_member_role');
    }
}
