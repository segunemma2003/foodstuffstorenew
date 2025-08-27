<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyRegistrationEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $uniqueCode;
    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct($uniqueCode, $otp)
    {
        $this->uniqueCode = $uniqueCode;
        $this->otp = $otp;
    }

    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Company Registration Email',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    // /**
    //  * Get the attachments for the message.
    //  *
    //  * @return array<int, \Illuminate\Mail\Mailables\Attachment>
    //  */
    // public function attachments(): array
    // {
    //     return [];
    // }

    public function build()
    {
        return $this->subject('Company Registration Email')
                    ->view('emails.company_registration')
                    ->with([
                        'uniqueCode' => $this->uniqueCode,
                        'otp'=> $this->otp
                    ]);
    }
}
