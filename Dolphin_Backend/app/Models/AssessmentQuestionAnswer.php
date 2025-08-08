<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentQuestionAnswer extends Model
{
    protected $table = 'assessment_question_answers';
    protected $fillable = [
        'assessment_id',
        'question_id',
        'member_id',
        'group_id',
        'answer',
    ];
}
