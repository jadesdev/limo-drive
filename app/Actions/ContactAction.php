<?php

namespace App\Actions;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Stevebauman\Purify\Facades\Purify;

class ContactAction
{
    /**
     * Create a new contact message
     *
     * @param array $data
     * @return ContactMessage
     */
    public function createContactMessage(array $data): ContactMessage
    {
        $validated = Purify::clean($data);

        $adminEmail = config('mail.from.address');
        $this->sendEmail($adminEmail, 'New Enquiry Received', $validated['message']);
        return ContactMessage::create($validated);
    }

    /**
     * Get paginated contact messages with optional search
     *
     * @param string|null $search
     * @param int $perPage
     * @return LengthAwarePaginator
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
     * @param string $id
     * @return ContactMessage
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
     * @param string $id
     * @return bool
     */
    public function markAsOpened(ContactMessage $contact): bool
    {
        return $contact->update(['status' => 'opened', 'is_read' => true]);
    }

    /**
     * Delete a contact message
     *
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function deleteMessage(ContactMessage $contact): bool
    {
        return $contact->delete();
    }

    /**
     * Send a reply to a contact message
     *
     */
    public function sendReply(ContactMessage $contact, array $replyData): bool
    {
        $contact->update([
            'status' => 'replied',
            'is_read' => true,
        ]);

        $this->sendEmail($contact->email, 'Re: ' . $contact->subject, $replyData['message']);

        return true;
    }


    function sendEmail($email, $subject, $message)
    {
        // Mail::to($email)->send(new ContactReplyMail($message));
    }
}
