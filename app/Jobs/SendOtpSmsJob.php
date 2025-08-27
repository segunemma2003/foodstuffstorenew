<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\JusibeService;
use Illuminate\Support\Facades\Log;


class SendOtpSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $otp;
    /**
     * Create a new job instance.
     */
    public function __construct($phone, $otp)
    {
        $this->phone = $phone;
        $this->otp = $otp;
    }


    /**
     * Execute the job.
     */
    public function handle(JusibeService $jusibeService)
    {
        $message = "Your OTP code is: {$this->otp}. It expires in 10 minutes.";

        $response = $jusibeService->sendSingleSMSTermii($this->phone, 'FoodStuff Store', $message);

        // if (!$response || $response['status'] !== 'Sent') {
        //     // Handle failure, possibly re-queue or log
        //     Log::error("Failed to send OTP SMS to {$this->phone}");
        // }
    }
}
