<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesAndNullableUserId extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds deleted_at to several tables and makes user_id nullable where appropriate.
     */
    public function up()
    {
        $tables = [
            'answers',
            'assessments',
            'groups',
            'members',
            'organizations',
            'sessions',
            'subscriptions',
            'user_details',
            // 'user_roles' is excluded because user_id is part of a composite primary key
            // and making it nullable would cause MySQL errors. Skip changes here.
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    if (!Schema::hasColumn($table, 'deleted_at')) {
                        $tableBlueprint->softDeletes();
                    }
                    if (Schema::hasColumn($table, 'user_id')) {
                        // make user_id nullable so we can dissociate on soft-delete
                        try {
                            $tableBlueprint->unsignedBigInteger('user_id')->nullable()->change();
                        } catch (\Exception $e) {
                            // some DB drivers don't support change() without the doctrine/dbal package;
                            // ignore if change fails — user will need to run adjust manually.
                        }
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $tables = [
            'answers',
            'assessments',
            'groups',
            'members',
            'organizations',
            'sessions',
            'subscriptions',
            'user_details',
            'user_roles',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    if (Schema::hasColumn($table, 'deleted_at')) {
                        $tableBlueprint->dropSoftDeletes();
                    }
                    if (Schema::hasColumn($table, 'user_id')) {
                        try {
                            $tableBlueprint->unsignedBigInteger('user_id')->nullable(false)->change();
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }
                });
            }
        }
    }
}
