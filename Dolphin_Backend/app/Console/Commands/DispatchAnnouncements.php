<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DispatchAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announcements:dispatch-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch pending scheduled announcements as jobs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
  

        $announcements = \App\Models\Announcement::whereNull('sent_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($announcements->isEmpty()) {
          
            $this->info('No announcements to dispatch');
            return 0;
        }

        foreach ($announcements as $announcement) {
            try {
                dispatch(new \App\Jobs\SendScheduledAnnouncementJob($announcement));
                // NOTE: do not mark `sent_at` here — the job itself sets `sent_at` when it completes.
                // Marking `sent_at` before the job runs causes the job to skip sending (it checks sent_at at start).
                // If you need to prevent duplicate dispatches, add a separate `dispatched_at` column and set it here.
                Log::info('[DispatchAnnouncements] dispatched job', ['announcement_id' => $announcement->id]);
                $this->info('Dispatched announcement: '.$announcement->id);
            } catch (\Exception $e) {
                Log::error('[DispatchAnnouncements] failed to dispatch', ['announcement_id' => $announcement->id, 'error' => $e->getMessage()]);
                $this->error('Failed to dispatch announcement: '.$announcement->id);
            }
        }

      
        return 0;
    }
}
