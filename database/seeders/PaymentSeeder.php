<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::has('customer')->get();
        $statuses = ['pending', 'paid', 'failed', 'cancelled'];
        $methods = ['stripe', 'cash', 'paypal'];

        foreach ($bookings as $booking) {
            $numPayments = rand(1, 3);
            for ($i = 0; $i < $numPayments; $i++) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'payment_intent_id' => 'pi_' . Str::random(12),
                    'amount' => $booking->price,
                    'currency' => 'USD',
                    'customer_name' => $booking->customer->first_name . ' ' . $booking->customer->last_name,
                    'customer_email' => $booking->customer->email,
                    'gateway_name' => 'stripe',
                    'gateway_ref' => 'gw_' . Str::random(12),
                    'payment_method' => $methods[array_rand($methods)],
                    'status' => $statuses[array_rand($statuses)],
                ]);
            }
        }
    }
}
