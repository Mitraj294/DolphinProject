<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class EnsurePassportClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ensure:passport-clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure Passport personal and password grant clients exist (idempotent)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!\Schema::hasTable('oauth_clients')) {
            $this->error('oauth_clients table does not exist. Please run migrations first.');
            return 1;
        }

        $this->info('Checking for personal access client...');
        $exists = DB::table('oauth_clients')->whereRaw("JSON_CONTAINS(IFNULL(grant_types,'[]'), '\"personal_access\"')")->exists();

        if (! $exists) {
            $this->info('Creating personal access client...');
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
            $this->info('Personal access client created.');
        } else {
            $this->info('Personal access client already exists.');
        }

        $this->info('Checking for password grant client...');
        $existsPassword = DB::table('oauth_clients')->whereRaw("JSON_CONTAINS(IFNULL(grant_types,'[]'), '\"password\"')")->exists();

        if (! $existsPassword) {
            $this->info('Creating password grant client...');
            DB::table('oauth_clients')->insert([
                'id' => (string) Str::uuid(),
                'owner_type' => null,
                'owner_id' => null,
                'name' => 'Password Grant Client',
                'secret' => Str::random(40),
                'provider' => null,
                'redirect_uris' => json_encode([]),
                'grant_types' => json_encode(['password','refresh_token']),
                'revoked' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info('Password grant client created.');
        } else {
            $this->info('Password grant client already exists.');
        }

        $this->info('Done.');
        return 0;
    }
}
