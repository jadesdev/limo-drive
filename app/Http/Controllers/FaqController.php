<?php

namespace App\Http\Controllers;

use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{

    /**
     * Get all active faqs
     * 
     * @unauthenticated
     */
    public function index()
    {
        $faqs = Faq::active()->orderBy('order', 'asc')->get();
        // success response
        return response()->json(
            [
                'success' => true,
                'message' => 'Faqs Fetched successfully',
                'data' => FaqResource::collection($faqs)
            ]
        );
    }
}
