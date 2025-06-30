<?php

namespace App\Services\PaymentGateways;

use App\Models\Booking;
use App\Services\PayPalService;
use Log;
use Str;

class PaypalGateway implements PaymentGatewayInterface
{
    public function __construct(
        private PayPalService $paypalService
    ) {}

    public function createPaymentIntent(Booking $booking): array
    {
        $paymentIntent = $this->paypalService->createPayment($booking->price, 'USD', [
            'booking_id' => $booking->id,
            'reference' => $booking->id,
            'description' => 'Booking Payment on ' . config('app.name'),
            'booking_code' => $booking->code,
        ]);

        return [
            'intent' => $paymentIntent['id'],
            'method' => 'paypal',
            'client_secret' => $paymentIntent['id'],
            'amount' => $booking->price,
            'currency' => 'USD',
        ];
    }

    public function confirmPayment(Booking $booking, string $paymentIntentId): array
    {
        $paymentIntent = $this->paypalService->getOrderDetails($paymentIntentId);
        if ($paymentIntent['status'] !== 'APPROVED' && $paymentIntent['status'] !== 'COMPLETED') {
            return [
                'success' => false,
                'message' => 'Payment has not been approved yet.',
            ];
        }

        $customId = $paymentIntent['purchase_units'][0]['custom_id'] ?? null;
        if ($customId !== $booking->id) {
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

    public function processWebhook($paymentResponse): ?array
    {
        $bookingId = $this->extractBookingIdFromWebhook($paymentResponse);

        if (! $bookingId) {
            return null;
        }

        return [
            'booking_id' => $bookingId,
            'payment_intent_id' => $paymentResponse['resource']['id'],
            'amount' => (float) $paymentResponse['resource']['amount'],
            'currency' => $paymentResponse['resource']['currency_code'],
            'payment_method' => 'paypal',
            'gateway_name' => 'paypal',
            'gateway_ref' => $paymentResponse['resource']['purchase_units'][0]['reference_id'] ?? null,
            'gateway_payload' => $paymentResponse,
        ];
    }

    private function formatPaymentData($paymentIntent): array
    {
        return [
            'payment_intent_id' => $paymentIntent['id'],
            'amount' => (float) $paymentIntent['purchase_units'][0]['amount']['value'],
            'currency' => $paymentIntent['purchase_units'][0]['amount']['currency_code'],
            'payment_method' => 'paypal',
            'gateway_name' => 'paypal',
            'gateway_ref' => $paymentIntent['purchase_units'][0]['reference_id'] . '_' . Str::random(6) ?? null,
            'gateway_payload' => $paymentIntent,
        ];
    }

    private function extractBookingIdFromWebhook($paymentResponse): ?string
    {
        // This might need adjustment based on your PayPal webhook structure
        return $paymentResponse['resource']['custom_id'] ??
            $paymentResponse['booking_id'] ??
            null;
    }
}
