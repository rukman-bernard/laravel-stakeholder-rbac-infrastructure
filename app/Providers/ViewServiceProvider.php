<?php

namespace App\Providers;

use App\View\Composers\AdminLteLayoutComposer;
use App\View\Composers\ErrorViewComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

final class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register view composers used across the application.
     *
     * View composers let us inject shared data into Blade views in a single place
     * (instead of duplicating logic in every controller/component).
     */
    public function boot(): void
    {
        $this->registerErrorComposers();
        $this->registerLayoutComposers();
    }

    /**
     * Enrich all error pages (404/403/500...) with consistent data.
     *
     * Supports both:
     * - "errors.*"   (e.g., errors.404)
     * - "errors::*"  (namespaced error views if you use them)
     */
    private function registerErrorComposers(): void
    {
        View::composer(['errors.*', 'errors::*'], ErrorViewComposer::class);
    }

    /**
     * Inject AdminLTE layout data into the shared Livewire layout.
     */
    private function registerLayoutComposers(): void
    {
        View::composer('components.layouts.app', AdminLteLayoutComposer::class);
    }
}