<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Rename notifications table to announcements
        Schema::rename('notifications', 'announcements');

        // Remove organization_ids, admin_ids, group_ids columns
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['organization_ids', 'admin_ids', 'group_ids']);
        });

        // Create pivot tables
        if (! Schema::hasTable('announcement_organization')) {
            Schema::create('announcement_organization', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedBigInteger('organization_id');
                $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            });
        }
        if (! Schema::hasTable('announcement_admin')) {
            Schema::create('announcement_admin', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedBigInteger('admin_id');
                $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
                $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
        if (! Schema::hasTable('announcement_group')) {
            Schema::create('announcement_group', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedBigInteger('group_id');
                $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Drop pivot tables
        Schema::dropIfExists('announcement_organization');
        Schema::dropIfExists('announcement_admin');
        Schema::dropIfExists('announcement_group');

        // Add columns back (if needed)
        Schema::table('announcements', function (Blueprint $table) {
            $table->json('organization_ids')->nullable();
            $table->json('admin_ids')->nullable();
            $table->json('group_ids')->nullable();
        });

        // Rename announcements table back to notifications
        Schema::rename('announcements', 'notifications');
    }
};
