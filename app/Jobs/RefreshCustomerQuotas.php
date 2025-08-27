<?php

namespace App\Jobs;

use App\Models\BusinessCustomer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshCustomerQuotas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


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
        $customers = BusinessCustomer::where('status', 'approved')->get();

        foreach ($customers as $customer) {
            $currentQuota = $customer->available_quota;
            $monthlyQuota = $customer->monthly_quota_amount;
            $newQuota = $currentQuota + $monthlyQuota;

            $customer->update(['available_quota' => $newQuota]);

            $customer->quotaHistory()->create([
                'amount' => $monthlyQuota,
                'balance' => $newQuota,
                'description' => 'Monthly quota refresh'
            ]);
        }
    }
}
