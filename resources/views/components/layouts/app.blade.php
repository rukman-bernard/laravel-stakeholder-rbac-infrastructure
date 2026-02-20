{{-- 
    Common application layout used by ALL stakeholder portals.
    - Extends the base AdminLTE wrapper layout.
    - Dynamically loads the correct UI skin based on the detected auth guard.
    - Provides a consistent centered content region for Livewire + Blade views.
--}}

@php
    /** @var string $guard */
    $guard = app(\App\Services\Auth\GuardResolver::class)->detect() ?? 'web';

    /**
     * Vite entry for the skin bundle (optional).
     * Example config path: config('nka.ui.skins.web') => 'resources/sass/skins/admin.scss'
     */
    $skinEntry = config('nka.ui.skins.' . $guard, '');

    /**
     * Vite entry for the miscellaneous js for each stakeholder type.
     * Example config path: config('nka.ui.js.web') => 'resources/js/web/misc.js'
     */

    $miscJS = config('nka.ui.js.' . $guard, '');
@endphp

@extends('components.layouts.adminlte-app')

@section('adminlte_css')
    @if (!empty($skinEntry))
        @vite($skinEntry)
    @endif
@endsection


{{-- Default header content (can be overridden by child views) --}}
@section('subtitle', 'Welcome')
@section('content_header_title', 'Home')
@section('content_header_subtitle', 'NKA')

@section('content_body')
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            {{-- 
                Support both:
                1) Traditional Blade views using @section('content')
                2) Livewire component layouts using $slot
            --}}
            @if (View::hasSection('content'))
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif

        </div>
    </div>
@endsection


@section('adminlte_js')
    @if (!empty($miscJS))
        @vite($miscJS)
    @endif
@endsection

