<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;

class ContactController extends Controller
{
    use ApiResponse;

    /**
     * Send a contact message
     *
     * @unauthenticated
     */
    public function store(ContactFormRequest $request)
    {
        $validated = Purify::clean($request->validated());
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $contactMessage = ContactMessage::create($validated);
        // Send email to admin

        return $this->successResponse('Contact message sent successfully');
    }

    /**
     * Fetch all contact messages
     *
     */
    public function adminIndex(Request $request)
    {
        $query = ContactMessage::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('message', 'like', '%' . $request->search . '%');
        }

        $contactMessages = $query->orderBy('created_at', 'desc')->paginate(20);

        return $this->paginatedResponse('All Contact Messages', ContactMessageResource::collection($contactMessages), $contactMessages);
    }

    /**
     * Fetch a contact message by id
     *
     */
    public function adminShow(ContactMessage $contact)
    {
        return $this->dataResponse('Contact Message Details', ContactMessageResource::make($contact));
    }

    /**
     * Delete a contact message
     *
     */
    public function destroy(ContactMessage $contact)
    {
        $contact->delete();

        return $this->successResponse('Contact Message deleted successfully');
    }
}
