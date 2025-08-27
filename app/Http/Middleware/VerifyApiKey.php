<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $request->bearerToken();



        if (!$key) {
            return response()->json(['message' => 'API key required'], 401);
        }

        $apiKey = ApiKey::where('key', $key)
            ->where('is_active', true)
            ->with('business') // Eager load business
            ->first();
            // var_dump($apiKey->business);

        if (!$apiKey) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        // Update last used at
        $apiKey->update(['last_used_at' => now()]);
        // var_dump($apiKey->business);
        // Attach business to request for controller access
        $request->merge(['api_business' => $apiKey->business]);
        $request->attributes->set('api_business', $apiKey->business);

        return $next($request);
    }
}
