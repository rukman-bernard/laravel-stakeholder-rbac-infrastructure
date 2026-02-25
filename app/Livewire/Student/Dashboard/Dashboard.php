<?php

namespace App\Livewire\Student\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
final class Dashboard extends Component
{
    /**
     * Authenticated student instance.
     * Will be populated in mount() when needed.
     */
    public mixed $student = null;

    /**
     * Student-related collections (e.g., batches, modules).
     */
    public array $batches = [];

    /**
     * Component boot hook.
     *
     * This is the correct place to:
     * - Resolve authenticated student
     * - Load active batches
     * - Eager-load related programme/config/module data
     */
    public function mount(): void
    {
        // Intentionally minimal for now.
        // Add student data loading here when dashboard metrics are implemented.
    }

    /**
     * Render the student dashboard view.
     */
    public function render()
    {
        return view('livewire.student.dashboard');
    }
}