<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'org_name')) {
                $table->string('org_name')->nullable()->after('user_id');
            }
        });

        // Backfill org_name from related user.user_details if available
        try {
            $rows = \DB::table('organizations')
                ->leftJoin('users', 'users.id', '=', 'organizations.user_id')
                ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
                ->select('organizations.id as org_id', 'user_details.org_name')
                ->whereNotNull('user_details.org_name')
                ->get();

            foreach ($rows as $r) {
                \DB::table('organizations')->where('id', $r->org_id)->update(['org_name' => $r->org_name]);
            }
        } catch (\Exception $e) {
            // don't fail migration for backfill issues; log and continue
            \Log::warning('[migration] backfill org_name failed', ['error' => $e->getMessage()]);
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
            if (Schema::hasColumn('organizations', 'org_name')) {
                $table->dropColumn('org_name');
            }
        });
    }
};
