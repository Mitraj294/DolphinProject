<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * An announcement to be sent to users, groups, or organizations.
 */
class Announcement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'body',
        'sender_id',
        'scheduled_at',
        'dispatched_at',
        'sent_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user who sent the announcement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * The users that are direct recipients of this announcement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        // The project migrations create an `announcement_admin` pivot table
        // (with columns announcement_id and admin_id) for direct user recipients.
        // Use that pivot and map admin_id -> users.id.
        return $this->belongsToMany(User::class, 'announcement_admin', 'announcement_id', 'admin_id');
    }

    /**
     * Backwards-compatible alias for admin recipients.
     * Some parts of the codebase call ->admins(), others call ->users().
     * Keep both to avoid undefined relationship errors.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_admin', 'announcement_id', 'admin_id');
    }

    /**
     * The organizations that are recipients of this announcement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'announcement_organization');
    }

    /**
     * The groups that are recipients of this announcement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'announcement_group');
    }
}
