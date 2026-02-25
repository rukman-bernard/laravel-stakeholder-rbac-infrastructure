<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\DashboardResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct(private readonly DashboardResolver $dashResolver)
    {
        // Only web guests can see login; logout requires an authenticated web user
        $this->middleware('guest:web')->except('logout');
        $this->middleware('auth:web')->only('logout');
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