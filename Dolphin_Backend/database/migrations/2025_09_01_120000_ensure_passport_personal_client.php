<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run if oauth_clients table exists
        if (! Schema::hasTable('oauth_clients')) {
            return;
        }

        $clients = DB::table('oauth_clients')->get();

        foreach ($clients as $c) {
            $grantTypes = null;
            if (isset($c->grant_types) && $c->grant_types !== null) {
                // grant_types is stored as JSON text in this schema
                $decoded = json_decode($c->grant_types, true);
                if (is_array($decoded) && in_array('personal_access', $decoded, true)) {
                    return; // personal access client already exists
                }
            }
        }

        // If we reached here, no personal access client was found. Insert one.
        DB::table('oauth_clients')->insert([
            'id' => (string) Str::uuid(),
            'owner_type' => null,
            'owner_id' => null,
            'name' => 'Personal Access Client',
            'secret' => Str::random(40),
            'provider' => null,
            'redirect_uris' => json_encode([]),
            'grant_types' => json_encode(['personal_access']),
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('oauth_clients')) {
            return;
        }

        // Remove any client that appears to be a personal access client created by this migration
        $rows = DB::table('oauth_clients')->get();
        foreach ($rows as $r) {
            if (isset($r->grant_types) && $r->grant_types !== null) {
                $decoded = json_decode($r->grant_types, true);
                if (is_array($decoded) && in_array('personal_access', $decoded, true)) {
                    DB::table('oauth_clients')->where('id', $r->id)->delete();
                }
            }
        }
    }
};
