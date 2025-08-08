<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledEmail extends Model
{
    protected $fillable = [
        'recipient_email',
        'subject',
        'body',
        'send_at',
        'sent',
    ];
}
