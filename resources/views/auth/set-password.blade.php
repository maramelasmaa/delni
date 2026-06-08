@extends('layouts.auth')

@section('title', __('auth.set_password_title') . ' - ' . config('app.name'))

@section('auth_title')
    دلني لأفضل<br/><span class="text-primary-500">الخدمات والمزودين</span>
@endsection

@section('auth_subtitle')
    ابحث، قارن، واتصل مع أفضل المزودين في منطقتك بسهولة وثقة.
@endsection

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">{{ __('auth.set_password_title') }}</h1>
        <p class="auth-subtitle">{{ __('auth.set_password_subtitle') }}</p>

        <form method="POST" action="{{ route('onboarding.set-password') }}" class="auth-form">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Email Field (Read-only) -->
            <div class="form-group">
                <label for="email" class="form-label">{{ __('auth.email') }}</label>
                <input type="email" id="email" class="form-control" value="{{ $email }}" readonly>
                <small class="form-text">{{ __('auth.email_cannot_change') }}</small>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">{{ __('auth.new_password') }}</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                <small class="form-text password-requirements">
                    {{ __('auth.password_requirements') }}
                </small>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="form-group">
                <label for="password_confirmation" class="form-label">{{ __('auth.confirm_password') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required>
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-block">
                {{ __('auth.set_password_button') }}
            </button>

            <!-- Back to Login -->
            <div class="auth-footer">
                <a href="{{ route('login') }}" class="back-link">{{ __('auth.back_to_login') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
