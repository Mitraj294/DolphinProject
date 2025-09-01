<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait OwnedByActiveUserScope
{
    /**
     * Scope a query to only include records whose owner user is not soft-deleted.
     */
    public function scopeOwnedByActiveUser(Builder $query)
    {
        return $query->whereHas('user', function ($q) {
            $q->whereNull('deleted_at');
        });
    }

    /**
     * Optionally apply the global scope when the model boots.
     */
    public static function bootOwnedByActiveUserScope()
    {
        static::addGlobalScope('owned_by_active_user', function (Builder $builder) {
            $builder->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
            });
        });
    }
}
