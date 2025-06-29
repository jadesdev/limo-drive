@extends('emails.layouts')

@section('title', 'New Job Assignment')
@php
    $pickupQuery =
        $booking->pickup_latitude && $booking->pickup_longitude
            ? $booking->pickup_latitude . ',' . $booking->pickup_longitude
            : urlencode($booking->pickup_address);
@endphp
@section('content')
    <span class="badge badge-primary">New Job Assignment</span>
    <h2>You have a new job assignment for Booking #{{ $booking->code }}.</h2>
    <p>Please review the details below carefully.</p>

    <div class="card">
        <h3>Trip Details</h3>
        <div class="detail-item">
            <span class="detail-label">Booking Service:</span>
            <span class="detail-value">{{ Str::title(str_replace('_', ' ', $booking->service_type)) }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Pickup Date:</span>
            <span class="detail-value">{{ $booking->pickup_datetime->format('l, F j, Y') }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Pickup Time:</span>
            <span class="detail-value">{{ $booking->pickup_datetime->format('g:i A') }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Pickup Location:</span>
            <span class="detail-value">
                {{ $booking->pickup_address }}<br>
                <a href="https://www.google.com/maps/search/?api=1&query={{ $pickupQuery }}"
                    target="_blank">Open in Maps</a>
            </span>
        </div>
        @if ($booking->dropoff_address)
            <div class="detail-item">
                <span class="detail-label">Drop-off Location:</span>
                <span class="detail-value">
                    {{ $booking->dropoff_address }}<br>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($booking->dropoff_address) }}"
                        target="_blank">Open in Maps</a>
                </span>
            </div>
        @endif
    </div>

    <div class="card">
        <h3>Customer & Trip Info</h3>
        <div class="detail-item">
            <span class="detail-label">Customer Name:</span>
            <span class="detail-value">{{ $booking->customer->first_name }} {{ $booking->customer->last_name }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Customer Phone:</span>
            <span class="detail-value">{{ $booking->customer->phone ?? 'Not provided' }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Passengers:</span>
            <span class="detail-value">{{ $booking->passenger_count }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Luggage:</span>
            <span class="detail-value">{{ $booking->bag_count }} bags</span>
        </div>
    </div>

    @if ($booking->notes)
        <div class="alert alert-warning">
            <strong>Customer Notes:</strong><br>
            {{ $booking->notes }}
        </div>
    @endif

    {{-- <div class="btn-container">
        <a href="{{ config('app.url') }}" class="btn btn-primary">Acknowledge Job</a>
    </div> --}}
@endsection
