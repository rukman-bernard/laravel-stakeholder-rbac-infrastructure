<?php

namespace App\Http\Controllers\Auth;

use App\Constants\Guards;
use App\Http\Controllers\Controller;
use App\Services\Auth\GuardLogoutService;
use Illuminate\Http\RedirectResponse;

class CommonLogoutController extends Controller
{
    /**
     * Logs out the first authenticated session-guard user (single-session model),
     * then redirects to the appropriate login screen.
     */
    public function __invoke(): RedirectResponse
    {
        $loggedOutGuard = GuardLogoutService::logoutAnyAuthenticatedGuard();

        // Nothing to log out (no active sessions) → send user to portal hub
        if (! $loggedOutGuard) {
            return redirect()->route('portal.hub');
        }

        // Non-web guards redirect to their own login route: "{guard}.login"
        if ($loggedOutGuard !== Guards::WEB) {
            return redirect()->route("{$loggedOutGuard}.login");
        }

        // Web guard uses the default Laravel login route
        return redirect()->route('login');
    }
}