<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Minimal set of states for seeded countries
        $states = [
            // United States (country_id = 1)
            ['id' => 1, 'country_id' => 1, 'name' => 'California'],
            ['id' => 2, 'country_id' => 1, 'name' => 'New York'],
            // India (country_id = 2)
            ['id' => 3, 'country_id' => 2, 'name' => 'Maharashtra'],
            ['id' => 4, 'country_id' => 2, 'name' => 'Gujarat'],
            // United Kingdom (country_id = 3)
            ['id' => 5, 'country_id' => 3, 'name' => 'England'],
        ];

        foreach ($states as $state) {
            DB::table('states')->updateOrInsert(['id' => $state['id']], $state);
        }
    }
}
