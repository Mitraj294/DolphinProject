<?php

namespace Database\Factories;

use App\Models\Lead; 
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'password' => Hash::make('password'), // You can use a default password or generate a random one
            'find_us' => fake()->word(),
            'organization_name' => fake()->company(),
            'organization_size' => fake()->randomElement(['1-50 Employees (Small)', '51-200 Employees (Medium)', '201-500 Employees (Large)', '500+ Employees (Enterprise)']),
            'address' => fake()->address(),
            'country' => fake()->country(),
            'state' => fake()->state(),
            'city' => fake()->city(),
            'zip' => fake()->postcode(),
        ];
    }
}