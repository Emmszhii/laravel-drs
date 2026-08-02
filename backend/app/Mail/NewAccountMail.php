<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when an admin creates a new user — invites them to set their own
 * password instead of one being typed into the create-user modal.
 */
class NewAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $userRole,
        public string $setPasswordUrl,
        public int $expiryHours,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your DisasterResource+ account is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-account',
        );
    }
}
