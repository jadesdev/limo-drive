<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Exception;
use Log;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class BookingPaymentService
{
    /**
     * Create a Stripe or PayPal Payment Intent for a booking.
     */
    public function createPaymentIntent(Booking $booking)
    {
        if ($booking->payment_method === 'paypal') {
            return $this->createPayPalOrder($booking);
        } elseif ($booking->payment_method === 'stripe') {
            return $this->createStripePaymentIntent($booking);
        }
        throw new Exception('Invalid payment method: ' . $booking->payment_method);
    }

    /**
     * Create a PayPal Payment Intent for a booking.
     */
    public function createPayPalOrder(Booking $booking)
    {
        $paypalService = app(PayPalService::class);
        $paymentIntent = $paypalService->createPayment($booking->price, 'USD', [
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

    /**
     * Create a Stripe Payment Intent for a booking.
     */
    public function createStripePaymentIntent(Booking $booking)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $booking->price * 100,
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
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

    /**
     * Confirm payment and update booking status
     * This is used as a fallback when webhook fails
     */
    public function confirmPayment(string $bookingId, string $paymentIntentId)
    {
        try {
            $booking = Booking::findOrFail($bookingId);
            if ($booking->payment_method === 'stripe') {
                return $this->confirmStripePayment($booking, $paymentIntentId);
            } elseif ($booking->payment_method === 'paypal') {
                return $this->confirmPaypalPayment($booking, $paymentIntentId);
            }

            return [
                'success' => false,
                'message' => 'Invalid Booking payment',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to confirm payment: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to confirm payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm a PayPal payment
     */
    public function confirmPaypalPayment($booking, $paymentIntentId)
    {
        $paypalService = app(PayPalService::class);
        $paymentIntent = $paypalService->getOrderDetails($paymentIntentId);

        if ($paymentIntent['status'] !== 'APPROVED') {
            return [
                'success' => false,
                'message' => 'Payment has not been approved yet.',
            ];
        }

        $code = $paymentIntent['purchase_units'][0]['custom_id'] ?? null;
        if ($code !== $booking->id) {
            return [
                'success' => false,
                'message' => 'Payment does not match this booking.',
            ];
        }

        if ($booking->status === 'pending_payment') {
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'payment_intent_id' => $paymentIntent['id'],
                'amount' => (float) $paymentIntent['purchase_units'][0]['amount']['value'],
                'currency' => $paymentIntent['purchase_units'][0]['amount']['currency_code'],
                'customer_name' => $booking->customer?->first_name . ' ' . $booking->customer?->last_name,
                'customer_email' => $booking->customer?->email,
                'status' => 'completed',
                'payment_method' => 'paypal',
                'gateway_name' => 'paypal',
                'gateway_ref' => $paymentIntent['purchase_units'][0]['reference_id'],
                'gateway_payload' => $paymentIntent,
            ]);
        }

        return [
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
            ],
        ];
    }

    /**
     * Confirm a Stripe payment
     */
    public function confirmStripePayment($booking, $paymentIntentId)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
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

        if ($booking->status === 'pending_payment') {
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'customer_name' => $booking->customer?->first_name . ' ' . $booking->customer?->last_name,
                'customer_email' => $booking->customer?->email,
                'status' => 'completed',
                'payment_method' => 'stripe',
                'gateway_name' => 'stripe',
                'gateway_ref' => $paymentIntent->payment_method,
                'gateway_payload' => $paymentIntent,
            ]);
        }

        return [
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
            ],
        ];
    }

    /**
     * Process webhook payment confirmation for Stripe
     */
    public function processStripeWebhook($paymentIntent): bool
    {
        try {
            $bookingId = $paymentIntent->metadata->booking_id ?? null;

            if (! $bookingId) {
                \Log::warning('Webhook payment intent missing booking_id', [
                    'payment_intent_id' => $paymentIntent->id,
                ]);

                return false;
            }

            $booking = Booking::find($bookingId);
            if (! $booking) {
                \Log::warning('Booking not found for webhook', [
                    'booking_id' => $bookingId,
                    'payment_intent_id' => $paymentIntent->id,
                ]);

                return false;
            }

            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            Payment::updateOrCreate(
                ['payment_intent_id' => $paymentIntent->id],
                [
                    'booking_id' => $booking->id,
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => $paymentIntent->currency,
                    'customer_name' => $booking->customer?->first_name . ' ' . $booking->customer?->last_name,
                    'customer_email' => $booking->customer?->email,
                    'status' => 'completed',
                    'payment_method' => 'stripe',
                    'gateway_name' => 'stripe',
                    'gateway_ref' => $paymentIntent->payment_method,
                    'gateway_payload' => $paymentIntent,
                ]
            );

            \Log::info('Booking payment confirmed via webhook', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->code,
                'gateway' => 'stripe',
                'payment_intent_id' => $paymentIntent->id,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Webhook payment processing failed', [
                'gateway' => 'stripe',
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process PayPal webhook payment confirmation
     */
    public function processPaypalWebhook($bookingId, $paymentResponse)
    {
        try {
            $booking = Booking::find($bookingId);
            if (! $booking) {
                \Log::warning('Booking not found for webhook', [
                    'booking_id' => $bookingId,
                    'gateway' => 'paypal',
                    'payment_intent_id' => $paymentResponse['resource']['id'] ?? null,
                ]);

                return false;
            }
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            Payment::updateOrCreate(
                ['payment_intent_id' => $paymentResponse['resource']['id']],
                [
                    'booking_id' => $booking->id,
                    'amount' => (float) $paymentResponse['resource']['amount'],
                    'currency' => $paymentResponse['resource']['currency_code'],
                    'customer_name' => $booking->customer?->first_name . ' ' . $booking->customer?->last_name,
                    'customer_email' => $booking->customer?->email,
                    'status' => 'completed',
                    'payment_method' => 'paypal',
                    'gateway_name' => 'paypal',
                    'gateway_ref' => $paymentResponse['resource']['purchase_units'][0]['reference_id'],
                    'gateway_payload' => $paymentResponse,
                ]
            );

            \Log::info('Booking payment confirmed via webhook', [
                'booking_id' => $booking->id,
                'gateway' => 'paypal',
                'booking_code' => $booking->code,
                'payment_intent_id' => $paymentResponse['resource']['id'],
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('paypal webhook payment processing failed', [
                'booking_id' => $bookingId,
                'gateway' => 'paypal',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
