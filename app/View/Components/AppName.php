<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class AppName extends Component
{
    /**
     * Short display name (large gradient title).
     */
    public function __construct(
        public string $short = 'NKA HUB',

        /**
         * Full institutional name (subtitle badge).
         */
        public string $full = 'Negombo Knowledge Academy',
    ) {}

    public function render(): View
    {
        return view('components.app-name');
    }
}