<?php

namespace App\View\Composers;

use App\Constants\Guards;
use App\Services\Auth\GuardResolver;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

final class EmailVerificationViewComposer
{
    public function __construct(
        private readonly GuardResolver $guardResolver,
    ) {}

    public function compose(View $view): void
    {
        $guard = $this->guardResolver->detect() ?? Guards::WEB;

        $routeName = ($guard === Guards::WEB)
            ? 'verification.resend'
            : "{$guard}.verification.resend";

        if (! Route::has($routeName)) {
            $routeName = 'verification.resend';
        }

        $view->with('verificationResendRouteName', $routeName);
    }
}