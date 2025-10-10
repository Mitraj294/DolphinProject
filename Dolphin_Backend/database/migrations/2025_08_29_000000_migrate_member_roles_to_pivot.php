<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // If the members table or the legacy column doesn't exist, nothing to do.
        if (!Schema::hasTable('members') || !Schema::hasColumn('members', 'member_role')) {
            return;
        }

        // If either the member_roles table or the pivot table doesn't exist, drop the legacy column and exit.
        if (!Schema::hasTable('member_roles') || !Schema::hasTable('member_member_role')) {
            Schema::table('members', function (Blueprint $table) {
                if (Schema::hasColumn('members', 'member_role')) {
                    $table->dropColumn('member_role');
                }
            });
            return;
        }

        // Migrate legacy textual role values into the normalized tables.
        $this->migrateLegacyRolesToPivot();
    }

    /**
     * Return members that have a non-empty legacy member_role value.
     * Extracted to reduce cognitive complexity in up().
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getMembersWithLegacyRole()
    {
        return DB::table('members')
            ->select('id', 'member_role')
            ->whereNotNull('member_role')
            ->where('member_role', '<>', '')
            ->get();
    }

    /**
     * Do the actual migration of role names into member_roles and pivot.
     */
    protected function migrateLegacyRolesToPivot(): void
    {
        $members = $this->getMembersWithLegacyRole();
        $roleNameToId = [];

        foreach ($members as $m) {
            $name = trim((string) $m->member_role);
            if ($name === '') {
                continue;
            }

            if (!isset($roleNameToId[$name])) {
                $existing = DB::table('member_roles')->where('name', $name)->first();
                if ($existing) {
                    $roleNameToId[$name] = $existing->id;
                } else {
                    $roleNameToId[$name] = DB::table('member_roles')->insertGetId([
                        'name' => $name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $roleId = $roleNameToId[$name];

            $exists = DB::table('member_member_role')
                ->where('member_id', $m->id)
                ->where('member_role_id', $roleId)
                ->exists();

            if (!$exists) {
                DB::table('member_member_role')->insert([
                    'member_id' => $m->id,
                    'member_role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Drop the legacy column once migration is attempted.
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'member_role')) {
                $table->dropColumn('member_role');
            }
        });
    }

    public function down(): void
    {
        // Recreate the member_role column (nullable) and populate with first linked role name
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'member_role')) {
                $table->string('member_role')->nullable();
            }
        });

        // For each member, set member_role to the first associated role name (if any)
        $members = DB::table('members')->select('id')->get();
        foreach ($members as $m) {
            $role = DB::table('member_member_role')
                ->join('member_roles', 'member_member_role.member_role_id', '=', 'member_roles.id')
                ->where('member_member_role.member_id', $m->id)
                ->orderBy('member_member_role.id')
                ->limit(1)
                ->value('member_roles.name');
            if ($role) {
                DB::table('members')->where('id', $m->id)->update(['member_role' => $role]);
            }
        }
    }
};
