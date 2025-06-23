<?php

namespace App\Http\Controllers;

use App\Actions\ContactAction;
use App\Http\Requests\ContactFormRequest;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiResponse;

    public function __construct(protected ContactAction $contactAction) {}

    /**
     * Send a contact message
     *
     * @unauthenticated
     * @param ContactFormRequest $request
     * @return JsonResponse
     */
    public function store(ContactFormRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();
        $this->contactAction->createContactMessage($data);

        return $this->successResponse('Contact message sent successfully');
    }

    /**
     * Fetch all contact messages
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 20);

        $contactMessages = $this->contactAction->getPaginatedMessages($search, $perPage);

        return $this->paginatedResponse(
            'All Contact Messages',
            ContactMessageResource::collection($contactMessages),
            $contactMessages
        );
    }

    /**
     * Fetch a contact message by id
     *
     * @param ContactMessage $contact
     * @return JsonResponse
     */
    public function show(ContactMessage $contact): JsonResponse
    {
        $this->contactAction->markAsOpened($contact);

        return $this->dataResponse(
            'Contact Message Details',
            ContactMessageResource::make($contact)
        );
    }

    /**
     * Send a reply to a contact message
     *
     * @param ContactMessage $contact
     * @param Request $request
     * @return JsonResponse
     */
    public function reply(ContactMessage $contact, Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|min:10',
        ]);

        $this->contactAction->sendReply($contact, [
            'message' => $request->message,
        ]);

        return $this->successResponse('Reply sent successfully');
    }

    /**
     * Delete a contact message
     *
     * @param ContactMessage $contact
     * @return JsonResponse
     */
    public function destroy(ContactMessage $contact): JsonResponse
    {
        $this->contactAction->deleteMessage($contact);

        return $this->successResponse('Contact message deleted successfully');
    }
}
