<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing FKs to match dolphin_db
        // Only run if FKs don't already exist
        
        try {
            // announcement_admin.announcement_id FK
            if (Schema::hasTable('announcement_admin') && Schema::hasTable('announcements')) {
                $exists = DB::select("SELECT COUNT(*) as cnt FROM information_schema.key_column_usage 
                    WHERE table_schema = DATABASE() 
                    AND table_name = 'announcement_admin' 
                    AND column_name = 'announcement_id' 
                    AND referenced_table_name = 'announcements'");
                if ($exists[0]->cnt == 0) {
                    Schema::table('announcement_admin', function (Blueprint $table) {
                        $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
                    });
                }
            }
        } catch (\Exception $e) {}

        try {
            // announcement_group.announcement_id FK
            if (Schema::hasTable('announcement_group') && Schema::hasTable('announcements')) {
                $exists = DB::select("SELECT COUNT(*) as cnt FROM information_schema.key_column_usage 
                    WHERE table_schema = DATABASE() 
                    AND table_name = 'announcement_group' 
                    AND column_name = 'announcement_id' 
                    AND referenced_table_name = 'announcements'");
                if ($exists[0]->cnt == 0) {
                    Schema::table('announcement_group', function (Blueprint $table) {
                        $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
                    });
                }
            }
        } catch (\Exception $e) {}

        try {
            // announcement_organization.announcement_id FK
            if (Schema::hasTable('announcement_organization') && Schema::hasTable('announcements')) {
                $exists = DB::select("SELECT COUNT(*) as cnt FROM information_schema.key_column_usage 
                    WHERE table_schema = DATABASE() 
                    AND table_name = 'announcement_organization' 
                    AND column_name = 'announcement_id' 
                    AND referenced_table_name = 'announcements'");
                if ($exists[0]->cnt == 0) {
                    Schema::table('announcement_organization', function (Blueprint $table) {
                        $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
                    });
                }
            }
        } catch (\Exception $e) {}

        try {
            // user_details FKs to cities/countries/states
            if (Schema::hasTable('user_details')) {
                if (Schema::hasColumn('user_details', 'city_id') && Schema::hasTable('cities')) {
                    $exists = DB::select("SELECT COUNT(*) as cnt FROM information_schema.key_column_usage 
                        WHERE table_schema = DATABASE() 
                        AND table_name = 'user_details' 
                        AND column_name = 'city_id' 
                        AND referenced_table_name = 'cities'");
                    if ($exists[0]->cnt == 0) {
                        Schema::table('user_details', function (Blueprint $table) {
                            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
                        });
                    }
                }

                if (Schema::hasColumn('user_details', 'country_id') && Schema::hasTable('countries')) {
                    $exists = DB::select("SELECT COUNT(*) as cnt FROM information_schema.key_column_usage 
                        WHERE table_schema = DATABASE() 
                        AND table_name = 'user_details' 
                        AND column_name = 'country_id' 
                        AND referenced_table_name = 'countries'");
                    if ($exists[0]->cnt == 0) {
                        Schema::table('user_details', function (Blueprint $table) {
                            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
                        });
                    }
                }

                if (Schema::hasColumn('user_details', 'state_id') && Schema::hasTable('states')) {
                    $exists = DB::select("SELECT COUNT(*) as cnt FROM information_schema.key_column_usage 
                        WHERE table_schema = DATABASE() 
                        AND table_name = 'user_details' 
                        AND column_name = 'state_id' 
                        AND referenced_table_name = 'states'");
                    if ($exists[0]->cnt == 0) {
                        Schema::table('user_details', function (Blueprint $table) {
                            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
                        });
                    }
                }
            }
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        try {
            Schema::table('announcement_admin', function (Blueprint $table) {
                $table->dropForeign(['announcement_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('announcement_group', function (Blueprint $table) {
                $table->dropForeign(['announcement_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('announcement_organization', function (Blueprint $table) {
                $table->dropForeign(['announcement_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('user_details', function (Blueprint $table) {
                $table->dropForeign(['city_id']);
                $table->dropForeign(['country_id']);
                $table->dropForeign(['state_id']);
            });
        } catch (\Exception $e) {}
    }
};
