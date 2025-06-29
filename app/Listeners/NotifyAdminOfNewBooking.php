<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Mail\AdminNewBookingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifyAdminOfNewBooking
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
        $adminEmail = config('mail.from.address');
        if ($adminEmail) {
            Mail::to($adminEmail)
                ->queue(new AdminNewBookingNotification($event->booking));
        }
    }
}
