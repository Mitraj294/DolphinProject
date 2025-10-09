<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Reorder columns in users table. These statements modify column order only.
        DB::statement("ALTER TABLE `users` MODIFY `first_name` varchar(255) NOT NULL AFTER `id`;");
        DB::statement("ALTER TABLE `users` MODIFY `last_name` varchar(255) NULL AFTER `first_name`;");
        DB::statement("ALTER TABLE `users` MODIFY `email` varchar(255) NOT NULL AFTER `last_name`;");
        DB::statement("ALTER TABLE `users` MODIFY `password` varchar(255) NOT NULL AFTER `email`;");
        DB::statement("ALTER TABLE `users` MODIFY `created_at` datetime NULL AFTER `password`;");
        DB::statement("ALTER TABLE `users` MODIFY `updated_at` datetime NULL AFTER `created_at`;");
        DB::statement("ALTER TABLE `users` MODIFY `deleted_at` datetime NULL AFTER `updated_at`;");
    }

    public function down(): void
    {
        // Revert to previous order: id, email, password, created_at, updated_at, deleted_at, first_name, last_name
        DB::statement("ALTER TABLE `users` MODIFY `email` varchar(255) NOT NULL AFTER `id`;");
        DB::statement("ALTER TABLE `users` MODIFY `password` varchar(255) NOT NULL AFTER `email`;");
        DB::statement("ALTER TABLE `users` MODIFY `created_at` datetime NULL AFTER `password`;");
        DB::statement("ALTER TABLE `users` MODIFY `updated_at` datetime NULL AFTER `created_at`;");
        DB::statement("ALTER TABLE `users` MODIFY `deleted_at` datetime NULL AFTER `updated_at`;");
        DB::statement("ALTER TABLE `users` MODIFY `first_name` varchar(255) NOT NULL AFTER `deleted_at`;");
        DB::statement("ALTER TABLE `users` MODIFY `last_name` varchar(255) NULL AFTER `first_name`;");
    }
};
