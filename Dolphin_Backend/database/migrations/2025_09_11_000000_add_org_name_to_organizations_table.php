<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // By default, avoid adding optional organization columns so migrations produce the canonical dolphin_db schema.
        if (env('ALLOW_ADD_ORG_COLUMNS') !== '1') {
            return;
        }
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'organization_name')) {
                $table->string('organization_name')->nullable()->after('user_id');
            }
        });

        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'organization_size')) {
                // place organization_size immediately after organization_name per requested order
                $table->string('organization_size')->nullable()->after('organization_name');
            }
        });

        // Backfill organization_name from related user.user_details if available
        try {
            $rows = DB::table('organizations')
                ->leftJoin('users', 'users.id', '=', 'organizations.user_id')
                ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
                ->select('organizations.id as org_id', 'user_details.organization_name', 'user_details.organization_size')
                ->get();

            foreach ($rows as $r) {
                $update = [];
                if (!empty($r->organization_name)) {
                    $update['organization_name'] = $r->organization_name;
                }
                if (!empty($r->organization_size)) {
                    $update['organization_size'] = $r->organization_size;
                }
                if (!empty($update)) {
                    DB::table('organizations')->where('id', $r->org_id)->update($update);
                }
            }
        } catch (\Exception $e) {
            // don't fail migration for backfill issues; log and continue
            Log::warning('[migration] backfill organization_name failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'organization_name')) {
                $table->dropColumn('organization_name');
            }
        });
    }
};
