<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OAuth access tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting expired token cleanup...');

        // Get count of expired tokens before cleanup
        $expiredCount = DB::table('oauth_access_tokens')
            ->where('expires_at', '<', Carbon::now())
            ->count();

        if ($expiredCount === 0) {
            $this->info('No expired tokens found.');
            return 0;
        }

        $this->info("Found {$expiredCount} expired tokens.");

        // Ask for confirmation unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Do you want to delete {$expiredCount} expired tokens?")) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        // Delete expired tokens
        $deletedCount = DB::table('oauth_access_tokens')
            ->where('expires_at', '<', Carbon::now())
            ->delete();

        $this->info("Successfully deleted {$deletedCount} expired tokens.");

        // Also clean up revoked tokens older than 7 days
        $revokedCount = DB::table('oauth_access_tokens')
            ->where('revoked', 1)
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->count();

        if ($revokedCount > 0) {
            $this->info("Found {$revokedCount} old revoked tokens.");
            
            if ($this->option('force') || $this->confirm("Do you want to delete {$revokedCount} old revoked tokens?")) {
                $deletedRevokedCount = DB::table('oauth_access_tokens')
                    ->where('revoked', 1)
                    ->where('created_at', '<', Carbon::now()->subDays(7))
                    ->delete();
                
                $this->info("Successfully deleted {$deletedRevokedCount} old revoked tokens.");
            }
        }

        // Clean up orphaned refresh tokens
        $refreshTokensCount = DB::table('oauth_refresh_tokens')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('oauth_access_tokens')
                    ->whereRaw('oauth_refresh_tokens.access_token_id = oauth_access_tokens.id');
            })
            ->count();

        if ($refreshTokensCount > 0) {
            $this->info("Found {$refreshTokensCount} orphaned refresh tokens.");
            
            if ($this->option('force') || $this->confirm("Do you want to delete {$refreshTokensCount} orphaned refresh tokens?")) {
                $deletedRefreshCount = DB::table('oauth_refresh_tokens')
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('oauth_access_tokens')
                            ->whereRaw('oauth_refresh_tokens.access_token_id = oauth_access_tokens.id');
                    })
                    ->delete();
                
                $this->info("Successfully deleted {$deletedRefreshCount} orphaned refresh tokens.");
            }
        }

        $this->info('Token cleanup completed successfully!');
        return 0;
    }
}
