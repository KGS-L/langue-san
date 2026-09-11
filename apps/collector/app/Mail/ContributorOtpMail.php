<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContributorOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly int $ttlMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre code de connexion Langue SAN');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contributor-otp',
            with: ['code' => $this->code, 'ttlMinutes' => $this->ttlMinutes],
        );
    }
}
