<?php

namespace App\Services;

use App\Events\BookingConfirmed;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentGateways\PaymentGatewayInterface;
use App\Services\PaymentGateways\PayPalGateway;
use App\Services\PaymentGateways\StripeGateway;
use Exception;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class BookingPaymentService
{
    private const SUPPORTED_GATEWAYS = ['stripe', 'paypal'];

    public function __construct(
        private StripeGateway $stripeGateway,
        private PayPalGateway $paypalGateway
    ) {}

    /**
     * Create a payment intent for a booking
     */
    public function createPaymentIntent(Booking $booking): array
    {
        $gateway = $this->getPaymentGateway($booking->payment_method);

        return $gateway->createPaymentIntent($booking);
    }

    /**
     * Confirm payment and update booking status
     */
    public function confirmPayment(string $bookingId, string $paymentIntentId): array
    {
        try {
            $booking = Booking::findOrFail($bookingId);
            $gateway = $this->getPaymentGateway($booking->payment_method);

            $result = $gateway->confirmPayment($booking, $paymentIntentId);

            if ($result['success']) {
                $this->processSuccessfulPayment($booking, $result['payment_data']);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Payment confirmation failed', [
                'booking_id' => $bookingId,
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to confirm payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process webhook payment confirmation
     */
    public function processWebhook(string $gateway, $payload): bool
    {
        try {
            $paymentGateway = $this->getPaymentGateway($gateway);
            $webhookData = $paymentGateway->processWebhook($payload);

            if (! $webhookData) {
                return false;
            }

            $booking = $this->findBookingFromWebhook($webhookData);
            if (! $booking) {
                return false;
            }

            $this->processSuccessfulPayment($booking, $webhookData);

            Log::info('Booking payment confirmed via webhook', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->code,
                'gateway' => $gateway,
                'payment_intent_id' => $webhookData['payment_intent_id'],
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Webhook payment processing failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the appropriate payment gateway
     */
    private function getPaymentGateway(string $method): PaymentGatewayInterface
    {
        if (! in_array($method, self::SUPPORTED_GATEWAYS)) {
            throw new InvalidArgumentException("Unsupported payment method: {$method}");
        }

        return match ($method) {
            'stripe' => $this->stripeGateway,
            'paypal' => $this->paypalGateway,
        };
    }

    /**
     * Process successful payment and update booking
     */
    private function processSuccessfulPayment(Booking $booking, array $paymentData): void
    {
        if ($booking->status !== 'pending_payment') {
            return;
        }

        $booking->update([
            'status' => 'in_progress',
            'payment_status' => 'paid',
        ]);

        $this->createOrUpdatePaymentRecord($booking, $paymentData);

        event(new BookingConfirmed($booking->fresh()));
    }

    /**
     * Create or update payment record
     */
    private function createOrUpdatePaymentRecord(Booking $booking, array $paymentData): void
    {
        Payment::updateOrCreate(
            ['payment_intent_id' => $paymentData['payment_intent_id']],
            [
                'booking_id' => $booking->id,
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'],
                'customer_name' => $this->getCustomerName($booking),
                'customer_email' => $booking->customer?->email,
                'status' => 'completed',
                'payment_method' => $paymentData['payment_method'],
                'gateway_name' => $paymentData['gateway_name'],
                'gateway_ref' => $paymentData['gateway_ref'],
                'gateway_payload' => $paymentData['gateway_payload'],
            ]
        );
    }

    /**
     * Find booking from webhook data
     */
    private function findBookingFromWebhook(array $webhookData): ?Booking
    {
        $bookingId = $webhookData['booking_id'] ?? null;

        if (! $bookingId) {
            Log::warning('Webhook missing booking_id', [
                'payment_intent_id' => $webhookData['payment_intent_id'] ?? 'unknown',
            ]);

            return null;
        }

        $booking = Booking::find($bookingId);

        if (! $booking) {
            Log::warning('Booking not found for webhook', [
                'booking_id' => $bookingId,
                'payment_intent_id' => $webhookData['payment_intent_id'] ?? 'unknown',
            ]);
        }

        return $booking;
    }

    /**
     * Get formatted customer name
     */
    private function getCustomerName(Booking $booking): string
    {
        $customer = $booking->customer;

        if (! $customer) {
            return '';
        }

        return trim($customer->first_name . ' ' . $customer->last_name);
    }
}
