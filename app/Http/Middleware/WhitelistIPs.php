<?php

namespace App\Http\Middleware;

use App\Models\Webhook;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WhitelistIPs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for registration endpoint
        if ($request->is('api/webhooks/register')) {
            return $next($request);
        }

        // Get webhook by secret (from header or parameter)
        $secret = $request->header('X-Webhook-Secret') ?? $request->input('secret');

        if (!$secret) {
            return response()->json(['error' => 'Webhook secret required'], 401);
        }

        $webhook = Webhook::where('secret', $secret)->first();

        if (!$webhook) {
            return response()->json(['error' => 'Invalid webhook secret'], 403);
        }

        // If strict IP is disabled, allow all IPs
        if (!$webhook->strict_ip) {
            return $next($request);
        }

        // Check if IP is allowed
        $allowedIPs = $webhook->allowed_ips ?? [];
        $clientIP = $request->ip();

        if (!empty($allowedIPs) && !in_array($clientIP, $allowedIPs)) {
            return response()->json(['error' => 'IP address not allowed'], 403);
        }

        return $next($request);
    }
}
