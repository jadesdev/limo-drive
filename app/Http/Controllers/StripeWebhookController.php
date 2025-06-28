<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    use ApiResponse;

    public function __construct(private BookingService $bookingService) {}

    /**
     * Handle a Stripe webhook call.
     *
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
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            // Invalid payload or signature
            Log::error('Stripe webhook invalid payload', ['error' => $e->getMessage()]);

            // TODO: remove in production
            // return response('Invalid request', 400);
        }
        // TODO: remove in production
        $event = json_decode($payload);

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            case 'payment_intent.canceled':
                $this->handlePaymentIntentCanceled($event->data->object);
                break;

            default:
                // Log unhandled event types
                \Log::info('Unhandled Stripe webhook event', [
                    'type' => $event->type,
                    'id' => $event->id,
                ]);
        }

        return response('Webhook handled', 200);
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentIntentSucceeded($paymentIntent): void
    {
        \Log::info('Processing successful payment intent', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'booking_id' => $paymentIntent->metadata->booking_id ?? null,
        ]);

        $success = $this->bookingService->processWebhookPayment($paymentIntent);

        if ($success) {
            \Log::info('Booking confirmed via webhook', [
                'payment_intent_id' => $paymentIntent->id,
                'booking_id' => $paymentIntent->metadata->booking_id,
            ]);

            // TODO: Send confirmation email to customer
            // TODO: Send notification to admin/driver
            // $this->sendBookingConfirmationEmail($paymentIntent->metadata->booking_id);

        } else {
            \Log::error('Failed to process successful payment webhook', [
                'payment_intent_id' => $paymentIntent->id,
            ]);
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentIntentFailed($paymentIntent): void
    {
        \Log::warning('Payment intent failed', [
            'payment_intent_id' => $paymentIntent->id,
            'booking_id' => $paymentIntent->metadata->booking_id ?? null,
            'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Unknown',
        ]);

        // TODO: Update booking status to 'payment_failed'
        // TODO: Send notification to customer about failed payment
        // TODO: Maybe automatically cancel booking after X failed attempts
    }

    /**
     * Handle canceled payment
     */
    private function handlePaymentIntentCanceled($paymentIntent): void
    {
        \Log::info('Payment intent canceled', [
            'payment_intent_id' => $paymentIntent->id,
            'booking_id' => $paymentIntent->metadata->booking_id ?? null,
        ]);

        // TODO: Update booking status to 'canceled'
        // TODO: Send notification to customer
    }

    /**
     * Handle the logic for a successful payment.
     */
    protected function handlePaymentSucceeded(PaymentIntent $paymentIntent): void
    {
        $bookingId = $paymentIntent->metadata->booking_id ?? null;
        if (! $bookingId) {
            return;
        }

        $booking = Booking::find($bookingId);
        if (! $booking) {
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
