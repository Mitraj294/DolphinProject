<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestLink extends Model
{
    protected $table = 'guest_links';

    protected $fillable = [
        'code', 'user_id', 'lead_id', 'meta', 'expires_at', 'used_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
