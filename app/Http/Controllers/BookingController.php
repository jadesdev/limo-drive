<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\GetQuoteRequest;
use App\Models\Booking;
use App\Services\BookingPaymentService;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BookingService $bookingService,
        protected BookingPaymentService $bookingPaymentService
    ) {}

    /**
     * Step 1: Get price and vehicles.
     *
     * @unauthenticated
     */
    public function getQuote(GetQuoteRequest $request): JsonResponse
    {
        $quoteData = $this->bookingService->getQuote($request->validated());

        return $this->dataResponse('Quote retrieved successfully.', $quoteData);
    }

    /**
     * Step 2: Submit booking Details and create payment intent.
     *
     * payment intent is created for stripe and paypal and they are use ed to charge customers.
     *
     * @unauthenticated
     */
    public function store(CreateBookingRequest $request)
    {
        $booking = $this->bookingService->createBooking($request->validated());

        $bookingData = [
            'booking_id' => $booking->id,
            'booking_code' => $booking->code,
            'price' => $booking->price,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
        ];
        $paymentMethod = $booking->payment_method;
        [$message, $paymentIntent] = match ($paymentMethod) {
            'cash' => [
                'Booking confirmed successfully. Payment will be collected in cash.',
                null,
            ],
            default => [
                'Booking created successfully. Please proceed to payment.',
                $this->bookingPaymentService->createPaymentIntent($booking),
            ],
        };

        return $this->dataResponse(
            $message,
            [
                'booking' => $bookingData,
                'payment' => $paymentIntent,
            ],
            201
        );
    }

    /**
     * Create a payment intent for a given booking.
     *
     * @unauthenticated
     */
    public function createPaymentIntent(Booking $booking): JsonResponse
    {
        if ($booking->payment_status === 'paid') {
            return $this->errorResponse('This booking has already been paid for.', 409);
        }

        $paymentIntent = $this->bookingPaymentService->createPaymentIntent($booking);

        return $this->dataResponse('Payment intent created successfully.', $paymentIntent);
    }

    /**
     * Confirm payment completion
     *
     * This handles cases where webhook fails or needs manual confirmation.
     *
     * @unauthenticated
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|uuid|exists:bookings,id',
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $result = $this->bookingPaymentService->confirmPayment(
                $validated['booking_id'],
                $validated['payment_intent_id']
            );

            if ($result['success']) {
                return $this->dataResponse(
                    'Payment confirmed successfully.',
                    [
                        'booking' => $result['booking'],
                        'payment_status' => 'paid',
                    ]
                );
            } else {
                return $this->errorResponse(
                    $result['message'] ?? 'Payment confirmation failed.',
                    400
                );
            }
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Payment confirmation error: ' . $e->getMessage(),
                422
            );
        }
    }

    /**
     * Get booking details by ID or Code
     *
     * @unauthenticated
     */
    public function show(string $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingDetails($id);

        return $this->dataResponse(
            'Booking retrieved successfully.',
            [
                'id' => $booking->id,
                'code' => $booking->code,
                'service_type' => $booking->service_type,
                'price' => $booking->price,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
                'created_at' => $booking->created_at->toISOString(),
                'updated_at' => $booking->updated_at->toISOString(),
            ]
        );
    }
}
