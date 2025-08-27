<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiKeyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index(Request $request)
    {
        $apiKeys = $request->user()->apiKeys()
            ->with('business')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $apiKeys
        ]);
    }

    public function store(StoreApiKeyRequest $request)
    {
        $business = $request->user()->business;

        if (!$business) {
            return response()->json([
                'message' => 'You must be associated with a business to create an API key'
            ], 403);
        }

        $apiKey = $request->user()->apiKeys()->create([
            'name' => $request->name,
            'key' => Str::random(64),
            'is_active' => true,
            'business_id' => $business->id,
        ]);

        return response()->json([
            'message' => 'API key created successfully',
            'data' => $apiKey
        ], 201);
    }

    public function destroy($id)
    {
        $apiKey = request()->user()->apiKeys()
            ->where('id', $id)
            ->firstOrFail();

        $apiKey->delete();

        return response()->json([
            'message' => 'API key deleted successfully'
        ]);
    }
}
