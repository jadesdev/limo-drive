<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Fetch all services
     * 
     * @unauthenticated
     */
    function index(Request $request)
    {
        $services = Service::active()->orderBy('order', 'asc')->get();
        return response()->json([
            'success' => true,
            'message' => 'All Services',
            'data' => ServiceResource::collection($services)
        ]);
    }


    /**
     * Fetch a service by slug or id
     * 
     * @unauthenticated
     */
    function show($slug)
    {
        if (Str::isUuid($slug)) {
            $service = Service::where('id', $slug)->firstOrFail();
        } else {
            $service = Service::where('slug', $slug)->firstOrFail();
        }
        return response()->json([
            'success' => true,
            'message' => 'Service Details',
            'data' => ServiceResource::make($service)
        ]);
    }
}
