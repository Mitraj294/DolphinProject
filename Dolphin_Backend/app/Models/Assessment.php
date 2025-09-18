<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'organization_id',
    ];

    public function questions()
    {
        return $this->belongsToMany(OrganizationAssessmentQuestion::class, 'assessment_question', 'assessment_id', 'question_id');
    }

    public function assessmentQuestions()
    {
        return $this->hasMany(AssessmentQuestion::class, 'assessment_id');
    }
}
