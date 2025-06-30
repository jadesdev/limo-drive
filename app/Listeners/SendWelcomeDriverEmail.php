<?php

namespace App\Listeners;

use App\Events\DriverCreated;
use App\Mail\WelcomeDriverMail;
use Mail;

class SendWelcomeDriverEmail
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
    public function handle(DriverCreated $event): void
    {
        if ($event->driver->email) {
            Mail::to($event->driver->email)
                ->queue(new WelcomeDriverMail($event->driver));
        }
    }
}
