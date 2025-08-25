<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drops a set of organization-level columns that will be sourced from
     * the owning user / user_details instead.
     *
     * NOTE: run only after ensuring application no longer writes to these
     * columns (we added fallback accessors in the model to avoid breakage).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'org_name')) {
                $table->dropColumn(['org_name']);
            }
            if (Schema::hasColumn('organizations', 'size')) {
                $table->dropColumn(['size']);
            }
            if (Schema::hasColumn('organizations', 'source')) {
                $table->dropColumn(['source']);
            }
            if (Schema::hasColumn('organizations', 'address1')) {
                $table->dropColumn(['address1']);
            }
            if (Schema::hasColumn('organizations', 'address2')) {
                $table->dropColumn(['address2']);
            }
            // Drop foreign keys first (if they exist) to avoid SQL errors
            try {
                if (Schema::hasColumn('organizations', 'country_id')) {
                    $table->dropForeign(['country_id']);
                }
            } catch (\Exception $e) {
                // ignore
            }
            try {
                if (Schema::hasColumn('organizations', 'state_id')) {
                    $table->dropForeign(['state_id']);
                }
            } catch (\Exception $e) {
                // ignore
            }
            try {
                if (Schema::hasColumn('organizations', 'city_id')) {
                    $table->dropForeign(['city_id']);
                }
            } catch (\Exception $e) {
                // ignore
            }
            if (Schema::hasColumn('organizations', 'country_id')) {
                $table->dropColumn(['country_id']);
            }
            if (Schema::hasColumn('organizations', 'state_id')) {
                $table->dropColumn(['state_id']);
            }
            if (Schema::hasColumn('organizations', 'city_id')) {
                $table->dropColumn(['city_id']);
            }
            if (Schema::hasColumn('organizations', 'zip')) {
                $table->dropColumn(['zip']);
            }
            if (Schema::hasColumn('organizations', 'main_contact')) {
                $table->dropColumn(['main_contact']);
            }
            if (Schema::hasColumn('organizations', 'admin_email')) {
                $table->dropColumn(['admin_email']);
            }
            if (Schema::hasColumn('organizations', 'admin_phone')) {
                $table->dropColumn(['admin_phone']);
            }
        });
    }

    /**
     * Reverse the migrations.
     * Re-creates columns with sensible defaults (nullable).
     * @return void
     */
    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'org_name')) {
                $table->string('org_name')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'size')) {
                $table->string('size')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'source')) {
                $table->string('source')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'address1')) {
                $table->string('address1')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'address2')) {
                $table->string('address2')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'state_id')) {
                $table->unsignedBigInteger('state_id')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'zip')) {
                $table->string('zip')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'main_contact')) {
                $table->string('main_contact')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'admin_email')) {
                $table->string('admin_email')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'admin_phone')) {
                $table->string('admin_phone')->nullable();
            }
        });
    }
};
