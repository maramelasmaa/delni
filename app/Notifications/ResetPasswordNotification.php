<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $token)
    {
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Link to the browser-based reset page so the email works everywhere — desktop
        // webmail included — not just inside the mobile app. The page handles the reset
        // directly; on a phone the user then opens the app to sign in. (A Universal/App
        // Link could later upgrade this to open the app directly on mobile.)
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور — '.config('app.name'))
            ->view(
                ['emails.reset-password', 'emails.reset-password-text'],
                ['url' => $url, 'userName' => $notifiable->name ?? '']
            );
    }
}
