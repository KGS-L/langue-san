<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ModeratorInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $setupUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Invitation à l’espace équipe Langue SAN');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.moderator-invitation',
            with: ['name' => $this->name, 'setupUrl' => $this->setupUrl],
        );
    }
}
