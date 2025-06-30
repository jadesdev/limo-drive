<?php

namespace App\Listeners;

use App\Events\TripCompleted;
use App\Mail\TripCompletedMail;
use Illuminate\Support\Facades\Mail;

class SendTripCompletedNotification
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
    public function handle(TripCompleted $event): void
    {
        if ($event->booking->customer?->email) {
            Mail::to($event->booking->customer->email)
                ->queue(new TripCompletedMail($event->booking));
        }
    }
}
