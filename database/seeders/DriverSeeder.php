<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'John Doe',
                'email' => 'john.driver@example.com',
                'phone' => '+1234567890',
                'address' => '123 Main St, Anytown, USA',
                'language' => 'en',
                'dob' => '1985-06-15',
                'gender' => 'male',
                'status' => 'active',
                'orders' => 17,
                'hire_date' => '2023-01-15',
                'notes' => 'Reliable driver with 5+ years of experience',
                'is_available' => true,
                'last_online_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.driver@example.com',
                'phone' => '+1987654321',
                'address' => '456 Oak Ave, Somewhere, USA',
                'language' => 'es',
                'dob' => '1990-03-22',
                'gender' => 'female',
                'status' => 'active',
                'orders' => 12,
                'hire_date' => '2023-02-20',
                'notes' => 'Bilingual driver, speaks fluent Spanish and English',
                'is_available' => true,
                'last_online_at' => now()->subHours(2),
            ],
            [
                'name' => 'Robert Johnson',
                'email' => 'robert.driver@example.com',
                'phone' => '+1555123456',
                'address' => '789 Pine Rd, Nowhere, USA',
                'language' => 'fr',
                'dob' => '1988-11-05',
                'gender' => 'male',
                'status' => 'on_leave',
                'orders' => 1,
                'hire_date' => '2022-11-10',
                'termination_date' => null,
                'notes' => 'Currently on leave until further notice',
                'is_available' => false,
                'last_online_at' => now()->subDays(5),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.driver@example.com',
                'phone' => '+1444555666',
                'address' => '321 Elm St, Anywhere, USA',
                'language' => 'en',
                'dob' => '1992-07-30',
                'gender' => 'female',
                'status' => 'inactive',
                'orders' => 20,
                'hire_date' => '2023-03-05',
                'termination_date' => '2023-12-31',
                'notes' => 'No longer with the company',
                'is_available' => false,
                'last_online_at' => now()->subMonths(2),
            ],
        ];

        foreach ($drivers as $driverData) {
            Driver::updateOrCreate(
                ['email' => $driverData['email']],
                $driverData
            );
        }
    }
}
