<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    public function up() {
        Schema::table('assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->unsignedBigInteger('organization_id')->nullable()->after('user_id');
            // Add foreign keys if needed
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
        });
    }
    public function down() {
        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'user_id')) {
                try { $table->dropForeign(['user_id']); } catch (\Exception $e) { Log::warning('Could not drop FK assessments.user_id', ['error' => $e->getMessage()]); }
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('assessments', 'organization_id')) {
                try { $table->dropForeign(['organization_id']); } catch (\Exception $e) { Log::warning('Could not drop FK assessments.organization_id', ['error' => $e->getMessage()]); }
                $table->dropColumn('organization_id');
            }
        });
    }
};
