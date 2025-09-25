<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupRoles extends Command
{
    protected $signature = 'roles:cleanup';
    protected $description = 'Keep only the most recent role record per user in user_roles';

    public function handle()
    {
        $this->info('Starting roles cleanup...');

        // Find the latest id for each user in user_roles
        $sub = DB::table('user_roles')
            ->select(DB::raw('MAX(created_at) as max_created_at, user_id'))
            ->groupBy('user_id');

        $rows = DB::table('user_roles as ur')
            ->joinSub($sub, 'latest', function ($join) {
                $join->on('ur.user_id', '=', 'latest.user_id')
                    ->on('ur.created_at', '=', 'latest.max_created_at');
            })
            ->select('ur.user_id', 'ur.role_id', 'ur.created_at')
            ->get();

        $kept = [];
        foreach ($rows as $r) {
            $kept[$r->user_id] = $r->created_at;
        }

        // Delete any rows where created_at is less than the kept timestamp for that user
        $deleted = 0;
        foreach ($kept as $userId => $createdAt) {
            $d = DB::table('user_roles')
                ->where('user_id', $userId)
                ->where('created_at', '<', $createdAt)
                ->delete();
            $deleted += $d;
        }

        $this->info("Roles cleanup complete. Deleted {$deleted} old rows.");
        return 0;
    }
}
