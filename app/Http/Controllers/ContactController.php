<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Models\ContactMessage;
use App\Traits\ApiResponse;
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
        $contactMessage = ContactMessage::create($validated);
        // Send email to admin

        return $this->successResponse('Contact message sent successfully');
    }
}
