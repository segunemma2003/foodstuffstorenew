<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use LaravelDaily\Invoices\Invoice;
use App\Models\User;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;

class SendInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

     protected $invoice;
     protected $customer;

     public function __construct($invoice, User $customer)
    {
        $this->invoice = $invoice;
        $this->customer = $customer;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: 'support@foodstuff.store',
            subject: 'Invoice From Foodstuff store',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.invoice',
            with:[
                'customer'=>$this->customer
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $name =$this->customer->name;
        $time = time();
        return [
            Attachment::fromPath($this->invoice)
                ->as("$$time.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
