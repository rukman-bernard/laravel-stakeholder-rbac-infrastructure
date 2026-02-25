<?php

namespace App\Livewire\Sysadmin\Dashboard;

use App\Constants\Permissions;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
final class Dashboard extends Component
{
    /**
     * Page header (used by shared layout).
     */
    public string $header_title = 'Dashboard';

    /**
     * Optional subtitle.
     */
    public string $subtitle = '';

    /**
     * Authorisation check for sysadmin dashboard.
     *
     * We perform this in mount() instead of render() so that:
     * - The check runs once
     * - The component does not partially render
     * - Laravel handles 403 automatically
     */
    public function mount(): void
    {
        $this->authorize(Permissions::VIEW_SYSTEM_ADMIN_DASHBOARD);
    }

    /**
     * Render the sysadmin dashboard view.
     */
    public function render()
    {
        return view('livewire.sysadmin.dashboard.dashboard');
    }
}