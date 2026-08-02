<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Custom-styled replacement for Laravel's default password-reset
 * notification — sent by User::sendPasswordResetNotification().
 */
class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $resetUrl,
        public int $expiryMinutes,
        public string $requestIp,
        public string $requestTime,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your DisasterResource+ password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
        );
    }
}
