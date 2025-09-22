<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create a group-only announcement and run the job directly
$group = App\Models\Group::find(32);
if (!$group) { echo "Group 32 not found\n"; exit(1); }
$a = App\Models\Announcement::create(['body'=>'Group-only test '.time(),'sender_id'=>1,'scheduled_at'=>null,'sent_at'=>null]);
$a->groups()->attach([$group->id]);

echo "Created announcement id: {$a->id}\n";

// Run job handle
$job = new App\Jobs\SendScheduledAnnouncementJob($a);
$job->handle();

// Check notifications for this announcement
$rows = DB::table('notifications')->whereRaw("JSON_EXTRACT(data,'$.announcement_id') = ?", [ (string)$a->id ])->get();
echo "Notifications rows for announcement {$a->id}:\n";
echo json_encode($rows->toArray(), JSON_PRETTY_PRINT) . "\n";
