<?php

namespace App\Mail\Contact;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MessageMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $contact) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $femail = config('mail.from.address');
        $fname = config('mail.from.name');

        return new Envelope(
            from: new Address($femail, $fname),
            subject: 'New Enquiry Received',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.send',
            with: [
                'contact' => $this->contact,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
