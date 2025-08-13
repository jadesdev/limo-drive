@extends('emails.layouts')

@section('title', 'New Booking Received!')

@section('content')
    <span class="badge badge-primary">New Booking</span>
    <h2>A new booking has been received and paid for.</h2>
    <p>Please review the details below and assign a driver if necessary.</p>

    <div class="card">
        <h3>Customer Details</h3>
        <div class="detail-item">
            <span class="detail-label">Name:</span>
            <span class="detail-value">{{ $booking->customer->first_name }} {{ $booking->customer->last_name }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Email:</span>
            <span class="detail-value primary">{{ $booking->customer->email }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Phone:</span>
            <span class="detail-value success">{{ $booking->customer->phone }}</span>
        </div>
    </div>

    <div class="card">
        <h3>Trip Details</h3>
        <div class="detail-item">
            <span class="detail-label">Booking Code:</span>
            <span class="detail-value">{{ $booking->code }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Payment Method:</span>
            <span class="detail-value">{{ $booking->payment_method }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Payment Status:</span>
            <span class="detail-value">{{ $booking->payment_status }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Pickup:</span>
            <span class="detail-value">{{ $booking->pickup_datetime->format('M d, Y @ g:i A') }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Vehicle:</span>
            <span class="detail-value">{{ $booking->fleet->name }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Price:</span>
            <span class="detail-value price">${{ number_format($booking->price, 2) }}</span>
        </div>
    </div>

@endsection
