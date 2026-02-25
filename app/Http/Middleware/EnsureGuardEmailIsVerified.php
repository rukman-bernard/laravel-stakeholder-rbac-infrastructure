<?php

namespace App\Http\Middleware;

use App\Constants\Guards;
use App\Services\Auth\GuardRedirectService;
use App\Services\Auth\GuardResolver;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureGuardEmailIsVerified
{
    public function __construct(
        private readonly GuardResolver $guardResolver,
        private readonly GuardRedirectService $redirectService,
    ) {}

    /**
     * Ensure the authenticated user (under the resolved guard) has a verified email.
     *
     * Usage examples:
     * - email.verified
     * - email.verified:web
     * - email.verified:student
     * - email.verified:web,student,employer
     *
     * Rules:
     * - If multiple guards are provided, the first authenticated guard wins.
     * - If no guards are provided, defaults to all session guards (single-session model).
     * - If the authenticated user does not implement MustVerifyEmail, this is a no-op.
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guards = $this->normalizeGuards($guards);

        // First authenticated guard wins (your resolver contract)
        ['guard' => $guard, 'user' => $user] = $this->guardResolver->identity($guards);

        // -------------------------------------------------------------
        // 1) Not authenticated under any expected guard
        // -------------------------------------------------------------
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            return $this->redirectService->redirectToLogin($request);
        }

        // -------------------------------------------------------------
        // 2) Authenticated but email is not verified (when required)
        // -------------------------------------------------------------
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Email address is not verified.'], 403);
            }

            // $guard should always be a string here if user exists, but keep fallback safe.
            return redirect()->route($this->verificationNoticeRouteFor($guard ?: Guards::default()));
        }

        // -------------------------------------------------------------
        // 3) Verified (or verification not required)
        // -------------------------------------------------------------
        return $next($request);
    }

    /**
     * Normalize middleware guard parameters.
     *
     * - Removes invalid/empty entries
     * - Normalizes names using Guards::normalize()
     * - Deduplicates
     * - If none provided, defaults to session guards (single-session model)
     *
     * @param  array<int, mixed>  $guards
     * @return array<int, string>
     */
    private function normalizeGuards(array $guards): array
    {
        $normalized = collect($guards)
            ->filter(fn ($g) => is_string($g) && trim($g) !== '')
            ->map(fn ($g) => Guards::normalize($g))
            ->filter(fn ($g) => is_string($g) && $g !== '')
            ->unique()
            ->values()
            ->all();

        return $normalized ?: Guards::session();
    }

    /**
     * Resolve the verification notice route name for a guard.
     *
     * web    -> verification.notice
     * others -> {guard}.verification.notice (student.verification.notice, employer.verification.notice, ...)
     */
    private function verificationNoticeRouteFor(string $guard): string
    {
        return $guard === Guards::WEB
            ? 'verification.notice'
            : "{$guard}.verification.notice";
    }
}