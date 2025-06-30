<?php

namespace App\Http\Controllers;

use App\Services\BookingPaymentService;
use App\Services\PayPalService;
use App\Traits\ApiResponse;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    use ApiResponse;

    public function __construct(private PayPalService $paypalService) {}

    #[ExcludeRouteFromDocs]
    public function handleWebhook(Request $request)
    {
        try {
            // Get raw request body
            $requestBody = $request->getContent();

            // Get headers for signature verification
            $headers = [
                'paypal-transmission-id' => $request->header('paypal-transmission-id'),
                'paypal-cert-id' => $request->header('paypal-cert-id'),
                'paypal-auth-algo' => $request->header('paypal-auth-algo'),
                'paypal-transmission-time' => $request->header('paypal-transmission-time'),
            ];

            // Verify webhook signature
            if (! $this->paypalService->verifyWebhookSignature($requestBody, $headers)) {
                Log::warning('PayPal webhook signature verification failed', [
                    'headers' => $headers,
                    'body' => $requestBody,
                ]);

                // return response('Webhook signature verification failed', 400);
            }

            // Parse webhook data
            $webhookData = json_decode($requestBody, true);

            if (! $webhookData) {
                Log::error('PayPal webhook: Invalid JSON payload');

                return response('Invalid JSON payload', 400);
            }

            // Handle different webhook event types
            $eventType = $webhookData['event_type'] ?? '';

            switch ($eventType) {
                case 'CHECKOUT.ORDER.APPROVED':
                    $this->handleOrderApproved($webhookData);
                    break;

                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePaymentCaptured($webhookData);
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                    $this->handlePaymentDenied($webhookData);
                    break;

                case 'PAYMENT.CAPTURE.REFUNDED':
                    $this->handlePaymentRefunded($webhookData);
                    break;

                default:
                    Log::info('PayPal webhook: Unhandled event type', [
                        'event_type' => $eventType,
                        'data' => $webhookData,
                    ]);
            }

            return response('Webhook processed successfully', 200);
        } catch (\Exception $e) {
            Log::error('PayPal webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('Webhook processing failed', 500);
        }
    }

    private function handleOrderApproved(array $webhookData)
    {
        $orderId = $webhookData['resource']['id'] ?? null;
        $bookingId = null;
        if (
            isset($webhookData['resource']['purchase_units']) &&
            is_array($webhookData['resource']['purchase_units']) &&
            count($webhookData['resource']['purchase_units']) > 0 &&
            isset($webhookData['resource']['purchase_units'][0]['custom_id'])
        ) {
            $bookingId = $webhookData['resource']['purchase_units'][0]['custom_id'];
        }

        if (! $orderId || ! $bookingId) {
            Log::error('PayPal webhook: Missing order ID or booking ID in CHECKOUT.ORDER.APPROVED');

            return;
        }

        if (! $orderId || ! $bookingId) {
            Log::error('PayPal webhook: Missing order ID or booking ID in CHECKOUT.ORDER.APPROVED');

            return;
        }

        Log::info('PayPal order approved', ['order_id' => $orderId]);
        $bookingService = app(BookingPaymentService::class);
        $success = $bookingService->processWebhook('paypal',$webhookData);

        if ($success) {
            \Log::info('Booking confirmed via webhook', [
                'payment_intent_id' => $orderId,
                'booking_id' => $bookingId,
            ]);

            // TODO: Send confirmation email to customer
            // TODO: Send notification to admin/driver
            // $this->sendBookingConfirmationEmail($paymentIntent->metadata->booking_id);

        } else {
            \Log::error('Failed to process successful payment webhook', [
                'payment_intent_id' => $orderId,
            ]);
        }
    }

    private function handlePaymentCaptured(array $webhookData): void
    {
        $resource = $webhookData['resource'] ?? [];
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;
        $captureId = $resource['id'] ?? null;
        $amount = $resource['amount'] ?? [];

        if (! $orderId || ! $captureId) {
            Log::error('PayPal webhook: Missing required data in PAYMENT.CAPTURE.COMPLETED', [
                'resource' => $resource,
            ]);

            return;
        }

        Log::info('PayPal payment captured', [
            'order_id' => $orderId,
            'capture_id' => $captureId,
            'amount' => $amount,
        ]);

        // Update your database - mark payment as completed
        // dispatch(new ProcessPayPalPaymentCapture($orderId, $captureId, $amount));
    }

    private function handlePaymentDenied(array $webhookData): void
    {
        $resource = $webhookData['resource'] ?? [];
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        Log::warning('PayPal payment denied', [
            'order_id' => $orderId,
            'resource' => $resource,
        ]);

        // Update your database - mark payment as failed
        // dispatch(new ProcessPayPalPaymentDenied($orderId));
    }

    private function handlePaymentRefunded(array $webhookData): void
    {
        $resource = $webhookData['resource'] ?? [];
        $refundId = $resource['id'] ?? null;
        $amount = $resource['amount'] ?? [];

        Log::info('PayPal payment refunded', [
            'refund_id' => $refundId,
            'amount' => $amount,
        ]);

        // Update your database - process refund
        // dispatch(new ProcessPayPalRefund($refundId, $amount));
    }
}
