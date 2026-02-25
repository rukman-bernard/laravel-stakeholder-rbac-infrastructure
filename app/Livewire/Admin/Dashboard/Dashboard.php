<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;

final class Dashboard extends Component
{
    /**
     * Page header (used by the shared AdminLTE layout).
     */
    public string $header_title = 'Dashboard';

    /**
     * Optional subtitle shown under the header.
     */
    public string $subtitle = '';

    /**
     * Component boot hook.
     *
     * Keep this method even if empty for now:
     * - dashboard pages typically evolve to load stats/cards
     * - mount() is the canonical place for that initial state
     */
    public function mount(): void
    {
        // Intentionally empty (reserved for future dashboard metrics).
    }

    /**
     * Render the dashboard view.
     */
    public function render()
    {
        return view('livewire.admin.dashboard.dashboard');
    }
}