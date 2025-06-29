<?php

namespace App\Mail\Contact;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public array $data) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $femail = config('mail.from.address');
        $fname = config('mail.from.name');

        return new Envelope(
            from: new Address($femail, $fname),
            subject: 'Re: ' . $this->data['subject'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.reply',
            with: [
                'subject' => $this->data['subject'],
                'contact' => $this->data['contact'],
                'responseMessage' => $this->data['responseMessage'] ?? null,
                'adminName' => $this->data['adminName'] ?? null,
                'adminTitle' => $this->data['adminTitle'] ?? null,
                'bookingUrl' => $this->data['bookingUrl'] ?? null,
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
