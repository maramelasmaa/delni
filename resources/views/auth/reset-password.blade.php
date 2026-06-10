@extends('layouts.auth')

@section('title', __('auth.reset_password_title') . ' - ' . config('app.name'))

@section('auth_title')
    إنشاء <span>كلمة مرور جديدة</span>
@endsection

@section('auth_subtitle')
    أدخل كلمة مرور قوية لحساب دلني الخاص بك.
@endsection

@section('content')
    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4l1.3-1.3 1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/>
            </svg>
            <div>
                <strong>تعذر تحديث كلمة المرور</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST" class="auth-form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-field">
            <label for="email" class="auth-label">{{ __('auth.email') }}</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $email) }}"
                required
                readonly
                class="auth-input is-dark @error('email') is-invalid @enderror"
                placeholder="you@example.com"
                autocomplete="email"
            >
            @error('email')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password" class="auth-label">{{ __('auth.new_password') }}</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="auth-input @error('password') is-invalid @enderror"
                placeholder="••••••••"
                autocomplete="new-password"
            >
            <span class="auth-hint">{{ __('auth.password_requirements') }}</span>
            @error('password')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation" class="auth-label">{{ __('auth.confirm_password') }}</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                class="auth-input @error('password_confirmation') is-invalid @enderror"
                placeholder="••••••••"
                autocomplete="new-password"
            >
            @error('password_confirmation')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-submit">
            {{ __('auth.reset_password_button') }}
        </button>
    </form>

    <div class="auth-footer">
        <a href="{{ route('login') }}" class="auth-link">
            {{ __('auth.back_to_login') }}
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
@endsection
