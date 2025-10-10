<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // This migration is MySQL-specific for aligning with local dolphin_db
        // PostgreSQL doesn't need these changes as it was created from scratch
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql') {
            return; // Skip for non-MySQL databases
        }

        // Drop test-only columns from organizations table to match dolphin_db
        if (Schema::hasTable('organizations')) {
            $columnsToCheck = [
                'source', 'address1', 'address2', 
                'country_id', 'state_id', 'city_id', 'zip',
                'main_contact', 'admin_email', 'admin_phone'
            ];

            $columnsToDrop = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('organizations', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                // Drop foreign keys first if they exist
                if (in_array('country_id', $columnsToDrop) || in_array('state_id', $columnsToDrop) || in_array('city_id', $columnsToDrop)) {
                    try {
                        Schema::table('organizations', function (Blueprint $table) use ($columnsToDrop) {
                            if (in_array('country_id', $columnsToDrop)) {
                                $table->dropForeign(['country_id']);
                            }
                            if (in_array('state_id', $columnsToDrop)) {
                                $table->dropForeign(['state_id']);
                            }
                            if (in_array('city_id', $columnsToDrop)) {
                                $table->dropForeign(['city_id']);
                            }
                        });
                    } catch (\Exception $e) {
                        // Foreign keys may not exist
                    }
                }

                // Drop columns
                Schema::table('organizations', function (Blueprint $table) use ($columnsToDrop) {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }
    }

    public function down(): void
    {
        // This migration is MySQL-specific
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql') {
            return; // Skip for non-MySQL databases
        }

        // Re-add columns if needed (reverse migration)
        if (Schema::hasTable('organizations')) {
            Schema::table('organizations', function (Blueprint $table) {
                if (!Schema::hasColumn('organizations', 'source')) {
                    $table->string('source')->nullable()->after('organization_size');
                }
                if (!Schema::hasColumn('organizations', 'address1')) {
                    $table->string('address1')->nullable()->after('source');
                }
                if (!Schema::hasColumn('organizations', 'address2')) {
                    $table->string('address2')->nullable()->after('address1');
                }
                if (!Schema::hasColumn('organizations', 'country_id')) {
                    $table->unsignedBigInteger('country_id')->nullable()->after('address2');
                }
                if (!Schema::hasColumn('organizations', 'state_id')) {
                    $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
                }
                if (!Schema::hasColumn('organizations', 'city_id')) {
                    $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
                }
                if (!Schema::hasColumn('organizations', 'zip')) {
                    $table->string('zip')->nullable()->after('city_id');
                }
                if (!Schema::hasColumn('organizations', 'main_contact')) {
                    $table->string('main_contact')->nullable()->after('sales_person_id');
                }
                if (!Schema::hasColumn('organizations', 'admin_email')) {
                    $table->string('admin_email')->nullable()->after('main_contact');
                }
                if (!Schema::hasColumn('organizations', 'admin_phone')) {
                    $table->string('admin_phone')->nullable()->after('admin_email');
                }
            });
        }
    }
};
