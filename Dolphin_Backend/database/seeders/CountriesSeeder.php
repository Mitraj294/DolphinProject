<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Minimal list to ensure `/api/countries` returns data on fresh installs.
        $countries = [
            ['id' => 1, 'name' => 'United States'],
            ['id' => 2, 'name' => 'India'],
            ['id' => 3, 'name' => 'United Kingdom'],
            ['id' => 4, 'name' => 'Canada'],
            ['id' => 5, 'name' => 'Australia'],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->updateOrInsert(['id' => $country['id']], $country);
        }
    }
}
