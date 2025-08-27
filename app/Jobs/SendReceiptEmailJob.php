<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use LaravelDaily\Invoices\Invoice;

class SendReceiptEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $receipt;
    protected $customerEmail;

    public function __construct(Invoice $receipt, $customerEmail)
    {
        $this->receipt = $receipt;
        $this->customerEmail = $customerEmail;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // $pdf = $this->receipt; // Generate PDF of the receipt

        // // Send email with receipt attached
        // Mail::send([], [], function ($message) use ($pdf) {
        //     $message->to($this->customerEmail)
        //             ->subject('Your Receipt')
        //             ->attachData($pdf->output(), 'receipt.pdf');
        // });
    }
}
