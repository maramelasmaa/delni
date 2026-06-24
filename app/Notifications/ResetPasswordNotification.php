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
        $email = urlencode($notifiable->getEmailForPasswordReset());
        $url = url('/reset-password/'.$this->token.'?email='.$email);

        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور — '.config('app.name'))
            ->view(
                ['emails.reset-password', 'emails.reset-password-text'],
                ['url' => $url, 'userName' => $notifiable->name ?? '']
            );
    }
}
