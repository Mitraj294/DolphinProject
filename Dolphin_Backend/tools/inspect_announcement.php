<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dolphin_backend\Bootstrap\App;
use App\Models\Announcement;

use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = $argv[1] ?? null;
if (!$id) {
    echo "Usage: php inspect_announcement.php <id>\n";
    exit(1);
}

$a = Announcement::find($id);
if (!$a) {
    echo "Announcement not found\n";
    exit(1);
}

$out = [
    'id' => $a->id,
    'groups' => $a->groups()->pluck('groups.id')->toArray(),
    'organizations' => $a->organizations()->pluck('organizations.id')->toArray(),
    'admins' => $a->admins()->pluck('users.id')->toArray(),
    'sent_at' => $a->sent_at ? (string)$a->sent_at : null,
];

echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
