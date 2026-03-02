<?php

namespace App\View\Composers;

use App\Constants\Guards;
use App\Services\Auth\GuardResolver;
use Illuminate\View\View;

final class AdminLteLayoutComposer
{
    public function __construct(
        private readonly GuardResolver $guardResolver,
    ) {}

    /**
     * Inject guard-specific AdminLTE skin configuration into the layout.
     *
     * Resolution:
     * - Uses GuardResolver to detect the active guard in the shared session context
     * - Falls back to default guard (`web`) when none detected
     */
    public function compose(View $view): void
    {
        $guard = $this->guardResolver->detect() ?? Guards::WEB;

        $skinEntry = config("nka.ui.skins.$guard");

        $view->with('adminlteSkinEntry', $skinEntry);
    }
}

// namespace App\View\Composers;

// use App\Constants\Guards;
// use Illuminate\View\View;

// final class AdminLteLayoutComposer
// {
//     /**
//      * Inject guard-specific AdminLTE skin configuration into the layout.
//      *
//      * Resolution:
//      * - Uses the currently active session guard
//      * - Falls back to default guard (web) when none detected
//      */
//     public function compose(View $view): void
//     {
//         $guard = nka_active_session_guard() ?? Guards::WEB;

//         $skinEntry = config("nka.ui.skins.$guard");

//         $view->with('adminlteSkinEntry', $skinEntry);
//     }
// }