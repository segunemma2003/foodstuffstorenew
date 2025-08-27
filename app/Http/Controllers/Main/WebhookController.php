<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'company_name' => 'required|string|max:255',
        'company_id' => 'required|string|max:255|unique:webhooks,company_id',
        'url' => 'required|url',
        'events' => 'nullable|array',
        'events.*' => 'string',
        'strict_ip' => 'boolean',
        'allowed_ips' => 'nullable|array',
        'allowed_ips.*' => 'ip',
        'data_filters' => 'nullable|array',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $webhook = Webhook::create([
        'company_name' => $request->company_name,
        'company_id' => $request->company_id,
        'url' => $request->url,
        'secret' => Str::random(40),
        'events' => $request->events,
        'strict_ip' => $request->boolean('strict_ip', true),
        'allowed_ips' => $request->allowed_ips,
        'data_filters' => $request->data_filters,
    ]);

    return response()->json([
        'message' => 'Webhook registered successfully',
        'webhook' => $webhook,
    ], 201);
}

// Add new methods for IP management
public function addAllowedIps(Request $request, Webhook $webhook)
{
    $validator = Validator::make($request->all(), [
        'ips' => 'required|array',
        'ips.*' => 'ip',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $currentIps = $webhook->allowed_ips ?? [];
    $newIps = array_unique(array_merge($currentIps, $request->ips));

    $webhook->update(['allowed_ips' => $newIps]);

    return response()->json([
        'message' => 'IPs added successfully',
        'allowed_ips' => $newIps,
    ]);
}

public function removeAllowedIps(Request $request, Webhook $webhook)
{
    $validator = Validator::make($request->all(), [
        'ips' => 'required|array',
        'ips.*' => 'ip',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $currentIps = $webhook->allowed_ips ?? [];
    $newIps = array_diff($currentIps, $request->ips);

    $webhook->update(['allowed_ips' => $newIps]);

    return response()->json([
        'message' => 'IPs removed successfully',
        'allowed_ips' => $newIps,
    ]);
}

    // List all webhooks (for admin purposes)
    public function index()
    {
        return response()->json(Webhook::all());
    }

    // Delete a webhook
    public function delete(Webhook $webhook)
    {
        $webhook->delete();
        return response()->json(['message' => 'Webhook deleted successfully']);
    }
}
