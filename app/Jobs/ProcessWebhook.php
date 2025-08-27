<?php

namespace App\Jobs;

use App\Models\FailedWebhook;
use App\Models\Webhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10; // Maximum number of attempts
    public $maxExceptions = 5;
    public $backoff = 1800; // 30 minutes between retries

    public function __construct(
        public Webhook $webhook,
        public string $eventType,
        public array $data,
        public string $idempotencyKey
    ) {}

    public function handle()
    {
        $payload = [
            'event' => $this->eventType,
            'data' => $this->data,
            'timestamp' => now()->toISOString(),
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $this->webhook->secret);

        try {
            $client = Http::timeout(15)
                ->withHeaders([
                    'X-Webhook-Signature' => $signature,
                    'X-Idempotency-Key' => $this->idempotencyKey,
                    'Content-Type' => 'application/json',
                ]);

            // If strict IP is enabled, use the allowed IPs
            if ($this->webhook->strict_ip && !empty($this->webhook->allowed_ips)) {
                $client->withOptions([
                    'curl' => [
                        CURLOPT_INTERFACE => $this->webhook->allowed_ips[array_rand($this->webhook->allowed_ips)]
                    ]
                ]);
            }

            $response = $client->post($this->webhook->url, $payload);

            if ($response->successful()) {
                // Success - delete any failed records
                FailedWebhook::where('webhook_id', $this->webhook->id)
                    ->where('idempotency_key', $this->idempotencyKey)
                    ->delete();
                return;
            }

            throw new \Exception("Webhook failed with status: {$response->status()}");

        } catch (\Exception $e) {
            $this->logFailure($e);
            throw $e; // Trigger retry
        }
    }

    protected function logFailure(\Exception $e)
    {
        FailedWebhook::updateOrCreate(
            [
                'webhook_id' => $this->webhook->id,
                'idempotency_key' => $this->idempotencyKey,
            ],
            [
                'payload' => $this->data,
                'event_type' => $this->eventType,
                'error' => $e->getMessage(),
                'attempts' => $this->attempts(),
                'next_attempt_at' => now()->addSeconds($this->backoff),
            ]
        );

        Log::error("Webhook delivery failed", [
            'webhook_id' => $this->webhook->id,
            'event_type' => $this->eventType,
            'attempt' => $this->attempts(),
            'error' => $e->getMessage(),
        ]);
    }

    public function failed(\Exception $e)
    {
        Log::critical("Webhook delivery failed after all attempts", [
            'webhook_id' => $this->webhook->id,
            'event_type' => $this->eventType,
            'error' => $e->getMessage(),
        ]);

        // Optionally notify admin or disable webhook
    }
}
