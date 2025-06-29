<?php

namespace App\Services\PaymentGateways;

use App\Models\Booking;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Booking $booking): array
    {
        $paymentIntent = PaymentIntent::create([
            'amount' => $booking->price * 100,
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'booking_id' => $booking->id,
                'booking_code' => $booking->code,
            ],
        ]);

        return [
            'intent' => $paymentIntent->id,
            'method' => 'stripe',
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $paymentIntent->amount / 100,
            'currency' => $paymentIntent->currency,
        ];
    }

    public function confirmPayment(Booking $booking, string $paymentIntentId): array
    {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        if ($paymentIntent->status !== 'succeeded') {
            return [
                'success' => false,
                'message' => 'Payment has not been completed yet.',
            ];
        }

        if ($paymentIntent->metadata->booking_id !== $booking->id) {
            return [
                'success' => false,
                'message' => 'Payment does not match this booking.',
            ];
        }

        return [
            'success' => true,
            'payment_data' => $this->formatPaymentData($paymentIntent),
            'booking' => [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => 'in_progress',
                'payment_status' => 'paid',
            ],
        ];
    }

    public function processWebhook($paymentIntent): ?array
    {
        $bookingId = $paymentIntent->metadata->booking_id ?? null;

        if (!$bookingId) {
            return null;
        }

        return [
            'booking_id' => $bookingId,
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount / 100,
            'currency' => $paymentIntent->currency,
            'payment_method' => 'stripe',
            'gateway_name' => 'stripe',
            'gateway_ref' => $paymentIntent->payment_method,
            'gateway_payload' => $paymentIntent,
        ];
    }

    private function formatPaymentData($paymentIntent): array
    {
        return [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount / 100,
            'currency' => $paymentIntent->currency,
            'payment_method' => 'stripe',
            'gateway_name' => 'stripe',
            'gateway_ref' => $paymentIntent->payment_method,
            'gateway_payload' => $paymentIntent,
        ];
    }
}
