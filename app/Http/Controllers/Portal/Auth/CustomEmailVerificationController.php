<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Constants\Guards;
use App\Http\Controllers\Controller;
use App\Services\Auth\DashboardResolver;
use App\Services\Auth\GuardResolver;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CustomEmailVerificationController extends Controller
{
    public function __construct(
        private readonly GuardResolver $guardResolver,
        private readonly DashboardResolver $dashboardResolver,
    ) {}

    public function showVerificationNotice()
    {
        ['guard' => $guard, 'user' => $user] = $this->guardResolver->identity();

        if (! $guard || ! $user) {
            return view('vendor.adminlte.auth.verify');
        }

        if (! ($user instanceof MustVerifyEmail)) {
            return $this->redirectToDashboard($guard, $user);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->redirectToDashboard($guard, $user);
        }

        return view('vendor.adminlte.auth.verify');
    }

    public function notice()
    {
        return $this->showVerificationNotice();
    }

    /**
     * Handle the signed verification link.
     */
    public function verify(Request $request, $id, $hash)
    {
        $guard = $this->resolveGuardFromVerificationRoute($request);

        if (! $guard) {
            abort(403, 'Invalid authentication context.');
        }

        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        $user = $this->resolveUserFromGuardProvider($guard, (int) $id);

        if (! $user || ! ($user instanceof MustVerifyEmail)) {
            abort(403, 'Invalid verification link.');
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            abort(403, 'Invalid verification link.');
        }

        $justVerified = false;

        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
                $justVerified = true;
            }
        }

        $currentGuard = $this->guardResolver->detect();

        if ($currentGuard !== $guard) {
            Auth::guard($guard)->login($user);
        }

        return $this->redirectToDashboard($guard, $user)
            ->with('message', $justVerified
                ? 'Your email has been verified successfully.'
                : 'Your email is already verified.');
    }

    public function resend(Request $request)
    {
        ['guard' => $guard, 'user' => $user] = $this->guardResolver->identity();

        if (! $guard || ! $user) {
            abort(403);
        }

        if (! ($user instanceof MustVerifyEmail)) {
            return $this->redirectToDashboard($guard, $user);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            return back()->with('message', 'A new verification link has been sent to your email address.');
        }

        return $this->redirectToDashboard($guard, $user);
    }

    /**
     * Resolve guard from the verification route itself, not from the session.
     */
    private function resolveGuardFromVerificationRoute(Request $request): ?string
    {
        $routeName = $request->route()?->getName();

        if (! is_string($routeName) || $routeName === '') {
            return null;
        }

        if ($routeName === 'verification.verify') {
            return Guards::WEB;
        }

        foreach (Guards::portal() as $guard) {
            if (Str::startsWith($routeName, "{$guard}.verification.")) {
                return $guard;
            }
        }

        return null;
    }

    private function resolveUserFromGuardProvider(string $guard, int $id): ?object
    {
        $provider = config("auth.guards.$guard.provider");
        $model = $provider ? config("auth.providers.$provider.model") : null;

        if (! is_string($model) || ! class_exists($model)) {
            abort(403, 'Invalid authentication context.');
        }

        $user = $model::find($id);

        if (! $user) {
            abort(403, 'Invalid verification link.');
        }

        return $user;
    }

    private function redirectToDashboard(string $guard, $user)
    {
        $role = $this->dashboardResolver->highestPriorityRole($guard, $user);
        $routeName = $this->dashboardResolver->routeName($guard, $role);

        if (! Route::has($routeName)) {
            return redirect()->route('auth.reset');
        }

        return redirect()->intended(route($routeName));
    }
}