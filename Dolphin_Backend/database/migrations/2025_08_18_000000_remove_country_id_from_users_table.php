<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country_id')) {
                try {
                    $table->dropForeign(['country_id']);
                } catch (\Exception $e) {
                    // Foreign key may not exist; continue
                }
                $table->dropColumn('country_id');
            }
        });
    }
    public function down() {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable();
            }
        });
    }
};
