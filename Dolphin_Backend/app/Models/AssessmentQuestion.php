<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    protected $table = 'assessment_question';
    // Add fillable fields if needed
    // protected $fillable = ['assessment_id', 'question_id'];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function organizationAssessmentQuestion()
    {
        return $this->hasOne(\App\Models\OrganizationAssessmentQuestion::class, 'assessment_question_id');
    }
}

