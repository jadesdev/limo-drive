@extends('emails.layouts')

@section('title', 'Re: ' . $subject)

@section('content')
    <h2>Re: {{ $subject }}</h2>

    <p>Hello {{ $contact->name }},</p>

    <p>Thank you for your inquiry about our limousine services.
        {{ $adminMessage ?? 'We are pleased to provide you with the following information:' }}</p>

    {{-- Admin's Response --}}
    @if (isset($responseMessage))
        <div class="card card-white" style="padding: 20px;">
            <h3>💼 Our Response</h3>
            <div style="color: #2c3e50; line-height: 1.7;">
                {!! nl2br(e($responseMessage)) !!}
            </div>
        </div>
    @endif

    {{-- Call to Action --}}
    <div class="btn-container">
        @if (isset($bookingUrl))
            <a href="{{ $bookingUrl }}" class="btn btn-primary">Book Now</a>
        @endif
        <a href="mailto:{{ config('app.email', 'support@ikengalimo.com') }}?subject=Re: {{ $subject }}"
            class="btn btn-secondary">Reply to This Email</a>
    </div>

    {{-- Additional Information --}}
    <div class="alert alert-info">
        <strong>🕒 Business Hours:</strong> Monday - Friday: 9:00 AM - 6:00 PM | Saturday - Sunday: 10:00 AM - 4:00 PM<br>
        <strong>📞 24/7 Booking Hotline:</strong> (555) 000-0000<br>
        <strong>📧 Email:</strong> support@ikengalimo.com
    </div>

    <p>We look forward to serving you and providing an exceptional limousine experience.</p>

    <p>Best regards,<br>
        {{ $adminName ?? 'The Ikenga Limo Team' }}<br>
        <span class="text-muted">{{ $adminTitle ?? 'Customer Service Team' }}</span>
    </p>
@endsection

@section('footer_meta',
    'This is a reply to your inquiry submitted on ' .
    $contact->created_at->format('M d,
    Y'))
