<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentQuestionAnswer extends Model
{
    protected $table = 'assessment_question_answers';
    protected $fillable = [
        'assessment_id',
        'organization_assessment_question_id',
        'assessment_question_id',
        'member_id',
        'group_id',
        'answer',
    ];
}
