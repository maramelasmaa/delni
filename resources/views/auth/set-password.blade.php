@extends('layouts.auth')

@section('title', __('auth.set_password_title') . ' - ' . config('app.name'))
@section('hide_home_back', true)

@section('auth_title')
    {{ __('auth.set_password_title') }}
@endsection

@section('auth_subtitle')
    {{ __('auth.set_password_subtitle') }}
@endsection

@section('content')
    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4l1.3-1.3 1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/>
            </svg>
            <div>
                <strong>حدث خطأ</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('onboarding.set-password') }}" class="auth-form" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-field">
            <div class="auth-label-row">
                <label for="email" class="auth-label">{{ __('auth.email') }}</label>
                <span class="auth-hint">موثق</span>
            </div>

            <input
                type="email"
                id="email"
                class="auth-input is-dark"
                value="{{ $email }}"
                readonly
                tabindex="-1"
                aria-readonly="true"
            >

            <span class="auth-hint">
                هذا البريد مرتبط بحسابك ولا يمكن تعديله من هذه الصفحة.
            </span>
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
                minlength="8"
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
                minlength="8"
            >
            @error('password_confirmation')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-submit">
            {{ __('auth.set_password_button') }}
        </button>
    </form>
@endsection
