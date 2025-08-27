<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;

class WebhookRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'webhook:' . ($request->ip() ?? 'global');

        $executed = RateLimiter::attempt(
            $key,
            100, // Max 100 requests
            function() {},
            60 // Per minute
        );

        if (!$executed) {
            return response()->json(['error' => 'Too many requests'], 429);
        }

        return $next($request);
    }
}
