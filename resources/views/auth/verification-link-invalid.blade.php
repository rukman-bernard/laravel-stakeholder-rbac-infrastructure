@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('auth_header', 'Verification Link Invalid')

@section('auth_body')
    <div class="alert alert-danger mb-3" role="alert">
        This email verification link is invalid or has expired.
    </div>

    <p class="mb-3">
        Please sign in to your <strong>{{ ucfirst($guard) }}</strong> account and request a new verification email.
    </p>

    <div class="d-flex flex-column flex-sm-row gap-2">
        <a href="{{ route($loginRoute) }}" class="btn btn-primary mr-sm-2 mb-2 mb-sm-0">
            <i class="fas fa-sign-in-alt mr-1"></i> Go to {{ ucfirst($guard) }} Login
        </a>

        <a href="{{ route($resendNoticeRoute) }}" class="btn btn-outline-secondary">
            <i class="fas fa-envelope mr-1"></i> Verification Instructions
        </a>
    </div>
@endsection