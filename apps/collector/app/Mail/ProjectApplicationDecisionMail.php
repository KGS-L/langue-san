<?php

namespace App\Mail;

use App\Enums\ProjectApplicationStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectApplicationDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly ProjectApplicationStatus $status,
        public readonly ?string $reason,
        public readonly string $accountUrl,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->status === ProjectApplicationStatus::APPROVED
            ? 'Votre candidature Langue SAN a été acceptée'
            : 'Mise à jour de votre candidature Langue SAN';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.project-application-decision',
            with: [
                'name' => $this->name,
                'status' => $this->status,
                'reason' => $this->reason,
                'accountUrl' => $this->accountUrl,
            ],
        );
    }
}
