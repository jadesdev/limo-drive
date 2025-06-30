@extends('emails.layouts')

@section('title', 'Trip Reminder')

@section('content')
    <span class="badge badge-warning">Friendly Reminder</span>
    <h2>Your Trip is Tomorrow!</h2>
    <p>This is a friendly reminder about your upcoming trip with {{ config('app.name') }}. Please review the details below.
    </p>

    <div class="card">
        <h3>Trip Summary</h3>
        <div class="detail-item">
            <span class="detail-label">Booking Code:</span>
            <span class="detail-value primary">{{ $booking->code }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Pickup Date & Time:</span>
            <span class="detail-value">{{ $booking->pickup_datetime->format('l, F j, Y') }} at
                {{ $booking->pickup_datetime->format('g:i A') }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Pickup Location:</span>
            <span class="detail-value">{{ $booking->pickup_address }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Vehicle:</span>
            <span class="detail-value">{{ $booking->fleet->name }}</span>
        </div>
    </div>

    @if ($booking->driver)
        <div class="alert alert-info">
            <strong>Your Chauffeur:</strong> Your assigned chauffeur for this trip is
            <strong>{{ $booking->driver->name }}</strong>.
        </div>
    @endif

    <p>We look forward to providing you with an exceptional experience. If you have any last-minute questions, please feel
        free to contact us.</p>
@endsection
