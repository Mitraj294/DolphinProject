<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country_id')) {
                // For PostgreSQL, check if the constraint exists before trying to drop it
                $constraint = DB::selectOne("
                    SELECT constraint_name
                    FROM information_schema.table_constraints
                    WHERE table_name = 'users'
                    AND constraint_type = 'FOREIGN KEY'
                    AND constraint_name = 'users_country_id_foreign'
                ");
                if ($constraint) {
                    $table->dropForeign(['country_id']);
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