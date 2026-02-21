<?php

namespace App\Livewire\Admin\Dashboard;

use App\Constants\Permissions;
use App\Models\Level;
use App\Models\Programme;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;

class Dashboard extends Component
{
    public string $header_title = 'Dashboard';
    public string $subtitle = '';

    public $programmeLabels = [], $programmeCounts = [];
    public $levelLabels = [], $levelCounts = [];

    public function mount()
    {

    }

    public function render()
    {
        return view('livewire.admin.dashboard.dashboard');
    }}
