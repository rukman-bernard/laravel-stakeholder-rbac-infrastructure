@extends('adminlte::page')

{{-- Extend and customize the browser title --}}

@section('title')
    {{ config('adminlte.title') }}
    @hasSection('subtitle') | @yield('subtitle') @endif
@stop

{{-- Extend and customize the page content header --}}

@section('content_header')
    @hasSection('content_header_title')
        <h1 class="text-muted">
            @yield('content_header_title')

            @hasSection('content_header_subtitle')
                <small class="text-dark">
                    <i class="fas fa-xs fa-angle-right text-muted"></i>
                    @yield('content_header_subtitle')
                </small>
            @endif
        </h1>
    @endif
@stop

{{-- Rename section content to content_body --}}

@section('content')
    @yield('content_body')
@stop

{{-- Create a common footer --}}

@section('footer')
    <div class="float-right">
        Version: {{ config('app.version', '1.0.0') }}
    </div>

    <strong>
        <a href="{{ config('app.url', '#') }}">
            {{ config('app.name', 'My company') }}
        </a>
    </strong>
@stop

{{-- 
    Asset Injection Policy:

    This project standardises on:
    - @section('adminlte_css')
    - @section('adminlte_css_pre')
    - @section('adminlte_js')

    Blade stacks (@push('css') / @push('js')) are intentionally not used
    to maintain a predictable asset-loading strategy.

    If stack-based extension is required in the future, refer to:
    https://github.com/jeroennoten/Laravel-AdminLTE/blob/master/resources/views/master.blade.php
--}}
