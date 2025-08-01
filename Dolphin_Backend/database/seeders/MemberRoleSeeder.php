<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MemberRole;

class MemberRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Manager'],
            ['name' => 'CEO'],
            ['name' => 'Owner'],
            ['name' => 'Support'],
        ];
        foreach ($roles as $role) {
            MemberRole::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
