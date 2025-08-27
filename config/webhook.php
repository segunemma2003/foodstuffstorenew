<?php

return [
    'allowed_ips' => env('WEBHOOK_ALLOWED_IPS', '')
        ? explode(',', env('WEBHOOK_ALLOWED_IPS'))
        : [],
];
