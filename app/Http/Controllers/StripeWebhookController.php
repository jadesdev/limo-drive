<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;
use Stripe\Exception\UnexpectedValueException;
use App\Models\Booking;
use App\Models\Payment;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;

class StripeWebhookController extends Controller
{
    use ApiResponse;
    /**
     * Handle a Stripe webhook call.
     * @ignore
     */
    #[ExcludeRouteFromDocs]
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->server('HTTP_STRIPE_SIGNATURE');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (UnexpectedValueException | SignatureVerificationException $e) {
            // Invalid payload or signature
            return response('Invalid request', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $this->handlePaymentSucceeded($paymentIntent);
        }

        return response('Webhook Handled', 200);
    }

    /**
     * Handle the logic for a successful payment.
     *
     * @param  \Stripe\PaymentIntent  $paymentIntent
     * @return void
     */
    protected function handlePaymentSucceeded(PaymentIntent $paymentIntent): void
    {
        $bookingId = $paymentIntent->metadata->booking_id ?? null;
        if (!$bookingId) {
            return; 
        }

        $booking = Booking::find($bookingId);
        if (!$booking) {
            return; 
        }

        if (Payment::where('gateway_ref', $paymentIntent->id)->exists()) {
            return;
        }

        Payment::create([
            'booking_id' => $booking->id,
            'customer_name' => $booking->customer_name,
            'customer_email' => $booking->customer_email,
            'gateway_name' => 'stripe',
            'gateway_ref' => $paymentIntent->id,
            'amount' => $paymentIntent->amount / 100, 
            'currency' => $paymentIntent->currency,
            'status' => $paymentIntent->status, 
            'gateway_payload' => $paymentIntent->toArray(),
        ]);

        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // TODO: Send confirmation email to customer
        // Mail::to($booking->customer_email)->send(new BookingConfirmedMail($booking));
    }
}
