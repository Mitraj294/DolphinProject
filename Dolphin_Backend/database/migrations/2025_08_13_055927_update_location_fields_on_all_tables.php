<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        // Location FK action constant
        define('LOCATION_FK_ACTION', 'set null');

        // Small helper to add a nullable unsignedBigInteger column and optional FK
        $addLocationColumn = function (string $tableName, string $columnName, ?string $afterColumn, string $refTable) {
            if (! Schema::hasColumn($tableName, $columnName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columnName, $afterColumn) {
                    if ($afterColumn) {
                        $table->unsignedBigInteger($columnName)->nullable()->after($afterColumn);
                    } else {
                        $table->unsignedBigInteger($columnName)->nullable();
                    }
                });

                if (Schema::hasTable($refTable)) {
                    Schema::table($tableName, function (Blueprint $table) use ($columnName, $refTable) {
                        $table->foreign($columnName)->references('id')->on($refTable)->onDelete(LOCATION_FK_ACTION);
                    });
                }
            }
        };

        // LEADS: add/drop columns; add FKs only if referenced tables exist
        if (Schema::hasTable('leads')) {
            // remove old text fields if present
            Schema::table('leads', function (Blueprint $table) {
                if (Schema::hasColumn('leads', 'country')) { $table->dropColumn('country'); }
                if (Schema::hasColumn('leads', 'state')) { $table->dropColumn('state'); }
                if (Schema::hasColumn('leads', 'city')) { $table->dropColumn('city'); }
            });

            $addLocationColumn('leads', 'country_id', 'address', 'countries');
            $addLocationColumn('leads', 'state_id', 'country_id', 'states');
            $addLocationColumn('leads', 'city_id', 'state_id', 'cities');
        }

        // ORGANIZATIONS
        if (Schema::hasTable('organizations')) {
            Schema::table('organizations', function (Blueprint $table) {
                if (Schema::hasColumn('organizations', 'country')) { $table->dropColumn('country'); }
                if (Schema::hasColumn('organizations', 'state')) { $table->dropColumn('state'); }
                if (Schema::hasColumn('organizations', 'city')) { $table->dropColumn('city'); }
            });

            $addLocationColumn('organizations', 'country_id', 'address2', 'countries');
            $addLocationColumn('organizations', 'state_id', 'country_id', 'states');
            $addLocationColumn('organizations', 'city_id', 'state_id', 'cities');
        }

        // USER_DETAILS
        if (Schema::hasTable('user_details')) {
            Schema::table('user_details', function (Blueprint $table) {
                if (Schema::hasColumn('user_details', 'country')) { $table->dropColumn('country'); }
                if (Schema::hasColumn('user_details', 'state')) { $table->dropColumn('state'); }
                if (Schema::hasColumn('user_details', 'city')) { $table->dropColumn('city'); }
            });

            $addLocationColumn('user_details', 'country_id', 'address', 'countries');
            $addLocationColumn('user_details', 'state_id', 'country_id', 'states');
            $addLocationColumn('user_details', 'city_id', 'state_id', 'cities');
        }

        // USERS
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'country')) { $table->dropColumn('country'); }
            });

            $addLocationColumn('users', 'country_id', 'phone', 'countries');
        }
}

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        // LEADS
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
            $table->string('country')->nullable()->after('address');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
        });

        // ORGANIZATIONS
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
            $table->string('country')->nullable()->after('address2');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
        });

        // USER_DETAILS
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
            $table->string('country')->nullable()->after('address');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
        });

        // USERS
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
            $table->string('country')->nullable()->after('phone');
        });
    }
};
