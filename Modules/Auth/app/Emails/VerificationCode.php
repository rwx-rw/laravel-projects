<?php

namespace Modules\Auth\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public string $code) {}

    public function envelope() : Envelope{
        return new Envelope(
            subject: 'Verification Code'
        );
    }

    public function content() : Content{
        return new Content(
            markdown: "auth::mails.verification-code",
            with: [
                'code' => $this->code
            ]
        );
    }
}
