<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create pivot tables (created through helper to keep complexity low)
        $this->createPivot('announcement_organization', 'announcement_id', 'organization_id', 'announcements', 'organizations');
        $this->createPivot('announcement_admin', 'announcement_id', 'admin_id', 'announcements', 'users');
        $this->createPivot('announcement_group', 'announcement_id', 'group_id', 'announcements', 'groups');
    }

    protected function createPivot(string $pivot, string $left, string $right, string $leftTable, string $rightTable): void
    {
        if (Schema::hasTable($pivot)) {
            return;
        }

        Schema::create($pivot, function (Blueprint $table) use ($left, $right, $leftTable, $rightTable) {
            $table->id();
            $table->unsignedBigInteger($left);
            $table->unsignedBigInteger($right);

            if (Schema::hasTable($leftTable)) {
                $table->foreign($left)->references('id')->on($leftTable)->onDelete('cascade');
            }
            if (Schema::hasTable($rightTable)) {
                $table->foreign($right)->references('id')->on($rightTable)->onDelete('cascade');
            }
        });
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
