<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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
            'user_roles',
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
                           Log::error('Error making user_id nullable', [
                               'error' => $e->getMessage()
                           ]);
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
                            Log::error('Error reverting user_id to non-nullable', [
                                'error' => $e->getMessage()
                            ]);
                            // some DB drivers don't support change() without the doctrine/dbal package;
                            
                        }
                    }
                });
            }
        }
    }
}
