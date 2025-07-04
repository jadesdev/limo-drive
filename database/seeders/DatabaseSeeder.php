<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => 'Password123',
        ]);
        $admin->role = 'admin';
        $admin->save();

        $this->call([
            ServiceSeeder::class,
            FleetSeeder::class,
            FaqSeeder::class,
            DriverSeeder::class,
            CustomerSeeder::class,
            BookingSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
