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
  

        // Only announcements that haven't been dispatched yet
        $announcements = \App\Models\Announcement::whereNull('sent_at')
            ->whereNull('dispatched_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($announcements->isEmpty()) {
          
            $this->info('No announcements to dispatch');
            return 0;
        }

        foreach ($announcements as $announcement) {
            // Use atomic update to prevent race conditions
            $affected = \App\Models\Announcement::whereNull('sent_at')
                ->whereNull('dispatched_at')
                ->where('id', $announcement->id)
                ->update(['dispatched_at' => now()]);
            
            if ($affected === 0) {
                // Another process already dispatched this announcement
                $this->info('Announcement already dispatched: '.$announcement->id);
                continue;
            }
            
            try {
                dispatch(new \App\Jobs\SendScheduledAnnouncementJob($announcement));
                
                Log::info('[DispatchAnnouncements] dispatched job', ['announcement_id' => $announcement->id]);
                $this->info('Dispatched announcement: '.$announcement->id);
            } catch (\Exception $e) {
                // If dispatching fails, reset dispatched_at to allow retry
                $announcement->update(['dispatched_at' => null]);
                
                Log::error('[DispatchAnnouncements] failed to dispatch', ['announcement_id' => $announcement->id, 'error' => $e->getMessage()]);
                $this->error('Failed to dispatch announcement: '.$announcement->id);
            }
        }

      
        return 0;
    }
}
