<?php

namespace App\Actions;

use App\Mail\Contact\MessageMail;
use App\Mail\Contact\ReplyMail;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Mail;
use Stevebauman\Purify\Facades\Purify;

class ContactAction
{
    /**
     * Create a new contact message
     */
    public function createContactMessage(array $data): ContactMessage
    {
        $validated = Purify::clean($data);

        $adminEmail = config('mail.from.address');

        $contact = ContactMessage::create($validated);
        Mail::to($adminEmail)->queue(new MessageMail($contact));
        return $contact;
    }

    /**
     * Get paginated contact messages with optional search
     */
    public function getPaginatedMessages(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = ContactMessage::query();

        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get a single contact message by ID
     *
     * @param  string  $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getMessageById(ContactMessage $contact): ContactMessage
    {
        $contact->update(['status' => 'opened', 'is_read' => true]);

        return $contact;
    }

    /**
     * Mark message as opened
     *
     * @param  string  $id
     */
    public function markAsOpened(ContactMessage $contact): bool
    {
        return $contact->update(['status' => 'opened', 'is_read' => true]);
    }

    /**
     * Delete a contact message
     *
     * @param  string  $id
     *
     * @throws \Exception
     */
    public function deleteMessage(ContactMessage $contact): bool
    {
        return $contact->delete();
    }

    /**
     * Send a reply to a contact message
     */
    public function sendReply(ContactMessage $contact, array $replyData): bool
    {
        $contact->update([
            'status' => 'replied',
            'is_read' => true,
        ]);

        // Prepare variables for the reply email view
        $adminName = config('mail.from.name', 'The LuxeRide Team');
        $adminTitle = 'Customer Service Team';
        $responseMessage = $replyData['message'];
        $bookingUrl = $replyData['booking_url'] ?? null;

        Mail::to($contact->email)->queue(new ReplyMail([
            'contact' => $contact,
            'responseMessage' => $responseMessage,
            'adminName' => $adminName,
            'adminTitle' => $adminTitle,
            'bookingUrl' => $bookingUrl,
            'subject' => "Enquiry from " . config('app.name'),
        ]));

        return true;
    }
}
