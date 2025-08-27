<?php

namespace App\Listeners;

use App\Events\WebhookEvent;
use App\Jobs\ProcessWebhook;
use App\Models\FailedWebhook;
use App\Models\IdempotencyKey;
use App\Models\Webhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebhookEvent $event)
    {
        $webhooks = Webhook::where('is_active', true)
            ->get()
            ->filter(function($webhook) use ($event) {
                return $webhook->shouldReceiveEvent($event->eventType, $event->data);
            });

        foreach ($webhooks as $webhook) {
            $idempotencyKey = md5($webhook->id . $event->eventType . json_encode($event->data));

            // Check if we've already processed this event
            if (!IdempotencyKey::where('key', $idempotencyKey)->exists()) {
                IdempotencyKey::create([
                    'key' => $idempotencyKey,
                    'webhook_id' => $webhook->id,
                    'payload' => $event->data,
                    'event_type' => $event->eventType,
                ]);

                // Dispatch to queue for async processing
                ProcessWebhook::dispatch($webhook, $event->eventType, $event->data, $idempotencyKey)
                    ->onQueue('webhooks');
            }
        }
    }

    protected function logFailedWebhook($webhook, $payload, $statusCode, $response, $error = null)
    {
        FailedWebhook::create([
            'webhook_id' => $webhook->id,
            'payload' => $payload,
            'response' => $response,
            'status_code' => $statusCode,
            'error' => $error,
            'attempts' => 1,
            'next_attempt_at' => now()->addMinutes(5), // First retry after 5 minutes
        ]);

        Log::error("Webhook failed for {$webhook->company_name}", [
            'url' => $webhook->url,
            'status' => $statusCode,
            'response' => $response,
            'error' => $error,
        ]);
    }


    protected function sendWebhook($webhook, $payload)
{
    try {
        $client = Http::timeout(15)
            ->withHeaders([
                'X-Webhook-Signature' => hash_hmac('sha256', json_encode($payload), $webhook->secret),
                'Content-Type' => 'application/json',
            ]);

        // If strict IP is enabled, use the allowed IPs for outbound connection
        if ($webhook->strict_ip && !empty($webhook->allowed_ips)) {
            $client->withOptions([
                'curl' => [
                    CURLOPT_INTERFACE => $webhook->allowed_ips[array_rand($webhook->allowed_ips)]
                ]
            ]);
        }

        $response = $client->post($webhook->url, $payload);

        if (!$response->successful()) {
            $this->logFailedWebhook($webhook, $payload, $response->status(), $response->body());
        }
    } catch (\Exception $e) {
        $this->logFailedWebhook($webhook, $payload, null, null, $e->getMessage());
    }
}
}
