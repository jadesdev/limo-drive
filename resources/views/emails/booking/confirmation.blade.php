@extends('emails.layouts')

@section('title', 'Your Booking is Confirmed!')

@section('content')
    <span class="badge badge-success">Booking Confirmed</span>
    <h2>Thank You for Your Booking, {{ $booking->customer->first_name }}!</h2>
    <p>Your luxury transportation is scheduled. Here are your trip details:</p>

    <div class="card">
        <h3>Trip Summary</h3>
        <div class="detail-item">
            <span class="detail-label">Booking Code:</span>
            <span class="detail-value primary">{{ $booking->code }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Service:</span>
            <span class="detail-value">{{ Str::title(str_replace('_', ' ', $booking->service_type)) }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Vehicle:</span>
            <span class="detail-value">{{ $booking->fleet->name }}</span>
        </div>
    </div>

    <div class="card">
        <h3>Pickup Details</h3>
        <div class="detail-item">
            <span class="detail-label">Date:</span>
            <span class="detail-value">{{ $booking->pickup_datetime->format('l, F j, Y') }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Time:</span>
            <span class="detail-value">{{ $booking->pickup_datetime->format('g:i A') }}
                ({{ $booking->pickup_datetime->tzName }})</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Address:</span>
            <span class="detail-value">{{ $booking->pickup_address }}</span>
        </div>
    </div>

    @if ($booking->dropoff_address)
        <div class="card">
            <h3>Drop-off Details</h3>
            <div class="detail-item">
                <span class="detail-label">Address:</span>
                <span class="detail-value">{{ $booking->dropoff_address }}</span>
            </div>
        </div>
    @endif

    <div class="card">
        <h3>Payment Details</h3>
        <div class="detail-item">
            <span class="detail-label">Total Paid:</span>
            <span class="detail-value price">${{ number_format($booking->price, 2) }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Payment Method:</span>
            <span class="detail-value">
                {{ $booking->payment_method === 'stripe' ? 'Card' : Str::title($booking->payment_method) }}
            </span>
        </div>
    </div>


    <p>If you have any questions or need to make changes, please don't hesitate to contact us.</p>
@endsection
