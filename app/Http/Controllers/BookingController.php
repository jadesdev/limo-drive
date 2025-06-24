<?php

namespace App\Http\Controllers;

use App\Actions\BookingAction;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\GetQuoteRequest;
use App\Models\Booking;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(protected BookingAction $bookingAction) {}

    /**
     * Get a price quote booking.
     *
     * @unauthenticated
     */
    public function getQuote(GetQuoteRequest $request): JsonResponse
    {
        $quoteData = $this->bookingAction->getQuote($request->validated());

        return $this->dataResponse('Quote retrieved successfully.', $quoteData);
    }

    /**
     * Store a new booking.
     *
     * @unauthenticated
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingAction->createBooking($request->validated());

        return $this->dataResponse(
            'Booking created successfully. Please proceed to payment.',
            [
                'booking_id' => $booking->id,
                'booking_code' => $booking->code,
                'price' => $booking->price,
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

        $paymentIntent = $this->bookingAction->createPaymentIntent($booking);

        return $this->dataResponse('Payment intent created successfully.', [
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }
}
