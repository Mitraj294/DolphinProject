<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Organization extends Model
{
    protected $fillable = ['org_name', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
