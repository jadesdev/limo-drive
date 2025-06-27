<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Fleet;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch some customers and fleets for association
        $customers = Customer::all();
        $fleets = Fleet::all();

        if ($customers->isEmpty() || $fleets->isEmpty()) {
            return;
        }

        $statuses = ['pending_payment', 'confirmed', 'cancelled'];
        $paymentStatuses = ['unpaid', 'paid'];
        $serviceTypes = ['point_to_point', 'airport_pickup', 'wedding', 'airport_transfer', 'round_trip', 'event'];

        foreach ($customers as $customer) {
            $fleet = $fleets->random();
            for ($i = 0; $i < 4; $i++) {
                Booking::create([
                    'fleet_id' => $fleet->id,
                    'service_type' => $serviceTypes[array_rand($serviceTypes)],
                    'is_accessible' => rand(0, 1),
                    'is_return_service' => rand(0, 1),
                    'duration_hours' => rand(1, 8),
                    'customer_id' => $customer->id,
                    'pickup_datetime' => now()->addDays(rand(-40, 40)),
                    'pickup_address' => fake()->address(),
                    'pickup_latitude' => fake()->latitude(),
                    'pickup_longitude' => fake()->longitude(),
                    'dropoff_address' => fake()->address(),
                    'dropoff_latitude' => fake()->latitude(),
                    'dropoff_longitude' => fake()->longitude(),
                    'passenger_count' => rand(2, 6),
                    'bag_count' => rand(0, 4),
                    'price' => rand(50, 500),
                    'payment_method' => 'stripe',
                    'payment_status' => $paymentStatuses[array_rand($paymentStatuses)],
                    'notes' => 'Seeder generated booking',
                    'status' => $statuses[array_rand($statuses)],
                ]);
            }
        }
    }
}
