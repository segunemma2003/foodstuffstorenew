<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    private $kwikApiKey;
    private $uberApiKey;
    private $gokadaApiKey;
    private $goshiipApiKey;

    public function __construct()
    {
        $this->kwikApiKey = config('services.kwik.api_key');
        $this->uberApiKey = config('services.uber.api_key');
        $this->gokadaApiKey = config('services.gokada.api_key');
        $this->goshiipApiKey = config('services.goshiip.api_key');
    }

    public function estimateDelivery(Request $request)
    {
        $request->validate([
            'pickup_address' => 'required|string',
            'delivery_address' => 'required|string',
            'city' => 'required|in:lagos,abuja',
            'package_size' => 'required|in:small,medium,large'
        ]);

        // Cache key based on request parameters
        $cacheKey = "delivery_estimate_" . md5(json_encode($request->all()));

        // Try to get from cache first
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $deliveryOptions = [];

        // Get estimates from different services
        try {
            // Kwik Delivery Estimate
            $kwikEstimate = $this->getKwikEstimate($request);
            if ($kwikEstimate) {
                $deliveryOptions[] = $kwikEstimate;
            }
        } catch (\Exception $e) {
            Log::error('Kwik Delivery API Error: ' . $e->getMessage());
        }

        try {
            // Uber Delivery Estimate
            $uberEstimate = $this->getUberEstimate($request);
            if ($uberEstimate) {
                $deliveryOptions[] = $uberEstimate;
            }
        } catch (\Exception $e) {
            Log::error('Uber Delivery API Error: ' . $e->getMessage());
        }

        try {
            // Gokada Estimate
            $gokadaEstimate = $this->getGokadaEstimate($request);
            if ($gokadaEstimate) {
                $deliveryOptions[] = $gokadaEstimate;
            }
        } catch (\Exception $e) {
            Log::error('Gokada API Error: ' . $e->getMessage());
        }

        try {
            // Goshiip Estimate
            $goshiipEstimate = $this->getGoshiipEstimate($request);
            if ($goshiipEstimate) {
                $deliveryOptions[] = $goshiipEstimate;
            }
        } catch (\Exception $e) {
            Log::error('Goshiip API Error: ' . $e->getMessage());
        }

        if (empty($deliveryOptions)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to get delivery estimates at this time'
            ], 503);
        }

        // Sort by price and estimated time
        usort($deliveryOptions, function($a, $b) {
            // First sort by price
            if ($a['price'] !== $b['price']) {
                return $a['price'] <=> $b['price'];
            }
            // If prices are equal, sort by estimated time
            return $a['estimated_time'] <=> $b['estimated_time'];
        });

        // Get the best option
        $bestOption = $deliveryOptions[0];

        // Cache the result for 5 minutes
        Cache::put($cacheKey, $bestOption, 300);

        return response()->json($bestOption);
    }

    private function getKwikEstimate($request)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->kwikApiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.kwik.com/v1/estimate', [
            'pickup_address' => $request->pickup_address,
            'delivery_address' => $request->delivery_address,
            'city' => $request->city,
            'package_size' => $request->package_size
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'service' => 'Kwik Delivery',
                'price' => $data['price'],
                'estimated_time' => $data['estimated_time'],
                'delivery_partner' => [
                    'name' => $data['delivery_partner']['name'],
                    'phone' => $data['delivery_partner']['phone']
                ],
                'service_id' => 'kwik_' . $data['estimate_id']
            ];
        }

        return null;
    }

    private function getUberEstimate($request)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->uberApiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.uber.com/v1/deliveries/estimate', [
            'pickup_address' => $request->pickup_address,
            'delivery_address' => $request->delivery_address,
            'city' => $request->city,
            'package_size' => $request->package_size
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'service' => 'Uber Delivery',
                'price' => $data['price'],
                'estimated_time' => $data['estimated_time'],
                'delivery_partner' => [
                    'name' => $data['delivery_partner']['name'],
                    'phone' => $data['delivery_partner']['phone']
                ],
                'service_id' => 'uber_' . $data['estimate_id']
            ];
        }

        return null;
    }

    private function getGokadaEstimate($request)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->gokadaApiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.gokada.com/v1/estimate', [
            'pickup_address' => $request->pickup_address,
            'delivery_address' => $request->delivery_address,
            'city' => $request->city,
            'package_size' => $request->package_size
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'service' => 'Gokada',
                'price' => $data['price'],
                'estimated_time' => $data['estimated_time'],
                'delivery_partner' => [
                    'name' => $data['delivery_partner']['name'],
                    'phone' => $data['delivery_partner']['phone']
                ],
                'service_id' => 'gokada_' . $data['estimate_id']
            ];
        }

        return null;
    }

    private function getGoshiipEstimate($request)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->goshiipApiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.goshiip.com/v1/estimate', [
            'pickup_address' => $request->pickup_address,
            'delivery_address' => $request->delivery_address,
            'city' => $request->city,
            'package_size' => $request->package_size
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'service' => 'Goshiip',
                'price' => $data['price'],
                'estimated_time' => $data['estimated_time'],
                'delivery_partner' => [
                    'name' => $data['delivery_partner']['name'],
                    'phone' => $data['delivery_partner']['phone']
                ],
                'service_id' => 'goshiip_' . $data['estimate_id']
            ];
        }

        return null;
    }
}
