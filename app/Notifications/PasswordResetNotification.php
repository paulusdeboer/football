<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetNotification extends ResetPassword
{
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'user_id' => $notifiable->getKey(),
        ], false));

        $expiresIn = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject(__('Reset password'))
            ->from(config('mail.from.address'), 'Vrijdag voetbal')
            ->view([
                'html' => 'emails.password_reset',
                'text' => 'emails.password_reset_plain',
            ], [
                'logoUrl' => asset('favicon.svg'),
                'userName' => $notifiable->name,
                'resetUrl' => $resetUrl,
                'expiresIn' => $expiresIn,
            ]);
    }
}
