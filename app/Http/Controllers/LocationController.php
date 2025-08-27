<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Fetch all locations with any necessary relationships (e.g., productQuantities)
            $locations = Location::get();

            // Return the locations in a JSON response
            return response()->json($locations, 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to fetch locations: ' . $e->getMessage());

            // Return a 500 error response if something goes wrong
            return response()->json(['error' => 'Failed to fetch locations. Please try again later.'], 500);
        }
    }
}
