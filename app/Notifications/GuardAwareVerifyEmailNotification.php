<?php

namespace App\Notifications;

use App\Constants\Guards;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

final class GuardAwareVerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return $this->buildMailMessage($verificationUrl);
    }

    protected function verificationUrl($notifiable): string
    {
        $guard = $this->resolveGuard($notifiable);

        $routeName = $guard === Guards::WEB
            ? 'verification.verify'
            : "{$guard}.verification.verify";

        return URL::temporarySignedRoute(
            $routeName,
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    private function resolveGuard($notifiable): string
    {
        if (is_object($notifiable) && method_exists($notifiable, 'authGuardName')) {
            $guard = $notifiable->authGuardName();

            return is_string($guard) && $guard !== ''
                ? $guard
                : Guards::WEB;
        }

        return Guards::WEB;
    }
}