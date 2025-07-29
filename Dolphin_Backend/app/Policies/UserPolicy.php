<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the given user can impersonate another user.
     *
     * @param  \App\Models\User  $user      The currently authenticated user
     * @param  \App\Models\User  $targetUser The user being impersonated
     * @return bool
     */
    public function impersonate(User $user, User $targetUser): bool
    {
        // A superadmin can impersonate any user, except another superadmin or themselves.
        return $user->isSuperAdmin() && !$targetUser->isSuperAdmin() && $user->id !== $targetUser->id;
    }
}
