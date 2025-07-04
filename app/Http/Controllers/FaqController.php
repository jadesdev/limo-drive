<?php

namespace App\Http\Controllers;

use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ApiResponse;

    /**
     * Get all active faqs
     *
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $faqs = cache()->remember('faqList', now()->addHours(1), function () {
            return Faq::active()->orderBy('order', 'asc')->get();
        });

        // success response
        return $this->dataResponse('Faqs Fetched successfully', FaqResource::collection($faqs));
    }

    /**
     * Fetch all FAQs (Admin)
     */
    public function adminIndex(Request $request)
    {
        $query = Faq::query();
        $perPage = $request->input('per_page', 20);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Search by question or answer
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->orderBy('order', 'asc')->paginate($perPage);

        return $this->paginatedResponse('All FAQs', FaqResource::collection($faqs), $faqs);
    }

    /**
     * FAQ Details (Admin)
     */
    public function adminShow(Faq $faq)
    {
        return $this->dataResponse('FAQ Details', FaqResource::make($faq));
    }

    /**
     * Add new FAQ
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:2000',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:1',
        ]);

        // Set order if not provided (next highest order)
        if (empty($validated['order'])) {
            $validated['order'] = Faq::max('order') + 1;
        }

        $faq = Faq::create($validated);

        cache()->forget('faqList');

        return $this->dataResponse('FAQ created successfully', FaqResource::make($faq), 201);
    }

    /**
     * Update FAQ
     */
    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'sometimes|required|string|max:500',
            'answer' => 'sometimes|required|string|max:2000',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:1',
        ]);

        $faq->update($validated);

        cache()->forget('faqList');

        return $this->dataResponse('FAQ updated successfully', FaqResource::make($faq->fresh()));
    }

    /**
     *  Delete FAQ
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();

        cache()->forget('faqList');

        return $this->successResponse('FAQ deleted successfully');
    }

    /**
     * Toggle FAQ status
     */
    public function toggleStatus(Faq $faq)
    {
        $faq->update(['is_active' => ! $faq->is_active]);

        cache()->forget('faqList');

        $status = $faq->is_active ? 'activated' : 'deactivated';

        return $this->dataResponse("FAQ {$status} successfully", FaqResource::make($faq));
    }
}
