<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Minimal set of cities for seeded states
        $cities = [
            // California (state_id = 1)
            ['id' => 1, 'state_id' => 1, 'name' => 'Los Angeles'],
            ['id' => 2, 'state_id' => 1, 'name' => 'San Francisco'],
            // New York (state_id = 2)
            ['id' => 3, 'state_id' => 2, 'name' => 'New York City'],
            // Maharashtra (state_id = 3)
            ['id' => 4, 'state_id' => 3, 'name' => 'Mumbai'],
            // Gujarat (state_id = 4)
            ['id' => 5, 'state_id' => 4, 'name' => 'Ahmedabad'],
        ];

        foreach ($cities as $city) {
            DB::table('cities')->updateOrInsert(['id' => $city['id']], $city);
        }
    }
}
