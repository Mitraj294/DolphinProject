<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = $argv[1] ?? null;
if (!$id) {
    echo "Usage: php inspect_announcement.php <id>\n";
    exit(1);
}

$a = App\Models\Announcement::find($id);
if (!$a) {
    echo "Announcement not found\n";
    exit(1);
}

$out = [
    'id' => $a->id,
    'groups' => $a->groups()->pluck('groups.id')->toArray(),
    'organizations' => $a->organizations()->pluck('organizations.id')->toArray(),
    'admins' => $a->admins()->pluck('users.id')->toArray(),
    'sent_at' => (string)$a->sent_at,
];

echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
