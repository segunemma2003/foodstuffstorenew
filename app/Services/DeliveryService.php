<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DeliveryService
{
    protected $client;
    protected $kwikApiKey;
    protected $boltApiKey;
    protected $gokadaApiKey;
    protected $timeout = 7; // 7 second timeout

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => $this->timeout,
        ]);

        $this->kwikApiKey = config('services.kwik.api_key');
        $this->boltApiKey = config('services.bolt.api_key');
        $this->gokadaApiKey = config('services.gokada.api_key');
    }

    /**
     * Get estimated price from multiple delivery services concurrently
     *
     * @param string $pickupAddress
     * @param string $deliveryAddress
     * @param float $weight
     * @param array $packageDimensions
     * @return array
     */
    public function getEstimatedPrice(
        string $pickupAddress,
        string $deliveryAddress,
        float $weight = 0.5,
        array $packageDimensions = []
    ): array {
        // Create promises for concurrent requests
        $promises = [
            'kwik' => $this->getKwikEstimatePromise($pickupAddress, $deliveryAddress, $weight, $packageDimensions),
            'bolt' => $this->getBoltEstimatePromise($pickupAddress, $deliveryAddress, $weight, $packageDimensions),
            'gokada' => $this->getGokadaEstimatePromise($pickupAddress, $deliveryAddress, $weight, $packageDimensions),
        ];

        try {
            // Wait for all promises to complete or fail
            $responses = Promise\Utils::settle($promises)->wait();

            // Process responses
            $estimates = [];
            foreach ($responses as $service => $response) {
                if ($response['state'] === 'fulfilled') {
                    $estimates[$service] = $this->processEstimateResponse($service, $response['value']);
                } else {
                    Log::error("Error fetching estimate from {$service}: " . $response['reason']);
                }
            }

            // Find the best option based on delivery time and price
            return $this->findBestDeliveryOption($estimates);
        } catch (\Exception $e) {
            Log::error('Error fetching delivery estimates: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch delivery estimates',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a delivery request with selected service
     *
     * @param string $service
     * @param array $deliveryDetails
     * @return array
     */
    public function createDeliveryRequest(string $service, array $deliveryDetails): array
    {
        try {
            switch ($service) {
                case 'kwik':
                    return $this->createKwikDelivery($deliveryDetails);
                case 'bolt':
                    return $this->createBoltDelivery($deliveryDetails);
                case 'gokada':
                    return $this->createGokadaDelivery($deliveryDetails);
                default:
                    return [
                        'success' => false,
                        'message' => 'Invalid delivery service selected'
                    ];
            }
        } catch (\Exception $e) {
            Log::error("Error creating {$service} delivery: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Failed to create delivery with {$service}",
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Track a delivery status
     *
     * @param string $service
     * @param string $trackingId
     * @return array
     */
    public function trackDelivery(string $service, string $trackingId): array
    {
        try {
            // First check if we have a cached response to avoid unnecessary API calls
            $cacheKey = "delivery_track_{$service}_{$trackingId}";
            $cachedResponse = Cache::get($cacheKey);

            if ($cachedResponse) {
                return $cachedResponse;
            }

            $trackingResponse = match($service) {
                'kwik' => $this->trackKwikDelivery($trackingId),
                'bolt' => $this->trackBoltDelivery($trackingId),
                'gokada' => $this->trackGokadaDelivery($trackingId),
                default => [
                    'success' => false,
                    'message' => 'Invalid delivery service selected'
                ],
            };

            // Cache the response for 30 seconds
            if ($trackingResponse['success'] ?? false) {
                Cache::put($cacheKey, $trackingResponse, 30);
            }

            return $trackingResponse;
        } catch (\Exception $e) {
            Log::error("Error tracking {$service} delivery: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Failed to track delivery with {$service}",
                'error' => $e->getMessage()
            ];
        }
    }

    // Helper methods for each service's price estimation

    private function getKwikEstimatePromise(string $pickupAddress, string $deliveryAddress, float $weight, array $dimensions): Promise\PromiseInterface
    {
        $payload = [
            'pickup_address' => $pickupAddress,
            'delivery_address' => $deliveryAddress,
            'weight' => $weight,
            'dimensions' => $dimensions,
        ];

        return $this->client->requestAsync('POST', 'https://api.kwik.delivery/v1/estimates', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->kwikApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'json' => $payload
        ]);
    }

    private function getBoltEstimatePromise(string $pickupAddress, string $deliveryAddress, float $weight, array $dimensions): Promise\PromiseInterface
    {
        $payload = [
            'pickup_location' => $pickupAddress,
            'destination' => $deliveryAddress,
            'parcel_weight' => $weight,
            'parcel_dimensions' => $dimensions,
        ];

        return $this->client->requestAsync('POST', 'https://api.bolt.eu/delivery/v1/estimates', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->boltApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'json' => $payload
        ]);
    }

    private function getGokadaEstimatePromise(string $pickupAddress, string $deliveryAddress, float $weight, array $dimensions): Promise\PromiseInterface
    {
        $payload = [
            'pickup_address' => $pickupAddress,
            'delivery_address' => $deliveryAddress,
            'weight' => $weight,
            'dimensions' => $dimensions,
        ];

        return $this->client->requestAsync('POST', 'https://api.gokada.ng/api/v2/calculate_price', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->gokadaApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'json' => $payload
        ]);
    }

    // Process estimate responses

    private function processEstimateResponse(string $service, $response): array
    {
        $body = json_decode($response->getBody()->getContents(), true);

        // Extract relevant information based on the service
        switch ($service) {
            case 'kwik':
                return [
                    'service' => 'kwik',
                    'price' => $body['data']['price'] ?? 0,
                    'currency' => 'NGN',
                    'estimated_delivery_time' => $body['data']['estimated_delivery_time'] ?? 0, // in minutes
                    'raw_response' => $body
                ];
            case 'bolt':
                return [
                    'service' => 'bolt',
                    'price' => $body['price'] ?? 0,
                    'currency' => 'NGN',
                    'estimated_delivery_time' => $body['estimated_time'] ?? 0, // in minutes
                    'raw_response' => $body
                ];
            case 'gokada':
                return [
                    'service' => 'gokada',
                    'price' => $body['amount'] ?? 0,
                    'currency' => 'NGN',
                    'estimated_delivery_time' => $body['estimated_delivery_time'] ?? 0, // in minutes
                    'raw_response' => $body
                ];
            default:
                return [
                    'service' => $service,
                    'price' => 0,
                    'currency' => 'NGN',
                    'estimated_delivery_time' => 0,
                    'raw_response' => $body
                ];
        }
    }

    // Find the best delivery option
    private function findBestDeliveryOption(array $estimates): array
    {
        if (empty($estimates)) {
            return [
                'success' => false,
                'message' => 'No delivery estimates available'
            ];
        }

        // Sort by estimated delivery time (ascending)
        usort($estimates, function($a, $b) {
            return $a['estimated_delivery_time'] <=> $b['estimated_delivery_time'];
        });

        // If the fastest option is within 120 minutes (2 hours), choose it
        if ($estimates[0]['estimated_delivery_time'] <= 120) {
            return [
                'success' => true,
                'message' => 'Fastest delivery option found',
                'selected_service' => $estimates[0]['service'],
                'estimated_delivery_time' => $estimates[0]['estimated_delivery_time'],
                'price' => $estimates[0]['price'],
                'currency' => $estimates[0]['currency'],
                'all_options' => $estimates
            ];
        }

        // If no option is within 2 hours, sort by price (ascending)
        usort($estimates, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });

        return [
            'success' => true,
            'message' => 'Most affordable delivery option found',
            'selected_service' => $estimates[0]['service'],
            'estimated_delivery_time' => $estimates[0]['estimated_delivery_time'],
            'price' => $estimates[0]['price'],
            'currency' => $estimates[0]['currency'],
            'all_options' => $estimates
        ];
    }

    // Create delivery requests for each service

    private function createKwikDelivery(array $deliveryDetails): array
    {
        $payload = [
            'pickup_address' => $deliveryDetails['pickup_address'],
            'pickup_name' => $deliveryDetails['pickup_name'],
            'pickup_phone' => $deliveryDetails['pickup_phone'],
            'delivery_address' => $deliveryDetails['delivery_address'],
            'delivery_name' => $deliveryDetails['delivery_name'],
            'delivery_phone' => $deliveryDetails['delivery_phone'],
            'weight' => $deliveryDetails['weight'] ?? 0.5,
            'description' => $deliveryDetails['description'] ?? '',
            'payment_method' => $deliveryDetails['payment_method'] ?? 'card',
        ];

        try {
            $response = $this->client->request('POST', 'https://api.kwik.delivery/v1/orders', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->kwikApiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $payload
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'message' => 'Kwik delivery request created successfully',
                'tracking_id' => $body['data']['tracking_id'] ?? '',
                'delivery_id' => $body['data']['id'] ?? '',
                'service' => 'kwik',
                'raw_response' => $body
            ];
        } catch (\Exception $e) {
            Log::error('Error creating Kwik delivery: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create Kwik delivery',
                'error' => $e->getMessage()
            ];
        }
    }

    private function createBoltDelivery(array $deliveryDetails): array
    {
        $payload = [
            'pickup_location' => $deliveryDetails['pickup_address'],
            'pickup_contact_name' => $deliveryDetails['pickup_name'],
            'pickup_contact_phone' => $deliveryDetails['pickup_phone'],
            'destination' => $deliveryDetails['delivery_address'],
            'recipient_name' => $deliveryDetails['delivery_name'],
            'recipient_phone' => $deliveryDetails['delivery_phone'],
            'parcel_weight' => $deliveryDetails['weight'] ?? 0.5,
            'parcel_description' => $deliveryDetails['description'] ?? '',
            'payment_method' => $deliveryDetails['payment_method'] ?? 'card',
        ];

        try {
            $response = $this->client->request('POST', 'https://api.bolt.eu/delivery/v1/deliveries', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->boltApiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $payload
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'message' => 'Bolt delivery request created successfully',
                'tracking_id' => $body['tracking_id'] ?? '',
                'delivery_id' => $body['delivery_id'] ?? '',
                'service' => 'bolt',
                'raw_response' => $body
            ];
        } catch (\Exception $e) {
            Log::error('Error creating Bolt delivery: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create Bolt delivery',
                'error' => $e->getMessage()
            ];
        }
    }

    private function createGokadaDelivery(array $deliveryDetails): array
    {
        $payload = [
            'pickup_address' => $deliveryDetails['pickup_address'],
            'pickup_name' => $deliveryDetails['pickup_name'],
            'pickup_phone' => $deliveryDetails['pickup_phone'],
            'delivery_address' => $deliveryDetails['delivery_address'],
            'delivery_name' => $deliveryDetails['delivery_name'],
            'delivery_phone' => $deliveryDetails['delivery_phone'],
            'weight' => $deliveryDetails['weight'] ?? 0.5,
            'description' => $deliveryDetails['description'] ?? '',
            'payment_method' => $deliveryDetails['payment_method'] ?? 'card',
        ];

        try {
            $response = $this->client->request('POST', 'https://api.gokada.ng/api/v2/orders', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->gokadaApiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $payload
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'message' => 'Gokada delivery request created successfully',
                'tracking_id' => $body['tracking_id'] ?? '',
                'delivery_id' => $body['order_id'] ?? '',
                'service' => 'gokada',
                'raw_response' => $body
            ];
        } catch (\Exception $e) {
            Log::error('Error creating Gokada delivery: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create Gokada delivery',
                'error' => $e->getMessage()
            ];
        }
    }

    // Track deliveries

    private function trackKwikDelivery(string $trackingId): array
    {
        try {
            $response = $this->client->request('GET', "https://api.kwik.delivery/v1/orders/tracking/{$trackingId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->kwikApiKey,
                    'Accept' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'message' => 'Kwik delivery tracking successful',
                'status' => $body['data']['status'] ?? 'unknown',
                'current_location' => $body['data']['current_location'] ?? null,
                'estimated_delivery_time' => $body['data']['estimated_delivery_time'] ?? null,
                'driver_info' => $body['data']['driver'] ?? null,
                'raw_response' => $body
            ];
        } catch (\Exception $e) {
            Log::error('Error tracking Kwik delivery: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to track Kwik delivery',
                'error' => $e->getMessage()
            ];
        }
    }

    private function trackBoltDelivery(string $trackingId): array
    {
        try {
            $response = $this->client->request('GET', "https://api.bolt.eu/delivery/v1/deliveries/{$trackingId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->boltApiKey,
                    'Accept' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'message' => 'Bolt delivery tracking successful',
                'status' => $body['status'] ?? 'unknown',
                'current_location' => $body['current_location'] ?? null,
                'estimated_delivery_time' => $body['estimated_delivery_time'] ?? null,
                'driver_info' => $body['courier'] ?? null,
                'raw_response' => $body
            ];
        } catch (\Exception $e) {
            Log::error('Error tracking Bolt delivery: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to track Bolt delivery',
                'error' => $e->getMessage()
            ];
        }
    }

    private function trackGokadaDelivery(string $trackingId): array
    {
        try {
            $response = $this->client->request('GET', "https://api.gokada.ng/api/v2/orders/{$trackingId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->gokadaApiKey,
                    'Accept' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'message' => 'Gokada delivery tracking successful',
                'status' => $body['status'] ?? 'unknown',
                'current_location' => $body['pilot_location'] ?? null,
                'estimated_delivery_time' => $body['estimated_delivery_time'] ?? null,
                'driver_info' => $body['pilot'] ?? null,
                'raw_response' => $body
            ];
        } catch (\Exception $e) {
            Log::error('Error tracking Gokada delivery: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to track Gokada delivery',
                'error' => $e->getMessage()
            ];
        }
    }
}
