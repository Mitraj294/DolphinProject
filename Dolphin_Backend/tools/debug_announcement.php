<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Announcement;
$id = $argv[1] ?? 95;
$a = Announcement::with(['organizations','groups'])->find($id);
if(!$a){ echo "not found\n"; exit(1); }
$u = [];
$m = [];
$u = array_merge($u, $a->admins()->pluck('users.id')->toArray());
foreach($a->organizations as $org){
    try{ if(method_exists($org,'users')){ $ou = $org->users()->pluck('users.id')->toArray(); } else { $ou = []; } } catch (\Exception $e) { $ou = []; }
    if(empty($ou) && isset($org->user_id) && $org->user_id) $ou[] = $org->user_id;
    $u = array_merge($u, $ou);
    $orgAdminEmail = $org->admin_email ?? ($org->user->email ?? null);
    if (!empty($orgAdminEmail)) $m[] = $orgAdminEmail;
}
if($a->groups && $a->groups->isNotEmpty()){
    foreach($a->groups as $g){
        try{ if(method_exists($g,'members')){ $m = array_merge($m, $g->members()->pluck('email')->toArray()); $mu = $g->members()->whereNotNull('user_id')->pluck('user_id')->toArray(); $u = array_merge($u, $mu); } } catch (\Exception $e) {}
    }
}
$u = array_values(array_unique(array_filter($u)));
$m = array_values(array_unique(array_filter($m)));

$out = [
    'announcement_id' => $a->id,
    'organizations' => [],
    'groups' => [],
    'user_ids' => $u,
    'member_emails' => $m,
];

foreach ($a->organizations as $org) {
    $out['organizations'][] = [
        'id' => $org->id,
    'organization_name' => $org->organization_name ?? null,
    'admin_email' => $org->admin_email ?? $org->user->email ?? null,
        'user_id' => $org->user_id ?? null,
    ];
}

foreach ($a->groups as $g) {
    $groupInfo = ['id' => $g->id, 'name' => $g->name ?? null, 'member_emails' => []];
    try {
        if (method_exists($g, 'members')) {
            $groupInfo['member_emails'] = $g->members()->pluck('email')->toArray();
        }
    } catch (\Exception $e) {
        $groupInfo['member_emails_error'] = $e->getMessage();
    }
    $out['groups'][] = $groupInfo;
}

echo json_encode($out, JSON_PRETTY_PRINT), "\n";
