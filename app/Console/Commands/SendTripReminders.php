<?php

namespace App\Console\Commands;

use App\Mail\DriverTripReminderMail;
use App\Mail\TripReminderMail;
use App\Models\Booking;
use Illuminate\Console\Command;
use Mail;

class SendTripReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-trip-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends reminders for bookings occurring in the next 24 hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to send trip reminders...');

        $startTime = now();
        $endTime = now()->addHours(24);

        $bookings = Booking::with(['customer', 'driver'])
            ->where('status', 'confirmed')
            ->whereBetween('pickup_datetime', [$startTime, $endTime])
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No upcoming bookings to remind.');
            return;
        }

        $this->info("Found {$bookings->count()} bookings to remind.");

        $bookings->each(function (Booking $booking) {
            // Send reminder to the customer
            if ($booking->customer?->email) {
                Mail::to($booking->customer->email)->queue(new TripReminderMail($booking));
                $this->line(" - Queued reminder for customer: {$booking->customer->email} (Booking: {$booking->code})");
            }

            // Send reminder to the driver, if one is assigned
            if ($booking->driver?->email) {
                Mail::to($booking->driver->email)->queue(new DriverTripReminderMail($booking));
                $this->line(" - Queued reminder for driver: {$booking->driver->email} (Booking: {$booking->code})");
            }
        });

        $this->info('Finished sending trip reminders.');
    }
}
