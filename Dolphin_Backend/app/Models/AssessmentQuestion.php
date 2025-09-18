<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentQuestion extends Model
{
    use HasFactory;

    protected $table = 'assessment_question';
    protected $fillable = ['assessment_id', 'question_id'];


    // Get the organization assessment question associated with this entry.
    public function question(): BelongsTo
    {
        return $this->belongsTo(OrganizationAssessmentQuestion::class, 'question_id');
    }
}
