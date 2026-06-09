<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $email,
        private readonly string $setPasswordLink,
        private readonly string $userName,
    ) {
        $this->onQueue('default');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->email],
            subject: __('auth.set_password_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.set-password',
            with: [
                'setPasswordLink' => $this->setPasswordLink,
                'userName' => $this->userName,
            ],
        );
    }
}
