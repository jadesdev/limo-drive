<?php

namespace App\Listeners;

use App\Events\TripCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\TripCompletedMail;

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
