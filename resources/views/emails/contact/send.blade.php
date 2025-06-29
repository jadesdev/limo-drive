@extends('emails.layouts')

@section('title', 'New Contact Message - ' . config('app.name'))

@section('content')
    <span class="badge badge-danger">New Message</span>
    <h2>Contact Form Submission</h2>

    <p>A new contact message has been received through your website.</p>

    {{-- Contact Information Card --}}
    <div class="card">
        <h3>👤 Contact Details</h3>

        <div class="detail-item">
            <span class="detail-label">Name:</span>
            <span class="detail-value">{{ $contact->name }}</span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Email:</span>
            <span class="detail-value primary">{{ $contact->email }}</span>
        </div>

        @if ($contact->phone)
            <div class="detail-item">
                <span class="detail-label">Phone:</span>
                <span class="detail-value success">{{ $contact->phone }}</span>
            </div>
        @endif


    </div>

    {{-- Message Content --}}
    <div class="card card-white">
        <h3>💬 Message</h3>
        <p style="font-style: italic; color: #2c3e50; line-height: 1.7; font-size: 16px;">
            "{{ $contact->message }}"
        </p>
        <p class="text-muted mb-0" style="font-size: 13px; margin-top: 15px;">
            Submitted on {{ $contact->created_at->format('M d, Y \a\t g:i A') }}
            @if ($contact->ip_address)
                • IP: {{ $contact->ip_address }}
            @endif
        </p>
    </div>

    {{-- Action Buttons --}}
    <div class="btn-container">
        <div class="btn-group">
            <a href="mailto:{{ $contact->email }}?subject=Re: New Enquiry Received" class="btn btn-primary">
                📧 Reply via Email
            </a>
            @if ($contact->phone)
                <a href="tel:{{ $contact->phone }}" class="btn btn-secondary">
                    📞 Call Customer
                </a>
            @endif
        </div>
    </div>

    {{-- Response Time Alert --}}
    <div class="alert alert-warning">
        <strong>⚠️ Response Time:</strong> Please respond within 2 hours during business hours for optimal customer
        satisfaction.
    </div>
@endsection
