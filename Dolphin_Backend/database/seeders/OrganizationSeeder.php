<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('organizations')->insert([
            [
                'name' => 'Meetraj',
                'size' => 'Large',
                'source' => 'Google',
                'address1' => '714',
                'address2' => 'Isanpur',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'zip' => '382443',
                'country' => 'India',
                'contract_start' => '2024-06-18',
                'contract_end' => '2025-06-18',
                'main_contact' => 'Meet',
                'admin_email' => 'meetrajsinhjadeja04@gmail.com',
                'admin_phone' => '9662300727',
               
                'last_contacted' => '2024-12-15',
                'certified_staff' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
       
        ]);
    }
}
