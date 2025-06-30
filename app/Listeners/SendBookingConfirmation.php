<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Mail\BookingConfirmationMail;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmation
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookingConfirmed $event): void
    {
        if ($event->booking->customer && $event->booking->customer->email) {
            Mail::to($event->booking->customer->email)
                ->queue(new BookingConfirmationMail($event->booking));
        }
    }
}
