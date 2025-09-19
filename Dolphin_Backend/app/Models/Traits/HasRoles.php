<?php

namespace App\Models\Traits;

use App\Models\Role;
use Illuminate\Support\Collection;

trait HasRoles
{
    /**
     * Get the user's roles.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Check if the user has a given role name.
     */
    public function hasRole(string $roleName): bool
    {
        // This is from your existing user model
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(...$roles): bool
    {
        // Accept either: hasAnyRole('admin', 'editor') or hasAnyRole(['admin', 'editor'])
        if (count($roles) === 1 && is_array($roles[0])) {
            $roles = $roles[0];
        }

        // If no roles were provided, return false early
        if (empty($roles)) {
            return false;
        }

        // Efficiently checks if the user's roles relation contains any of the provided names
        return $this->roles()->whereIn('name', $roles)->exists();
    }
}