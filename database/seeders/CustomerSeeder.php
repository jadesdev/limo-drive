<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            Customer::create([
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->email(),
                'phone' => fake()->phoneNumber(),
                'language' => fake()->randomElement(['English', 'French', 'Spanish', 'German']),
                'last_active' => now()->addDays(rand(-20, 20)),
                'bookings_count' => rand(3, 30),
            ]);
        }
    }
}
