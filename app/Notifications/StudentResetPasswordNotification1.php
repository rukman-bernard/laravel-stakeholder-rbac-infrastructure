<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

final class StudentResetPasswordNotification extends ResetPassword
{
    /**
     * Custom password reset email for the Student guard.
     *
     * Why this exists:
     * - Student accounts use a dedicated guard.
     * - The reset link must include the guard to ensure the correct broker + route flow.
     * - Messaging is student-specific (subject/body text).
     */
    public function toMail($notifiable): MailMessage
    {
        $guard = method_exists($notifiable, 'authGuardName')
            ? $notifiable->authGuardName()
            : 'student';

        $resetUrl = $this->resetUrl($notifiable, $guard);

        // Prefer guard-specific broker config, fall back safely.
        $expireMinutes = config("auth.passwords.{$guard}.expire")
            ?? config('auth.passwords.users.expire')
            ?? 60;

        return (new MailMessage)
            ->subject('Reset Your NKA Student Account Password')
            ->greeting('Hello!')
            ->line('We received a request to reset the password for your NKA Student account.')
            ->action('Reset Password', $resetUrl)
            ->line("This password reset link will expire in {$expireMinutes} minutes.")
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Regards, NKA Support Team');
    }

    /**
     * Generate a guard-aware reset URL.
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