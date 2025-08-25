<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$announcements = App\Models\Announcement::with(['groups','organizations'])->get();
$out = [];
foreach ($announcements as $a) {
    $hasGroups = $a->groups()->exists();
    $hasOrgs = $a->organizations()->exists();
    if ($hasGroups && !$hasOrgs) {
        // check notifications that reference this announcement_id in JSON payload
        $rows = \DB::table('notifications')->whereRaw("JSON_EXTRACT(data,'$.announcement_id') = ?", [(string)$a->id])->get();
        $out[] = [
            'announcement_id' => $a->id,
            'body' => $a->body,
            'sent_at' => (string)$a->sent_at,
            'group_ids' => $a->groups()->pluck('groups.id')->toArray(),
            'notification_count' => count($rows),
            'notifications' => array_map(function($r){ return ['id'=>$r->id,'notifiable_type'=>$r->notifiable_type,'notifiable_id'=>$r->notifiable_id,'created_at'=>$r->created_at]; }, $rows->toArray())
        ];
    }
}

echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
