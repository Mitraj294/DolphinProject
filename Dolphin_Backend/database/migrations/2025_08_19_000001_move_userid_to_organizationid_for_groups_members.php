<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add organization_id to groups and members, nullable at first
        Schema::table('groups', function (Blueprint $table) {
            if (!Schema::hasColumn('groups', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            }
        });

        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            }
        });

        // Backfill organization_id using existing user_id -> organizations.user_id mapping
        // This is best-effort: if Organization with user_id exists, map it.
        if (Schema::hasTable('groups') && Schema::hasTable('organizations')) {
            $groups = DB::table('groups')->get();
            foreach ($groups as $g) {
                if (isset($g->user_id) && $g->user_id) {
                    $org = DB::table('organizations')->where('user_id', $g->user_id)->first();
                    if ($org) {
                        DB::table('groups')->where('id', $g->id)->update(['organization_id' => $org->id]);
                    }
                }
            }
        }

        if (Schema::hasTable('members') && Schema::hasTable('organizations')) {
            $members = DB::table('members')->get();
            foreach ($members as $m) {
                if (isset($m->user_id) && $m->user_id) {
                    $org = DB::table('organizations')->where('user_id', $m->user_id)->first();
                    if ($org) {
                        DB::table('members')->where('id', $m->id)->update(['organization_id' => $org->id]);
                    }
                }
            }
        }

        // Optionally drop old user_id columns after backfill
        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }

    public function down(): void
    {
        // Recreate user_id as nullable and remove organization_id
        Schema::table('groups', function (Blueprint $table) {
            if (!Schema::hasColumn('groups', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            if (Schema::hasColumn('groups', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });

        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            if (Schema::hasColumn('members', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });
    }
};
