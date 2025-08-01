<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function questions()
    {
        return $this->belongsToMany(OrganizationAssessmentQuestion::class, 'assessment_question', 'assessment_id', 'question_id');
    }
}



