<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MarkGeneratedMigrationsApplied extends Migration
{
    public function up(): void
    {
        $migrationsPath = database_path('migrations');
        if (! is_dir($migrationsPath)) {
            return;
        }

        $files = scandir($migrationsPath);
        if ($files === false) {
            return;
        }

        // Collect migration filenames we generated on 2025-10-08 prefix 12xxxxx
        $candidates = [];
        foreach ($files as $f) {
            if (! is_string($f)) {
                continue;
            }
            // match files like 2025_10_08_12xxxxx_name.php
            if (preg_match('/^2025_10_08_12\d{3}_.*\.php$/', $f)) {
                $candidates[] = pathinfo($f, PATHINFO_FILENAME);
            }
        }

        if (empty($candidates)) {
            return;
        }

        $currentMaxBatch = (int) (DB::table('migrations')->max('batch') ?? 0);
        $batch = $currentMaxBatch + 1;

        $toInsert = [];
        foreach ($candidates as $migration) {
            $exists = DB::table('migrations')->where('migration', $migration)->exists();
            if ($exists) {
                continue;
            }
            $toInsert[] = ['migration' => $migration, 'batch' => $batch];
        }

        if (! empty($toInsert)) {
            DB::table('migrations')->insert($toInsert);
        }
    }

    public function down(): void
    {
        $migrationsPath = database_path('migrations');
        if (! is_dir($migrationsPath)) {
            return;
        }

        $files = scandir($migrationsPath);
        if ($files === false) {
            return;
        }

        foreach ($files as $f) {
            if (! is_string($f)) {
                continue;
            }
            if (preg_match('/^2025_10_08_12\d{3}_.*\.php$/', $f)) {
                $migration = pathinfo($f, PATHINFO_FILENAME);
                DB::table('migrations')->where('migration', $migration)->delete();
            }
        }
    }
}
