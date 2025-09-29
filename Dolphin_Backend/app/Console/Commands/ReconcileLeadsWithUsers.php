<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconcileLeadsWithUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:reconcile-with-users {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile leads with users by email and mark leads as Registered when a user exists';

    public function handle()
    {
        $result = 0;
        $dryRun = $this->option('dry-run');

        $this->info('Starting leads <> users reconciliation' . ($dryRun ? ' (dry-run)' : ''));

        // Find lead rows that match users by email but are not marked Registered
        $rows = DB::select(
            "SELECT l.id AS lead_id, l.email AS lead_email, l.status AS lead_status, l.registered_at AS lead_registered_at, u.id AS user_id, u.created_at AS user_created_at
             FROM leads l
             JOIN users u ON LOWER(u.email) = LOWER(l.email)
             WHERE (l.status IS NULL OR LOWER(l.status) <> 'registered')"
        );

        $count = count($rows);
        $this->info("Found {$count} lead(s) with matching user but not marked Registered.");

        if ($count > 0) {
            foreach ($rows as $r) {
                $this->line("Lead ID: {$r->lead_id}, email: {$r->lead_email} -> user_id: {$r->user_id}");
            }

            if (!$dryRun) {
                // Perform safe update using a join
                DB::beginTransaction();
                try {
                    $updated = DB::update(
                        "UPDATE leads l
                         JOIN users u ON LOWER(u.email) = LOWER(l.email)
                         SET l.status = 'Registered', l.registered_at = COALESCE(l.registered_at, u.created_at)
                         WHERE (l.status IS NULL OR LOWER(l.status) <> 'registered')"
                    );
                    DB::commit();

                    $this->info("Updated {$updated} lead(s).");
                    Log::info('ReconcileLeadsWithUsers: updated leads count', ['updated' => $updated]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error('Failed to update leads: ' . $e->getMessage());
                    Log::error('ReconcileLeadsWithUsers failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    $result = 1;
                }
            } else {
                $this->info('Dry-run: no updates applied.');
            }
        }

        return $result;
    }
}
