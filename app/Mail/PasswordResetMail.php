<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $email,
        private readonly string $resetLink,
        private readonly string $userName,
    ) {
        $this->onQueue('default');
        $this->delay(now());
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->email],
            subject: __('auth.password_reset_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'resetLink' => $this->resetLink,
                'userName' => $this->userName,
            ],
        );
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('PasswordResetMail failed to send', [
            'email' => $this->email,
            'userName' => $this->userName,
            'exception' => $exception?->getMessage(),
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
