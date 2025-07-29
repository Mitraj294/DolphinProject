<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lead;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Make sure DB facade is imported
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles
        $roles = [
            'superadmin',
            'user',
            'organizationadmin',
            'dolphinadmin',
            'salesperson',
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // superadmin
        if (!User::where('email', 'sdolphin632@gmail.com')->exists()) {
            $user = User::factory()->create([
                'name' => 'Super Admin',
                'email' => 'sdolphin632@gmail.com',
                'password' => bcrypt('superadmin@123'),
            ]);
            $superadminRole = Role::where('name', 'superadmin')->first();
            if ($superadminRole) {
                $user->roles()->sync([$superadminRole->id]);
            }
        }
        // Assign 'user' role to all users who don't have any roles
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $users = User::all();
            foreach ($users as $user) {
                if ($user->roles()->count() === 0) {
                    $user->roles()->sync([$userRole->id]);
                }
            }
        }

        // assessment questions
        $options = [
            'Relaxed', 'Persuasive', 'Stable', 'Charismatic', 'Individualistic', 'Optimistic',
            'Conforming', 'Methodical', 'Serious', 'Friendly', 'Humble', 'Unrestrained',
            'Competitive', 'Docile', 'Restless',
        ];
        DB::table('questions')->insert([
            [
                'question' => 'Q.1 Please select the words below that describe how other people expect you to be in most daily situations.',
                'options' => json_encode($options),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'Q.2 Please select the words below that describe how you really are, not how you are expected to be.',
                'options' => json_encode($options),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        // Create 1 lead using the factory
        Lead::factory()->count(1)->create();
    }
}