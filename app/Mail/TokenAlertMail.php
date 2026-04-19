<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TokenAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $patientToken, $doctorName, $tokensLeft, $currentToken;

    /**
     * Create a new message instance.
     */
    public function __construct($patientToken, $doctorName, $tokensLeft, $currentToken)
    {
        $this->patientToken = $patientToken;
        $this->doctorName = $doctorName;
        $this->tokensLeft = $tokensLeft;
        $this->currentToken = $currentToken;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏰ Your Turn is Approaching! - MedFlow Healthcare',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.token_alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
