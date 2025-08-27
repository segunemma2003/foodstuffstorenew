<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\DeliveryFareEstimate;
use App\Models\DeliveryOrder;

class FoodDeliveryController extends Controller
{
    private $kwikBaseUrl = 'https://staging-api-test.kwik.delivery/';
    private $kwikDomain = 'staging-client-panel.kwik.delivery';
    private $kwikEmail = 'support@foodstuff.store';
    private $kwikPassword = 'Kwik2025$';
    private $accessToken = "94c2f2d5de81a1d9205e55e74b5ee9b2";
    private $vendorId = "3526";

    /**
     * Get KWIK access token
     */
    private function getKwikAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $response = Http::post($this->kwikBaseUrl . 'vendor_login', [
                'email' => $this->kwikEmail,
                'password' => $this->kwikPassword,
                'domain_name' => $this->kwikDomain,
                'api_login' => 1
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status'] == 200) {
                $this->accessToken = $data['data']['access_token'];
                $this->vendorId = $data['data']['vendor_id'];
                return $this->accessToken;
            }

            throw new \Exception('Failed to get access token: ' . ($data['message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('KWIK Login Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get vehicle ID for bike (size 0)
     */
    private function getVehicleId()
    {
        try {
            $response = Http::get($this->kwikBaseUrl . 'getVehicle', [
                'access_token' => $this->getKwikAccessToken(),
                'is_vendor' => 1,
                'size' => 0 // 0 = bike
            ]);
            // var_dump($response->body());
            $data = $response->json();

            if ($response->successful() && $data['status'] == 200) {
                Log::info('KWIK Vehicle ID Response: ', $data);
                return $data['data'][0]['id'] ?? 1; // Return first vehicle ID or default to 1
            }

            return 1; // Default vehicle ID
        } catch (\Exception $e) {
            Log::error('Vehicle ID Error: ' . $e->getMessage());
            return 1; // Default vehicle ID
        }
    }

    /**
     * API 1: Get Estimated Fare for Food Delivery
     */
    public function getEstimatedFare(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_address' => 'required|string',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'pickup_phone' => 'required|string',
            'pickup_email' => 'required|email',
            'delivery_address' => 'required|string',
            'delivery_latitude' => 'required|numeric',
            'delivery_longitude' => 'required|numeric',
            'delivery_phone' => 'required|string',
            'delivery_email' => 'required|email',
            'restaurant_name' => 'required|string',
            'customer_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

       try {
    $this->getKwikAccessToken();
    $vehicleId = $this->getVehicleId();

    // Log the access token and vehicle ID
    Log::info('KWIK API Debug - Access Token: ' . substr($this->accessToken, 0, 10) . '...');
    Log::info('KWIK API Debug - Vehicle ID: ' . $vehicleId);
    Log::info('KWIK API Debug - Vendor ID: ' . $this->vendorId);

    // Prepare pickup and delivery data
    $pickupData = [
        'address' => $request->pickup_address,
        'email' => $request->pickup_email,
        'phone' => $request->pickup_phone,
        'latitude' => (string)$request->pickup_latitude,
        'longitude' => (string)$request->pickup_longitude,
        'name' => $request->restaurant_name
    ];

    $deliveryData = [
        'address' => $request->delivery_address,
        'email' => $request->delivery_email,
        'phone' => $request->delivery_phone,
        'latitude' => (string)$request->delivery_latitude,
        'longitude' => (string)$request->delivery_longitude,
        'name' => $request->customer_name,
        'has_return_task' => false
    ];

    // Log the request payload
    $requestPayload = [
        'access_token' => $this->accessToken,
        'domain_name' => $this->kwikDomain,
        'vendor_id' => $this->vendorId,
        'auto_assignment' => 1,
        'layout_type' => 0,
        'has_pickup' => 1,
        'has_delivery' => 1,
        'is_multiple_tasks' => 1,
        'payment_method' => 524288, // End of month billing
        'form_id' => 2,
        'is_schedule_task' => 0,
        'pickups' => [$pickupData],
        'deliveries' => [$deliveryData],
        'is_loader_required' => 0,
        'loaders_amount' => 0,
        'loaders_count' => 0,
        'delivery_instruction' => 'Food delivery - Handle with care',
        'vehicle_id' => $vehicleId,
        'delivery_images' => '',
        'is_cod_job' => 0,
        'parcel_amount' => 0
    ];

    Log::info('KWIK API Request Payload: ', $requestPayload);

    // Call KWIK pricing API
    $pricingResponse = Http::post($this->kwikBaseUrl . 'send_payment_for_task', $requestPayload);

    // Log the full response
    Log::info('KWIK API Response Status: ' . $pricingResponse->status());
    Log::info('KWIK API Response Headers: ', $pricingResponse->headers());
    Log::info('KWIK API Response Body: ' . $pricingResponse->body());

    $pricingData = $pricingResponse->json();
    Log::info('KWIK API Parsed Response: ', $pricingData ?: ['parse_error' => 'Failed to parse JSON']);

    if (!$pricingResponse->successful()) {
        throw new \Exception('HTTP Error ' . $pricingResponse->status() . ': ' . $pricingResponse->body());
    }

    if (!isset($pricingData['status']) || $pricingData['status'] != 200) {
        throw new \Exception('API Error - Status: ' . ($pricingData['status'] ?? 'unknown') . ', Message: ' . ($pricingData['message'] ?? 'No message provided') . ', Full Response: ' . json_encode($pricingData));
    }
            // Save fare estimate to database
            $fareEstimate = DeliveryFareEstimate::create([
                'pickup_address' => $request->pickup_address,
                'pickup_latitude' => $request->pickup_latitude,
                'pickup_longitude' => $request->pickup_longitude,
                'pickup_phone' => $request->pickup_phone,
                'pickup_email' => $request->pickup_email,
                'delivery_address' => $request->delivery_address,
                'delivery_latitude' => $request->delivery_latitude,
                'delivery_longitude' => $request->delivery_longitude,
                'delivery_phone' => $request->delivery_phone,
                'delivery_email' => $request->delivery_email,
                'restaurant_name' => $request->restaurant_name,
                'customer_name' => $request->customer_name,
                'vehicle_id' => $vehicleId,
                'kwik_response' => json_encode($pricingData['data']),
                'fare_amount' => $pricingData['data']['per_task_cost'] ?? 0,
                'service_charge' => $pricingData['data']['total_service_charge'] ?? 0,
                'insurance_amount' => $pricingData['data']['insurance_amount'] ?? 0,
                'total_tasks' => $pricingData['data']['total_no_of_tasks'] ?? 1,
                'expires_at' => now()->addHours(2) // Fare estimate expires in 2 hours
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fare estimated successfully',
                'data' => [
                    'fare_unique_id' => $fareEstimate->id,
                    'estimated_fare' => $pricingData['data']['per_task_cost'] ?? 0,
                    'service_charge' => $pricingData['data']['total_service_charge'] ?? 0,
                    'insurance_amount' => $pricingData['data']['insurance_amount'] ?? 0,
                    'total_amount' => ($pricingData['data']['per_task_cost'] ?? 0) + ($pricingData['data']['total_service_charge'] ?? 0),
                    'currency' => 'NGN',
                    'expires_at' => $fareEstimate->expires_at->toISOString(),
                    'distance_km' => $pricingData['data']['distance'] ?? 0,
                    'estimated_duration_minutes' => $pricingData['data']['duration'] ?? 0
                ]
            ]);

        } catch (\Exception $e) {
           Log::error('Fare Estimation Error: ' . $e->getMessage());
    Log::error('Error Trace: ' . $e->getTraceAsString());
    return response()->json([
        'success' => false,
        'message' => 'Failed to estimate fare: ' . $e->getMessage()
    ], 500);
        }
    }

    /**
     * API 2: Create Food Delivery Order
     */
    public function createDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fare_unique_id' => 'required|exists:delivery_fare_estimates,id',
            'order_reference' => 'required|string|unique:delivery_orders,order_reference',
            'special_instructions' => 'nullable|string',
            'pickup_time' => 'nullable|date',
            'delivery_time' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Get fare estimate from database
            $fareEstimate = DeliveryFareEstimate::findOrFail($request->fare_unique_id);

            // Check if fare estimate has expired
            if ($fareEstimate->expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fare estimate has expired. Please get a new estimate.'
                ], 400);
            }

            // Check if fare estimate is already used
            if ($fareEstimate->is_used) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fare estimate has already been used.'
                ], 400);
            }

            $this->getKwikAccessToken();
            $kwikData = json_decode($fareEstimate->kwik_response, true);

            // Prepare pickup and delivery data
            $pickupData = [
                'address' => $fareEstimate->pickup_address,
                'name' => $fareEstimate->restaurant_name,
                'latitude' => (string)$fareEstimate->pickup_latitude,
                'longitude' => (string)$fareEstimate->pickup_longitude,
                'time' => $request->pickup_time ?? now()->addMinutes(30)->format('Y-m-d H:i:s'),
                'phone' => $fareEstimate->pickup_phone,
                'email' => $fareEstimate->pickup_email,
                'template_data' => [
                    [
                        'label' => 'Order Reference',
                        'data' => $request->order_reference
                    ],
                    [
                        'label' => 'Item Type',
                        'data' => 'Food Delivery'
                    ],
                    [
                        'label' => 'Weight',
                        'data' => '0.5kg'
                    ]
                ]
            ];

            $deliveryData = [
                'address' => $fareEstimate->delivery_address,
                'name' => $fareEstimate->customer_name,
                'latitude' => (string)$fareEstimate->delivery_latitude,
                'longitude' => (string)$fareEstimate->delivery_longitude,
                'time' => $request->delivery_time ?? now()->addMinutes(60)->format('Y-m-d H:i:s'),
                'phone' => $fareEstimate->delivery_phone,
                'email' => $fareEstimate->delivery_email,
                'template_data' => [
                    [
                        'label' => 'Order Reference',
                        'data' => $request->order_reference
                    ],
                    [
                        'label' => 'Special Instructions',
                        'data' => $request->special_instructions ?? 'Handle with care - Food delivery'
                    ]
                ],
                'has_return_task' => false,
                'is_package_insured' => 0
            ];

            // Create delivery task
            $deliveryResponse = Http::post($this->kwikBaseUrl . 'v2/create_task_via_vendor', [
                'domain_name' => $this->kwikDomain,
                'access_token' => $this->accessToken,
                'vendor_id' => $this->vendorId,
                'is_multiple_tasks' => 1,
                'timezone' => '-60', // Lagos timezone (UTC+1)
                'has_pickup' => 1,
                'has_delivery' => 1,
                'layout_type' => 0,
                'auto_assignment' => 1,
                'team_id' => 1, // Default team
                'pickups' => [$pickupData],
                'deliveries' => [$deliveryData],
                'insurance_amount' => $fareEstimate->insurance_amount,
                'total_no_of_tasks' => $fareEstimate->total_tasks,
                'total_service_charge' => $fareEstimate->service_charge,
                'payment_method' => 524288, // End of month billing
                'amount' => $fareEstimate->fare_amount,
                'is_loader_required' => 0,
                'loaders_amount' => 0,
                'loaders_count' => 0,
                'delivery_instruction' => $request->special_instructions ?? 'Food delivery - Handle with care',
                'vehicle_id' => $fareEstimate->vehicle_id,
                'delivery_images' => '',
                'is_cod_job' => 0,
                'surge_cost' => 0,
                'surge_type' => 0,
                'is_task_otp_required' => 0
            ]);

            $deliveryData = $deliveryResponse->json();

            if (!$deliveryResponse->successful() || $deliveryData['status'] != 200) {
                throw new \Exception('Failed to create delivery: ' . ($deliveryData['message'] ?? 'Unknown error'));
            }

            // Save delivery order to database
            $deliveryOrder = DeliveryOrder::create([
                'fare_estimate_id' => $fareEstimate->id,
                'order_reference' => $request->order_reference,
                'kwik_job_id' => $deliveryData['data']['job_id'],
                'kwik_job_token' => $deliveryData['data']['job_token'],
                'pickup_tracking_link' => $deliveryData['data']['pickup_tracking_link'],
                'delivery_tracking_link' => $deliveryData['data']['delivery_tracing_link'],
                'pickup_hash' => $deliveryData['data']['pickup_hash'],
                'delivery_hash' => $deliveryData['data']['delivery_hash'],
                'special_instructions' => $request->special_instructions,
                'status' => 'created',
                'kwik_response' => json_encode($deliveryData['data'])
            ]);

            // Mark fare estimate as used
            $fareEstimate->update(['is_used' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Delivery created successfully',
                'data' => [
                    'delivery_id' => $deliveryOrder->id,
                    'order_reference' => $request->order_reference,
                    'kwik_job_id' => $deliveryData['data']['job_id'],
                    'pickup_tracking_link' => $deliveryData['data']['pickup_tracking_link'],
                    'delivery_tracking_link' => $deliveryData['data']['delivery_tracing_link'],
                    'estimated_fare' => $fareEstimate->fare_amount,
                    'service_charge' => $fareEstimate->service_charge,
                    'total_amount' => $fareEstimate->fare_amount + $fareEstimate->service_charge,
                    'status' => 'created',
                    'pickup_address' => $fareEstimate->pickup_address,
                    'delivery_address' => $fareEstimate->delivery_address,
                    'created_at' => $deliveryOrder->created_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Delivery Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API 3: Track Delivery Status
     */
    public function trackDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'sometimes|exists:delivery_orders,id',
            'order_reference' => 'sometimes|exists:delivery_orders,order_reference',
            'kwik_job_id' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Find delivery order
            $deliveryOrder = null;
            if ($request->delivery_id) {
                $deliveryOrder = DeliveryOrder::findOrFail($request->delivery_id);
            } elseif ($request->order_reference) {
                $deliveryOrder = DeliveryOrder::where('order_reference', $request->order_reference)->firstOrFail();
            } elseif ($request->kwik_job_id) {
                $deliveryOrder = DeliveryOrder::where('kwik_job_id', $request->kwik_job_id)->firstOrFail();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide delivery_id, order_reference, or kwik_job_id'
                ], 400);
            }

            $this->getKwikAccessToken();

            // Get job status from KWIK
            $statusResponse = Http::get($this->kwikBaseUrl . 'getJobStatus', [
                'unique_order_id' => $deliveryOrder->kwik_job_id,
                'customer_id' => $this->vendorId
            ]);

            $statusData = $statusResponse->json();

            if ($statusResponse->successful() && $statusData['status'] == 200) {
                $jobStatus = $statusData['data']['job_status'] ?? 6;
                $statusText = $this->getStatusText($jobStatus);

                // Update local status
                $deliveryOrder->update([
                    'job_status' => $jobStatus,
                    'status' => strtolower($statusText),
                    'last_status_update' => now()
                ]);

                $fareEstimate = $deliveryOrder->fareEstimate;

                return response()->json([
                    'success' => true,
                    'message' => 'Delivery status retrieved successfully',
                    'data' => [
                        'delivery_id' => $deliveryOrder->id,
                        'order_reference' => $deliveryOrder->order_reference,
                        'kwik_job_id' => $deliveryOrder->kwik_job_id,
                        'status' => strtolower($statusText),
                        'status_code' => $jobStatus,
                        'status_description' => $this->getStatusDescription($jobStatus),
                        'pickup_tracking_link' => $deliveryOrder->pickup_tracking_link,
                        'delivery_tracking_link' => $deliveryOrder->delivery_tracking_link,
                        'pickup_address' => $fareEstimate->pickup_address,
                        'delivery_address' => $fareEstimate->delivery_address,
                        'restaurant_name' => $fareEstimate->restaurant_name,
                        'customer_name' => $fareEstimate->customer_name,
                        'agent_details' => $statusData['data']['agent_details'] ?? null,
                        'last_updated' => $deliveryOrder->last_status_update->toISOString(),
                        'created_at' => $deliveryOrder->created_at->toISOString()
                    ]
                ]);
            } else {
                throw new \Exception('Failed to get delivery status: ' . ($statusData['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error('Delivery Tracking Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to track delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status text from status code
     */
    private function getStatusText($statusCode)
    {
        $statuses = [
            0 => 'UPCOMING',
            1 => 'STARTED',
            2 => 'ENDED',
            3 => 'FAILED',
            4 => 'ARRIVED',
            6 => 'UNASSIGNED',
            7 => 'ACCEPTED',
            8 => 'DECLINED',
            9 => 'CANCELLED',
            10 => 'DELETED'
        ];

        return $statuses[$statusCode] ?? 'UNKNOWN';
    }

    /**
     * Get status description
     */
    private function getStatusDescription($statusCode)
    {
        $descriptions = [
            0 => 'The task has been assigned to an agent.',
            1 => 'The task has been started and the agent is on the way.',
            2 => 'The task has been completed successfully.',
            3 => 'The task has been completed unsuccessfully.',
            4 => 'The task is being performed and the agent has reached the destination.',
            6 => 'The task has not been assigned to any agent.',
            7 => 'The task has been accepted by the assigned agent.',
            8 => 'The task has been declined by the assigned agent.',
            9 => 'The task has been cancelled.',
            10 => 'The task has been deleted from the dashboard.'
        ];

        return $descriptions[$statusCode] ?? 'Unknown status';
    }
}
