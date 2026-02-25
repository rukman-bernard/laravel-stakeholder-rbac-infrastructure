<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

final class EmployerResetPasswordNotification extends ResetPassword
{
    /**
     * Build the password reset email for Employer guard.
     *
     * This overrides Laravel's default ResetPassword notification
     * to customise:
     * - Subject
     * - Guard-aware reset URL
     * - Employer-specific wording
     */
    public function toMail($notifiable): MailMessage
    {
        $guard = method_exists($notifiable, 'authGuardName')
            ? $notifiable->authGuardName()
            : 'web';

        $resetUrl = $this->resetUrl($notifiable, $guard);

        $expireMinutes = config("auth.passwords.{$guard}.expire")
            ?? config('auth.passwords.users.expire')
            ?? 60;

        return (new MailMessage)
            ->subject('Reset Your NKA Employer Account Password')
            ->greeting('Hello!')
            ->line('We received a request to reset the password for your NKA Employer account.')
            ->action('Reset Password', $resetUrl)
            ->line("This password reset link will expire in {$expireMinutes} minutes.")
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Regards, NKA Support Team');
    }

    /**
     * Generate guard-aware reset URL.
     */
    protected function resetUrl($notifiable): string
    {
        $guard = (is_object($notifiable) && method_exists($notifiable, 'authGuardName'))
            ? (string) ($notifiable->authGuardName() ?: 'web')
            : 'web';

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'guard' => $guard,
        ], false));
    }
}