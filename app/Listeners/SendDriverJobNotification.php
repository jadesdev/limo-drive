<?php

namespace App\Listeners;

use App\Events\DriverAssignedToBooking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\DriverJobAssignedMail;
use Illuminate\Support\Facades\Mail;

class SendDriverJobNotification
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(DriverAssignedToBooking $event): void
    {
        if ($event->driver->email) {
            Mail::to($event->driver->email)
                ->queue(new DriverJobAssignedMail($event->booking));
        }
    }
}
