<?php

namespace App\Jobs;

use App\Models\FailedWebhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class RetryFailedWebhooks implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $failedWebhooks = FailedWebhook::where('next_attempt_at', '<=', now())
            ->where('attempts', '<', 5) // Max 5 attempts
            ->with('webhook')
            ->get();

        foreach ($failedWebhooks as $failed) {
            try {
                $signature = hash_hmac('sha256', json_encode($failed->payload), $failed->webhook->secret);

                $response = Http::timeout(15)
                    ->withHeaders([
                        'X-Webhook-Signature' => $signature,
                        'Content-Type' => 'application/json',
                    ])->post($failed->webhook->url, $failed->payload);

                if ($response->successful()) {
                    $failed->delete();
                    continue;
                }

                // Calculate next attempt with exponential backoff
                $nextAttempt = now()->addMinutes(pow(2, $failed->attempts));

                $failed->update([
                    'response' => $response->body(),
                    'status_code' => $response->status(),
                    'attempts' => $failed->attempts + 1,
                    'next_attempt_at' => $nextAttempt,
                ]);

            } catch (\Exception $e) {
                $nextAttempt = now()->addMinutes(pow(2, $failed->attempts));

                $failed->update([
                    'error' => $e->getMessage(),
                    'attempts' => $failed->attempts + 1,
                    'next_attempt_at' => $nextAttempt,
                ]);
            }
        }
    }
}
