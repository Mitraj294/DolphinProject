<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        \Log::info('[DispatchAnnouncements] started');

        $announcements = \App\Models\Announcement::whereNull('sent_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($announcements->isEmpty()) {
            \Log::info('[DispatchAnnouncements] no announcements to dispatch');
            $this->info('No announcements to dispatch');
            return 0;
        }

        foreach ($announcements as $announcement) {
            try {
                dispatch(new \App\Jobs\SendScheduledAnnouncementJob($announcement));
                \Log::info('[DispatchAnnouncements] dispatched job', ['announcement_id' => $announcement->id]);
                $this->info('Dispatched announcement: '.$announcement->id);
            } catch (\Exception $e) {
                \Log::error('[DispatchAnnouncements] failed to dispatch', ['announcement_id' => $announcement->id, 'error' => $e->getMessage()]);
                $this->error('Failed to dispatch announcement: '.$announcement->id);
            }
        }

        \Log::info('[DispatchAnnouncements] completed');
        return 0;
    }
}
