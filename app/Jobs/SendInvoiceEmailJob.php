<?php

namespace App\Jobs;

use App\Mail\SendInvoiceMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Mail;
use LaravelDaily\Invoices\Invoice;


class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $invoice;
    protected $customer;

    public function __construct($invoice, User $customer)
    {
        $this->invoice = $invoice;
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to([$this->customer->email, 'support@foodstuff.store'])->queue(new SendInvoiceMail($this->invoice, $this->customer));
    }
}
