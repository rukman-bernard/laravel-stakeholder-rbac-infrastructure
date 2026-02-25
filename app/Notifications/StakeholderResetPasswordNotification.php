<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

final class StakeholderResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $guard = $this->resolveGuard($notifiable);

        $resetUrl = $this->resetUrl($notifiable);

        $label = config("nka.auth.reset_password.labels.$guard")
            ?? ucfirst($guard);

        $subject = config("nka.auth.reset_password.subjects.$guard")
            ?? "Reset Your {$label} Account Password";

        $expireMinutes = config("auth.passwords.$guard.expire")
            ?? config('auth.passwords.users.expire')
            ?? 60;

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello!')
            ->line("We received a request to reset the password for your {$label} account.")
            ->action('Reset Password', $resetUrl)
            ->line("This password reset link will expire in {$expireMinutes} minutes.")
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Regards, Support Team');
    }

    /**
     * Must match Laravel's parent signature exactly.
     */
    protected function resetUrl($notifiable): string
    {
        $guard = $this->resolveGuard($notifiable);

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'guard' => $guard,
        ], false));
    }

    private function resolveGuard($notifiable): string
    {
        if (is_object($notifiable) && method_exists($notifiable, 'authGuardName')) {
            return (string) ($notifiable->authGuardName() ?: 'web');
        }

        return 'web';
    }
}