<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class UserObserver
{
    /**
     * Handle the User "deleted" event (soft delete).
     */
    public function deleted(User $user)
    {
        $now = Carbon::now();
        try {
            // Soft-delete full records where appropriate
            $softDeleteTables = [
                'organizations',
                'groups',
                'subscriptions',
                'user_details',
            ];
            foreach ($softDeleteTables as $t) {
                if (DB::getSchemaBuilder()->hasTable($t)) {
                    DB::table($t)
                        ->where('user_id', $user->id)
                        ->update(['deleted_at' => $now]);
                }
            }

            // Nullify ownership on tables we want to keep but dissociate
            $nullifyTables = [
                'answers',
                'assessments',
                'members',
                'sessions',
                'user_roles',
                'oauth_access_tokens',
                'oauth_auth_codes',
                'oauth_device_codes',
            ];
            foreach ($nullifyTables as $t) {
                if (DB::getSchemaBuilder()->hasTable($t) && DB::getSchemaBuilder()->hasColumn($t, 'user_id')) {
                    DB::table($t)->where('user_id', $user->id)->update(['user_id' => null]);
                }
            }
        } catch (\Exception $e) {
            // Log and continue — observer should not block app flow
            Log::error('UserObserver deleted error: ' . $e->getMessage());
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user)
    {
        // We will not automatically restore or reassign records that were nullified
        // because the original mapping may no longer exist. Manual restoration
        // should be performed by an admin if needed.
    }
}
