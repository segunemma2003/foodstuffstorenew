<?php

namespace App\Http\Controllers;

use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    /**
     * Get delivery price estimates from multiple services
     */
    public function getEstimates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_address' => 'required|string',
            'delivery_address' => 'required|string',
            'weight' => 'numeric|min:0.1|max:20',
            'dimensions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $estimates = $this->deliveryService->getEstimatedPrice(
            $request->pickup_address,
            $request->delivery_address,
            $request->weight ?? 0.5,
            $request->dimensions ?? []
        );

        return response()->json($estimates);
    }

    /**
     * Create a delivery request
     */
    public function createDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service' => 'required|string|in:kwik,bolt,gokada',
            'pickup_address' => 'required|string',
            'pickup_name' => 'required|string',
            'pickup_phone' => 'required|string',
            'delivery_address' => 'required|string',
            'delivery_name' => 'required|string',
            'delivery_phone' => 'required|string',
            'weight' => 'numeric|min:0.1|max:20',
            'description' => 'nullable|string',
            'payment_method' => 'nullable|string|in:card,cash,wallet',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $deliveryResult = $this->deliveryService->createDeliveryRequest(
            $request->service,
            $request->all()
        );

        return response()->json($deliveryResult);
    }

    /**
     * Track a delivery
     */
    public function trackDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service' => 'required|string|in:kwik,bolt,gokada',
            'tracking_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $trackingResult = $this->deliveryService->trackDelivery(
            $request->service,
            $request->tracking_id
        );

        return response()->json($trackingResult);
    }
}
