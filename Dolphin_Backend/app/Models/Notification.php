<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'sender_id',
        'organization_ids',
        'admin_ids',
        'group_ids',
        'scheduled_at',
        'sent_at',
    ];
}
