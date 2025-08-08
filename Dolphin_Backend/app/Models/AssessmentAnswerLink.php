<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentAnswerLink extends Model
{
    protected $fillable = [
        'assessment_id',
        'member_id',
        'token',
        'completed',
        'completed_at',
    ];
}
