<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentSchedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'assessment_id',
        'date',
        'time',
        'group_ids',
        'member_ids',
    ];

    protected $casts = [
        'group_ids' => 'array',
        'member_ids' => 'array',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
