<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private string $clientId;
    private string $secret;
    private string $baseUrl;
    
    // Cache key for storing access token
    private const ACCESS_TOKEN_CACHE_KEY = 'paypal_access_token';

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->secret = config('services.paypal.secret');
        $this->baseUrl = config('services.paypal.mode') === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api.paypal.com';
    }

    /**
     * Get PayPal OAuth access token with proper caching
     *
     * @throws Exception
     */
    private function getAccessToken(): string
    {
        // Try to get cached token first
        $cachedToken = Cache::get(self::ACCESS_TOKEN_CACHE_KEY);
        
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = Http::withBasicAuth($this->clientId, $this->secret)
                ->timeout(30)
                ->asForm()
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful()) {
                throw new Exception("PayPal OAuth failed with status {$response->status()}: {$response->body()}");
            }

            $data = $response->json();
            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 32400;
            
            $cacheTime = max(60, $expiresIn - 300); 
            
            Cache::put(self::ACCESS_TOKEN_CACHE_KEY, $accessToken, $cacheTime);

            return $accessToken;

        } catch (Exception $e) {
            Log::error('PayPal getAccessToken failed', [
                'error' => $e->getMessage(),
                'client_id' => $this->clientId,
                'base_url' => $this->baseUrl,
            ]);

            throw new Exception('Failed to retrieve PayPal access token: ' . $e->getMessage());
        }
    }

    /**
     * Create a PayPal payment order
     *
     * @throws Exception
     */
    public function createPayment(float $amount, string $currency, array $details): array
    {
        try {
            $accessToken = $this->getAccessToken();
            $formattedAmount = number_format($amount, 2, '.', '');

            $payload = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => $formattedAmount,
                        ],
                        'description' => $details['description'] ?? 'Payment',
                        'custom_id' => $details['reference'] ?? null,
                        'invoice_id' => $details['booking_code'] ?? null,
                    ],
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'landing_page' => 'BILLING',
                ],
            ];

            // Add return/cancel URLs only if provided (for redirect flow)
            if (isset($details['returnUrl'])) {
                $payload['application_context']['return_url'] = $details['returnUrl'];
            }
            if (isset($details['cancelUrl'])) {
                $payload['application_context']['cancel_url'] = $details['cancelUrl'];
            }

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post("{$this->baseUrl}/v2/checkout/orders", $payload);

            if (!$response->successful()) {
                throw new Exception("PayPal create order failed with status {$response->status()}: {$response->body()}");
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('PayPal createPayment failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'currency' => $currency,
                'details' => $details,
            ]);

            throw new Exception('Failed to create PayPal payment: ' . $e->getMessage());
        }
    }

    /**
     * Capture a PayPal order (complete the payment)
     *
     * @throws Exception
     */
    public function captureOrder(string $orderId): array
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

            if (!$response->successful()) {
                throw new Exception("PayPal capture failed with status {$response->status()}: {$response->body()}");
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('PayPal captureOrder failed', [
                'error' => $e->getMessage(),
                'orderId' => $orderId,
            ]);

            throw new Exception('Failed to capture PayPal order: ' . $e->getMessage());
        }
    }

    /**
     * Get order details
     *
     * @throws Exception
     */
    public function getOrderDetails(string $orderId): array
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->get("{$this->baseUrl}/v2/checkout/orders/{$orderId}");

            if (!$response->successful()) {
                throw new Exception("PayPal get order failed with status {$response->status()}: {$response->body()}");
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('PayPal getOrderDetails failed', [
                'error' => $e->getMessage(),
                'orderId' => $orderId,
            ]);

            throw new Exception('Failed to get PayPal order details: ' . $e->getMessage());
        }
    }

    /**
     * Verify webhook signature (for webhook handling)
     *
     * @param string $requestBody Raw request body
     * @param array $headers Request headers
     * @throws Exception
     */
    public function verifyWebhookSignature(string $requestBody, array $headers): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            
            $webhookId = config('services.paypal.webhook_id');
            if (!$webhookId) {
                throw new Exception('PayPal webhook ID not configured');
            }

            $payload = [
                'transmission_id' => $headers['paypal-transmission-id'] ?? '',
                'cert_id' => $headers['paypal-cert-id'] ?? '',
                'auth_algo' => $headers['paypal-auth-algo'] ?? '',
                'transmission_time' => $headers['paypal-transmission-time'] ?? '',
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($requestBody, true),
            ];

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", $payload);

            if ($response->successful()) {
                $result = $response->json();
                return ($result['verification_status'] ?? '') === 'SUCCESS';
            }

            Log::warning('PayPal webhook verification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('PayPal webhook verification error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clear cached access token (useful for testing or error recovery)
     */
    public function clearTokenCache(): void
    {
        Cache::forget(self::ACCESS_TOKEN_CACHE_KEY);
    }
}