@extends('emails.layouts')

@section('title', 'Thank You For Riding With Us!')

@section('content')
    <span class="badge badge-success">Trip Completed</span>
    <h2>Thank You, {{ $booking->customer->first_name }}!</h2>
    <p>We appreciate you choosing {{ config('app.name') }} for your trip on {{ $booking->pickup_datetime->format('F jS') }}.
        We hope your experience was exceptional.</p>

    <div class="card card-white">
        <h3>How Was Your Experience?</h3>
        <p>Your feedback is invaluable to us. It helps us improve our service and recognize our excellent chauffeurs. Please
            take a moment to leave a review.</p>

        {{-- <div class="btn-container">
            <a href="https://www.google.com" class="btn btn-primary">Leave a Review</a>
        </div> --}}
    </div>

    <div class="card">
        <h3>Your Trip Receipt</h3>
        <div class="detail-item">
            <span class="detail-label">Booking Code:</span>
            <span class="detail-value primary">{{ $booking->code }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Vehicle:</span>
            <span class="detail-value">{{ $booking->fleet->name }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Chauffeur:</span>
            <span class="detail-value">{{ $booking->driver->name ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Total Paid:</span>
            <span class="detail-value price">${{ number_format($booking->price, 2) }}</span>
        </div>
    </div>

    <p class="text-center">We look forward to serving you again soon.</p>
@endsection
