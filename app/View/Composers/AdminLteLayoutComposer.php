<?php

namespace App\View\Composers;

use App\Constants\Guards;
use Illuminate\View\View;

final class AdminLteLayoutComposer
{
    /**
     * Inject guard-specific AdminLTE skin configuration into the layout.
     *
     * Resolution:
     * - Uses the currently active session guard
     * - Falls back to default guard (web) when none detected
     */
    public function compose(View $view): void
    {
        $guard = nka_active_session_guard() ?? Guards::WEB;

        $skinEntry = config("nka.ui.skins.$guard");

        $view->with('adminlteSkinEntry', $skinEntry);
    }
}