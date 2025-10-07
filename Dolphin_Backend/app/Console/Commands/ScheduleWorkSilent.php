<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleWorkSilent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:work-silent {--sleep=60 : Number of seconds to sleep when no job is available}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the schedule worker (silent mode - only shows when tasks actually run)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting silent schedule worker...');
        
        $sleep = (int) $this->option('sleep');
        
        while (true) {
            $schedule = $this->laravel->make(Schedule::class);
            $events = $schedule->dueEvents($this->laravel);
            
            if (count($events) > 0) {
                $this->info('[' . now()->format('Y-m-d H:i:s') . '] Running ' . count($events) . ' scheduled task(s)');
                
                foreach ($events as $event) {
                    if (! $event->filtersPass($this->laravel)) {
                        continue;
                    }
                    
                    $this->info('Running: ' . $event->description);
                    $event->run($this->laravel);
                }
            }
            
            sleep($sleep);
        }
    }
}