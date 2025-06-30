<?php

namespace App\Services\PaymentGateways;

use App\Models\Booking;

interface PaymentGatewayInterface
{
    public function createPaymentIntent(Booking $booking): array;

    public function confirmPayment(Booking $booking, string $paymentIntentId): array;

    public function processWebhook($payload): ?array;
}
