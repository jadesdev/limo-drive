<?php

namespace Database\Seeders;

use App\Models\Fleet;
use Illuminate\Database\Seeder;
use Str;

class FleetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fleetsData = [
            [
                'name' => 'Ford Expedition Max',
                'description' => 'The Ford Expedition Max is a full-size SUV that offers spacious seating for up to 8 passengers, advanced technology features, and a powerful engine for a smooth and comfortable ride. It is perfect for family trips, corporate travel, or any occasion that requires ample space and luxury.',
                'thumbnail' => 'fleets/ford_expedition_max_thumbnail.jpg',
                'seats' => 7,
                'bags' => 5,
                'images' => [
                    'fleets/ford_expedition_max_interior.jpg',
                    'fleets/ford_expedition_max_rear.jpg',
                ],
                'features' => [
                    'Sunroof',
                    'Navigation System',
                    'Rear Entertainment System',
                    'Fully insured',
                    'Child Seat Available',
                    'Black Leather Interior',
                    'Tri-Zone Climate Control',
                ],
                'specifications' => [
                    ['label' => 'Engine', 'value' => '3.5L V6 EcoBoost'],
                    ['label' => 'Transmission', 'value' => '10-Speed Automatic'],
                    ['label' => 'Fuel Economy', 'value' => '17 MPG City / 24 MPG Highway'],
                    ['label' => 'Passengers (Config)', 'value' => '2-3'],
                    ['label' => 'Interior Material', 'value' => 'Leather'],
                    ['label' => 'Smoking Allowed', 'value' => 'No'],
                    ['label' => 'Bag Capacity (Detailed)', 'value' => '2 large, 3 small'],
                    ['label' => 'Bluetooth Connectivity', 'value' => 'Yes'],
                    ['label' => 'Surround Sound', 'value' => 'Yes'],
                ],
                'is_active' => true,
                'order' => 1,
                'base_fee' => 10,
                'rate_per_mile' => 5,
                'rate_per_hour' => 5,
                'minimum_hours' => 1,
            ],
            [
                'name' => 'Mercedes-Benz S-Class',
                'description' => 'Experience unparalleled luxury and cutting-edge technology with the Mercedes-Benz S-Class. This flagship sedan sets the standard for comfort, performance, and sophistication, making it the ideal choice for executive travel and special occasions.',
                'thumbnail' => 'fleets/mercedes_s_class_thumbnail.jpg',
                'seats' => 4,
                'bags' => 3,
                'images' => [
                    'fleets/mercedes_s_class_interior.jpg',
                    'fleets/mercedes_s_class_detail.jpg',
                ],
                'features' => [
                    'Panoramic Sunroof',
                    'Ambient Lighting',
                    'Premium Burmester Sound System',
                    'Air Balance Package',
                    'Heated and Ventilated Seats',
                ],
                'specifications' => [
                    ['label' => 'Engine', 'value' => '3.0L Inline-6 Turbo with EQ Boost'],
                    ['label' => 'Transmission', 'value' => '9G-TRONIC 9-Speed Automatic'],
                    ['label' => 'Horsepower', 'value' => '429 hp @ 6,100 rpm'],
                    ['label' => '0-60 mph', 'value' => '4.9 seconds'],
                    ['label' => 'Interior Material', 'value' => 'Nappa Leather'],
                    ['label' => 'Driver Assistance', 'value' => 'Active Package Plus'],
                ],
                'is_active' => true,
                'order' => 2,
                'base_fee' => 10,
                'rate_per_mile' => 6,
                'rate_per_hour' => 6,
                'minimum_hours' => 1,
            ],
        ];

        foreach ($fleetsData as $fleetData) {
            $fleetData['slug'] = Str::slug($fleetData['name']);
            Fleet::create($fleetData);
        }
    }
}
