@extends('emails.layouts')

@section('title', 'Job Reminder')

@section('content')
    <span class="badge badge-warning">Job Reminder</span>
    <h2>Upcoming Job: {{ $booking->pickup_datetime->format('l, F j, Y') }}</h2>
    <p>This is a reminder for your assigned job tomorrow. Please review the details to ensure you are prepared.</p>

    <div class="card">
        <h3>Essential Details</h3>
        <div class="detail-item">
            <span class="detail-label">Booking Code:</span>
            <span class="detail-value primary">{{ $booking->code }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Scheduled Time:</span>
            <span class="detail-value">{{ $booking->pickup_datetime->format('g:i A') }}
                ({{ $booking->pickup_datetime->tzName }})</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Pickup Location:</span>
            <span class="detail-value">
                {{ $booking->pickup_address }}<br>
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($booking->pickup_address) }}"
                    target="_blank">Open in Maps</a>
            </span>
        </div>
        @if ($booking->dropoff_address)
            <div class="detail-item">
                <span class="detail-label">Drop-off:</span>
                <span class="detail-value">{{ $booking->dropoff_address }}</span>
            </div>
        @endif
        <div class="detail-item">
            <span class="detail-label">Vehicle:</span>
            <span class="detail-value">{{ $booking->fleet->name }}</span>
        </div>
    </div>

    <div class="card">
        <h3>Customer Information</h3>
        <div class="detail-item">
            <span class="detail-label">Name:</span>
            <span class="detail-value">{{ $booking->customer->first_name }} {{ $booking->customer->last_name }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Contact Phone:</span>
            <span class="detail-value">{{ $booking->customer->phone ?? 'Not provided' }}</span>
        </div>
    </div>

    @if ($booking->notes)
        <div class="alert alert-warning">
            <strong>Important Customer Notes:</strong><br>
            {{ $booking->notes }}
        </div>
    @endif

    <div class="alert alert-info">
        Please be at the pickup location 15 minutes prior to the scheduled time. Ensure the vehicle is clean and ready for
        service.
    </div>
@endsection
