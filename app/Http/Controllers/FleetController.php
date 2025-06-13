<?php

namespace App\Http\Controllers;

use App\Http\Resources\FleetResource;
use App\Models\Fleet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FleetController extends Controller
{
    /**
     * Fetch all fleets
     *
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $fleets = Fleet::active()->orderBy('order', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'All Fleets',
            'data' => FleetResource::collection($fleets),
        ]);
    }

    /**
     * Fetch a fleet by slug or id
     *
     * @unauthenticated
     */
    public function show($slug)
    {
        if (Str::isUuid($slug)) {
            $fleet = Fleet::where('id', $slug)->firstOrFail();
        } else {
            $fleet = Fleet::where('slug', $slug)->firstOrFail();
        }

        return response()->json([
            'success' => true,
            'message' => 'Fleet Details',
            'data' => FleetResource::make($fleet),
        ]);
    }
}
