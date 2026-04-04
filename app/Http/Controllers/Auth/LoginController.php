<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\DashboardResolver;
use App\Services\Auth\GuardLogoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct(
    private readonly DashboardResolver $dashResolver, 
    private readonly GuardLogoutService $logoutService,
    )
    {
        // Only web guests can see login; logout requires an authenticated web user
    
    }

    /**
     * Handle a login request to the application.
    */
    public function login(Request $request)
    {
        
        $this->validateLogin($request);

         // Enforce single-session behaviour across session guards
        $this->logoutService->logoutAnyAuthenticatedGuard();

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Show the AdminLTE login view for the web guard.
     */
    public function showLoginForm(): View
    {
        return view('vendor.adminlte.auth.login');
    }

    /**
     * Resolve the post-login redirect URL for the web guard.
     *
     * Laravel expects a URL string here (not a route name).
     */
    protected function redirectTo(): string
    {
        $user = Auth::guard('web')->user();

        // Defensive fallback (shouldn't happen under normal login flow)
        if (! $user) {
            return $this->dashResolver->url('web', null);
        }

        // Resolve role key (Spatie role name) for web dashboard mapping
        $role = $this->dashResolver->highestPriorityRole('web', $user);

        return $this->dashResolver->url('web', $role);
    }
}