<?php

namespace App\Livewire\Employer\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
final class Dashboard extends Component
{
    /**
     * Placeholder for future employer-specific data.
     * Keep typed where possible as the model becomes stable.
     */
    public mixed $employer = null;

    /**
     * Related data collections (e.g., students, batches, reports).
     */
    public array $batches = [];

    /**
     * Boot hook for initial data loading.
     *
     * Keep this method even if empty — dashboards typically evolve
     * to load metrics, counts, and related models here.
     */
    public function mount(): void
    {
        // Intentionally left minimal.
        // Load employer-specific data here when implemented.
    }

    /**
     * Render the employer dashboard view.
     */
    public function render()
    {
        return view('livewire.employer.dashboard');
    }
}