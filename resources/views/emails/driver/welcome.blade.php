@extends('emails.layouts')

@section('title', 'Welcome Aboard!')

@section('content')
    <span class="badge badge-success">Welcome to the Team!</span>
    <h2>Welcome, {{ $driver->name }}!</h2>
    <p>We are thrilled to have you join the {{ config('app.name') }} team. We are confident that you will be a valuable
        asset to our company and look forward to a successful partnership.</p>

    <div class="card">
        <h3>Your Profile Information</h3>
        <p>Your profile has been created in our system. Please review the details below and let us know if any corrections
            are needed:</p>
        <div class="detail-item">
            <span class="detail-label">Name:</span>
            <span class="detail-value">{{ $driver->name }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Email:</span>
            <span class="detail-value primary">{{ $driver->email }}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Phone:</span>
            <span class="detail-value">{{ $driver->phone ?? 'Not set' }}</span>
        </div>
    </div>

    <h3>Next Steps</h3>
    <p>You will receive job assignments via email to this address. Each assignment will contain all the necessary details
        for the trip, including customer information and pickup/drop-off locations.</p>

    <div class="alert alert-info">
        <strong>Important:</strong> Please ensure this email address is checked regularly for new job notifications and
        updates.
    </div>

    <p>If you have any questions, please contact the dispatch team at <a
            href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.</p>

    <p>We look forward to working with you!</p>
@endsection
