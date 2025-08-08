<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentAnswerToken extends Model
{
    protected $fillable = [
        'assessment_id',
        'member_id',
        'group_id',
        'token',
        'expires_at',
        'used'
    ];

    public $timestamps = true;
}
