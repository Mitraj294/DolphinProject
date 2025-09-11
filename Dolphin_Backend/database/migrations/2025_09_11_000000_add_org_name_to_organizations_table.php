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
            if (!Schema::hasColumn('organizations', 'organization_name')) {
                $table->string('organization_name')->nullable()->after('user_id');
            }
        });

        // Backfill organization_name from related user.user_details if available
        try {
            $rows = \DB::table('organizations')
                ->leftJoin('users', 'users.id', '=', 'organizations.user_id')
                ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
                ->select('organizations.id as org_id', 'user_details.organization_name')
                ->whereNotNull('user_details.organization_name')
                ->get();

            foreach ($rows as $r) {
                \DB::table('organizations')->where('id', $r->org_id)->update(['organization_name' => $r->organization_name]);
            }
        } catch (\Exception $e) {
            // don't fail migration for backfill issues; log and continue
            \Log::warning('[migration] backfill organization_name failed', ['error' => $e->getMessage()]);
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
