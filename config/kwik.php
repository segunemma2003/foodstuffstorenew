<?php

return [
    'base_url' => env('KWIK_BASE_URL', 'https://staging-api-test.kwik.delivery/'),
    'domain' => env('KWIK_DOMAIN', 'staging-client-panel.kwik.delivery'),
    'email' => env('KWIK_EMAIL', ''),
    'password' => env('KWIK_PASSWORD', ''),
    'default_vehicle_size' => 0, // 0 = bike
    'fare_expiry_hours' => 2,
    'payment_method' => 524288, // End of month billing
    'timezone_offset' => -60, // Lagos timezone (UTC+1)
];
