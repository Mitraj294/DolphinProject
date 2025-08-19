<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'sender_id',
        'scheduled_at',
        'sent_at',
    ];

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'announcement_organization');
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'announcement_admin', 'announcement_id', 'admin_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'announcement_group');
    }
}
