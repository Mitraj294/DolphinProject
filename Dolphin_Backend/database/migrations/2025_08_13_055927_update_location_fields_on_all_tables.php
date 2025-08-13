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
        // LEADS
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'country')) $table->dropColumn('country');
            if (Schema::hasColumn('leads', 'state')) $table->dropColumn('state');
            if (Schema::hasColumn('leads', 'city')) $table->dropColumn('city');
            if (!Schema::hasColumn('leads', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->after('address');
                $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            }
            if (!Schema::hasColumn('leads', 'state_id')) {
                $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
                $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            }
            if (!Schema::hasColumn('leads', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            }
        });

        // ORGANIZATIONS
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'country')) $table->dropColumn('country');
            if (Schema::hasColumn('organizations', 'state')) $table->dropColumn('state');
            if (Schema::hasColumn('organizations', 'city')) $table->dropColumn('city');
            if (!Schema::hasColumn('organizations', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->after('address2');
                $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            }
            if (!Schema::hasColumn('organizations', 'state_id')) {
                $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
                $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            }
            if (!Schema::hasColumn('organizations', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            }
        });

        // USER_DETAILS
        Schema::table('user_details', function (Blueprint $table) {
            if (Schema::hasColumn('user_details', 'country')) $table->dropColumn('country');
            if (Schema::hasColumn('user_details', 'state')) $table->dropColumn('state');
            if (Schema::hasColumn('user_details', 'city')) $table->dropColumn('city');
            if (!Schema::hasColumn('user_details', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->after('address');
                $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            }
            if (!Schema::hasColumn('user_details', 'state_id')) {
                $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
                $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            }
            if (!Schema::hasColumn('user_details', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            }
        });

        // USERS
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country')) $table->dropColumn('country');
            if (!Schema::hasColumn('users', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->after('phone');
                $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            }
        });
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
