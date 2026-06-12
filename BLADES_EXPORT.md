# Blade Files Export

**Generated:** 2026-06-11 20:39:12

## Table of Contents

- [auth\account-edit.blade.php](#auth-account-edit-blade-php)
- [auth\forgot-password.blade.php](#auth-forgot-password-blade-php)
- [auth\login.blade.php](#auth-login-blade-php)
- [auth\register.blade.php](#auth-register-blade-php)
- [auth\reset-password.blade.php](#auth-reset-password-blade-php)
- [auth\set-password.blade.php](#auth-set-password-blade-php)
- [components\chatbot-widget.blade.php](#components-chatbot-widget-blade-php)
- [components\empty-state.blade.php](#components-empty-state-blade-php)
- [components\provider-card.blade.php](#components-provider-card-blade-php)
- [components\provider-grid.blade.php](#components-provider-grid-blade-php)
- [components\render-icon.blade.php](#components-render-icon-blade-php)
- [components\search-filters.blade.php](#components-search-filters-blade-php)
- [components\svg-icon.blade.php](#components-svg-icon-blade-php)
- [dashboard.blade.php](#dashboard-blade-php)
- [emails\password-reset.blade.php](#emails-password-reset-blade-php)
- [emails\set-password.blade.php](#emails-set-password-blade-php)
- [errors\403.blade.php](#errors-403-blade-php)
- [errors\404.blade.php](#errors-404-blade-php)
- [errors\500.blade.php](#errors-500-blade-php)
- [errors\503.blade.php](#errors-503-blade-php)
- [errors\panel.blade.php](#errors-panel-blade-php)
- [filament\brand.blade.php](#filament-brand-blade-php)
- [layouts\auth.blade.php](#layouts-auth-blade-php)
- [onboarding-link.blade.php](#onboarding-link-blade-php)
- [public\categories.blade.php](#public-categories-blade-php)
- [public\category.blade.php](#public-category-blade-php)
- [public\city.blade.php](#public-city-blade-php)
- [public\contact.blade.php](#public-contact-blade-php)
- [public\home.blade.php](#public-home-blade-php)
- [public\layout.blade.php](#public-layout-blade-php)
- [public\legal\disclaimer.blade.php](#public-legal-disclaimer-blade-php)
- [public\legal\privacy.blade.php](#public-legal-privacy-blade-php)
- [public\legal\terms.blade.php](#public-legal-terms-blade-php)
- [public\legal_layout.blade.php](#public-legal_layout-blade-php)
- [public\provider.blade.php](#public-provider-blade-php)
- [public\search.blade.php](#public-search-blade-php)
- [public\subcategory.blade.php](#public-subcategory-blade-php)
- [public\top-rated.blade.php](#public-top-rated-blade-php)

---

## auth\account-edit.blade.php

```blade
@extends('layouts.auth')

@section('auth_title', 'Edit Account')

@section('content')
<h2 class="text-2xl font-bold mb-6 text-navy-800">Edit Account</h2>

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded">
        <ul class="list-disc list-inside text-red-600 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('account.update') }}" method="POST">
    @csrf

    <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $user->name) }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            required
        />
    </div>

    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $user->email) }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            required
        />
    </div>

    <div class="mb-6">
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input
            type="tel"
            id="phone"
            name="phone"
            value="{{ old('phone', $user->phone) }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500"
        />
    </div>

    <button type="submit" class="w-full bg-primary-600 text-white py-2 rounded-md font-medium hover:bg-primary-700 transition">
        Save Changes
    </button>
</form>

<div class="mt-4 text-center">
    <a href="{{ route('dashboard') }}" class="text-sm text-primary-600 hover:text-primary-700">Back to Dashboard</a>
</div>
@endsection

```

## auth\forgot-password.blade.php

```blade
@extends('layouts.auth')

@section('title', __('auth.forgot_password_title') . ' - ' . config('app.name'))

@section('auth_title')
    نسيت كلمة المرور؟
@endsection

@section('auth_subtitle')
    أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين.
@endsection

@section('content')
    @if (session('status'))
        <div class="auth-alert auth-alert-success">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <strong>تم الإرسال</strong>
                {{ session('status') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4l1.3-1.3 1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/>
            </svg>
            <div>
                <strong>تعذر إرسال الرابط</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-label">البريد الإلكتروني</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="auth-input @error('email') is-invalid @enderror"
                placeholder="you@example.com"
                autocomplete="email"
            >
            @error('email')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-submit">
            إرسال رابط إعادة التعيين
        </button>
    </form>

    <div class="auth-footer">
        <p>تذكرت كلمة المرور؟</p>
        <a href="{{ route('login') }}" class="auth-link">
            تسجيل الدخول
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
@endsection

```

## auth\login.blade.php

```blade
@extends('layouts.auth')

@section('title', __('auth.login_title') . ' - ' . config('app.name'))

@section('auth_title')
    تسجيل الدخول
@endsection

@section('auth_subtitle')
    أدخل بياناتك للمتابعة إلى حسابك.
@endsection

@section('content')
    @if ($errors->any())
        <div class="auth-alert auth-alert-danger">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4l1.3-1.3 1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/>
            </svg>
            <div>
                <strong>تعذر تسجيل الدخول</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-label">البريد الإلكتروني</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="auth-input @error('email') is-invalid @enderror"
                placeholder="you@example.com"
                autocomplete="email"
            >
            @error('email')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <div class="auth-label-row">
                <label for="password" class="auth-label">كلمة المرور</label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-help-link">
                        نسيت كلمة المرور؟
                    </a>
                @endif
            </div>

            <input
                type="password"
                id="password"
                name="password"
                required
                class="auth-input @error('password') is-invalid @enderror"
                placeholder="••••••••"
                autocomplete="current-password"
            >
            @error('password')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-submit">
            تسجيل الدخول
        </button>
    </form>

    <div class="auth-divider">
        <span>أو</span>
    </div>

    <a href="{{ route('auth.google') }}" class="auth-oauth-button auth-oauth-google">
        <svg class="auth-oauth-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        <span>تسجيل دخول عبر Google</span>
    </a>

    <div class="auth-footer">
        <p>ليس لديك حساب؟</p>
        <a href="{{ route('register') }}" class="auth-link">
            إنشاء حساب
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
@endsection

```

## auth\register.blade.php

```blade
@extends('layouts.auth')

@section('title', 'إنشاء حساب - ' . config('app.name'))

@section('auth_title')
    إنشاء حساب
@endsection

@section('auth_subtitle')
    ابدأ رحلتك للعثور على أفضل الخدمات في ليبيا.
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

    <form action="{{ route('register') }}" method="POST" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="name" class="auth-label">الاسم الكامل</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                class="auth-input @error('name') is-invalid @enderror"
                placeholder="أدخل اسمك الكامل"
                autocomplete="name"
            >
            @error('name')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="email" class="auth-label">البريد الإلكتروني</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="auth-input @error('email') is-invalid @enderror"
                placeholder="you@example.com"
                autocomplete="email"
            >
            @error('email')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="phone" class="auth-label">رقم الهاتف</label>
            <input
                type="tel"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                required
                class="auth-input @error('phone') is-invalid @enderror"
                placeholder="+218 91 123 4567"
                autocomplete="tel"
            >
            @error('phone')
                <span class="auth-error-text">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-grid">
            <div class="auth-field">
                <label for="password" class="auth-label">كلمة المرور</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="auth-input @error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    autocomplete="new-password"
                >
                @error('password')
                    <span class="auth-error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password_confirmation" class="auth-label">تأكيد كلمة المرور</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="auth-input"
                    placeholder="••••••••"
                    autocomplete="new-password"
                >
            </div>
        </div>

        <button type="submit" class="auth-submit">
            إنشاء حساب
        </button>
    </form>

    <div class="auth-divider">
        <span>أو</span>
    </div>

    <a href="{{ route('auth.google') }}" class="auth-oauth-button auth-oauth-google">
        <svg class="auth-oauth-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        <span>إنشاء حساب عبر Google</span>
    </a>

    <div class="auth-footer">
        <p>هل لديك حساب بالفعل؟</p>
        <a href="{{ route('login') }}" class="auth-link">
            تسجيل الدخول
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
@endsection

```

## auth\reset-password.blade.php

```blade
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

```

## auth\set-password.blade.php

```blade
@extends('layouts.auth')

@section('title', __('auth.set_password_title') . ' - ' . config('app.name'))

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

```

## components\chatbot-widget.blade.php

```blade
<div class="delni-chat" data-delni-chat>
    <button class="delni-chat__toggle" type="button" data-chat-toggle aria-label="مساعد دلني">
        <svg class="delni-chat__icon delni-chat__icon--open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.33.105.746.41 1.02.73a4.015 4.015 0 0 1 .46 3.633l-.534 1.832c-.173.593-.733.994-1.35.994h-.477a4.5 4.5 0 0 0-4.5 4.5v.477c0 .617-.401 1.177-.994 1.35l-1.832.534a4.016 4.016 0 0 1-3.633-.46c-.32-.274-.625-.69-.73-1.02M3.75 13.5h.008v.008H3.75V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM12 15.75h.007v.008H12v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.875-6a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.598.598 0 0 1-.774-.615c.08-.858.261-1.696.533-2.493C3.411 16.277 2.25 14.269 2.25 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
        </svg>
        <svg class="delni-chat__icon delni-chat__icon--close" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </button>

    <section class="delni-chat__panel" data-chat-panel hidden>
        <header class="delni-chat__header">
            <div class="delni-chat__brand">
                <strong>مساعد دلني</strong>
                <span>اسأل عن خدمة أو مقدم خدمة</span>
            </div>
            <button type="button" class="delni-chat__reset-btn" data-chat-reset aria-label="إعادة تعيين المحادثة">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>إعادة</span>
            </button>
        </header>

        <div class="delni-chat__messages" data-chat-messages></div>

        <div class="delni-chat__suggestions" data-chat-suggestions>
            <button type="button">محامي في طرابلس</button>
            <button type="button">فني تكييف في بنغازي</button>
            <button type="button">مصور أفراح</button>
        </div>

        <form class="delni-chat__form" data-chat-form>
            <input type="text" name="message" maxlength="500" autocomplete="off" placeholder="اكتب طلبك هنا..." data-chat-input required>
            <button type="submit" data-chat-submit aria-label="إرسال">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 18px; height: 18px; transform: rotate(180deg);">
                    <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                </svg>
            </button>
        </form>
    </section>
</div>

@once
    <style>
        :root {
            --delni-primary-rgb: 241, 98, 15;
            --delni-navy-rgb: 11, 26, 52;
        }

        .delni-chat {
            position: fixed;
            inset-inline-end: 1.5rem;
            bottom: 1.5rem;
            z-index: 9999;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Ubuntu, sans-serif;
            direction: rtl;
        }

        /* Toggle Button Styles */
        .delni-chat__toggle {
            width: 56px;
            height: 56px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 50%;
            background: var(--delni-primary);
            color: #fff;
            box-shadow: 0 8px 24px rgba(var(--delni-primary-rgb), 0.35);
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .delni-chat__toggle:hover {
            transform: scale(1.05);
            background: #e0560d;
        }

        .delni-chat__toggle:active {
            transform: scale(0.95);
        }

        .delni-chat__icon {
            width: 24px;
            height: 24px;
            transition: opacity 0.2s ease;
        }

        /* Main Panel Styles */
        .delni-chat__panel {
            position: absolute;
            inset-inline-end: 0;
            bottom: 4.5rem;
            width: min(calc(100vw - 2rem), 380px);
            height: 520px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--delni-border);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 12px 40px rgba(var(--delni-navy-rgb), 0.12);
            opacity: 0;
            transform: translateY(15px) scale(0.95);
            transition: opacity 0.25s ease, transform 0.25s ease;
            pointer-events: none;
        }

        .delni-chat__panel:not([hidden]) {
            display: flex;
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        /* Header */
        .delni-chat__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: var(--delni-navy);
            color: #fff;
        }

        .delni-chat__brand strong {
            display: block;
            font-size: 1rem;
            font-weight: 700;
        }

        .delni-chat__brand span {
            display: block;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.75rem;
            margin-top: 0.15rem;
        }

        .delni-chat__reset-btn {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            border: 0;
            border-radius: 8px;
            padding: 0.4rem 0.65rem;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .delni-chat__reset-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Message Area */
        .delni-chat__messages {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            overflow-y: auto;
            padding: 1.25rem;
            background: #f8f9fa;
            scroll-behavior: smooth;
        }

        /* Chat Bubbles */
        .delni-chat__bubble {
            max-width: 85%;
            padding: 0.75rem 1rem;
            border-radius: 16px 16px 2px 16px;
            color: var(--delni-navy);
            background: #fff;
            border: 1px solid var(--delni-border);
            font-size: 0.875rem;
            line-height: 1.5;
            align-self: flex-start; /* Bot message default on right side */
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .delni-chat__bubble.is-user {
            align-self: flex-end; /* User message shifts left */
            background: var(--delni-primary);
            color: #fff;
            border-color: var(--delni-primary);
            border-radius: 16px 16px 16px 2px;
        }

        /* Provider Cards */
        .delni-chat__provider {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            padding: 1rem;
            border: 1px solid var(--delni-border);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            max-width: 85%;
            align-self: flex-start;
        }

        .delni-chat__provider strong {
            color: var(--delni-navy);
            font-size: 0.9rem;
            font-weight: 700;
        }

        .delni-chat__provider span {
            color: var(--delni-muted);
            font-size: 0.78rem;
        }

        .delni-chat__provider .rating {
            color: #ffb400;
            font-weight: 600;
        }

        .delni-chat__provider a {
            color: var(--delni-primary);
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            margin-top: 0.25rem;
            display: inline-flex;
            align-items: center;
        }

        .delni-chat__provider a:hover {
            text-decoration: underline;
        }

        /* Quick Suggestions */
        .delni-chat__suggestions {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.75rem 1rem;
            background: #fff;
            border-top: 1px solid rgba(0,0,0,0.04);
            scrollbar-width: none; /* Hide standard scrollbars */
        }
        .delni-chat__suggestions::-webkit-scrollbar {
            display: none;
        }

        .delni-chat__suggestions button {
            flex: 0 0 auto;
            border: 1px solid var(--delni-border);
            border-radius: 20px;
            padding: 0.45rem 0.85rem;
            background: #fff;
            color: var(--delni-navy);
            font-family: inherit;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .delni-chat__suggestions button:hover {
            background: rgba(var(--delni-primary-rgb), 0.08);
            border-color: var(--delni-primary);
            color: var(--delni-primary);
        }

        /* Input Form */
        .delni-chat__form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.5rem;
            padding: 0.75rem 1rem 1rem;
            background: #fff;
            border-top: 1px solid var(--delni-border);
        }

        .delni-chat__form input {
            min-width: 0;
            border: 1px solid var(--delni-border);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-family: inherit;
            font-size: 0.875rem;
            background: #f8f9fa;
            transition: all 0.2s;
        }

        .delni-chat__form input:focus {
            outline: none;
            border-color: var(--delni-primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(var(--delni-primary-rgb), 0.15);
        }

        .delni-chat__form button {
            border: 0;
            border-radius: 12px;
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            background: var(--delni-primary);
            color: #fff;
            cursor: pointer;
            transition: background 0.2s;
        }

        .delni-chat__form button:hover {
            background: #e0560d;
        }

        .delni-chat__form input:disabled,
        .delni-chat__form button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Mobile Optimization */
        @media (max-width: 480px) {
            .delni-chat {
                inset-inline-end: 1rem;
                bottom: 1rem;
            }

            .delni-chat__panel {
                position: fixed;
                inset: 0;
                width: 100%;
                height: 100%;
                border-radius: 0;
                bottom: 0;
            }
        }
    </style>
@endonce

@once
@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-delni-chat]');
            if (!root) return;

            const toggleBtn = root.querySelector('[data-chat-toggle]');
            const iconOpen = root.querySelector('.delni-chat__icon--open');
            const iconClose = root.querySelector('.delni-chat__icon--close');
            const panel = root.querySelector('[data-chat-panel]');
            const messages = root.querySelector('[data-chat-messages]');
            const form = root.querySelector('[data-chat-form]');
            const input = root.querySelector('[data-chat-input]');
            const submitBtn = root.querySelector('[data-chat-submit]');
            const suggestions = root.querySelector('[data-chat-suggestions]');
            let sessionId = localStorage.getItem('delni_chat_session_id') || '';

            const appendBubble = (text, type = 'assistant') => {
                const bubble = document.createElement('div');
                bubble.className = 'delni-chat__bubble' + (type === 'user' ? ' is-user' : '');
                bubble.textContent = text;
                messages.appendChild(bubble);
                messages.scrollTop = messages.scrollHeight;
            };

            const appendProviders = (providers = []) => {
                providers.forEach((provider) => {
                    const card = document.createElement('article');
                    card.className = 'delni-chat__provider';
                    card.innerHTML = `
                        <strong>${provider.name || 'مقدم خدمة'}</strong>
                        <span>${[provider.city, provider.category].filter(Boolean).join(' · ')}</span>
                        <span class="rating">${Number(provider.rating || 0).toFixed(1)} ★ <span style="color: var(--delni-muted); font-weight: normal;">(${provider.reviews_count || 0} تقييم)</span></span>
                        <a href="${provider.url}">عرض الملف الخصي ←</a>
                    `;
                    messages.appendChild(card);
                });
                messages.scrollTop = messages.scrollHeight;
            };

            const sendMessage = async (message) => {
                appendBubble(message, 'user');
                input.value = '';

                // Block input during request
                input.disabled = true;
                submitBtn.disabled = true;

                try {
                    const response = await fetch('{{ route('api.chat.message') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ message, session_id: sessionId }),
                    });
                    const payload = await response.json();
                    sessionId = payload.session_id || sessionId;
                    localStorage.setItem('delni_chat_session_id', sessionId);
                    appendBubble(payload.message || 'حدث خطأ بسيط، يرجى المحاولة مرة أخرى.');
                    appendProviders(payload.providers || []);
                } catch (error) {
                    appendBubble('عذرًا، حدث خطأ في الاتصال بالشبكة. حاول مرة ثانية.');
                } finally {
                    input.disabled = false;
                    submitBtn.disabled = false;
                    input.focus();
                }
            };

            // Toggle Panel & Toggle Icons Smoothly
            toggleBtn.addEventListener('click', () => {
                const isHidden = panel.hasAttribute('hidden');
                if (isHidden) {
                    panel.removeAttribute('hidden');
                    iconOpen.style.display = 'none';
                    iconClose.style.display = 'block';
                    if (messages.children.length === 0) {
                        appendBubble('أهلاً بك، أنا مساعد دلني الذكي. قلّي كيف نقدر نساعدك اليوم أو شن الخدمة يلي تبحث عليها؟');
                    }
                } else {
                    panel.setAttribute('hidden', '');
                    iconOpen.style.display = 'block';
                    iconClose.style.display = 'none';
                }
            });

            root.querySelector('[data-chat-reset]').addEventListener('click', async () => {
                if(confirm("هل أنت متأكد من مسح المحادثة بالكامل؟")) {
                    messages.innerHTML = '';
                    try {
                        await fetch('{{ route('api.chat.reset') }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ session_id: sessionId }),
                        });
                    } catch (e) {}
                    sessionId = '';
                    localStorage.removeItem('delni_chat_session_id');
                    appendBubble('تم مسح المحادثة بنجاح. تفضل، كيف نقدر نساعدك توا؟');
                }
            });

            suggestions.addEventListener('click', (event) => {
                if (event.target.matches('button') && !input.disabled) {
                    sendMessage(event.target.textContent.trim());
                }
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const message = input.value.trim();
                if (message && !input.disabled) sendMessage(message);
            });
        })();
    </script>
@endpush
@endonce

```

## components\empty-state.blade.php

```blade
@props([
    'icon' => 'heroicon-o-magnifying-glass',
    'title' => __('messages.public.no_results'),
    'message' => __('messages.public.try_again_later'),
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="delni-empty-state">
    <div class="delni-empty-state__icon">
        <x-render-icon :icon="$icon" />
    </div>

    <h3 class="delni-empty-state__title">
        {{ $title }}
    </h3>

    @if($message)
        <p class="delni-empty-state__message">
            {{ $message }}
        </p>
    @endif

    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="delni-empty-state__action">
            {{ $actionLabel }}
        </a>
    @endif
</div>

@once
    @push('styles')
        <style>
            .delni-empty-state {
                text-align: center;
                padding: clamp(2rem, 5vw, 3.5rem) 1.25rem;
                background:
                    radial-gradient(circle at top, rgba(255, 122, 26, 0.08), transparent 38%),
                    #ffffff;
                border: 1px solid #e8edf4;
                border-radius: 24px;
                box-shadow: 0 14px 38px rgba(11, 26, 52, 0.06);
            }

            .delni-empty-state__icon {
                width: 64px;
                height: 64px;
                margin: 0 auto 1.25rem;
                border-radius: 20px;
                background: #fff7ed;
                color: #f1620f;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(241, 98, 15, 0.14);
            }

            .delni-empty-state__icon svg {
                width: 30px;
                height: 30px;
            }

            .delni-empty-state__title {
                margin: 0 0 0.5rem;
                font-size: 1.12rem;
                font-weight: 900;
                color: #0b1a34;
                letter-spacing: -0.02em;
            }

            .delni-empty-state__message {
                max-width: 420px;
                margin: 0 auto;
                color: #64748b;
                line-height: 1.8;
                font-size: 0.94rem;
                font-weight: 500;
            }

            .delni-empty-state__action {
                margin-top: 1.2rem;
                min-height: 44px;
                padding: 0.7rem 1.1rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
                background: linear-gradient(135deg, #ff8533, #ff6b1a);
                color: #fff;
                text-decoration: none;
                font-size: 0.9rem;
                font-weight: 900;
                box-shadow: 0 12px 24px rgba(255, 107, 26, 0.18);
                transition: 0.15s ease;
            }

            .delni-empty-state__action:hover {
                transform: translateY(-1px);
                box-shadow: 0 14px 32px rgba(255, 107, 26, 0.24);
            }

            @media (max-width: 640px) {
                .delni-empty-state {
                    padding: 2rem 1rem;
                }

                .delni-empty-state__icon {
                    width: 56px;
                    height: 56px;
                    margin-bottom: 1rem;
                }

                .delni-empty-state__title {
                    font-size: 1rem;
                }

                .delni-empty-state__message {
                    font-size: 0.88rem;
                }

                .delni-empty-state__action {
                    min-height: 40px;
                    font-size: 0.85rem;
                }
            }
        </style>
    @endpush
@endonce

```

## components\provider-card.blade.php

```blade
@props([
    'provider',
    'showBio' => true,
])

@php
    $businessName = $provider->business_name ?? __('messages.public.provider');

    $cardImage = null;
    if ($provider->cover_image) {
        $cardImage = \Illuminate\Support\Facades\Storage::disk('public')->url($provider->cover_image);
    } elseif ($provider->logo) {
        $cardImage = \Illuminate\Support\Facades\Storage::disk('public')->url($provider->logo);
    }

    $rating = (float) ($provider->stats?->rating_avg ?? 0);
    $reviewsCount = (int) ($provider->stats?->reviews_count ?? 0);

    $categoryName = $provider->category
        ? ($provider->category->localized_name ?? $provider->category->name)
        : null;

    $cityName = $provider->city
        ? ($provider->city->localized_name ?? $provider->city->name)
        : null;

    $whatsappNumber = $provider->whatsapp ? preg_replace('/[^0-9]/', '', $provider->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وجدتك عبر دلني وأرغب بالاستفسار عن الخدمة.');
@endphp

<article class="delni-provider-card">
    <a href="{{ route('public.provider', $provider->slug) }}" class="delni-provider-card__media">
        @if($cardImage)
            <img
                src="{{ $cardImage }}"
                alt="{{ $businessName }}"
                loading="lazy"
                class="delni-provider-card__image"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            >
            <div class="delni-provider-card__fallback" style="display:none;">
                {{ mb_substr($businessName, 0, 1) }}
            </div>
        @else
            <div class="delni-provider-card__fallback">
                {{ mb_substr($businessName, 0, 1) }}
            </div>
        @endif

        @if($rating >= 4.5 && $reviewsCount >= 5)
            <span class="delni-provider-card__badge">
                الأعلى تقييماً
            </span>
        @endif
    </a>

    <div class="delni-provider-card__body">
        <div class="delni-provider-card__top">
            <h3 class="delni-provider-card__title">
                <a href="{{ route('public.provider', $provider->slug) }}">
                    {{ $businessName }}
                </a>
            </h3>

            <div class="delni-provider-card__rating">
                <span class="delni-provider-card__star">★</span>
                <strong>{{ number_format($rating, 1) }}</strong>
                <span>({{ $reviewsCount }})</span>
            </div>
        </div>

        <div class="delni-provider-card__meta">
            @if($categoryName)
                <span>
                    <x-render-icon icon="heroicon-o-briefcase" />
                    {{ $categoryName }}
                </span>
            @endif

            @if($cityName)
                <span>
                    <x-render-icon icon="heroicon-o-map-pin" />
                    {{ $cityName }}
                </span>
            @endif

            @if($provider->offers_remote_work)
                <span>
                    <x-render-icon icon="heroicon-o-globe-alt" />
                    عن بعد
                </span>
            @endif
        </div>

        @if($showBio && filled($provider->bio))
            <p class="delni-provider-card__bio">
                {{ Str::limit(strip_tags($provider->bio), 110) }}
            </p>
        @endif

        <div class="delni-provider-card__actions">
            <a href="{{ route('public.provider', $provider->slug) }}" class="delni-provider-card__primary">
                عرض الملف
            </a>

            @if($whatsappNumber)
                <a
                    href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="delni-provider-card__whatsapp"
                >
                    واتساب
                </a>
            @endif
        </div>
    </div>
</article>

@once
    @push('styles')
        <style>
            .delni-provider-card {
                min-width: 0;
                height: 100%;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                border-radius: 24px;
                background: #fff;
                border: 1px solid #E7E7E7;
                box-shadow: 0 12px 28px rgba(11, 26, 52, .06);
                transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            }

            .delni-provider-card:hover {
                transform: translateY(-3px);
                border-color: rgba(241, 98, 15, .22);
                box-shadow: 0 18px 40px rgba(11, 26, 52, .1);
            }

            .delni-provider-card__media {
                position: relative;
                display: block;
                height: 170px;
                overflow: hidden;
                background: #0B1A34;
                text-decoration: none;
            }

            .delni-provider-card__image,
            .delni-provider-card__fallback {
                width: 100%;
                height: 100%;
            }

            .delni-provider-card__image {
                display: block;
                object-fit: cover;
                transition: transform .28s ease;
            }

            .delni-provider-card:hover .delni-provider-card__image {
                transform: scale(1.035);
            }

            .delni-provider-card__fallback {
                display: flex;
                align-items: center;
                justify-content: center;
                background:
                    radial-gradient(circle at 30% 22%, rgba(241, 98, 15, .32), transparent 32%),
                    linear-gradient(135deg, #0B1A34, #13264A);
                color: #F1620F;
                font-size: 3rem;
                font-weight: 950;
            }

            .delni-provider-card__badge {
                position: absolute;
                top: .8rem;
                inset-inline-start: .8rem;
                min-height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: .45rem .75rem;
                border-radius: 999px;
                background: #22C55E;
                color: #fff;
                font-size: .78rem;
                font-weight: 900;
                box-shadow: 0 10px 20px rgba(34, 197, 94, .25);
            }

            .delni-provider-card__body {
                flex: 1;
                display: flex;
                flex-direction: column;
                padding: 1rem;
            }

            .delni-provider-card__top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: .8rem;
                margin-bottom: .85rem;
            }

            .delni-provider-card__title {
                margin: 0;
                min-width: 0;
                color: #0B1A34;
                font-size: 1.02rem;
                line-height: 1.45;
                font-weight: 950;
                letter-spacing: -.025em;
            }

            .delni-provider-card__title a {
                color: inherit;
                text-decoration: none;
            }

            .delni-provider-card__title a:hover {
                color: #F1620F;
            }

            .delni-provider-card__rating {
                flex-shrink: 0;
                display: inline-flex;
                align-items: center;
                gap: .25rem;
                color: #5D5959;
                font-size: .78rem;
                font-weight: 800;
                white-space: nowrap;
            }

            .delni-provider-card__rating strong {
                color: #0B1A34;
                font-weight: 950;
            }

            .delni-provider-card__star {
                color: #F59E0B;
            }

            .delni-provider-card__meta {
                display: flex;
                flex-wrap: wrap;
                gap: .45rem;
                margin-bottom: .85rem;
            }

            .delni-provider-card__meta span {
                min-height: 32px;
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                padding: .38rem .6rem;
                border-radius: 999px;
                background: #FCFBFB;
                border: 1px solid #E7E7E7;
                color: #5D5959;
                font-size: .76rem;
                font-weight: 850;
                max-width: 100%;
            }

            .delni-provider-card__meta svg {
                width: 15px;
                height: 15px;
                color: #F1620F;
                flex-shrink: 0;
            }

            .delni-provider-card__bio {
                margin: 0 0 1rem;
                color: #5D5959;
                font-size: .87rem;
                line-height: 1.8;
                font-weight: 500;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .delni-provider-card__actions {
                margin-top: auto;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: .55rem;
            }

            .delni-provider-card__primary,
            .delni-provider-card__whatsapp {
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
                text-decoration: none;
                font-size: .86rem;
                font-weight: 950;
                transition: .18s ease;
            }

            .delni-provider-card__primary {
                background: #F1620F;
                color: #fff;
                box-shadow: 0 10px 18px rgba(241, 98, 15, .18);
            }

            .delni-provider-card__primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 14px 26px rgba(241, 98, 15, .24);
            }

            .delni-provider-card__whatsapp {
                background: rgba(34, 197, 94, .1);
                color: #128C4A;
                border: 1px solid rgba(34, 197, 94, .18);
            }

            .delni-provider-card__whatsapp:hover {
                background: rgba(34, 197, 94, .16);
            }

            @media (max-width: 640px) {
                .delni-provider-card__media {
                    height: 164px;
                }

                .delni-provider-card__body {
                    padding: .9rem;
                }

                .delni-provider-card__top {
                    flex-direction: column;
                    gap: .35rem;
                }
            }
        </style>
    @endpush
@endonce

```

## components\provider-grid.blade.php

```blade
@props([
    'providers',
    'columns' => 3,
    'title' => null,
    'subtitle' => null,
])

@php
    $count = method_exists($providers, 'count') ? $providers->count() : count($providers);

    $gridClass = match((int) $columns) {
        1 => 'delni-provider-grid--one',
        2 => 'delni-provider-grid--two',
        4 => 'delni-provider-grid--four',
        default => 'delni-provider-grid--three',
    };
@endphp

<section class="delni-provider-section">
    @if($title || $subtitle)
        <header class="delni-section-head">
            <div>
                @if($title)
                    <h2 class="delni-section-title">{{ $title }}</h2>
                @endif

                @if($subtitle)
                    <p class="delni-section-subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @if($count > 0)
                <span class="delni-section-count">
                    {{ $count }} {{ __('messages.public.providers') }}
                </span>
            @endif
        </header>
    @endif

    @if($count > 0)
        <div class="delni-provider-grid {{ $gridClass }}">
            @foreach($providers as $provider)
                <x-provider-card :provider="$provider" />
            @endforeach
        </div>
    @else
        <x-empty-state
            title="{{ __('messages.public.no_providers_found') }}"
            message="{{ __('messages.public.try_different_search') }}"
            actionLabel="{{ __('messages.public.search') }}"
            actionUrl="{{ route('public.search') }}"
        />
    @endif
</section>

@once
    @push('styles')
        <style>
            .delni-provider-section {
                width: 100%;
            }

            .delni-section-head {
                display: flex;
                align-items: end;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1.25rem;
            }

            .delni-section-title {
                margin: 0;
                color: #0B1A34;
                font-size: clamp(1.35rem, 3vw, 1.9rem);
                line-height: 1.2;
                font-weight: 950;
                letter-spacing: -.04em;
            }

            .delni-section-subtitle {
                margin: .45rem 0 0;
                color: #5D5959;
                font-size: .95rem;
                line-height: 1.8;
                font-weight: 600;
            }

            .delni-section-count {
                flex-shrink: 0;
                min-height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: .55rem .85rem;
                border-radius: 999px;
                background: rgba(241, 98, 15, .08);
                color: #F1620F;
                border: 1px solid rgba(241, 98, 15, .12);
                font-size: .82rem;
                font-weight: 900;
            }

            .delni-provider-grid {
                display: grid;
                gap: 1rem;
            }

            .delni-provider-grid--one {
                grid-template-columns: 1fr;
            }

            .delni-provider-grid--two {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .delni-provider-grid--three {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .delni-provider-grid--four {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            @media (max-width: 1160px) {
                .delni-provider-grid--four {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }

            @media (max-width: 920px) {
                .delni-provider-grid--four,
                .delni-provider-grid--three {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .delni-section-head {
                    align-items: start;
                    flex-direction: column;
                    margin-bottom: 1rem;
                }

                .delni-provider-grid,
                .delni-provider-grid--four,
                .delni-provider-grid--three,
                .delni-provider-grid--two {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush
@endonce

```

## components\render-icon.blade.php

```blade
@props([
    'icon' => null,
    'class' => '',
])

@php
    $name = $icon ?: 'heroicon-o-square-3-stack-3d';

    $svgs = [
        'heroicon-o-phone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.37a1.5 1.5 0 00-1.024-1.423l-4.106-1.369a1.5 1.5 0 00-1.594.37l-1.03 1.03a11.25 11.25 0 01-6.734-6.734l1.03-1.03a1.5 1.5 0 00.37-1.594L7.293 3.274A1.5 1.5 0 005.87 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z" />',
        'heroicon-o-chat-bubble-left' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'heroicon-o-map-pin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />',
        'heroicon-o-briefcase' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.1A2.25 2.25 0 0118 20.5H6a2.25 2.25 0 01-2.25-2.25v-4.1m16.5 0A2.25 2.25 0 0018 11.9H6a2.25 2.25 0 00-2.25 2.25m16.5 0v-3.4A2.25 2.25 0 0018 8.5h-1.5m-13.5 5.65v-3.4A2.25 2.25 0 016 8.5h1.5m9 0V6.75A2.25 2.25 0 0014.25 4.5h-4.5A2.25 2.25 0 007.5 6.75V8.5m9 0h-9" />',
        'heroicon-o-globe-alt' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" /><path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M12 3c2.25 2.25 3.375 5.25 3.375 9S14.25 18.75 12 21M12 3C9.75 5.25 8.625 8.25 8.625 12S9.75 18.75 12 21" />',
        'heroicon-o-magnifying-glass' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.197 5.197a7.5 7.5 0 0010.606 10.606z" />',
        'heroicon-o-envelope' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0l-7.5-4.615A2.25 2.25 0 012.25 6.993V6.75" />',
        'heroicon-o-building-office-2' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V9.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636m0 0h9m-9 0L2.25 9m0 0V6.504c0-1.341 1.084-2.436 2.424-2.436h15.152c1.34 0 2.424 1.095 2.424 2.436V9m-21 0V3.75A2.25 2.25 0 015.25 1.5h13.5A2.25 2.25 0 0121 3.75V9" />',
        'heroicon-o-photo' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />',
        'heroicon-o-funnel' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.132 0 4.116.756 5.604 2.01m-7.08 8.994L12 15m0 0l2.475 2.006M12 15l-2.475 2.006M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />',
        'heroicon-o-arrow-path' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992M2.763 9.348c.547-4.055 4.029-7.036 8.237-7.036 4.735 0 8.659 3.373 9.021 7.646m15.997 3.464c-.547 4.055-4.029 7.036-8.236 7.036-4.735 0-8.659-3.373-9.021-7.646" />',
        'heroicon-o-square-3-stack-3d' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12a1.5 1.5 0 011.5 1.5V9m-18-6a1.5 1.5 0 00-1.5 1.5v6m0 0a1.5 1.5 0 001.5 1.5h12a1.5 1.5 0 001.5-1.5m-18 0V5.25m0 10.5a1.5 1.5 0 001.5 1.5h12a1.5 1.5 0 001.5-1.5M6 21h12a1.5 1.5 0 001.5-1.5v-6a1.5 1.5 0 00-1.5-1.5H6a1.5 1.5 0 00-1.5 1.5v6a1.5 1.5 0 001.5 1.5z" />',
    ];

    $path = $svgs[$name] ?? $svgs['heroicon-o-square-3-stack-3d'];
@endphp

<svg
    class="{{ $class }}"
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
    stroke-width="1.5"
    aria-hidden="true"
>
    {!! $path !!}
</svg>

```

## components\search-filters.blade.php

```blade
@props([
    'categories' => null,
    'cities' => null,
    'providerTypes' => null,
])

@php
    $hasFilters = request()->filled('keyword')
        || request()->filled('category_id')
        || request()->filled('city_id')
        || request()->filled('provider_type')
        || request()->filled('remote')
        || request()->filled('sort');
@endphp

<div class="delni-filters">
    <form method="GET" action="{{ route('public.search') }}" class="delni-filters__form">
        <header class="delni-filters__header">
            <div>
                <h3>مرشحات البحث</h3>
                <p>ضيّق النتائج حسب احتياجك.</p>
            </div>

            @if($hasFilters)
                <a href="{{ route('public.search') }}">مسح</a>
            @endif
        </header>

        <div class="delni-filter-field">
            <label for="keyword">كلمة البحث</label>
            <input
                type="text"
                id="keyword"
                name="keyword"
                value="{{ request('keyword') }}"
                maxlength="100"
                placeholder="مثال: تصوير، سباكة، تصميم..."
            >
        </div>

        @if($categories)
            <div class="delni-filter-field">
                <label for="category_id">الفئة</label>
                <select id="category_id" name="category_id">
                    <option value="">جميع الفئات</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                            {{ $category->localized_name ?? $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($cities)
            <div class="delni-filter-field">
                <label for="city_id">المدينة</label>
                <select id="city_id" name="city_id">
                    <option value="">جميع المدن</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                            {{ $city->localized_name ?? $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($providerTypes)
            <div class="delni-filter-field">
                <label for="provider_type">نوع المزود</label>
                <select id="provider_type" name="provider_type">
                    <option value="">جميع الأنواع</option>
                    @foreach($providerTypes as $code => $name)
                        <option value="{{ $code }}" @selected((string) request('provider_type') === (string) $code)>
                            {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <label class="delni-filter-check" for="remote">
            <input
                type="checkbox"
                id="remote"
                name="remote"
                value="1"
                @checked(request('remote') == 1)
            >
            <span>
                <strong>يدعم العمل عن بعد</strong>
                <small>مناسب للخدمات الرقمية والاستشارات</small>
            </span>
        </label>

        <div class="delni-filter-field">
            <label for="sort">ترتيب النتائج</label>
            <select id="sort" name="sort">
                <option value="" @selected(!request('sort'))>الأكثر صلة</option>
                <option value="rating" @selected(request('sort') === 'rating')>الأعلى تقييماً</option>
                <option value="reviews" @selected(request('sort') === 'reviews')>الأكثر مراجعات</option>
                <option value="newest" @selected(request('sort') === 'newest')>الأحدث</option>
            </select>
        </div>

        <button type="submit" class="delni-filters__submit">
            تطبيق البحث
        </button>
    </form>
</div>

@once
    @push('styles')
        <style>
            .delni-filters {
                border-radius: 24px;
                background: #fff;
                border: 1px solid #E7E7E7;
                box-shadow: 0 14px 34px rgba(11, 26, 52, .06);
                overflow: hidden;
            }

            .delni-filters__form {
                display: flex;
                flex-direction: column;
                gap: .9rem;
                padding: 1rem;
            }

            .delni-filters__header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                padding-bottom: .9rem;
                border-bottom: 1px solid #E7E7E7;
            }

            .delni-filters__header h3 {
                margin: 0;
                color: #0B1A34;
                font-size: 1.05rem;
                line-height: 1.3;
                font-weight: 950;
                letter-spacing: -.025em;
            }

            .delni-filters__header p {
                margin: .35rem 0 0;
                color: #5D5959;
                font-size: .82rem;
                line-height: 1.7;
                font-weight: 600;
            }

            .delni-filters__header a {
                color: #F1620F;
                text-decoration: none;
                font-size: .82rem;
                font-weight: 950;
            }

            .delni-filter-field {
                display: flex;
                flex-direction: column;
                gap: .4rem;
            }

            .delni-filter-field label {
                color: #0B1A34;
                font-size: .84rem;
                font-weight: 950;
            }

            .delni-filter-field input,
            .delni-filter-field select {
                width: 100%;
                height: 44px;
                padding-inline: .85rem;
                border-radius: 14px;
                border: 1px solid #E7E7E7;
                background: #FCFBFB;
                color: #0B1A34;
                font: inherit;
                font-size: .88rem;
                font-weight: 800;
                outline: none;
                transition: .18s ease;
            }

            .delni-filter-field input::placeholder {
                color: #9b9696;
                font-weight: 700;
            }

            .delni-filter-field input:focus,
            .delni-filter-field select:focus {
                border-color: rgba(241, 98, 15, .65);
                background: #fff;
                box-shadow: 0 0 0 4px rgba(241, 98, 15, .08);
            }

            .delni-filter-check {
                min-height: 74px;
                display: flex;
                align-items: center;
                gap: .75rem;
                padding: .8rem;
                border-radius: 18px;
                background: #FCFBFB;
                border: 1px solid #E7E7E7;
                cursor: pointer;
            }

            .delni-filter-check input {
                width: 20px;
                height: 20px;
                flex-shrink: 0;
                accent-color: #F1620F;
                cursor: pointer;
            }

            .delni-filter-check span {
                display: flex;
                flex-direction: column;
                gap: .15rem;
            }

            .delni-filter-check strong {
                color: #0B1A34;
                font-size: .9rem;
                font-weight: 950;
            }

            .delni-filter-check small {
                color: #5D5959;
                font-size: .78rem;
                line-height: 1.6;
                font-weight: 600;
            }

            .delni-filters__submit {
                min-height: 46px;
                border: 0;
                border-radius: 15px;
                background: #F1620F;
                color: #fff;
                font: inherit;
                font-size: .9rem;
                font-weight: 950;
                cursor: pointer;
                box-shadow: 0 12px 24px rgba(241, 98, 15, .2);
                transition: .18s ease;
            }

            .delni-filters__submit:hover {
                transform: translateY(-1px);
                box-shadow: 0 16px 30px rgba(241, 98, 15, .26);
            }

            @media (max-width: 900px) {
                .delni-filters__form {
                    padding: .9rem;
                }
            }
        </style>
    @endpush
@endonce

```

## components\svg-icon.blade.php

```blade
@props(['icon', 'size' => '24'])

@if($icon)
    <img
        src="{{ route('icon.show', $icon) }}"
        alt="{{ $icon->name ?? 'Icon' }}"
        width="{{ $size }}"
        height="{{ $size }}"
        style="width: {{ $size }}px !important; height: {{ $size }}px !important; object-fit: contain;"
        loading="lazy"
        {{ $attributes }}
    />
@else
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#F1620F"
        width="{{ $size }}"
        height="{{ $size }}"
        style="width: {{ $size }}px !important; height: {{ $size }}px !important;"
        {{ $attributes }}>
        <path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-2h2v16h-2z"/>
    </svg>
@endif

```

## dashboard.blade.php

```blade
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.dashboard') }} - {{ config('app.name') }}</title>

    <style>
        /* Design System Variables */
        :root {
            --brand-primary: #F1620F;
            --brand-primary-hover: #D7530A;
            --brand-dark: #0B1A34;
            --brand-dark-light: #14284D;
            --bg-canvas: #F8FAFC;
            --bg-surface: #FFFFFF;
            --text-primary: #0B1A34;
            --text-secondary: #475569;
            --border-color: #E2E8F0;
            --transition-smooth: all 0.2s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-canvas);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* Clean Dashboard Core Panel Card */
        .dashboard-container {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(11, 26, 52, 0.05), 0 8px 10px -6px rgba(11, 26, 52, 0.05);
            max-width: 540px;
            width: 100%;
            padding: 2.25rem 2rem;
        }

        /* Header Presentation Elements */
        .dashboard-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .dashboard-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--brand-dark);
            margin-bottom: 0.35rem;
        }

        .welcome-message {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .user-highlight {
            color: var(--brand-dark);
            font-weight: 700;
        }

        /* Core Navigation Stack Panel */
        .action-workspace-panel {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        /* Button & Hyperlink Framework Normatives */
        .nav-action-btn {
            width: 100%;
            height: 46px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: var(--transition-smooth);
        }

        .btn-link-public {
            background-color: var(--bg-canvas);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-link-public:hover {
            background-color: #EDF2F7;
            border-color: #CBD5E1;
        }

        .btn-logout-trigger {
            background-color: #FEF2F2;
            border: 1px solid #FEE2E2;
            color: #DC2626;
        }

        .btn-logout-trigger:hover {
            background-color: #FEE2E2;
            border-color: #FCA5A5;
        }

        /* Multi-directional Alignment Tuning fixes */
        html[dir="rtl"] .dashboard-header {
            text-align: right;
        }
        html[dir="ltr"] .dashboard-header {
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        {{-- Structural Header Component Block --}}
        <header class="dashboard-header">
            <h1 class="dashboard-title">{{ __('messages.dashboard') }}</h1>
            <p class="welcome-message">
                {{ __('messages.welcome') }}, <span class="user-highlight">{{ auth()->user()->name }}</span>
            </p>
        </header>

        {{-- Interactive Operations Panel Matrix --}}
        <main class="action-workspace-panel">
            <a href="{{ route('home') }}" class="nav-action-btn btn-link-public">
                <span>{{ __('messages.public.home') }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
                @csrf
                <button type="submit" class="nav-action-btn btn-logout-trigger">
                    <span>{{ __('messages.logout') }}</span>
                </button>
            </form>
        </main>
    </div>

</body>
</html>

```

## emails\password-reset.blade.php

```blade
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.password_reset_subject') }}</title>
</head>

<body style="margin:0; padding:0; background:#f7f7f7; font-family:Arial, Helvetica, sans-serif; color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f7f7f7; padding:24px 12px;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; background:#ffffff; border-radius:8px; overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background:#003366; padding:36px 20px;">
                            <div style="font-size:26px; font-weight:bold; color:#F1620F; margin-bottom:8px;">
                                دلني
                            </div>
                            <div style="font-size:14px; color:#ffffff;">
                                {{ __('auth.password_reset_subject') }}
                            </div>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:36px 28px; text-align:right;">

                            <h2 style="margin:0 0 18px; font-size:20px; color:#003366;">
                                {{ __('messages.hello', ['name' => $userName]) }}
                            </h2>

                            <p style="margin:0 0 24px; font-size:15px; line-height:1.8; color:#555;">
                                {{ __('messages.reset_password_message') }}
                            </p>

                            <div style="text-align:center; margin:32px 0;">
                                <a href="{{ $resetLink }}"
                                   style="display:inline-block; background:#F1620F; color:#ffffff; text-decoration:none; padding:14px 34px; border-radius:6px; font-size:16px; font-weight:bold;">
                                    {{ __('auth.reset_password_button') }}
                                </a>
                            </div>

                            <p style="margin:0 0 18px; font-size:14px; line-height:1.7; color:#666;">
                                {{ __('messages.reset_link_expires') }}
                            </p>

                            <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:5px; padding:14px; margin:22px 0; color:#856404; font-size:13px; line-height:1.7;">
                                <strong>{{ __('messages.security_warning') }}</strong><br>
                                {{ __('messages.reset_link_warning') }}
                            </div>

                            <p style="margin:22px 0 10px; font-size:13px; color:#666;">
                                {{ __('messages.reset_link_copy') }}
                            </p>

                            <div dir="ltr" style="background:#f9f9f9; border-radius:5px; padding:14px; font-size:12px; color:#777; word-break:break-all; text-align:left; font-family:Courier New, monospace;">
                                {{ $resetLink }}
                            </div>

                            <hr style="border:none; border-top:1px solid #eee; margin:28px 0;">

                            <p style="margin:0; font-size:13px; line-height:1.7; color:#666;">
                                {{ __('messages.reset_link_not_requested') }}
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background:#f9f9f9; border-top:1px solid #eee; padding:26px 20px; font-size:12px; color:#999;">

                            <p style="margin:0 0 10px;">
                                {{ __('messages.email_footer_text') }}
                            </p>

                            <p style="margin:10px 0; font-size:11px;">
                                <a href="{{ config('app.url') }}" style="color:#F1620F; text-decoration:none;">
                                    {{ config('app.name') }}
                                </a>
                                &nbsp;•&nbsp;
                                <a href="{{ url('/privacy') }}" style="color:#F1620F; text-decoration:none;">
                                    {{ __('messages.privacy_policy') }}
                                </a>
                                &nbsp;•&nbsp;
                                <a href="{{ url('/terms') }}" style="color:#F1620F; text-decoration:none;">
                                    {{ __('messages.terms_of_service') }}
                                </a>
                            </p>

                            <p style="margin:0; font-size:11px; color:#bbb;">
                                © {{ date('Y') }} {{ config('app.name') }}. {{ __('messages.all_rights_reserved') }}
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>

```

## emails\set-password.blade.php

```blade
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.set_password_subject') }}</title>
</head>

<body style="margin:0; padding:0; background:#f7f7f7; font-family:Arial, Helvetica, sans-serif; color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f7f7f7; padding:24px 12px;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; background:#ffffff; border-radius:8px; overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background:#003366; padding:36px 20px;">
                            <div style="font-size:26px; font-weight:bold; color:#F1620F; margin-bottom:8px;">
                                دلني
                            </div>
                            <div style="font-size:14px; color:#ffffff;">
                                {{ __('auth.set_password_subject') }}
                            </div>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:36px 28px; text-align:right;">

                            <h2 style="margin:0 0 18px; font-size:20px; color:#003366;">
                                {{ __('messages.hello', ['name' => $userName]) }}
                            </h2>

                            <p style="margin:0 0 24px; font-size:15px; line-height:1.8; color:#555;">
                                {{ __('messages.set_password_message') }}
                            </p>

                            <div style="text-align:center; margin:32px 0;">
                                <a href="{{ $setPasswordLink }}"
                                   style="display:inline-block; background:#F1620F; color:#ffffff; text-decoration:none; padding:14px 34px; border-radius:6px; font-size:16px; font-weight:bold;">
                                    {{ __('auth.set_password_button') }}
                                </a>
                            </div>

                            <p style="margin:0 0 18px; font-size:14px; line-height:1.7; color:#666;">
                                {{ __('messages.set_password_link_expires') }}
                            </p>

                            <div style="background:#e7f3ff; border:1px solid #b3d9ff; border-radius:5px; padding:14px; margin:22px 0; color:#0c5394; font-size:13px; line-height:1.7;">
                                <strong>{{ __('messages.set_password_info') }}</strong><br>
                                {{ __('messages.set_password_info_desc') }}
                            </div>

                            <p style="margin:22px 0 10px; font-size:13px; color:#666;">
                                {{ __('messages.set_password_link_copy') }}
                            </p>

                            <div dir="ltr" style="background:#f9f9f9; border-radius:5px; padding:14px; font-size:12px; color:#777; word-break:break-all; text-align:left; font-family:Courier New, monospace;">
                                {{ $setPasswordLink }}
                            </div>

                            <hr style="border:none; border-top:1px solid #eee; margin:28px 0;">

                            <p style="margin:0; font-size:13px; line-height:1.7; color:#666;">
                                {{ __('messages.set_password_not_requested') }}
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background:#f9f9f9; border-top:1px solid #eee; padding:26px 20px; font-size:12px; color:#999;">

                            <p style="margin:0 0 10px;">
                                {{ __('messages.email_footer_text') }}
                            </p>

                            <p style="margin:10px 0; font-size:11px;">
                                <a href="{{ config('app.url') }}" style="color:#F1620F; text-decoration:none;">
                                    {{ config('app.name') }}
                                </a>
                                &nbsp;•&nbsp;
                                <a href="{{ url('/privacy') }}" style="color:#F1620F; text-decoration:none;">
                                    {{ __('messages.privacy_policy') }}
                                </a>
                                &nbsp;•&nbsp;
                                <a href="{{ url('/terms') }}" style="color:#F1620F; text-decoration:none;">
                                    {{ __('messages.terms_of_service') }}
                                </a>
                            </p>

                            <p style="margin:0; font-size:11px; color:#bbb;">
                                © {{ date('Y') }} {{ config('app.name') }}. {{ __('messages.all_rights_reserved') }}
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>

```

## errors\403.blade.php

```blade
@extends('public.layout')

@section('title', __('messages.public.error_403_title') . ' - ' . config('app.name'))

@section('content')
<section style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px); padding: 2rem 1rem;">
    <style>
        .error-layout {
            max-width: 500px;
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #E7E7E7;
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
            text-align: center;
        }

        .error-code {
            font-size: clamp(2.5rem, 10vw, 3.5rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .error-title {
            font-size: clamp(1.5rem, 3vw, 1.8rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .error-message {
            font-size: 0.95rem;
            color: #5D5959;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .error-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .error-actions a.primary {
            background: #F1620F;
            color: white;
        }

        .error-actions a.primary:hover {
            background: #D9550C;
        }

        .error-actions a.secondary {
            background: transparent;
            border: 1px solid #E7E7E7;
            color: #0B1A34;
        }

        .error-actions a.secondary:hover {
            background: #FCFBFB;
            border-color: #F1620F;
            color: #F1620F;
        }

        .error-info {
            padding-top: 1.5rem;
            border-top: 1px solid #E7E7E7;
            background: #FCFBFB;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .error-info-text {
            font-size: 0.9rem;
            color: #5D5959;
            line-height: 1.6;
        }

        .error-info-text strong {
            color: #0B1A34;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            .error-layout {
                padding: 1.25rem;
            }

            .error-code {
                font-size: 2.2rem;
                margin-bottom: 0.4rem;
            }

            .error-title {
                font-size: 1.4rem;
                margin-bottom: 0.75rem;
            }

            .error-message {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .error-actions {
                gap: 0.5rem;
                margin-bottom: 1.25rem;
            }

            .error-actions a {
                padding: 0.65rem 1.25rem;
                font-size: 0.9rem;
            }

            .error-info {
                padding: 0.75rem;
                margin-top: 0.75rem;
            }

            .error-info-text {
                font-size: 0.85rem;
            }
        }
    </style>

    <div class="error-layout">
        <h1 class="error-code">403</h1>
        <h2 class="error-title">{{ __('messages.public.error_403_title') }}</h2>
        <p class="error-message">{{ __('messages.public.error_403_message') }}</p>

        <div class="error-actions">
            <a href="{{ route('home') }}" class="primary">{{ __('messages.public.back_home') }}</a>
            <a href="{{ route('public.search') }}" class="secondary">{{ __('messages.public.search') }}</a>
        </div>

        <div class="error-info">
            <p class="error-info-text">
                <strong>{{ __('messages.public.note') }}:</strong> {{ __('messages.public.error_403_info') }}
            </p>
        </div>
    </div>

    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #E7E7E7;">
        <div class="provider-cta-card" style="background: #FCFBFB; border: 1px solid #E7E7E7; border-radius: 16px; padding: 2rem; text-align: center; max-width: 500px; margin: 0 auto;">
            <h3 style="font-size: 1.3rem; font-weight: 900; color: #0B1A34; margin-bottom: 0.75rem; letter-spacing: -0.02em;">
                {{ __('messages.public.are_you_professional') }}
            </h3>
            <p style="font-size: 0.95rem; color: #5D5959; margin-bottom: 1.5rem; line-height: 1.6;">
                {{ __('messages.public.join_marketplace_description') }}
            </p>
            <a href="{{ route('contact') }}" style="display: inline-block; background: #F1620F; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 700; transition: background 0.2s ease;">
                {{ __('messages.public.contact_us') }}
            </a>
        </div>
    </div>
</section>
@endsection

```

## errors\404.blade.php

```blade
@extends('public.layout')

@section('title', __('messages.public.error_404_title') . ' - ' . config('app.name'))

@section('content')
<section style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px); padding: 2rem 1rem;">
    <style>
        .error-layout {
            max-width: 500px;
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #E7E7E7;
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
            text-align: center;
        }

        .error-code {
            font-size: clamp(2.5rem, 10vw, 3.5rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .error-title {
            font-size: clamp(1.5rem, 3vw, 1.8rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .error-message {
            font-size: 0.95rem;
            color: #5D5959;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .error-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .error-actions a.primary {
            background: #F1620F;
            color: white;
        }

        .error-actions a.primary:hover {
            background: #D9550C;
        }

        .error-actions a.secondary {
            background: transparent;
            border: 1px solid #E7E7E7;
            color: #0B1A34;
        }

        .error-actions a.secondary:hover {
            background: #FCFBFB;
            border-color: #F1620F;
            color: #F1620F;
        }

        .error-info {
            padding-top: 1.5rem;
            border-top: 1px solid #E7E7E7;
        }

        .error-info-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0B1A34;
            margin-bottom: 0.75rem;
        }

        .error-info-list {
            list-style: none;
            font-size: 0.9rem;
            color: #5D5959;
            font-weight: 500;
            display: inline-block;
            text-align: center;
        }

        .error-info-list li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        @media (max-width: 640px) {
            .error-layout {
                padding: 1.25rem;
            }

            .error-code {
                font-size: 2.2rem;
                margin-bottom: 0.4rem;
            }

            .error-title {
                font-size: 1.4rem;
                margin-bottom: 0.75rem;
            }

            .error-message {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .error-actions {
                gap: 0.5rem;
                margin-bottom: 1.25rem;
            }

            .error-actions a {
                padding: 0.65rem 1.25rem;
                font-size: 0.9rem;
            }

            .error-info {
                padding-top: 1.25rem;
            }

            .error-info-title {
                font-size: 0.8rem;
                margin-bottom: 0.6rem;
            }

            .error-info-list {
                font-size: 0.85rem;
            }

            .error-info-list li {
                margin-bottom: 0.4rem;
            }
        }
    </style>

    <div class="error-layout">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">{{ __('messages.public.error_404_title') }}</h2>
        <p class="error-message">{{ __('messages.public.error_404_message') }}</p>

        <div class="error-actions">
            <a href="{{ route('home') }}" class="primary">{{ __('messages.public.back_home') }}</a>
            <a href="{{ route('public.search') }}" class="secondary">{{ __('messages.public.search') }}</a>
        </div>

        <div class="error-info">
            <p class="error-info-title">{{ __('messages.public.suggestions') }}</p>
            <ul class="error-info-list">
                <li>{{ __('messages.public.error_404_suggestion_1') }}</li>
                <li>{{ __('messages.public.error_404_suggestion_2') }}</li>
                <li>{{ __('messages.public.error_404_suggestion_3') }}</li>
            </ul>
        </div>
    </div>

    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #E7E7E7;">
        <div class="provider-cta-card" style="background: #FCFBFB; border: 1px solid #E7E7E7; border-radius: 16px; padding: 2rem; text-align: center; max-width: 500px; margin: 0 auto;">
            <h3 style="font-size: 1.3rem; font-weight: 900; color: #0B1A34; margin-bottom: 0.75rem; letter-spacing: -0.02em;">
                {{ __('messages.public.are_you_professional') }}
            </h3>
            <p style="font-size: 0.95rem; color: #5D5959; margin-bottom: 1.5rem; line-height: 1.6;">
                {{ __('messages.public.join_marketplace_description') }}
            </p>
            <a href="{{ route('contact') }}" style="display: inline-block; background: #F1620F; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 700; transition: background 0.2s ease;">
                {{ __('messages.public.contact_us') }}
            </a>
        </div>
    </div>
</section>
@endsection

```

## errors\500.blade.php

```blade
﻿@extends('public.layout')

@section('title', __('messages.public.error_500_title', ['default' => 'Server Error']) . ' - ' . config('app.name'))

@section('content')
<section style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px); padding: 2rem 1rem;">
    <style>
        .error-layout {
            max-width: 500px;
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #E7E7E7;
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
            text-align: center;
        }

        .error-code {
            font-size: clamp(2.5rem, 10vw, 3.5rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .error-title {
            font-size: clamp(1.5rem, 3vw, 1.8rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .error-message {
            font-size: 0.95rem;
            color: #5D5959;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .error-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .error-actions a.primary {
            background: #F1620F;
            color: white;
        }

        .error-actions a.primary:hover {
            background: #D9550C;
        }

        .error-actions a.secondary {
            background: transparent;
            border: 1px solid #E7E7E7;
            color: #0B1A34;
        }

        .error-actions a.secondary:hover {
            background: #FCFBFB;
            border-color: #F1620F;
            color: #F1620F;
        }

        .error-info {
            padding: 1rem;
            border-radius: 8px;
            background: #FCFBFB;
            border-top: 1px solid #E7E7E7;
            margin-top: 1rem;
        }

        .error-info-heading {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0B1A34;
            margin-bottom: 0.5rem;
        }

        .error-info-text {
            font-size: 0.85rem;
            color: #5D5959;
            line-height: 1.6;
            font-weight: 500;
        }

        @media (max-width: 640px) {
            .error-layout {
                padding: 1.25rem;
            }

            .error-code {
                font-size: 2.2rem;
                margin-bottom: 0.4rem;
            }

            .error-title {
                font-size: 1.4rem;
                margin-bottom: 0.75rem;
            }

            .error-message {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .error-actions {
                gap: 0.5rem;
                margin-bottom: 1.25rem;
            }

            .error-actions a {
                padding: 0.65rem 1.25rem;
                font-size: 0.9rem;
            }

            .error-info {
                padding: 0.75rem;
                margin-top: 0.75rem;
            }

            .error-info-heading {
                font-size: 0.8rem;
                margin-bottom: 0.4rem;
            }

            .error-info-text {
                font-size: 0.8rem;
            }
        }
    </style>

    <div class="error-layout">
        <h1 class="error-code">500</h1>
        <h2 class="error-title">{{ __('messages.public.error_500_title', ['default' => 'Server Error']) }}</h2>
        <p class="error-message">{{ __('messages.public.error_500_message', ['default' => 'حدث خطأ في الخادم. يرجى المحاولة لاحقًا.']) }}</p>

        <div class="error-actions">
            <a href="{{ route('home') }}" class="primary">{{ __('messages.public.back_home') }}</a>
            @if (Route::has('contact'))
                <a href="{{ route('contact') }}" class="secondary">{{ __('messages.public.contact_support') }}</a>
            @endif
        </div>

        <div class="error-info">
            <div class="error-info-heading">{{ __('messages.public.error_500_code', ['default' => 'رمز الخطأ']) }}: 500</div>
            <div class="error-info-text">{{ __('messages.public.error_please_try_later', ['default' => 'يرجى محاولة الوصول مرة أخرى بعد قليل.']) }}</div>
        </div>
    </div>

    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #E7E7E7;">
        <div class="provider-cta-card" style="background: #FCFBFB; border: 1px solid #E7E7E7; border-radius: 16px; padding: 2rem; text-align: center; max-width: 500px; margin: 0 auto;">
            <h3 style="font-size: 1.3rem; font-weight: 900; color: #0B1A34; margin-bottom: 0.75rem; letter-spacing: -0.02em;">
                {{ __('messages.public.are_you_professional') }}
            </h3>
            <p style="font-size: 0.95rem; color: #5D5959; margin-bottom: 1.5rem; line-height: 1.6;">
                {{ __('messages.public.join_marketplace_description') }}
            </p>
            <a href="{{ route('contact') }}" style="display: inline-block; background: #F1620F; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 700; transition: background 0.2s ease;">
                {{ __('messages.public.contact_us') }}
            </a>
        </div>
    </div>
</section>
@endsection

```

## errors\503.blade.php

```blade
﻿@extends('public.layout')

@section('title', __('messages.public.error_503_title', ['default' => 'Service Unavailable']) . ' - ' . config('app.name'))

@section('content')
<section style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px); padding: 2rem 1rem;">
    <style>
        .error-layout {
            max-width: 500px;
            width: 100%;
            background: #FFFFFF;
            border: 1px solid #E7E7E7;
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
            text-align: center;
        }

        .error-code {
            font-size: clamp(2.5rem, 10vw, 3.5rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .error-title {
            font-size: clamp(1.5rem, 3vw, 1.8rem);
            font-weight: 900;
            color: #0B1A34;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .error-message {
            font-size: 0.95rem;
            color: #5D5959;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .error-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .error-actions a.primary {
            background: #F1620F;
            color: white;
        }

        .error-actions a.primary:hover {
            background: #D9550C;
        }

        .error-info {
            padding: 1rem;
            border-radius: 8px;
            background: #FCFBFB;
            border-top: 1px solid #E7E7E7;
            margin-top: 1rem;
        }

        .error-info-heading {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0B1A34;
            margin-bottom: 0.5rem;
        }

        .error-info-text {
            font-size: 0.85rem;
            color: #5D5959;
            line-height: 1.6;
            font-weight: 500;
        }

        .error-info-cta {
            font-size: 0.85rem;
            color: #5D5959;
            line-height: 1.6;
            margin-top: 0.5rem;
        }

        @media (max-width: 640px) {
            .error-layout {
                padding: 1.25rem;
            }

            .error-code {
                font-size: 2.2rem;
                margin-bottom: 0.4rem;
            }

            .error-title {
                font-size: 1.4rem;
                margin-bottom: 0.75rem;
            }

            .error-message {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .error-actions {
                gap: 0.5rem;
                margin-bottom: 1.25rem;
            }

            .error-actions a {
                padding: 0.65rem 1.25rem;
                font-size: 0.9rem;
            }

            .error-info {
                padding: 0.75rem;
                margin-top: 0.75rem;
            }

            .error-info-heading {
                font-size: 0.8rem;
                margin-bottom: 0.4rem;
            }

            .error-info-text,
            .error-info-cta {
                font-size: 0.8rem;
            }
        }
    </style>

    <div class="error-layout">
        <h1 class="error-code">503</h1>
        <h2 class="error-title">{{ __('messages.public.error_503_title', ['default' => 'Service Unavailable']) }}</h2>
        <p class="error-message">{{ __('messages.public.error_503_message', ['default' => 'الخدمة غير متاحة حاليًا. نحن نعمل على إصلاح المشكلة.']) }}</p>

        <div class="error-actions">
            <a href="{{ route('home') }}" class="primary">{{ __('messages.public.back_home') }}</a>
        </div>

        <div class="error-info">
            <div class="error-info-heading">{{ __('messages.public.maintenance', ['default' => 'جاري الصيانة']) }}</div>
            <div class="error-info-text">{{ __('messages.public.maintenance_message', ['default' => 'نعتذر عن عدم توفر الخدمة. نعمل بجد لإعادة الخدمة قريبًا.']) }}</div>
            <div class="error-info-cta">⏱️ {{ __('messages.public.check_back_soon', ['default' => 'يرجى التحقق لاحقًا']) }}</div>
        </div>
    </div>

    <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #E7E7E7;">
        <div class="provider-cta-card" style="background: #FCFBFB; border: 1px solid #E7E7E7; border-radius: 16px; padding: 2rem; text-align: center; max-width: 500px; margin: 0 auto;">
            <h3 style="font-size: 1.3rem; font-weight: 900; color: #0B1A34; margin-bottom: 0.75rem; letter-spacing: -0.02em;">
                {{ __('messages.public.are_you_professional') }}
            </h3>
            <p style="font-size: 0.95rem; color: #5D5959; margin-bottom: 1.5rem; line-height: 1.6;">
                {{ __('messages.public.join_marketplace_description') }}
            </p>
            <a href="{{ route('contact') }}" style="display: inline-block; background: #F1620F; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 700; transition: background 0.2s ease;">
                {{ __('messages.public.contact_us') }}
            </a>
        </div>
    </div>
</section>
@endsection

```

## errors\panel.blade.php

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #F1620F;
            --navy: #0B1A34;
            --bg: #FCFBFB;
            --surface: #FFFFFF;
            --border: #E7E7E7;
            --muted: #5D5959;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);
            color: var(--navy);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        .error-container {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
            text-align: center;
        }

        .error-code {
            font-size: clamp(2.5rem, 10vw, 3.5rem);
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .error-title {
            font-size: clamp(1.5rem, 3vw, 1.8rem);
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .error-message {
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .back-button {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 700;
            transition: background 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .back-button:hover {
            background: #D9550C;
        }

        .debug-info {
            text-align: left;
            background: #FCFBFB;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            border-top: 1px solid var(--border);
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 0.8rem;
            color: var(--muted);
            overflow-x: auto;
            line-height: 1.5;
        }

        .debug-info strong {
            color: var(--navy);
            font-weight: 700;
        }

        @media (max-width: 640px) {
            .error-container {
                padding: 1.25rem;
            }

            .error-code {
                font-size: 2.2rem;
                margin-bottom: 0.4rem;
            }

            .error-title {
                font-size: 1.4rem;
                margin-bottom: 0.75rem;
            }

            .error-message {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .back-button {
                padding: 0.65rem 1.25rem;
                font-size: 0.9rem;
            }

            .debug-info {
                padding: 0.75rem;
                margin-top: 1.25rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">⚠️</div>
        <div class="error-title">An Error Occurred</div>
        <div class="error-message">
            We encountered an issue processing your request.<br>
            Please try again or contact support if the problem persists.
        </div>
        @if(config('app.debug') && isset($exception))
            <div class="debug-info">
                <strong>{{ get_class($exception) }}:</strong> {{ $exception->getMessage() }}
                @if($exception->getFile())
                    <br><br><strong>File:</strong> {{ $exception->getFile() }}:{{ $exception->getLine() }}
                @endif
            </div>
        @endif
        <button class="back-button" onclick="history.back()">Go Back</button>
    </div>
</body>
</html>

```

## filament\brand.blade.php

```blade
<div
    class="fi-logo flex items-center gap-2 text-decoration-none"
>
    <img
        src="{{ asset('images/logo.jpg') }}"
        alt="دلني"
        class="fi-logo-image"
    >

    <span class="fi-logo-text">
        دلني
    </span>
</div>

<style>
    .fi-logo {
        padding-inline: 0.5rem;
    }

    .fi-logo-image {
        width: 34px;
        height: 34px;
        object-fit: cover;
        border-radius: 9999px;
        flex-shrink: 0;
        display: block;
    }

    .fi-logo-text {
        font-size: 1.1rem;
        font-weight: 700;
        color: white;
        line-height: 1;
        white-space: nowrap;
    }
</style>

```

## layouts\auth.blade.php

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        /* Modern System Variables Definition Matrix */
        :root {
            --auth-primary: #F1620F;
            --auth-primary-2: #ff7a1a;
            --auth-navy: #0B1A34;
            --auth-navy-gradient: #0d2541;
            --auth-bg-card: rgba(255, 255, 255, 0.06);
            --auth-bg-card-hover: rgba(255, 255, 255, 0.09);

            /* High Contrast Accessibility Overrides */
            --auth-text: #FFFFFF;
            --auth-soft-text: rgba(255, 255, 255, 0.72);
            --auth-muted: rgba(255, 255, 255, 0.65);
            --auth-border-glass: rgba(255, 255, 255, 0.12);

            --auth-radius-sm: 12px;
            --auth-radius-md: 18px;
            --auth-radius-lg: 24px;
            --auth-shadow: 0 25px 50px -12px rgba(11, 26, 52, 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Cairo', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(241, 98, 15, 0.15), transparent 40%),
                radial-gradient(circle at bottom left, rgba(37, 99, 235, 0.1), transparent 40%),
                linear-gradient(135deg, var(--auth-navy), var(--auth-navy-gradient));
            background-attachment: fixed;
            color: var(--auth-text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Clean Page Flex Centering Container */
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 4vw, 2.5rem);
            position: relative;
        }

        .auth-shell {
            width: 100%;
            max-width: 440px; /* Enhanced baseline to handle wider content safely */
            position: relative;
            z-index: 10;
        }

        /* Premium Glassmorphic Card Container */
        .auth-card {
            padding: clamp(1.5rem, 5vw, 2.5rem);
            border: 1px solid var(--auth-border-glass);
            border-radius: var(--auth-radius-lg);
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            box-shadow: var(--auth-shadow), inset 0 1px 1px rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            width: 100%;
        }

        /* Brand Identity Node Layout */
        .auth-brand {
            display: flex;
            justify-content: center;
            margin-bottom: 1.75rem;
        }

        .auth-brand a {
            width: 68px;
            height: 68px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--auth-radius-md);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--auth-border-glass);
            overflow: hidden;
            transition: all 0.2s ease-in-out;
        }

        .auth-brand a:hover {
            transform: scale(1.04);
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .auth-brand img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Form Typography Headers */
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-eyebrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            background: rgba(241, 98, 15, 0.15);
            border: 1px solid rgba(241, 98, 15, 0.25);
            color: #FFD7B5;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .auth-title {
            font-size: clamp(1.5rem, 5vw, 2rem);
            line-height: 1.25;
            font-weight: 800;
            color: var(--auth-text);
        }

        .auth-title span {
            color: var(--auth-primary);
        }

        .auth-subtitle {
            margin-top: 0.5rem;
            color: var(--auth-muted);
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.6;
        }

        /* Shared Form Input Element Sub-components */
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .auth-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .auth-field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .auth-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .auth-label {
            color: var(--auth-soft-text);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .auth-help-link {
            color: var(--auth-primary);
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.15s ease-in-out;
        }

        .auth-help-link:hover {
            color: var(--auth-primary-2);
            text-decoration: underline;
        }

        /* Global Input Styling Configuration Framework */
        .auth-input {
            width: 100%;
            height: 50px;
            padding: 0 1rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--auth-radius-sm);
            background: rgba(255, 255, 255, 0.96);
            color: #0F172A;
            font: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        .auth-input::placeholder {
            color: #94A3B8;
        }

        .auth-input:focus {
            background: #FFFFFF;
            border-color: var(--auth-primary);
            box-shadow: 0 0 0 4px rgba(241, 98, 15, 0.2);
        }

        .auth-input.is-dark {
            background: rgba(255, 255, 255, 0.07);
            color: var(--auth-text);
            border-color: var(--auth-border-glass);
        }

        .auth-input.is-dark:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--auth-primary);
            box-shadow: 0 0 0 4px rgba(241, 98, 15, 0.15);
        }

        .auth-input.is-invalid {
            border-color: #EF4444;
            background: rgba(239, 68, 68, 0.05);
        }

        .auth-error-text {
            color: #FCA5A5;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        /* Notification and Inline Alert States */
        .auth-alert {
            display: flex;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: var(--auth-radius-sm);
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
        }

        .auth-alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.25);
            color: #FCA5A5;
        }

        .auth-alert-success {
            background: rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.25);
            color: #86EFAC;
        }

        /* Clean Primary Call To Action Button */
        .auth-submit {
            width: 100%;
            height: 52px;
            margin-top: 0.5rem;
            border: 0;
            border-radius: var(--auth-radius-sm);
            background: linear-gradient(135deg, var(--auth-primary), var(--auth-primary-2));
            color: #FFFFFF;
            font: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(241, 98, 15, 0.3);
            transition: all 0.2s ease-in-out;
        }

        .auth-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(241, 98, 15, 0.4);
        }

        .auth-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Divider with Text */
        .auth-divider {
            position: relative;
            display: flex;
            align-items: center;
            margin: 1.75rem 0;
            gap: 1rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.15);
        }

        .auth-divider span {
            color: var(--auth-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* OAuth Social Login Buttons */
        .auth-oauth-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            height: 50px;
            padding: 0 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--auth-radius-sm);
            background: rgba(255, 255, 255, 0.06);
            color: var(--auth-text);
            font: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .auth-oauth-button:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .auth-oauth-button:active {
            transform: translateY(0);
        }

        .auth-oauth-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .auth-oauth-google {
            color: #FFFFFF;
        }

        /* Responsive Breakpoint Adaptability Rules */
        .auth-footer {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            text-align: center;
        }

        .auth-footer p {
            color: var(--auth-muted);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .auth-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--auth-primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: color 0.15s ease-in-out;
        }

        .auth-link:hover {
            color: var(--auth-primary-2);
        }

        .auth-link svg {
            width: 16px;
            height: 16px;
            transition: transform 0.2s ease;
        }

        /* Bi-Directional Direction Handling Logic Rules */
        html[dir="ltr"] .auth-link:hover svg {
            transform: translateX(3px);
        }

        html[dir="rtl"] .auth-link svg {
            transform: scaleX(-1);
        }

        html[dir="rtl"] .auth-link:hover svg {
            transform: scaleX(-1) translateX(3px);
        }

        /* Responsive Breakpoint Adaptability Rules */
        @media (max-width: 480px) {
            .auth-page {
                padding: 1rem;
            }
            .auth-card {
                padding: 1.5rem 1.25rem;
                border-radius: var(--auth-radius-md);
            }
            .auth-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <main class="auth-page">
        <section class="auth-shell">
            <div class="auth-card">

                {{-- Dynamic Routing Framework Header Node --}}
                <div class="auth-brand">
                    <a href="@if(Route::has('home')){{ route('home') }}@else{{ url('/') }}@endif" aria-label="{{ config('app.name') }}">
                        <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('app.name') }}">
                    </a>
                </div>

                <header class="auth-header">
                    @hasSection('auth_eyebrow')
                        <div class="auth-eyebrow">@yield('auth_eyebrow')</div>
                    @endif

                    <h1 class="auth-title">@yield('auth_title')</h1>

                    @hasSection('auth_subtitle')
                        <p class="auth-subtitle">@yield('auth_subtitle')</p>
                    @endif
                </header>

                {{-- Dynamic Blade Rendering Context --}}
                @yield('content')

            </div>
        </section>
    </main>
</body>
</html>

```

## onboarding-link.blade.php

```blade
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رابط الإعداد والتفعيل</title>
    {{-- High-quality typography addition --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        /* Design Tokens & Variables */
        :root {
            --brand-primary: #F1620F;
            --brand-primary-hover: #D7530A;
            --brand-dark: #0B1A34;
            --brand-dark-light: #14284D;
            --bg-surface: #FFFFFF;
            --bg-subtle: #F8FAFC;
            --text-primary: #0B1A34;
            --text-secondary: #475569;
            --border-color: #E2E8F0;
            --transition-smooth: all 0.2s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand-dark-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .container {
            background: var(--bg-surface);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            max-width: 580px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        h1 {
            color: var(--brand-dark);
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
            font-weight: 800;
        }

        .subtitle {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Clean Link Display Box */
        .link-box {
            background: var(--bg-subtle);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: var(--text-primary);
            line-height: 1.6;
            direction: ltr; /* Keeps URL slashes and tokens properly ordered */
            text-align: left;
        }

        /* Action Controls Layout Matrix */
        .actions-wrapper {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin: 1.5rem 0 2rem;
        }

        .btn {
            width: 100%;
            height: 50px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            border: none;
            transition: var(--transition-smooth);
        }

        .btn-primary {
            background-color: var(--brand-primary);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(241, 98, 15, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--brand-primary-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: var(--bg-subtle);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background-color: #EDF2F7;
            border-color: #CBD5E1;
        }

        /* Interactive Success Status Layer */
        .success-banner {
            display: none;
            background: #DEF7EC;
            color: #03543F;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-top: -0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            font-weight: 700;
            border: 1px solid #BCF0DA;
            animation: fadeIn 0.2s ease-out;
        }

        /* RTL-Correct Information Alert Box */
        .info-alert {
            background: rgba(241, 98, 15, 0.05);
            border-right: 4px solid var(--brand-primary);
            border-left: none; /* Corrects standard default left-border frameworks */
            padding: 1rem 1.25rem;
            text-align: right;
            border-radius: 4px 12px 12px 4px;
            color: #A73F05;
            font-size: 0.85rem;
            font-weight: 500;
            line-height: 1.6;
        }

        .footer-note {
            margin-top: 2rem;
            color: var(--text-light-muted);
            font-size: 0.8rem;
            font-weight: 500;
            line-height: 1.5;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .container {
                padding: 2rem 1.25rem;
            }
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>🔐 رابط الإعداد والتفعيل</h1>
        <p class="subtitle">قم بالضغط مباشرة على زر التفعيل المرفق بالأسفل، أو يمكنك نسخ الرابط المباشر واستخدامه في المتصفح الخاص بك.</p>

        {{-- Interactive URL text terminal node --}}
        <div class="link-box" id="linkBox">{{ $onboardingUrl }}</div>

        <div class="success-banner" id="successBanner">✓ تم نسخ رابط التفعيل بنجاح!</div>

        <div class="actions-wrapper">
            <a href="{{ $onboardingUrl }}" class="btn btn-primary">
                <span>إكمال عملية الإعداد والبدء</span>
                <span>←</span>
            </a>

            <button class="btn btn-secondary" onclick="copyToClipboard()">
                <span>📋 نسخ الرابط المباشر</span>
            </button>
        </div>

        <div class="info-alert">
            ⏰ <strong>تنبيه هام:</strong> هذا الرابط مخصص للاستخدام مرة واحدة وصالح لفترة زمنية محدودة فقط. يرجى إتمام عملية الإعداد قبل انتهاء صلاحية الجلسة.
        </div>

        <p class="footer-note">
            إذا لم يعمل زر الانتقال المباشر، يرجى نسخ عنوان الرابط ولصقه يدوياً في شريط العنوان أعلى متصفح الويب الخاص بك.
        </p>
    </div>

    <script>
        function copyToClipboard() {
            const text = document.getElementById('linkBox').innerText.trim();
            navigator.clipboard.writeText(text).then(() => {
                const banner = document.getElementById('successBanner');
                banner.style.display = 'block';
                setTimeout(() => {
                    banner.style.display = 'none';
                }, 3500);
            }).catch(() => {
                alert('عذراً، فشل النسخ التلقائي. يرجى تظليل الرابط ونسخه يدوياً.');
            });
        }
    </script>
</body>
</html>

```

## public\categories.blade.php

```blade
@extends('public.layout')

@section('title', __('messages.public.all_categories') . ' - ' . config('app.name'))

@section('content')
{{-- Compact Unified Page Header --}}
<section class="all-categories-hero">
    <div class="container">
        <div class="categories-hero-content">
            <h1>جميع الفئات</h1>
            <p>استكشف الفئات المتاحة على منبر دلني الموثوق للخدمات</p>
        </div>
    </div>
</section>

{{-- Main Directory Body Interface --}}
<section class="all-categories-section">
    <div class="container">
        <div class="categories-layout-grid">
            @forelse($categories as $category)
                <div class="category-panel-card">
                    {{-- Fixed Layout Header Area --}}
                    <div class="panel-main-interactive">
                        <div class="panel-identity-block">
                            <div class="category-icon-circle">
                                <x-svg-icon :icon="$category->getRelation('icon')" />
                            </div>
                            <div class="category-header-text">
                                <h2 class="category-title">{{ $category->localized_name ?? $category->name }}</h2>
                                <span class="category-provider-count">
                                    {{ $category->discoverable_profiles_count ?? 0 }} مزود خدمة
                                </span>
                            </div>
                        </div>

                        {{-- Action Controls: Native View Profile Route or Expand Children --}}
                        <div class="panel-action-controls">
                            @if($category->subcategories->isNotEmpty())
                                <button type="button"
                                        class="btn-trigger-drawer"
                                        data-category-id="drawer-{{ $category->id }}"
                                        aria-label="عرض الفئات الفرعية">
                                    <span>الفئات الفرعية</span>
                                    <x-render-icon icon="heroicon-o-chevron-left" class="icon-indicator" />
                                </button>
                            @else
                                <a href="{{ route('public.category', $category->slug) }}" class="btn-panel-link">
                                    <span>تصفح الكل</span>
                                    <x-render-icon icon="heroicon-o-arrow-left" />
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Scalable Subcategories Drawer Element --}}
                    @if($category->subcategories->isNotEmpty())
                        <div id="drawer-{{ $category->id }}" class="subcategories-panel-drawer">
                            <div class="drawer-inner-scroller">
                                <div class="mobile-drawer-header">
                                    <h3>{{ $category->localized_name ?? $category->name }}</h3>
                                    <button type="button" class="btn-close-drawer" data-close="drawer-{{ $category->id }}">✕</button>
                                </div>

                                <div class="subcategories-flex-list">
                                    {{-- Global fallback choice to view entire parent framework content safely --}}
                                    <a href="{{ route('public.category', $category->slug) }}" class="subcategory-link-item highlight-all">
                                        <span class="sub-name">عرض كافة خدمات الفئة الرئيسية ←</span>
                                    </a>

                                    @foreach($category->subcategories as $subcategory)
                                        <a href="{{ route('public.subcategory', $subcategory->slug) }}" class="subcategory-link-item">
                                            <div class="sub-meta-info">
                                                <span class="sub-name">{{ $subcategory->localized_name ?? $subcategory->name }}</span>
                                                <span class="sub-count">{{ $subcategory->discoverable_profiles_count ?? 0 }} مزود</span>
                                            </div>
                                            <x-render-icon icon="heroicon-o-chevron-left" class="sub-arrow" />
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state-card">
                    <x-render-icon icon="heroicon-o-folder-open" class="empty-icon" />
                    <p>لا توجد فئات متاحة حالياً على المنصة.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Provider CTA Section --}}
<section class="provider-cta-section">
    <div class="container">
        <div class="provider-cta-card">
            <h2 class="cta-title">{{ __('messages.public.are_you_professional') }}</h2>
            <p class="cta-description">{{ __('messages.public.join_marketplace_description') }}</p>
            <a href="{{ route('contact') }}" class="cta-button">{{ __('messages.public.contact_us') }}</a>
        </div>
    </div>
</section>

{{-- Global Overlay Shade Element for Side Drawers --}}
<div class="layout-drawer-backdrop" id="drawerBackdrop"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const triggers = document.querySelectorAll('.btn-trigger-drawer');
        const closeButtons = document.querySelectorAll('.btn-close-drawer');
        const backdrop = document.getElementById('drawerBackdrop');

        // Drawer toggle mechanism
        triggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const drawerId = trigger.getAttribute('data-category-id');
                const targetDrawer = document.getElementById(drawerId);

                if (targetDrawer) {
                    targetDrawer.classList.add('is-active');
                    backdrop.classList.add('is-active');
                    document.body.style.overflow = 'hidden'; // Block background viewport scrolls
                }
            });
        });

        // Close functions handler
        const closeAllDrawers = () => {
            document.querySelectorAll('.subcategories-panel-drawer').forEach(d => d.classList.remove('is-active'));
            backdrop.classList.remove('is-active');
            document.body.style.overflow = '';
        };

        closeButtons.forEach(btn => btn.addEventListener('click', closeAllDrawers));
        backdrop.addEventListener('click', closeAllDrawers);
    });
</script>

<style>
    :root {
        --brand-orange: #F1620F;
        --brand-orange-hover: #D7530A;
        --dark-blue: #0B1A34;
        --border-gray: #EAEAEA;
        --bg-light: #FAFAFA;
        --transition-standard: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Minimal Uniform Header Design */
    .all-categories-hero {
        background: linear-gradient(135deg, rgba(11, 26, 52, 0.95), rgba(20, 40, 77, 0.98)),
                    url('{{ asset('images/herobackground2.png') }}') center/cover no-repeat;
        padding: 3.5rem 0;
        color: #FFFFFF;
        text-align: center;
    }

    .categories-hero-content h1 {
        font-size: clamp(2rem, 4vw, 2.75rem);
        font-weight: 800;
        margin: 0 0 0.5rem;
    }

    .categories-hero-content p {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.75);
        margin: 0;
    }

    /* Professional Grid Structure */
    .all-categories-section {
        padding: 3rem 0;
        background: var(--bg-light);
    }

    .categories-layout-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.25rem;
    }

    /* Perfectly Symmetrical Visual Anchors */
    .category-panel-card {
        background: #FFFFFF;
        border: 1px solid var(--border-gray);
        border-radius: 18px;
        padding: 1.25rem;
        box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
        transition: var(--transition-standard);
    }

    .category-panel-card:hover {
        border-color: rgba(241, 98, 15, 0.3);
        box-shadow: 0 10px 25px rgba(11, 26, 52, 0.06);
    }

    .panel-main-interactive {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        gap: 1.25rem;
    }

    .panel-identity-block {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .category-icon-circle {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: rgba(241, 98, 15, 0.06);
        color: var(--brand-orange);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .category-icon-circle svg {
        width: 24px;
        height: 24px;
    }

    .category-header-text {
        overflow: hidden;
    }

    .category-title {
        margin: 0;
        color: var(--dark-blue);
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .category-provider-count {
        display: block;
        margin-top: 0.15rem;
        color: #64748B;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* Interactive Clean Action Triggers */
    .panel-action-controls {
        border-top: 1px solid var(--border-gray);
        padding-top: 0.85rem;
    }

    .btn-trigger-drawer, .btn-panel-link {
        width: 100%;
        height: 40px;
        border-radius: 10px;
        border: 1px solid var(--border-gray);
        background: #FFFFFF;
        color: var(--dark-blue);
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1rem;
        text-decoration: none;
        transition: var(--transition-standard);
    }

    .btn-trigger-drawer:hover, .btn-panel-link:hover {
        border-color: var(--brand-orange);
        color: var(--brand-orange);
        background: rgba(241, 98, 15, 0.02);
    }

    .btn-trigger-drawer svg, .btn-panel-link svg {
        width: 16px;
        height: 16px;
        transition: transform 0.2s ease;
    }

    .btn-trigger-drawer:hover .icon-indicator {
        transform: translateX(-4px); /* Moves arrow inline with Arabic layout flow */
    }

    /* Scalable Slide-Out Navigation Drawer Engine (Desktop) */
    .subcategories-panel-drawer {
        position: fixed;
        top: 0;
        left: -420px; /* Hidden off-canvas by default */
        width: 400px;
        height: 100vh;
        background: #FFFFFF;
        box-shadow: 25px 0 50px -12px rgba(11, 26, 52, 0.25);
        z-index: 1100;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Flip properties natively for Arabic RTL alignment */
    [dir="rtl"] .subcategories-panel-drawer {
        left: auto;
        right: -420px;
        box-shadow: -25px 0 50px -12px rgba(11, 26, 52, 0.25);
    }

    [dir="rtl"] .subcategories-panel-drawer.is-active {
        right: 0;
        left: auto;
    }

    .subcategories-panel-drawer.is-active {
        left: 0;
    }

    .drawer-inner-scroller {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 2rem 1.5rem;
    }

    .mobile-drawer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-gray);
        padding-bottom: 1rem;
    }

    .mobile-drawer-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--dark-blue);
    }

    .btn-close-drawer {
        background: var(--bg-light);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .subcategories-flex-list {
        flex: 1;
        overflow-y: auto;
        padding-right: 4px;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    /* Drawer Sub-Item Hyperlinks */
    .subcategory-link-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 1rem;
        background: var(--bg-light);
        border-radius: 10px;
        text-decoration: none;
        border: 1px solid transparent;
        transition: var(--transition-standard);
    }

    .subcategory-link-item:hover {
        background: #FFFFFF;
        border-color: var(--brand-orange);
    }

    .subcategory-link-item.highlight-all {
        background: rgba(241, 98, 15, 0.06);
        color: var(--brand-orange);
        font-weight: 700;
    }

    .sub-meta-info {
        display: flex;
        flex-direction: column;
    }

    .sub-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--dark-blue);
    }

    .subcategory-link-item:hover .sub-name {
        color: var(--brand-orange);
    }

    .sub-count {
        font-size: 0.75rem;
        color: #64748B;
        margin-top: 0.15rem;
    }

    .sub-arrow {
        width: 14px;
        height: 14px;
        color: #94A3B8;
    }

    /* Dim Backdrop Layer Overlay */
    .layout-drawer-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(11, 26, 52, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1050;
        display: none;
    }

    .layout-drawer-backdrop.is-active {
        display: block;
    }

    .empty-state-card {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem;
        background: #FFFFFF;
        border-radius: 18px;
        border: 1px dashed var(--border-gray);
    }

    /* Screen Adaptations: Responsive Transformation to Mobile Bottom Sheets */
    @media (max-width: 640px) {
        .subcategories-panel-drawer {
            width: 100% !important;
            height: 75vh !important;
            top: auto !important;
            bottom: -80vh !important;
            left: 0 !important;
            right: 0 !important;
            border-radius: 24px 24px 0 0;
            box-shadow: 0 -15px 30px rgba(0,0,0,0.15) !important;
            transition: bottom 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .subcategories-panel-drawer.is-active {
            bottom: 0 !important;
        }

        [dir="rtl"] .subcategories-panel-drawer,
        [dir="rtl"] .subcategories-panel-drawer.is-active {
            left: 0 !important;
            right: 0 !important;
        }
    }

    /* Provider CTA Section */
    .provider-cta-section {
        padding: 3rem 0;
        background: linear-gradient(135deg, rgba(241, 98, 15, 0.08), rgba(241, 98, 15, 0.04));
    }

    .provider-cta-card {
        background: #FFFFFF;
        border: 2px solid #F1620F;
        border-radius: 20px;
        padding: clamp(2rem, 5vw, 3rem);
        text-align: center;
        box-shadow: 0 10px 30px rgba(241, 98, 15, 0.1);
    }

    .cta-title {
        font-size: clamp(1.5rem, 4vw, 2.2rem);
        font-weight: 900;
        color: #0B1A34;
        margin-bottom: 1rem;
        letter-spacing: -0.03em;
    }

    .cta-description {
        font-size: clamp(0.9rem, 2vw, 1.05rem);
        color: #64748B;
        margin-bottom: 1.5rem;
        line-height: 1.7;
        max-width: 500px;
        margin-inline: auto;
    }

    .cta-button {
        display: inline-block;
        background: #F1620F;
        color: white;
        padding: 0.85rem 2rem;
        border-radius: 12px;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 700;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #F1620F;
    }

    .cta-button:hover {
        background: transparent;
        color: #F1620F;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .provider-cta-section {
            padding: 2rem 0;
        }

        .provider-cta-card {
            padding: 1.5rem;
        }

        .cta-title {
            margin-bottom: 0.75rem;
        }

        .cta-description {
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
        }

        .cta-button {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
        }
    }
</style>
@endsection

```

## public\category.blade.php

```blade
@extends('public.layout')

@section('title', $category->localized_name . ' - ' . config('app.name'))

@section('content')

{{-- Breadcrumbs Navigation Node --}}
<div class="breadcrumb-nav-wrapper">
    <div class="container">
        <nav aria-label="breadcrumb" class="modern-breadcrumb">
            <a href="{{ route('home') }}" class="breadcrumb-link">{{ __('messages.public.home') }}</a>
            <span class="breadcrumb-divider">/</span>
            <span class="breadcrumb-current">{{ $category->localized_name }}</span>
        </nav>
    </div>
</div>

{{-- Category Overview Hero Header Slot --}}
<section class="category-hero-header">
    <div class="container">
        <div class="category-hero-inner-grid">
            <div class="category-meta-details">
                <h1 class="category-title-main">
                    {{ $category->localized_name }}
                </h1>
                @if($category->description)
                    <p class="category-desc-para">{{ $category->description }}</p>
                @endif
                <div class="category-badge-pill">
                    <x-render-icon icon="heroicon-o-users" class="badge-icon-node" />
                    <span>{{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}</span>
                </div>
            </div>

            <div class="category-graphic-container">
                <div class="graphic-circle-backdrop">
                    <x-svg-icon :icon="$category->getRelation('icon')" class="graphic-svg" />
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Mobile Quick Action Bar Component --}}
<div class="mobile-action-bar-hub">
    <div class="container mobile-action-flex-container">
        <span class="mobile-results-counter">
            {{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}
        </span>
        <button type="button" id="openMobileFilters" class="btn-mobile-filter-trigger">
            <x-render-icon icon="heroicon-o-funnel" class="mobile-trigger-icon" />
            <span>خيارات التصفية</span>
            @if(request()->anyFilled(['city_id', 'sort']))
                <span class="active-filter-indicator-dot"></span>
            @endif
        </button>
    </div>
</div>

{{-- Search Engine Main Matrix Workspace --}}
<section class="archive-split-workspace">
    <div class="container">
        <div class="workspace-layout-grid">

            {{-- Filters Sidebar Block Module Wrapper Node --}}
            <div id="filterSidebarWrapper" class="workspace-sidebar-sticky hidden-mobile-wrapper">

                <div id="filterSidebarCard" class="filter-card-shell drawer-card-transform">
                    <div class="filter-card-header">
                        <div class="filter-header-main-title">
                            <x-render-icon icon="heroicon-o-funnel" class="filter-header-icon" />
                            <h3 class="filter-header-title">خيارات التصفية</h3>
                        </div>
                        <button type="button" id="closeMobileFilters" class="btn-mobile-drawer-close">✕</button>
                    </div>

                    <form method="GET" action="{{ url()->current() }}" class="filter-form-action-flow">
                        @if(isset($cities))
                            <div class="filter-input-group">
                                <label for="city_id" class="filter-field-label">{{ __('messages.public.city') }}</label>
                                <div class="filter-select-wrapper">
                                    <x-render-icon icon="heroicon-o-map-pin" class="select-embedded-icon" />
                                    <select id="city_id" name="city_id" onchange="this.form.submit()" class="filter-select-input">
                                        <option value="">{{ __('messages.public.all_cities') }}</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" @selected(request('city_id') == $city->id)>
                                                {{ $city->localized_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="filter-input-group">
                            <label for="sort" class="filter-field-label">{{ __('messages.public.sort_by') }}</label>
                            <div class="filter-select-wrapper">
                                <x-render-icon icon="heroicon-o-bars-3-bottom-left" class="select-embedded-icon" />
                                <select id="sort" name="sort" onchange="this.form.submit()" class="filter-select-input">
                                    <option value="" @selected(!request('sort'))>{{ __('messages.public.relevance') }}</option>
                                    <option value="rating" @selected(request('sort') === 'rating')>{{ __('messages.public.highest_rated') }}</option>
                                    <option value="reviews" @selected(request('sort') === 'reviews')>{{ __('messages.public.most_reviewed') }}</option>
                                    <option value="newest" @selected(request('sort') === 'newest')>{{ __('messages.public.newest') }}</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-filter-apply desktop-only-submit-btn">
                            <span>{{ __('messages.public.filter') }}</span>
                        </button>
                    </form>

                    @if(request()->anyFilled(['city_id', 'sort']))
                        <div class="clear-action-wrapper-node">
                            <a href="{{ route('public.category', $category->slug) }}" class="btn-filter-clear-trigger">
                                <x-render-icon icon="heroicon-o-arrow-path" class="clear-icon-svg" />
                                <span>{{ __('messages.public.clear_filters') }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Results Section Grid Dynamic Display Output --}}
            <main class="workspace-results-area">
                @if($profiles && $profiles->count() > 0)
                    <div class="provider-grid-wrapper-node">
                        <x-provider-grid :providers="$profiles" :columns="1" />
                    </div>

                    @if($profiles->hasPages())
                        <nav aria-label="Page navigation" class="pagination-footer-nav-container">
                            {{ $profiles->appends(request()->query())->links('pagination::tailwind') }}
                        </nav>
                    @endif
                @else
                    <div class="premium-empty-state-card">
                        <div class="empty-state-icon-backdrop">
                            <x-render-icon icon="heroicon-o-magnifying-glass" />
                        </div>
                        <h4 class="empty-state-heading">{{ __('messages.public.no_providers_found') }}</h4>
                        <p class="empty-state-description">
                            {{ __('messages.public.no_providers_in_category') }}
                        </p>
                        <a href="{{ route('public.search') }}" class="btn-empty-state-redirect">
                            <span>{{ __('messages.public.browse_all') }}</span>
                        </a>
                    </div>
                @endif
            </main>

        </div>
    </div>
</section>

<style>
    /* Design tokens mapping */
    :root {
        --brand-primary: #F1620F;
        --brand-primary-hover: #D7530A;
        --brand-dark: #0B1A34;
        --brand-dark-gradient: #14284D;
        --bg-surface: #FFFFFF;
        --bg-subtle: #F8FAFC;
        --text-primary: #0B1A34;
        --text-secondary: #475569;
        --text-light-muted: #94A3B8;
        --border-color: #E2E8F0;
        --transition-smooth: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Minimalist Breadcrumb Navigation */
    .breadcrumb-nav-wrapper {
        background-color: var(--bg-surface);
        border-bottom: 1px solid var(--border-color);
        padding: 0.85rem 0;
    }

    .modern-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .breadcrumb-link {
        color: var(--text-secondary);
        text-decoration: none;
        transition: var(--transition-smooth);
    }

    .breadcrumb-link:hover {
        color: var(--brand-primary);
    }

    .breadcrumb-divider {
        color: var(--text-light-muted);
    }

    .breadcrumb-current {
        color: var(--brand-dark);
        font-weight: 600;
    }

    /* Balanced Category Hero Header */
    .category-hero-header {
        background: linear-gradient(135deg, var(--brand-dark), var(--brand-dark-gradient));
        padding: 4rem 0;
        color: #FFFFFF;
    }

    .category-hero-inner-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        align-items: center;
        gap: 2rem;
    }

    .category-meta-details {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .category-title-main {
        font-size: clamp(1.75rem, 4vw, 2.75rem);
        font-weight: 800;
        margin: 0 0 1rem;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .category-desc-para {
        font-size: 1.05rem;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.8);
        max-width: 680px;
        margin: 0 0 1.5rem;
    }

    .category-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 0.4rem 1rem;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
    }

    .badge-icon-node {
        width: 16px;
        height: 16px;
        color: var(--brand-primary);
    }

    .category-graphic-container {
        display: flex;
        justify-content: flex-end;
    }

    .graphic-circle-backdrop {
        width: 110px;
        height: 110px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.85);
    }

    .graphic-svg {
        width: 52px;
        height: 52px;
    }

    /* Sticky Action Hub Above Mobile Content */
    .mobile-action-bar-hub {
        display: none;
        position: sticky;
        top: 0;
        z-index: 40;
        background-color: rgba(248, 250, 252, 0.9);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border-color);
        padding: 0.85rem 0;
    }

    .mobile-action-flex-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1rem;
    }

    .mobile-results-counter {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--brand-dark);
    }

    .btn-mobile-filter-trigger {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-secondary);
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        cursor: pointer;
    }

    .mobile-trigger-icon {
        width: 15px;
        height: 15px;
        color: var(--brand-primary);
    }

    .active-filter-indicator-dot {
        width: 7px;
        height: 7px;
        background-color: var(--brand-primary);
        border-radius: 50%;
        display: inline-block;
    }

    .btn-mobile-drawer-close {
        display: none;
        background: var(--bg-subtle);
        border: none;
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
        color: var(--text-secondary);
        font-weight: 700;
        cursor: pointer;
    }

    /* Workspace Architecture Grid Splitter */
    .archive-split-workspace {
        padding: 4.5rem 0;
        background-color: var(--bg-subtle);
    }

    .workspace-layout-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
        align-items: start;
    }

    /* Refined Sticky Search Filters Card */
    .workspace-sidebar-sticky {
        position: sticky;
        top: 110px;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        transition: background-color 0.2s ease, opacity 0.2s ease;
    }

    .filter-card-shell {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(11, 26, 52, 0.02);
        width: 100%;
        box-sizing: border-box;
    }

    .filter-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .filter-header-main-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-card-shell-inner-row {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
    }
    .filter-card-header {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
    }

    .filter-header-icon {
        width: 18px;
        height: 18px;
        color: var(--brand-primary);
    }

    .filter-header-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--brand-dark);
        margin: 0;
    }

    .filter-form-action-flow {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .filter-input-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-field-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-secondary);
    }

    .filter-select-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .select-embedded-icon {
        position: absolute;
        right: 1rem;
        width: 18px;
        height: 18px;
        color: var(--text-light-muted);
        pointer-events: none;
    }

    .filter-select-input {
        width: 100%;
        height: 46px;
        background-color: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0 2.75rem 0 1.25rem; /* Balanced explicitly for RTL spacing frameworks */
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--brand-dark);
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        transition: var(--transition-smooth);
        cursor: pointer;
    }

    .filter-select-input:focus {
        border-color: var(--brand-primary);
        background-color: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(241, 98, 15, 0.1);
    }

    .btn-filter-apply {
        background-color: var(--brand-dark);
        color: #FFFFFF;
        border: none;
        height: 46px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-smooth);
    }

    .btn-filter-apply:hover {
        background-color: var(--brand-primary);
        box-shadow: 0 4px 12px rgba(241, 98, 15, 0.2);
    }

    /* Filter Reset Controls Node Elements */
    .clear-action-wrapper-node {
        width: 100%;
        margin-top: 1rem;
    }

    .btn-filter-clear-trigger {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        height: 44px;
        background: transparent;
        border: 1px dashed var(--border-color);
        color: var(--text-secondary);
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: var(--transition-smooth);
    }

    .btn-filter-clear-trigger:hover {
        border-style: solid;
        border-color: var(--brand-primary);
        color: var(--brand-primary);
        background-color: rgba(241, 98, 15, 0.02);
    }

    .clear-icon-svg {
        width: 15px;
        height: 15px;
    }

    /* Results Workspace Core Component Node overrides */
    .workspace-results-area {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .provider-grid-wrapper-node {
        width: 100%;
    }

    .pagination-footer-nav-container {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    /* Premium Clean Empty State Matrix Minimalist Look */
    .premium-empty-state-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 4rem 2rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 4px 12px rgba(11, 26, 52, 0.01);
    }

    .empty-state-icon-backdrop {
        width: 72px;
        height: 72px;
        background-color: var(--bg-subtle);
        color: var(--text-light-muted);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .empty-state-icon-backdrop svg {
        width: 32px;
        height: 32px;
    }

    .empty-state-heading {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--brand-dark);
        margin: 0 0 0.5rem;
    }

    .empty-state-description {
        font-size: 0.95rem;
        color: var(--text-secondary);
        max-width: 400px;
        margin: 0 0 1.75rem;
        line-height: 1.5;
    }

    .btn-empty-state-redirect {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 46px;
        padding: 0 2rem;
        background-color: var(--brand-primary);
        color: #FFFFFF;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 700;
        border-radius: 10px;
        transition: var(--transition-smooth);
        box-shadow: 0 4px 12px rgba(241, 98, 15, 0.2);
    }

    .btn-empty-state-redirect:hover {
        background-color: var(--brand-primary-hover);
        transform: translateY(-1px);
    }

    /* Core Media Boundary Adaptations Queries */
    @media (max-width: 1024px) {
        .archive-split-workspace {
            padding: 2rem 0;
        }
        .mobile-action-bar-hub {
            display: block;
        }
        .workspace-layout-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        /* Transition Wrapper to Flyout Sheet Overlay */
        .workspace-sidebar-sticky {
            position: fixed;
            inset: 0;
            z-index: 50;
            background-color: rgba(11, 26, 52, 0.4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            margin: 0;
            padding: 0;
        }

        .workspace-sidebar-sticky.active-mobile-drawer {
            opacity: 1;
            pointer-events: auto;
        }

        .filter-card-shell {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: 24px 24px 0 0;
            border: none;
            border-top: 1px solid var(--border-color);
            max-height: 85vh;
            overflow-y: auto;
            transform: translateY(100%);
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .workspace-sidebar-sticky.active-mobile-drawer .filter-card-shell {
            transform: translateY(0);
        }

        .btn-mobile-drawer-close {
            display: block;
        }
        .desktop-only-submit-btn {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .category-hero-header {
            padding: 2.5rem 0;
        }
        .category-hero-inner-grid {
            grid-template-columns: 1fr;
        }
        .category-graphic-container {
            display: none; /* Strip layout containers from heavy responsive render trees */
        }
        .category-meta-details {
            align-items: center;
            text-align: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const openBtn = document.getElementById('openMobileFilters');
        const closeBtn = document.getElementById('closeMobileFilters');
        const sidebarWrapper = document.getElementById('filterSidebarWrapper');

        if (!openBtn || !sidebarWrapper) return;

        const openFilters = () => {
            sidebarWrapper.classList.remove('hidden-mobile-wrapper');
            // Allow display swap to hit browser layout engine prior to firing active transform animations
            setTimeout(() => {
                sidebarWrapper.classList.add('active-mobile-drawer');
            }, 10);
            document.body.style.overflow = 'hidden';
        };

        const closeFilters = () => {
            sidebarWrapper.classList.remove('active-mobile-drawer');
            setTimeout(() => {
                sidebarWrapper.classList.add('hidden-mobile-wrapper');
            }, 250);
            document.body.style.overflow = '';
        };

        openBtn.addEventListener('click', openFilters);
        closeBtn?.addEventListener('click', closeFilters);

        // Close modal sheet easily if clicking onto backdrop area mask
        sidebarWrapper.addEventListener('click', (e) => {
            if (e.target === sidebarWrapper) closeFilters();
        });
    });
</script>

@endsection

```

## public\city.blade.php

```blade
@extends('public.layout')

@section('title', $city->localized_name . ' - ' . config('app.name'))

@section('content')

<!-- Breadcrumb -->
<div class="container pt-3">
    <nav aria-label="breadcrumb" class="breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-primary-500">{{ __('messages.public.home') }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-600">{{ $city->localized_name }}</span>
    </nav>
</div>

<!-- Hero Section -->
<section class="bg-navy-800 text-white section-compact">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2">
                <h1 class="text-4xl font-black mb-4">
                    {{ $city->localized_name }}
                </h1>
                @if($city->description)
                    <p class="text-lg text-white/75 mb-3">{{ $city->description }}</p>
                @endif
                <p class="text-white/70">
                    {{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <div class="search-filters">
                    <form method="GET" class="space-y-4">
                        @if(isset($categories))
                            <div>
                                <label for="category_id" class="form-label">{{ __('messages.public.category') }}</label>
                                <select id="category_id" name="category_id" class="form-select">
                                    <option value="">{{ __('messages.public.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                            {{ $category->localized_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label for="sort" class="form-label">{{ __('messages.public.sort_by') }}</label>
                            <select id="sort" name="sort" class="form-select">
                                <option value="" @selected(!request('sort'))>{{ __('messages.public.relevance') }}</option>
                                <option value="rating" @selected(request('sort') === 'rating')>{{ __('messages.public.highest_rated') }}</option>
                                <option value="reviews" @selected(request('sort') === 'reviews')>{{ __('messages.public.most_reviewed') }}</option>
                                <option value="newest" @selected(request('sort') === 'newest')>{{ __('messages.public.newest') }}</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-full flex items-center justify-center gap-2">
                            <x-render-icon icon="heroicon-o-funnel" class="w-4 h-4" />
                            {{ __('messages.public.filter') }}
                        </button>
                    </form>
                </div>

                @if(request()->anyFilled(['category_id', 'sort']))
                    <div class="mt-4">
                        <a href="{{ route('public.city', $city->slug) }}" class="btn btn-outline btn-sm w-full flex items-center justify-center gap-2">
                            <x-render-icon icon="heroicon-o-arrow-path" class="w-4 h-4" />
                            {{ __('messages.public.clear_filters') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Results Section -->
        <div class="lg:col-span-3">
            @if($profiles && $profiles->count() > 0)
                <x-provider-grid :providers="$profiles" :columns="1" />

                @if($profiles->hasPages())
                    <nav aria-label="Page navigation" class="mt-8">
                        {{ $profiles->links('pagination::tailwind') }}
                    </nav>
                @endif
            @else
                <x-empty-state
                    icon="heroicon-o-magnifying-glass"
                    title="{{ __('messages.public.no_providers_found') }}"
                    message="{{ __('messages.public.no_providers_in_city') }}"
                    action-label="{{ __('messages.public.browse_all') }}"
                    action-url="{{ route('public.search') }}"
                />
            @endif
        </div>
    </div>
</section>

@endsection


```

## public\contact.blade.php

```blade
@extends('public.layout')

@section('title', __('messages.public.contact_us') . ' - ' . config('app.name'))

@section('content')
<section style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px); padding: 2rem 1rem;">
    <div style="max-width: 500px; width: 100%; background: #FFFFFF; border: 1px solid #E7E7E7; border-radius: 16px; padding: clamp(1.5rem, 4vw, 2.5rem); box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03); text-align: center;">
        <h1 style="font-size: 1.8rem; font-weight: 900; color: #0B1A34; margin-bottom: 1rem; letter-spacing: -0.03em;">
            {{ __('messages.public.contact_us') }}
        </h1>

        @if($contactInfo)
            <div style="text-align: center; margin-top: 2rem;">
                <p style="font-size: 0.95rem; color: #5D5959; margin-bottom: 2rem; line-height: 1.8;">
                    {{ __('messages.public.need_help') }}
                </p>

                <div style="background: #FCFBFB; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    @if($contactInfo->whatsapp)
                        <div style="margin-bottom: 1.25rem;">
                            <p style="font-size: 0.85rem; color: #5D5959; font-weight: 600; margin-bottom: 0.5rem;">
                                {{ __('filament.fields.whatsapp') }}
                            </p>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactInfo->whatsapp) }}" target="_blank" style="color: #F1620F; text-decoration: none; font-size: 0.95rem; font-weight: 700;">
                                {{ $contactInfo->whatsapp }}
                            </a>
                        </div>
                    @endif

                    @if($contactInfo->phone)
                        <div style="margin-bottom: 1.25rem;">
                            <p style="font-size: 0.85rem; color: #5D5959; font-weight: 600; margin-bottom: 0.5rem;">
                                {{ __('filament.fields.phone') }}
                            </p>
                            <a href="tel:{{ $contactInfo->phone }}" style="color: #F1620F; text-decoration: none; font-size: 0.95rem; font-weight: 700;">
                                {{ $contactInfo->phone }}
                            </a>
                        </div>
                    @endif

                    @if($contactInfo->email)
                        <div style="margin-bottom: 1.25rem;">
                            <p style="font-size: 0.85rem; color: #5D5959; font-weight: 600; margin-bottom: 0.5rem;">
                                {{ __('filament.fields.email') }}
                            </p>
                            <a href="mailto:{{ $contactInfo->email }}" style="color: #F1620F; text-decoration: none; font-size: 0.95rem; font-weight: 700;">
                                {{ $contactInfo->email }}
                            </a>
                        </div>
                    @endif

                    @if($contactInfo->address)
                        <div>
                            <p style="font-size: 0.85rem; color: #5D5959; font-weight: 600; margin-bottom: 0.5rem;">
                                {{ __('filament.fields.address') }}
                            </p>
                            <p style="color: #5D5959; font-size: 0.95rem; font-weight: 500;">
                                {{ $contactInfo->address }}
                            </p>
                        </div>
                    @endif
                </div>

                <a href="{{ route('home') }}" style="display: inline-block; background: #F1620F; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 700; transition: background 0.2s ease;">
                    {{ __('messages.public.back_home') }}
                </a>
            </div>
        @else
            <p style="font-size: 0.95rem; color: #5D5959; margin-bottom: 1.5rem;">
                {{ __('messages.public.contact_information') }}
            </p>

            <a href="{{ route('home') }}" style="display: inline-block; background: #F1620F; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 700; transition: background 0.2s ease;">
                {{ __('messages.public.back_home') }}
            </a>
        @endif
    </div>
</section>
@endsection

```

## public\home.blade.php

```blade
@extends('public.layout')

@section('title', __('messages.public.home') . ' - ' . config('app.name'))

@section('content')
@php
    $categories = $categories ?? collect();
    $cities = $cities ?? collect();
    $featuredProviders = $featuredProviders ?? collect();

    $providersCount = $categories->sum(fn ($category) => (int) ($category->discoverable_profiles_count ?? 0));
    $categoriesCount = $categories->count();
    $citiesCount = $cities->count();
@endphp

<div class="pwa-native-canvas" direction="rtl">

    <section class="app-dashboard-hero">
        <div class="orbs-backdrop">
            <div class="neon-orb orb-orange"></div>
            <div class="neon-orb orb-blue"></div>
        </div>

        <div class="container">
            <div class="app-core-interface">


                <h1 class="app-hero-title">
                    كل الكفاءات <br>
                    بين <span class="gradient-accent-text">يديك الآن</span>
                </h1>

                <form action="{{ route('public.search') }}" method="GET" class="glass-control-panel" id="searchForm">

                    <div class="panel-input-node input-keyword">
                        <x-render-icon icon="heroicon-o-magnifying-glass" class="panel-icon" />
                        <input
                            type="text"
                            name="keyword"
                            placeholder="ما الخدمة التي تبحث عنها؟"
                            maxlength="100"
                            autocomplete="off"
                            required
                        >
                    </div>

                    <div class="panel-desktop-filters-group">
                        <div class="panel-input-node">
                            <x-render-icon icon="heroicon-o-briefcase" class="panel-icon" />
                            <select name="category_id" id="desktopCategory">
                                <option value="">اختر التخصص</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->localized_name ?? $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="panel-input-node">
                            <x-render-icon icon="heroicon-o-map-pin" class="panel-icon" />
                            <select name="city_id" id="desktopCity">
                                <option value="">كل المدن</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->localized_name ?? $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="button" class="panel-mobile-trigger-pill" id="openMobileFilters">
                        <x-render-icon icon="heroicon-o-funnel" />
                        <span id="filterPillText">تصفية متقدمة</span>
                    </button>

                    <button type="submit" class="panel-submit-btn" aria-label="تنفيذ البحث">
                        <x-render-icon icon="heroicon-o-arrow-left" class="icon-submit-rotate" />
                    </button>

                    <div class="pwa-bottom-sheet-overlay" id="drawerOverlay">
                        <div class="pwa-bottom-sheet-card">
                            <div class="sheet-pull-bar"></div>
                            <div class="sheet-top-nav">
                                <h3>تخصيص الفلاتر</h3>
                                <button type="button" class="sheet-close-action" id="closeMobileFilters">&times;</button>
                            </div>
                            <div class="sheet-scrollable-body">
                                <div class="sheet-form-field">
                                    <label>تخصص ومجال الخدمة</label>
                                    <div class="sheet-custom-select-box">
                                        <select id="mobileCategory">
                                            <option value="">كل التخصصات والمجالات</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->localized_name ?? $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="sheet-form-field">
                                    <label>المدينة والنطاق الجغرافي</label>
                                    <div class="sheet-custom-select-box">
                                        <select id="mobileCity">
                                            <option value="">كل المدن والمناطق</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}">{{ $city->localized_name ?? $city->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="sheet-actions-bar">
                                <button type="button" id="applyMobileFilters" class="btn-sheet-apply">تأكيد ومزامنة الاختيارات</button>
                            </div>
                        </div>
                    </div>

                </form>

                @if($categories->count() > 0)
                <div class="app-stories-panel">
                    <p class="app-stories-header-title">أبرز المجالات المهنية</p>
                    <div class="app-stories-scroll-track" data-pwa-drag-track>
                        @foreach ($categories->take(10) as $category)
                        <a href="{{ route('public.category', $category->slug) }}" class="app-story-item">
                            <div class="story-neon-border">
                                <div class="story-mask-inner">
                                    <x-svg-icon :icon="$category->getRelation('icon')" size="22" />
                                </div>
                            </div>
                            <span class="story-label-text">{{ $category->localized_name ?? $category->name }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </section>

    <section class="app-floating-metrics">
        <div class="container">
            <div class="metrics-cosmic-card">
                <div class="metric-segment">
                    <strong class="metric-value">{{ number_format($categoriesCount) }}+</strong>
                    <span class="metric-caption">فئة متنوعة</span>
                </div>
                <div class="metric-segment">
                    <strong class="metric-value">{{ number_format($citiesCount) }}</strong>
                    <span class="metric-caption">مدينة تغطية</span>
                </div>
                <div class="metric-segment">
                    <strong class="metric-value">{{ number_format($providersCount) }}+</strong>
                    <span class="metric-caption">كفاءة موثوقة</span>
                </div>
            </div>
        </div>
    </section>

    @if($featuredProviders->count() > 0)
    <section class="app-grid-showcase">
        <div class="container">
            <div class="showcase-header-cluster">
                <div class="text-side">
                    <span class="micro-badge">نوصي بهم</span>
                    <h2 class="showcase-main-title">الكفاءات المتميزة المتاحة الآن</h2>
                </div>
                <a href="{{ route('public.categories') }}" class="app-pill-action">
                    <span>عرض كل التخصصات</span>
                    <x-render-icon icon="heroicon-o-sparkles" />
                </a>
            </div>

            <div class="organic-grid-wrapper">
                <x-provider-grid
                    :providers="$featuredProviders"
                    :columns="3"
                    compact="true"
                />
            </div>
        </div>
    </section>
    @endif

    <section class="app-cta-banner-viewport">
        <div class="container">
            <div class="perspective-cta-card">
                <div class="card-radial-gradient"></div>
                <div class="cta-inner-flex">
                    <div class="text-block">
                        <h2 class="cta-headline-main">{{ __('messages.public.are_you_professional') }}</h2>
                        <p class="cta-paragraph-sub">{{ __('messages.public.join_marketplace_description') }}</p>
                    </div>
                    <div class="button-block">
                        <a href="{{ route('contact') }}" class="cta-native-app-btn">
                            <span>{{ __('messages.public.contact_us') }}</span>
                            <x-render-icon icon="heroicon-o-rocket-launch" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

@once
<style>
    /* Premium Application Variable Setup & Native Tokens */
    :root {
        --app-primary: #F1620F;
        --app-primary-rgb: 241, 98, 15;
        --app-dark-core: #0B1A34;
        --app-dark-void: #050E1C;
        --app-surface: #FFFFFF;
        --app-bg-tint: #F3F4F6;
        --app-text-dark: #0F172A;
        --app-text-muted: #64748B;
        --app-border-subtle: #E2E8F0;

        --app-radius-full: 999px;
        --app-radius-xl: 28px;
        --app-radius-lg: 20px;
        --app-radius-md: 14px;
        --app-speed-smooth: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* PWA Screen Environment Safeguard */
    .pwa-native-canvas {
        background-color: #F8FAFC;
        overflow-x: hidden;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Cosmic Dashboard Hero Structure */
    .app-dashboard-hero {
        position: relative;
        background: var(--app-dark-void);
        padding: 5.5rem 0 9.5rem;
        color: var(--app-surface);
        border-bottom-left-radius: var(--app-radius-xl);
        border-bottom-right-radius: var(--app-radius-xl);
        overflow: hidden;
    }

    .orbs-backdrop {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 1;
    }

    .neon-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(140px);
        opacity: 0.22;
    }

    .orb-orange {
        width: 420px;
        height: 420px;
        background: var(--app-primary);
        top: -120px;
        right: -8px;
    }

    .orb-blue {
        width: 380px;
        height: 380px;
        background: #2563EB;
        bottom: -100px;
        left: -50px;
    }

    .app-core-interface {
        max-width: 860px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 5;
    }

    /* Floating Capsule Micro Badge */
    .app-action-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.12);
        padding: 0.5rem 1.25rem;
        border-radius: var(--app-radius-full);
        font-size: 0.8rem;
        font-weight: 700;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        margin-bottom: 2rem;
        color: rgba(255, 255, 255, 0.95);
    }

    .badge-glow-ring {
        width: 7px;
        height: 7px;
        background: #10B981;
        border-radius: 50%;
        box-shadow: 0 0 10px #10B981;
        animation: pulseRing 1.8s infinite alternate;
    }

    @keyframes pulseRing {
        0% { transform: scale(0.9); opacity: 0.6; }
        100% { transform: scale(1.25); opacity: 1; }
    }

    .app-hero-title {
        font-size: 3.25rem;
        font-weight: 900;
        line-height: 1.25;
        margin-bottom: 2.5rem;
        letter-spacing: -0.02em;
    }

    .app-hero-title .gradient-accent-text {
        background: linear-gradient(135deg, #FF8D4D 0%, var(--app-primary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* The Glassmorphic Floating Control Panel Engine */
    .glass-control-panel {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.09);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border-radius: var(--app-radius-lg);
        padding: 0.6rem;
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 0.5rem;
        align-items: center;
        box-shadow: 0 30px 60px rgba(4, 12, 26, 0.45);
        margin-bottom: 4rem;
    }

    .panel-input-node {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1.25rem;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.04);
        border-radius: var(--app-radius-md);
        transition: var(--app-speed-smooth);
    }

    .panel-input-node:focus-within {
        background: rgba(255, 255, 255, 0.09);
        border-color: var(--app-primary);
        box-shadow: 0 0 14px rgba(var(--app-primary-rgb), 0.35);
    }

    .panel-icon {
        width: 20px;
        height: 20px;
        color: rgba(255, 255, 255, 0.4);
        flex-shrink: 0;
    }

    .panel-input-node input,
    .panel-input-node select {
        width: 100%;
        border: 0;
        background: transparent;
        color: var(--app-surface);
        font-size: 0.95rem;
        font-weight: 600;
        outline: none;
        font-family: inherit;
    }

    .panel-input-node input::placeholder {
        color: rgba(255, 255, 255, 0.38);
    }

    .panel-input-node select option {
        background: var(--app-dark-void);
        color: var(--app-surface);
    }

    .panel-desktop-filters-group {
        display: flex;
        gap: 0.5rem;
    }

    .panel-mobile-trigger-pill {
        display: none; /* تظهر فقط في الشاشات الصغيرة للمحمول */
    }

    .panel-submit-btn {
        background: var(--app-primary);
        color: var(--app-surface);
        border: 0;
        border-radius: var(--app-radius-md);
        width: 52px;
        height: 52px;
        display: grid;
        place-items: center;
        cursor: pointer;
        transition: var(--app-speed-smooth);
        box-shadow: 0 8px 20px rgba(var(--app-primary-rgb), 0.3);
    }

    .panel-submit-btn:hover {
        background: #FF7426;
        transform: scale(1.04);
    }

    .panel-submit-btn svg {
        width: 20px;
        height: 20px;
    }

    .icon-submit-rotate {
        transform: rotate(180deg); /* تدوير مخصص للـ RTL لتوجيه السهم لليسار */
    }

    /* Native App Stories Layout Module Setup */
    .app-stories-panel {
        text-align: right;
    }

    .app-stories-header-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.45);
        margin-bottom: 1.2rem;
        padding-inline-start: 0.4rem;
    }

    .app-stories-scroll-track {
        display: flex;
        gap: 1.4rem;
        overflow-x: auto;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 0.4rem;
        cursor: grab;
    }

    .app-stories-scroll-track::-webkit-scrollbar {
        display: none;
    }

    .app-story-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.6rem;
        text-decoration: none;
        flex-shrink: 0;
        width: 78px;
        transition: var(--app-speed-smooth);
    }

    .story-neon-border {
        width: 66px;
        height: 66px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--app-primary) 0%, #3B82F6 50%, #10B981 100%);
        padding: 2.5px;
        display: grid;
        place-items: center;
        transition: var(--app-speed-smooth);
    }

    .story-mask-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: var(--app-dark-void);
        display: grid;
        place-items: center;
        color: var(--app-surface);
        transition: var(--app-speed-smooth);
    }

    .app-story-item:hover .story-mask-inner {
        background: var(--app-primary);
    }

    .app-story-item:hover {
        transform: scale(1.06);
    }

    .story-label-text {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.78rem;
        font-weight: 700;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
    }

    /* Floating Metrics Block Module Layout */
    .app-floating-metrics {
        position: relative;
        margin-top: -3.5rem;
        z-index: 10;
        padding: 0 1rem;
    }

    .metrics-cosmic-card {
        max-width: 1100px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        border: 1px solid var(--app-surface);
        border-radius: var(--app-radius-lg);
        padding: 1.75rem 2rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        box-shadow: 0 20px 50px rgba(5, 14, 28, 0.04);
    }

    .metric-segment {
        text-align: center;
        position: relative;
    }

    .metric-segment:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 15%;
        left: 0;
        width: 1px;
        height: 70%;
        background: #E2E8F0;
    }

    .metric-value {
        display: block;
        font-size: 2rem;
        font-weight: 900;
        color: var(--app-dark-core);
        letter-spacing: -0.02em;
    }

    .metric-caption {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--app-text-muted);
        margin-top: 0.2rem;
    }

    /* Premium Grid Architecture Layout Setup */
    .app-grid-showcase {
        padding: 5.5rem 0;
    }

    .showcase-header-cluster {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 3rem;
    }

    .micro-badge {
        display: inline-block;
        color: var(--app-primary);
        font-weight: 800;
        font-size: 0.8rem;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
        letter-spacing: 0.02em;
    }

    .showcase-main-title {
        color: var(--app-dark-core);
        font-size: 2rem;
        font-weight: 950;
        letter-spacing: -0.03em;
    }

    .app-pill-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--app-surface);
        color: var(--app-text-dark);
        border: 1px solid var(--app-border-subtle);
        padding: 0.75rem 1.5rem;
        border-radius: var(--app-radius-full);
        text-decoration: none;
        font-weight: 750;
        font-size: 0.9rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        transition: var(--app-speed-smooth);
    }

    .app-pill-action:hover {
        border-color: var(--app-primary);
        color: var(--app-primary);
        transform: translateY(-2px);
    }

    .app-pill-action svg {
        width: 18px;
        height: 18px;
    }

    /* حقن الخصائص العضوية المائلة على شبكة الكروت الجاهزة لكسر الرتابة */
    .organic-grid-wrapper .provider-grid,
    .organic-grid-wrapper [class*="grid"] {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 2rem !important;
    }

    .organic-grid-wrapper article,
    .organic-grid-wrapper .provider-card {
        background: var(--app-surface) !important;
        border: 1px solid var(--app-border-subtle) !important;
        border-radius: var(--app-radius-lg) !important;
        padding: 1.5rem !important;
        transition: var(--app-speed-smooth) !important;
    }

    /* تأثير ثلاثي الأبعاد وعائم عند ملامسة مؤشر الماوس */
    .organic-grid-wrapper article:hover,
    .organic-grid-wrapper .provider-card:hover {
        transform: translateY(-8px) scale(1.015) !important;
        box-shadow: 0 30px 60px -15px rgba(var(--app-primary-rgb), 0.08) !important;
        border-color: var(--app-primary) !important;
    }

    /* 3D Perspective App Banner Card Setup */
    .app-cta-banner-viewport {
        padding: 2rem 0 6rem;
    }

    .perspective-cta-card {
        position: relative;
        background: linear-gradient(135deg, var(--app-dark-core) 0%, var(--app-dark-void) 100%);
        border-radius: var(--app-radius-xl);
        padding: 4.5rem 3rem;
        overflow: hidden;
        box-shadow: 0 30px 60px rgba(5, 14, 28, 0.25);
    }

    .card-radial-gradient {
        position: absolute;
        width: 260px;
        height: 260px;
        background: #3B82F6;
        filter: blur(110px);
        opacity: 0.25;
        bottom: -60px;
        right: -40px;
        border-radius: 50%;
    }

    .cta-inner-flex {
        position: relative;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
    }

    .cta-headline-main {
        font-size: 2.25rem;
        font-weight: 900;
        color: var(--app-surface);
        margin-bottom: 0.75rem;
        letter-spacing: -0.02em;
    }

    .cta-paragraph-sub {
        color: rgba(255, 255, 255, 0.65);
        font-size: 1.05rem;
        line-height: 1.6;
        font-weight: 500;
        max-width: 520px;
    }

    .cta-native-app-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        background: var(--app-primary);
        color: var(--app-surface);
        padding: 1.1rem 2.5rem;
        border-radius: var(--app-radius-md);
        font-weight: 800;
        text-decoration: none;
        font-size: 1.05rem;
        box-shadow: 0 10px 25px rgba(var(--app-primary-rgb), 0.35);
        transition: var(--app-speed-smooth);
        white-space: nowrap;
    }

    .cta-native-app-btn:hover {
        background: #FF7426;
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(var(--app-primary-rgb), 0.5);
    }

    .cta-native-app-btn svg {
        width: 20px;
        height: 20px;
    }

    /* PWA Mobile Bottom Sheet Structural Filter Card Overlay Styles */
    .pwa-bottom-sheet-overlay {
        position: fixed;
        inset: 0;
        background: rgba(5, 14, 28, 0.6);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 12000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: flex-end;
    }

    .pwa-bottom-sheet-overlay.is-active {
        opacity: 1;
        pointer-events: auto;
    }

    .pwa-bottom-sheet-card {
        width: 100%;
        background: var(--app-surface);
        border-top-left-radius: var(--app-radius-xl);
        border-top-right-radius: var(--app-radius-xl);
        padding: 0 1.5rem 2.5rem;
        transform: translateY(100%);
        transition: transform 0.4s cubic-bezier(0.15, 1, 0.3, 1);
    }

    .pwa-bottom-sheet-overlay.is-active .pwa-bottom-sheet-card {
        transform: translateY(0);
    }

    .sheet-pull-bar {
        width: 42px;
        height: 5px;
        background: var(--app-border-subtle);
        border-radius: 999px;
        margin: 0.75rem auto 1.5rem;
    }

    .sheet-top-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
    }

    .sheet-top-nav h3 { font-size: 1.3rem; font-weight: 900; color: var(--app-dark-core); }

    .sheet-close-action {
        border: 0; background: var(--app-bg-tint); width: 36px; height: 36px;
        border-radius: 50%; font-size: 1.25rem; color: var(--app-text-muted); cursor: pointer;
    }

    .sheet-scrollable-body { display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2.5rem; }

    .sheet-form-field { display: flex; flex-direction: column; gap: 0.5rem; text-align: right; }

    .sheet-form-field label { font-size: 0.85rem; font-weight: 700; color: var(--app-text-muted); }

    .sheet-custom-select-box {
        background: var(--app-bg-tint); border-radius: var(--app-radius-md); padding: 0.9rem 1.25rem;
    }

    .sheet-custom-select-box select {
        width: 100%; border: 0; background: transparent; font-size: 0.95rem;
        font-weight: 700; color: var(--app-text-dark); outline: none; font-family: inherit;
    }

    .btn-sheet-apply {
        width: 100%; background: var(--app-primary); color: var(--app-surface);
        border: 0; border-radius: var(--app-radius-md); padding: 1.1rem 0;
        font-size: 1rem; font-weight: 800; cursor: pointer; box-shadow: 0 10px 20px rgba(var(--app-primary-rgb), 0.25);
    }

    /* Media Queries Scalers Breakdowns Control Layout */
    @media (max-width: 991px) {
        .app-hero-title { font-size: 2.5rem; }
        .glass-control-panel { grid-template-columns: 1fr auto; }
        .panel-desktop-filters-group { display: none; } /* إخفاء لتوفير المساحة */

        .panel-mobile-trigger-pill {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--app-radius-md); padding: 0 1.25rem; height: 52px;
            font-family: inherit; font-size: 0.9rem; font-weight: 700; color: var(--app-surface); cursor: pointer;
        }
        .panel-mobile-trigger-pill svg { width: 18px; height: 18px; color: var(--app-primary); }

        .metrics-cosmic-card { gap: 1rem; padding: 1.5rem 1rem; }

        .organic-grid-wrapper .provider-grid,
        .organic-grid-wrapper [class*="grid"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .cta-inner-flex { flex-direction: column; text-align: center; gap: 1.75rem; }
        .cta-paragraph-sub { max-width: 100%; }
        .cta-native-app-btn { width: 100%; justify-content: center; }
    }

    @media (max-width: 768px) {
        .app-dashboard-hero { padding: 4rem 0 11rem; }
        .app-hero-title { font-size: 2.1rem; margin-bottom: 2.25rem; }

        .glass-control-panel { grid-template-columns: 1fr; padding: 0.5rem; gap: 0.5rem; }
        .panel-mobile-trigger-pill { width: 100%; justify-content: center; }
        .panel-submit-btn { width: 100%; border-radius: var(--app-radius-md); }

        .app-floating-metrics { margin-top: -5.5rem; }
        .metrics-cosmic-card { grid-template-columns: 1fr; padding: 1.5rem; gap: 1.25rem; }
        .metric-segment:not(:last-child)::after { display: none; }

        .app-grid-showcase { padding: 4.5rem 0 2.5rem; }
        .showcase-header-cluster { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .app-pill-action { width: 100%; justify-content: center; }
        .showcase-main-title { font-size: 1.6rem; }

        .organic-grid-wrapper .provider-grid,
        .organic-grid-wrapper [class*="grid"] {
            grid-template-columns: 1fr !important;
            gap: 1.25rem !important;
        }

        .perspective-cta-card { padding: 3rem 1.5rem; }
        .cta-headline-main { font-size: 1.75rem; }
    }
</style>
@endonce

@push('scripts')
@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // إدارة الدرج السفلي السحاب لفلاتر المحمول الأصيلة
        const openBtn = document.getElementById('openMobileFilters');
        const closeBtn = document.getElementById('closeMobileFilters');
        const applyBtn = document.getElementById('applyMobileFilters');
        const overlay = document.getElementById('drawerOverlay');

        // عناصر نقل ومزامنة مدخلات البحث بين الواجهات
        const desktopCategory = document.getElementById('desktopCategory');
        const desktopCity = document.getElementById('desktopCity');
        const mobileCategory = document.getElementById('mobileCategory');
        const mobileCity = document.getElementById('mobileCity');
        const filterPillText = document.getElementById('filterPillText');

        if (openBtn && overlay) {
            openBtn.addEventListener('click', () => {
                overlay.classList.add('is-active');
                document.body.style.overflow = 'hidden'; // حظر التمرير الخلفي العشوائي للموبايل
            });
        }

        const closeDrawer = () => {
            if (overlay) {
                overlay.classList.remove('is-active');
                document.body.style.overflow = '';
            }
        };

        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (overlay) {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeDrawer();
            });
        }

        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                if (desktopCategory && mobileCategory) desktopCategory.value = mobileCategory.value;
                if (desktopCity && mobileCity) desktopCity.value = mobileCity.value;

                let activeTokens = [];
                if (mobileCategory && mobileCategory.options[mobileCategory.selectedIndex].value !== "") {
                    activeTokens.push(mobileCategory.options[mobileCategory.selectedIndex].text);
                }
                if (mobileCity && mobileCity.options[mobileCity.selectedIndex].value !== "") {
                    activeTokens.push(mobileCity.options[mobileCity.selectedIndex].text);
                }

                if (filterPillText) {
                    filterPillText.textContent = activeTokens.length > 0 ? activeTokens.join(' · ') : 'تصفية متقدمة';
                }
                closeDrawer();
            });
        }

        // إيماءة السحب الحر باللمس لشريط القصص الدائرية السريع (Touch Gesture Drag Track)
        const track = document.querySelector('[data-pwa-drag-track]');
        if (track) {
            let isDown = false;
            let startX;
            let scrollLeft;

            track.addEventListener('mousedown', (e) => {
                isDown = true;
                track.style.cursor = 'grabbing';
                startX = e.pageX - track.offsetLeft;
                scrollLeft = track.scrollLeft;
            });
            track.addEventListener('mouseleave', () => { isDown = false; track.style.cursor = 'grab'; });
            track.addEventListener('mouseup', () => { isDown = false; track.style.cursor = 'grab'; });
            track.addEventListener('mousemove', (e) => {
                if(!isDown) return;
                e.preventDefault();
                const x = e.pageX - track.offsetLeft;
                const walk = (x - startX) * 2.2; // عامل التسارع والنعومة للمس والسحب
                track.scrollLeft = scrollLeft - walk;
            });
        }
    });
</script>
@endonce
@endpush
@endsection

```

## public\layout.blade.php

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')

    <style>
        :root {
            --delni-primary: #F1620F;
            --delni-navy: #0B1A34;
            --delni-bg: #FCFBFB;
            --delni-gray: #C7C3C3;
            --delni-muted: #5D5959;
            --delni-border: #E7E7E7;
            --delni-success: #22C55E;
            --delni-warning: #F59E0B;

            --delni-radius-sm: 12px;
            --delni-radius-md: 18px;
            --delni-radius-lg: 26px;

            --delni-shadow-sm: 0 8px 20px rgba(11, 26, 52, .05);
            --delni-shadow-md: 0 16px 36px rgba(11, 26, 52, .08);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--delni-bg);
            color: var(--delni-navy);
            font-family: 'Cairo', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            text-align: start;
        }

        a {
            color: inherit;
        }

        img,
        svg {
            max-width: 100%;
            max-height: 100%;
        }

        .container {
            width: min(100% - 2rem, 1240px);
            margin-inline: auto;
        }

        .delni-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #fff;
            border-bottom: 1px solid var(--delni-border);
            backdrop-filter: none;
        }

        .delni-header__inner {
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .delni-logo {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            color: var(--delni-navy);
            text-decoration: none;
            font-size: 1.3rem;
            font-weight: 950;
            letter-spacing: -.04em;
        }

        .delni-logo__mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            overflow: hidden;
            background: var(--delni-navy);
        }

        .delni-logo__mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .delni-nav {
            display: none;
            align-items: center;
            gap: .35rem;
        }

        .delni-nav a {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .55rem .9rem;
            border-radius: 999px;
            color: var(--delni-muted);
            text-decoration: none;
            font-size: .92rem;
            font-weight: 850;
        }

        .delni-nav a:hover,
        .delni-nav a.is-active {
            color: var(--delni-primary);
            background: rgba(241, 98, 15, .08);
        }

        .delni-actions {
            display: none;
            align-items: center;
            gap: .6rem;
        }

        .delni-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            padding: .7rem 1rem;
            border-radius: 14px;
            border: 1px solid transparent;
            font-family: inherit;
            font-size: .9rem;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition: .18s ease;
        }

        .delni-btn--primary {
            background: var(--delni-primary);
            color: #fff;
            box-shadow: 0 12px 24px rgba(241, 98, 15, .22);
        }

        .delni-btn--primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(241, 98, 15, .28);
        }

        .delni-btn--ghost {
            background: #fff;
            color: var(--delni-navy);
            border-color: var(--delni-border);
        }

        .delni-btn--ghost:hover {
            border-color: rgba(241, 98, 15, .28);
            color: var(--delni-primary);
        }

        .delni-main {
            min-height: calc(100vh - 76px);
        }

        .delni-footer {
            margin-top: 4rem;
            padding: 2rem 0;
            border-top: 1px solid var(--delni-border);
            background: #fff;
            color: var(--delni-muted);
            font-size: .9rem;
            font-weight: 600;
        }

        .delni-footer__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .delni-footer a {
            color: var(--delni-muted);
            text-decoration: none;
            font-weight: 800;
        }

        .delni-footer a:hover {
            color: var(--delni-primary);
        }

        @media (min-width: 1025px) {
            .delni-nav {
                display: flex !important;
            }

            .delni-actions {
                display: flex !important;
            }

            .delni-header__inner {
                min-height: 76px;
            }

            .delni-logo {
                font-size: 1.45rem;
            }

            .delni-logo__mark {
                width: 46px;
                height: 46px;
            }
        }

        @media (max-width: 760px) {
            .container {
                width: min(100% - 1.25rem, 1240px);
            }

            .delni-header__inner {
                min-height: 56px;
                gap: .4rem;
            }

            .delni-logo {
                font-size: 1.15rem;
            }

            .delni-logo__mark {
                width: 36px;
                height: 36px;
                border-radius: 10px;
            }

            .delni-btn {
                min-height: 40px;
                padding: .6rem .8rem;
                font-size: .84rem;
            }
        }
    </style>
</head>

<body>
    <header class="delni-header">
        <div class="container">
            <div class="delni-header__inner">
                <a href="{{ route('home') }}" class="delni-logo">
                    <span class="delni-logo__mark">
                        <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('app.name') }}">
                    </span>
                    <span>دلني</span>
                </a>

                <nav class="delni-nav" aria-label="Main navigation">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">
                        الرئيسية
                    </a>
                    <a href="{{ route('public.top-rated') }}" class="{{ request()->routeIs('public.top-rated') ? 'is-active' : '' }}">
                        الأعلى تقييماً
                    </a>
                    <a href="{{ route('public.search') }}" class="{{ request()->routeIs('public.search') ? 'is-active' : '' }}">
                        بحث
                    </a>
                    <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'is-active' : '' }}">
                        {{ __('messages.public.contact_us') }}
                    </a>
                </nav>

                <div class="delni-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="delni-btn delni-btn--ghost">لوحتي</a>
                    @else
                        <a href="{{ route('login') }}" class="delni-btn delni-btn--primary">تسجيل</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="delni-main">
        @yield('content')
    </main>

    <footer class="delni-footer">
        <div class="container">
            <div class="delni-footer__inner">
                <span>© {{ date('Y') }} دلني. جميع الحقوق محفوظة.</span>
                <div>
                    <a href="{{ route('privacy') }}">الخصوصية</a>
                    ·
                    <a href="{{ route('terms') }}">الشروط</a>
                </div>
            </div>
        </div>
    </footer>

    <x-chatbot-widget />

    @stack('scripts')
</body>
</html>

```

## public\legal\disclaimer.blade.php

```blade
@extends('public.legal_layout')

@section('title', 'إخلاء المسؤولية - ' . config('app.name'))
@section('meta_description', 'إخلاء مسؤولية منصة دلني.')

@section('content')
<h1>إخلاء المسؤولية</h1>

<div class="legal-card-meta">
    آخر تحديث: {{ now()->format('d/m/Y') }}
</div>

<div class="legal-section">
    <h2>1. دلني منصة وسيطة</h2>
    <p>
        دلني منصة دليل إلكتروني تعرض معلومات مقدمي الخدمات لتسهيل الوصول إليهم.
        نحن لا نقدم الخدمات بأنفسنا، ولا نعمل كوكيل أو ممثل لأي مقدم خدمة.
    </p>
</div>

<div class="legal-section">
    <h2>2. عدم ضمان جودة الخدمات</h2>
    <p>
        لا تضمن دلني جودة أو نتيجة أو سلامة أي خدمة يقدمها أي مقدم خدمة.
        اختيار مقدم الخدمة والتعامل معه يكون على مسؤولية المستخدم.
    </p>
</div>

<div class="legal-section">
    <h2>3. دقة المعلومات</h2>
    <p>
        نحاول مراجعة المعلومات المعروضة قدر الإمكان، لكن قد تحتوي بعض الملفات على بيانات غير مكتملة أو قديمة أو غير دقيقة.
        مقدم الخدمة مسؤول عن تحديث بياناته وصحة ما يعرضه.
    </p>
</div>

<div class="legal-section">
    <h2>4. التواصل والدفع خارج المنصة</h2>
    <p>
        أي تواصل أو اتفاق أو دفع يتم خارج دلني، مثل الهاتف أو واتساب، يكون بين المستخدم ومقدم الخدمة مباشرة.
        دلني لا تتحمل مسؤولية النزاعات أو الخسائر الناتجة عن هذه التعاملات.
    </p>
</div>

<div class="legal-section">
    <h2>5. التقييمات والآراء</h2>
    <p>
        التقييمات والتعليقات تعبّر عن آراء أصحابها فقط، ولا تعبر بالضرورة عن رأي دلني.
        قد نقوم بحذف أي تقييم مخالف أو مسيء أو مشكوك في صحته.
    </p>
</div>

<div class="legal-section">
    <h2>6. الروابط الخارجية</h2>
    <p>
        قد تحتوي المنصة على روابط خارجية مثل واتساب أو مواقع التواصل أو مواقع مقدمي الخدمات.
        نحن لا نتحكم في هذه المواقع ولا نتحمل مسؤولية محتواها أو سياساتها.
    </p>
</div>

<div class="legal-section">
    <h2>7. توفر المنصة</h2>
    <p>
        نسعى لتوفير المنصة بشكل مستمر، لكن لا نضمن أن تعمل دون انقطاع أو أخطاء أو مشاكل تقنية.
        قد نقوم بإيقاف أو تعديل أي جزء من المنصة عند الحاجة.
    </p>
</div>

<div class="legal-section">
    <h2>8. حدود المسؤولية</h2>
    <p>
        لا تتحمل دلني مسؤولية أي خسائر أو أضرار مباشرة أو غير مباشرة تنتج عن استخدام المنصة،
        أو الاعتماد على معلومات منشورة فيها، أو التعامل مع مقدمي الخدمات.
    </p>
</div>

<div class="legal-section">
    <h2>9. لا نقدم نصائح مهنية</h2>
    <p>
        المعلومات الموجودة في دلني لغرض التعريف والبحث فقط.
        لا تعتبر المنصة مصدرًا لنصائح قانونية أو مالية أو طبية أو هندسية أو أي نصائح مهنية متخصصة.
    </p>
</div>

<div class="legal-section">
    <h2>10. قبول الإخلاء</h2>
    <p>
        باستخدامك لمنصة دلني، فإنك تقر بأنك فهمت حدود دور المنصة وتوافق على هذا الإخلاء.
    </p>
</div>
@endsection

```

## public\legal\privacy.blade.php

```blade
@extends('public.legal_layout')

@section('title', 'سياسة الخصوصية - ' . config('app.name'))
@section('meta_description', 'سياسة الخصوصية لمنصة دلني.')

@section('content')
<h1>سياسة الخصوصية</h1>

<div class="legal-card-meta">
    آخر تحديث: {{ now()->format('d/m/Y') }}
</div>

<div class="legal-section">
    <h2>1. من نحن</h2>
    <p>
        دلني منصة دليل إلكتروني تساعد المستخدمين في العثور على مقدمي خدمات داخل ليبيا.
        نحن لا نقدم الخدمات بأنفسنا، بل نعرض معلومات مقدمي الخدمات لتسهيل الوصول إليهم.
    </p>
</div>

<div class="legal-section">
    <h2>2. البيانات التي قد نجمعها</h2>
    <ul>
        <li>الاسم ورقم الهاتف والبريد الإلكتروني عند إنشاء حساب أو التواصل معنا.</li>
        <li>بيانات مقدمي الخدمات مثل اسم النشاط، المدينة، الفئة، الوصف، الصور، وروابط التواصل.</li>
        <li>التقييمات أو التعليقات التي يرسلها المستخدمون.</li>
        <li>بيانات استخدام بسيطة مثل الصفحات التي تمت زيارتها أو عمليات البحث لتحسين المنصة.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>3. كيف نستخدم البيانات</h2>
    <ul>
        <li>عرض مقدمي الخدمات داخل المنصة.</li>
        <li>تحسين تجربة البحث والتصفح.</li>
        <li>إدارة الحسابات والاشتراكات والمحتوى.</li>
        <li>التواصل مع المستخدم أو مقدم الخدمة عند الحاجة.</li>
        <li>مراجعة البلاغات أو منع الاستخدام المسيء للمنصة.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>4. مشاركة البيانات</h2>
    <p>
        لا نبيع بياناتك الشخصية. قد تظهر بعض بيانات مقدم الخدمة للعامة مثل الاسم التجاري،
        رقم الهاتف، الواتساب، المدينة، الصور، والوصف، لأن هذا هو الغرض الأساسي من المنصة.
    </p>
    <p>
        قد نشارك البيانات فقط عند الحاجة لتشغيل المنصة، أو عند وجود طلب قانوني، أو لحماية حقوق المنصة والمستخدمين.
    </p>
</div>

<div class="legal-section">
    <h2>5. حماية مقدمي الخدمات من الإزعاج</h2>
    <p>
        نحن ملتزمون بحماية مقدمي الخدمات من التحرش والإزعاج والسلوك المسيء.
        لا يجوز للمستخدمين تهديد أو مضايقة أو إرسال رسائل مسيئة أو مزعجة إلى مقدمي الخدمات.
    </p>
    <p>
        إذا تعرض مقدم خدمة للتحرش أو تلقى تهديدات أو رسائل مسيئة، يمكنه الإبلاغ عن ذلك لفريق دلني.
        قد نتخذ إجراءات ضد المستخدمين الذين ينتهكون هذه السياسة، بما في ذلك حظر الحساب.
    </p>
</div>

<div class="legal-section">
    <h2>6. التواصل خارج دلني</h2>
    <p>
        عند الضغط على رقم الهاتف أو واتساب أو أي رابط خارجي، قد تنتقل إلى تطبيق أو موقع خارج دلني.
        نحن لا نتحكم في سياسات الخصوصية أو طريقة استخدام البيانات خارج منصتنا.
    </p>
</div>

<div class="legal-section">
    <h2>7. حماية البيانات</h2>
    <p>
        نستخدم إجراءات مناسبة لحماية البيانات من الوصول غير المصرح به قدر الإمكان.
        ومع ذلك، لا توجد منصة إلكترونية يمكنها ضمان حماية كاملة بنسبة 100%.
    </p>
</div>

<div class="legal-section">
    <h2>8. التغييرات على السياسة</h2>
    <p>
        قد نقوم بتحديث هذه السياسة من وقت لآخر. استمرارك في استخدام المنصة بعد التحديث يعني موافقتك على النسخة الجديدة.
    </p>
</div>

<div class="legal-section">
    <h2>9. التواصل معنا</h2>
    <p>
        لأي سؤال بخصوص الخصوصية أو بياناتك، يمكنك التواصل مع فريق دلني عبر وسائل التواصل المتاحة في المنصة.
    </p>
</div>
@endsection

```

## public\legal\terms.blade.php

```blade
@extends('public.legal_layout')

@section('title', 'شروط الاستخدام - ' . config('app.name'))
@section('meta_description', 'شروط استخدام منصة دلني.')

@section('content')
<h1>شروط الاستخدام</h1>

<div class="legal-card-meta">
    آخر تحديث: {{ now()->format('d/m/Y') }}
</div>

<div class="legal-section">
    <h2>1. قبول الشروط</h2>
    <p>
        باستخدامك لمنصة دلني، فإنك توافق على الالتزام بهذه الشروط.
        إذا كنت لا توافق عليها، يرجى عدم استخدام المنصة.
    </p>
</div>

<div class="legal-section">
    <h2>2. طبيعة المنصة</h2>
    <p>
        دلني منصة دليل إلكتروني تعرض مقدمي خدمات ومعلوماتهم بهدف تسهيل الوصول إليهم.
        دلني ليست طرفًا في الاتفاق أو التعامل الذي يتم بين المستخدم ومقدم الخدمة.
    </p>
</div>

<div class="legal-section">
    <h2>3. مسؤولية المستخدم</h2>
    <ul>
        <li>استخدام المنصة بطريقة قانونية ومحترمة.</li>
        <li>التحقق من مقدم الخدمة قبل الاتفاق معه.</li>
        <li>عدم إرسال بلاغات أو تقييمات كاذبة أو مسيئة.</li>
        <li>عدم محاولة اختراق المنصة أو تعطيلها أو إساءة استخدامها.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>4. مسؤولية مقدم الخدمة</h2>
    <ul>
        <li>تقديم معلومات صحيحة وحديثة عن النشاط والخدمات.</li>
        <li>عدم نشر صور أو بيانات مضللة.</li>
        <li>الالتزام بالاتفاقات التي تتم مع العملاء خارج المنصة.</li>
        <li>تحمل مسؤولية جودة الخدمة والأسعار والتعامل مع العملاء.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>5. المحتوى الممنوع</h2>
    <p>يمنع نشر أو إرسال أي محتوى:</p>
    <ul>
        <li>مسيء أو تهديدي أو يحرض على الكراهية.</li>
        <li>مخالف للقانون أو الآداب العامة.</li>
        <li>ينتهك حقوق الآخرين أو يستخدم صورهم دون إذن.</li>
        <li>مضلل أو احتيالي أو يحتوي على معلومات غير صحيحة.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>6. التقييمات والمراجعات</h2>
    <p>
        يجوز للمستخدمين إرسال تقييمات عن تجربتهم. تحتفظ دلني بحق إخفاء أو حذف أي تقييم نراه مسيئًا،
        غير حقيقي، مكررًا، أو مخالفًا لشروط المنصة.
    </p>
</div>

<div class="legal-section">
    <h2>7. إدارة الحسابات والمحتوى</h2>
    <p>
        يحق لإدارة دلني تعديل أو إخفاء أو حذف أي حساب أو محتوى أو ملف مقدم خدمة إذا كان مخالفًا للشروط،
        أو يحتوي على معلومات غير دقيقة، أو يسبب ضررًا لتجربة المستخدمين.
    </p>
</div>

<div class="legal-section">
    <h2>8. الاشتراكات والظهور في المنصة</h2>
    <p>
        قد تكون بعض خدمات الظهور داخل دلني مدفوعة لمقدمي الخدمات.
        عدم دفع الرسوم أو انتهاء الاشتراك قد يؤدي إلى إخفاء الملف أو تقليل ظهوره داخل المنصة.
    </p>
</div>

<div class="legal-section">
    <h2>9. التعامل خارج المنصة</h2>
    <p>
        أي تواصل أو اتفاق أو دفع يتم بين المستخدم ومقدم الخدمة عبر الهاتف أو واتساب أو خارج دلني
        يكون مسؤولية الطرفين فقط.
    </p>
</div>

<div class="legal-section">
    <h2>10. تعديل الشروط</h2>
    <p>
        قد نقوم بتعديل هذه الشروط عند الحاجة. استمرار استخدامك للمنصة بعد التعديل يعني موافقتك على الشروط الجديدة.
    </p>
</div>

<div class="legal-section">
    <h2>11. القانون الحاكم</h2>
    <p>
        تخضع هذه الشروط للقوانين المعمول بها في دولة ليبيا، ما لم ينص القانون على غير ذلك.
    </p>
</div>
@endsection

```

## public\legal_layout.blade.php

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', '')">

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #F1620F;
            --navy: #0B1A34;
            --bg: #FCFBFB;
            --surface: #FFFFFF;
            --border: #E7E7E7;
            --muted: #5D5959;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Cairo', system-ui, sans-serif;
            background: var(--bg);
            color: var(--navy);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; }
        img, svg { max-width: 100%; }

        .container {
            width: min(100% - 2rem, 1240px);
            margin-inline: auto;
        }

        /* Header */
        .legal-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .legal-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 70px;
            gap: 1.5rem;
        }

        .legal-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 900;
            color: var(--navy);
            letter-spacing: -0.02em;
            flex-shrink: 0;
        }

        .legal-logo img {
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }

        .legal-tabs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legal-tab {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .legal-tab:hover,
        .legal-tab.active {
            color: var(--primary);
            background: rgba(241, 98, 15, 0.08);
        }

        /* Main Content */
        .legal-main {
            padding: 2.5rem 0;
        }

        .legal-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
        }

        .legal-card h1 {
            font-size: clamp(1.75rem, 4vw, 2.2rem);
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .legal-card-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 600;
        }

        /* Content Sections */
        .legal-section {
            margin-bottom: 2rem;
        }

        .legal-section:last-child {
            margin-bottom: 0;
        }

        .legal-section h2 {
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .legal-section p {
            font-size: 0.95rem;
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .legal-section p:last-child {
            margin-bottom: 0;
        }

        .legal-section ul,
        .legal-section ol {
            padding-inline-start: 1.5rem;
            margin: 1rem 0;
        }

        .legal-section li {
            font-size: 0.95rem;
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 0.6rem;
            font-weight: 500;
        }

        .legal-section strong {
            color: var(--navy);
            font-weight: 700;
        }

        .legal-section a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .legal-section a:hover {
            text-decoration: underline;
        }

        /* Footer */
        .legal-footer {
            padding: 2rem 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--border);
            margin-top: 3rem;
        }

        .legal-footer a {
            color: var(--muted);
            text-decoration: none;
            font-weight: 700;
        }

        .legal-footer a:hover {
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .legal-header__inner {
                min-height: 64px;
            }

            .legal-logo {
                font-size: 1rem;
            }

            .legal-logo img {
                width: 32px;
                height: 32px;
            }

            .legal-tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                flex: 1;
                gap: 0.3rem;
            }

            .legal-tab {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .legal-card {
                padding: 1.25rem;
            }

            .legal-card h1 {
                font-size: 1.5rem;
            }

            .legal-card-meta {
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                gap: 0.75rem;
                flex-wrap: wrap;
            }

            .legal-section {
                margin-bottom: 1.5rem;
            }

            .legal-section h2 {
                font-size: 1.05rem;
                margin-bottom: 0.75rem;
            }

            .legal-section p,
            .legal-section li {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                width: min(100% - 1rem, 1240px);
            }

            .legal-header__inner {
                min-height: 60px;
                gap: 1rem;
            }

            .legal-logo {
                font-size: 0.95rem;
            }

            .legal-logo img {
                width: 30px;
                height: 30px;
            }

            .legal-tab {
                padding: 0.35rem 0.7rem;
                font-size: 0.75rem;
            }

            .legal-card {
                padding: 1rem;
            }

            .legal-card h1 {
                font-size: 1.35rem;
                margin-bottom: 0.4rem;
            }

            .legal-card-meta {
                font-size: 0.8rem;
                margin-bottom: 1.25rem;
                padding-bottom: 0.75rem;
            }

            .legal-section {
                margin-bottom: 1.25rem;
            }

            .legal-section h2 {
                font-size: 1rem;
                margin-bottom: 0.6rem;
            }

            .legal-section p,
            .legal-section li {
                font-size: 0.88rem;
                line-height: 1.7;
            }
        }
    </style>
</head>
<body>

<header class="legal-header">
    <div class="container">
        <div class="legal-header__inner">
            <a href="{{ route('home') }}" class="legal-logo">
                <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('app.name') }}">
                <span>دلني</span>
            </a>

            <nav class="legal-tabs">
                <a href="{{ route('privacy') }}"
                   class="legal-tab {{ request()->routeIs('privacy') ? 'active' : '' }}">
                    الخصوصية
                </a>
                <a href="{{ route('terms') }}"
                   class="legal-tab {{ request()->routeIs('terms') ? 'active' : '' }}">
                    الشروط
                </a>
                <a href="{{ route('disclaimer') }}"
                   class="legal-tab {{ request()->routeIs('disclaimer') ? 'active' : '' }}">
                    إخلاء
                </a>
            </nav>
        </div>
    </div>
</header>

<main class="legal-main">
    <div class="container">
        <article class="legal-card">
            @yield('content')
        </article>
    </div>
</main>

<footer class="legal-footer">
    <div class="container">
        © {{ date('Y') }} دلني
        <span style="opacity: 0.3; margin: 0 0.5rem;">•</span>
        <a href="{{ route('privacy') }}">الخصوصية</a>
        <span style="opacity: 0.3; margin: 0 0.5rem;">•</span>
        <a href="{{ route('terms') }}">الشروط</a>
        <span style="opacity: 0.3; margin: 0 0.5rem;">•</span>
        <a href="{{ route('disclaimer') }}">إخلاء</a>
    </div>
</footer>

@stack('scripts')
</body>
</html>

```

## public\provider.blade.php

```blade
@extends('public.layout')

@section('title', ($profile->business_name ?? $profile->user?->name ?? 'مزود خدمة') . ' - ' . config('app.name'))

@section('content')
@php
    $businessName = $profile->business_name ?? $profile->user?->name ?? 'مزود خدمة';
    $rating = (float) ($profile->stats?->rating_avg ?? 0);
    $reviewsCount = (int) ($profile->stats?->reviews_count ?? 0);

    $logo = $profile->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($profile->logo) : null;
    $cover = $profile->cover_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($profile->cover_image) : null;

    $categoryName = $profile->category ? ($profile->category->localized_name ?? $profile->category->name) : null;
    $cityName = $profile->city ? ($profile->city->localized_name ?? $profile->city->name) : null;

    $phoneNumber = $profile->phone ? preg_replace('/\s+/', '', $profile->phone) : null;
    $whatsappNumber = $profile->whatsapp ? preg_replace('/[^0-9]/', '', $profile->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وصلت لملفك عبر دلني وأرغب بالاستفسار عن الخدمة.');

    $portfolioItems = ($portfolioItems ?? collect())->take(2);
    $reviews = $reviews ?? collect();
    $credentials = $credentials ?? ($profile->credentials ?? collect());
@endphp

<section class="profile-hero">
    @if($cover)
        <img src="{{ $cover }}" alt="{{ $businessName }}" class="profile-cover">
    @endif

    <div class="profile-hero__overlay">
        <div class="container">
            <div class="profile-head" style="align-items: flex-start;">
                <div class="profile-logo" style="flex-shrink: 0;">
                    @if($logo)
                        <img src="{{ $logo }}" alt="{{ $businessName }}" style="width: 400px; height: 400px; object-fit: cover;">
                    @else
                        <span>{{ mb_substr($businessName, 0, 1) }}</span>
                    @endif
                </div>

                <div class="profile-intro">
                    <h1>{{ $businessName }}</h1>

                    @if($profile->provider_type)
                        <p>{{ $profile->provider_type }}</p>
                    @endif

                    <div class="profile-meta">
                        @if($categoryName)
                            <span><x-render-icon icon="heroicon-o-briefcase" /> {{ $categoryName }}</span>
                        @endif

                        @if($cityName)
                            <span><x-render-icon icon="heroicon-o-map-pin" /> {{ $cityName }}</span>
                        @endif

                        @if($profile->experience_years)
                            <span>{{ $profile->experience_years }} سنوات خبرة</span>
                        @endif

                        @if($profile->offers_remote_work)
                            <span><x-render-icon icon="heroicon-o-globe-alt" /> عن بعد</span>
                        @endif
                    </div>

                    <div class="profile-rating">
                        <span class="stars">
                            @for($i = 1; $i <= 5; $i++)
                                <b class="{{ $i <= round($rating) ? '' : 'is-muted' }}">★</b>
                            @endfor
                        </span>
                        <strong>{{ number_format($rating, 1) }}</strong>
                        <a href="#reviews">{{ $reviewsCount }} تقييم</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="profile-jumpbar">
    <div class="container">
        <nav>
            @if($profile->bio)<a href="#about">نبذة</a>@endif
            @if($portfolioItems->isNotEmpty())<a href="#portfolio">الأعمال</a>@endif
            @if($credentials->isNotEmpty())<a href="#credentials">الشهادات</a>@endif
            <a href="#reviews">التقييمات</a>
            <a href="#contact">التواصل</a>
        </nav>
    </div>
</div>

<section class="profile-page">
    <div class="container">
        <div class="profile-layout">
            <main class="profile-main">
                @if($profile->bio)
                    <section id="about" class="profile-card">
                        <div class="section-head">
                            <span>نبذة</span>
                            <h2>عن مقدم الخدمة</h2>
                        </div>

                        <p class="profile-text">{{ $profile->bio }}</p>
                    </section>
                @endif

                @if($profile->subcategories->isNotEmpty() || $profile->service_area_note || $profile->offers_remote_work || $cityName)
                    <section class="profile-card">
                        <div class="section-head">
                            <span>الخدمات</span>
                            <h2>ماذا يقدم؟</h2>
                        </div>

                        @if($profile->subcategories->isNotEmpty())
                            <div class="tag-list">
                                @foreach($profile->subcategories as $subcategory)
                                    <span>{{ $subcategory->localized_name ?? $subcategory->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="info-grid">
                            @if($cityName)
                                <div>
                                    <strong>المدينة</strong>
                                    <span>{{ $cityName }}</span>
                                </div>
                            @endif

                            @if($profile->offers_remote_work)
                                <div>
                                    <strong>خدمة عن بعد</strong>
                                    <span>متاحة</span>
                                </div>
                            @endif

                            @if($profile->service_area_note)
                                <div class="wide">
                                    <strong>نطاق الخدمة</strong>
                                    <span>{{ $profile->service_area_note }}</span>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                @if($portfolioItems->isNotEmpty())
                    <section id="portfolio" class="profile-card">
                        <div class="section-head split">
                            <div>
                                <span>الأعمال</span>
                                <h2>مشاريع مختارة</h2>
                            </div>
                            <small>مشروعان فقط، وكل مشروع يحتوي معرض صور</small>
                        </div>

                        <div class="project-row">
                            @foreach($portfolioItems as $item)
                                @php
                                    $images = $item->images?->sortBy('sort_order') ?? collect();
                                    $firstImage = $images->first();
                                @endphp

                                <article class="project-card" data-project-card>
                                    <div class="project-slider" data-slider>
                                        @if($images->isNotEmpty())
                                            @foreach($images as $index => $image)
                                                <img
                                                    src="{{ Storage::disk('public')->url($image->path) }}"
                                                    alt="{{ $image->alt ?: $item->title }}"
                                                    class="{{ $index === 0 ? 'is-active' : '' }}"
                                                    data-slide
                                                >
                                            @endforeach
                                        @else
                                            <div class="project-empty">
                                                <x-render-icon icon="heroicon-o-photo" />
                                            </div>
                                        @endif

                                        @if($images->count() > 1)
                                            <button type="button" class="slider-btn slider-prev" data-prev>‹</button>
                                            <button type="button" class="slider-btn slider-next" data-next>›</button>
                                            <span class="slider-count">
                                                <b data-current>1</b> / {{ $images->count() }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="project-body">
                                        <h3>{{ $item->title }}</h3>

                                        @if($item->short_description)
                                            <p>{{ $item->short_description }}</p>
                                        @elseif($item->description)
                                            <p>{{ Str::limit(strip_tags($item->description), 130) }}</p>
                                        @endif

                                        <div class="project-actions">
                                            @if($images->count() > 1)
                                                <button type="button" data-next>
                                                    تصفح الصور
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($credentials->isNotEmpty())
                    <section id="credentials" class="profile-card">
                        <div class="section-head split">
                            <div>
                                <span>الثقة</span>
                                <h2>الشهادات والاعتمادات</h2>
                            </div>

                            <small>{{ $credentials->count() }} شهادة</small>
                        </div>

                        <div class="cert-strip">
                            @foreach($credentials as $credential)
                                <article class="cert-card">
                                    <h3>{{ $credential->title }}</h3>

                                    @if($credential->issuer)
                                        <p>{{ $credential->issuer }}</p>
                                    @endif

                                    <div class="cert-meta">
                                        @if($credential->issue_date)
                                            <span>{{ optional($credential->issue_date)->format('Y') }}</span>
                                        @endif

                                        @if($credential->verification_url)
                                            <a href="{{ $credential->verification_url }}" target="_blank" rel="noopener">
                                                تحقق
                                            </a>
                                        @endif
                                    </div>

                                    @if($credential->notes)
                                        <small>{{ Str::limit($credential->notes, 120) }}</small>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section id="reviews" class="profile-card reviews-card">
                    <div class="section-head split">
                        <div>
                            <span>التقييمات</span>
                            <h2>آراء العملاء</h2>
                        </div>

                        <div class="review-score">
                            <strong>{{ number_format($rating, 1) }}</strong>
                            <span>{{ $reviewsCount }} تقييم</span>
                        </div>
                    </div>

                    @if(!auth()->check())
                        <div class="review-notice">
                            <p>سجل الدخول لكتابة تقييمك بعد التعامل مع مقدم الخدمة.</p>
                            <div>
                                <a href="{{ route('login') }}">تسجيل الدخول</a>
                                <a href="{{ route('register') }}">إنشاء حساب</a>
                            </div>
                        </div>
                    @elseif(!auth()->user()->hasRole('user'))
                        <div class="review-notice">مزودو الخدمات لا يمكنهم كتابة تقييمات.</div>
                    @elseif($profile->user_id === auth()->id())
                        <div class="review-notice">لا يمكنك تقييم ملفك الخاص.</div>
                    @else
                        <form method="POST" action="{{ route('review.store', $profile) }}" class="review-form">
                            @csrf

                            <div>
                                <label for="rating">التقييم</label>
                                <select id="rating" name="rating" required>
                                    <option value="">اختر التقييم</option>
                                    @for($r = 5; $r >= 1; $r--)
                                        <option value="{{ $r }}" @selected(old('rating') == $r)>{{ $r }} / 5</option>
                                    @endfor
                                </select>
                            </div>

                            <div>
                                <label for="comment">رأيك</label>
                                <textarea id="comment" name="comment" rows="4" maxlength="2000" placeholder="شارك تجربتك باختصار...">{{ old('comment') }}</textarea>
                            </div>

                            <button type="submit">إرسال التقييم</button>
                        </form>
                    @endif

                    @php
                        $sortedReviews = $reviews->sortByDesc('created_at')->values();
                    @endphp

                    <div class="reviews-list" id="reviewsList">
                        @forelse($sortedReviews as $index => $review)
                            <article class="review-item {{ $index >= 3 ? 'is-hidden-review' : '' }}" data-review-item>
                                <div class="review-top">
                                    <strong>{{ $review->user?->name ?? $review->reviewer_name ?? 'مستخدم دلني' }}</strong>

                                    <span>
                                        @for($i = 1; $i <= 5; $i++)
                                            <b class="{{ $i <= (int) $review->rating ? '' : 'is-muted' }}">★</b>
                                        @endfor
                                    </span>
                                </div>

                                @if($review->comment)
                                    <p>{{ $review->comment }}</p>
                                @endif

                                @if($review->created_at)
                                    <small>{{ $review->created_at->diffForHumans() }}</small>
                                @endif
                            </article>
                        @empty
                            <div class="empty-reviews">
                                <h3>لا توجد تقييمات بعد</h3>
                                <p>ستظهر تقييمات العملاء هنا بعد اعتمادها.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($sortedReviews->count() > 3)
                        <button type="button" class="show-all-reviews-btn" id="showAllReviewsBtn">
                            عرض كل التقييمات
                        </button>
                    @endif
                </section>
            </main>

            <aside id="contact" class="profile-sidebar">
                <div class="contact-panel">
                    <h2>تواصل بسرعة</h2>
                    <p>ابدأ من هنا، أو انتقل مباشرة للتقييمات قبل التواصل.</p>

                    <div class="contact-actions">
                        @if($whatsappNumber)
                            <a class="is-whatsapp" href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener">
                                واتساب
                            </a>
                        @endif

                        @if($phoneNumber)
                            <a href="tel:{{ $phoneNumber }}">اتصال</a>
                        @endif

                        @if($profile->map_url)
                            <a href="{{ $profile->map_url }}" target="_blank" rel="noopener">الموقع</a>
                        @endif

                        <a href="#reviews" class="is-review">شوف التقييمات</a>
                    </div>

                    <div class="contact-stats">
                        <div>
                            <strong>{{ number_format($rating, 1) }}</strong>
                            <span>التقييم</span>
                        </div>

                        <div>
                            <strong>{{ $reviewsCount }}</strong>
                            <span>مراجعات</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-project-card]').forEach(function (card) {
            let slides = Array.from(card.querySelectorAll('[data-slide]'));
            let currentLabel = card.querySelector('[data-current]');
            let index = 0;

            function show(nextIndex) {
                if (!slides.length) return;
                index = (nextIndex + slides.length) % slides.length;

                slides.forEach(function (slide, i) {
                    slide.classList.toggle('is-active', i === index);
                });

                if (currentLabel) currentLabel.textContent = index + 1;
            }

            card.querySelectorAll('[data-next]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    show(index + 1);
                });
            });

            card.querySelectorAll('[data-prev]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    show(index - 1);
                });
            });
        });

        let showReviewsBtn = document.getElementById('showAllReviewsBtn');

        if (showReviewsBtn) {
            showReviewsBtn.addEventListener('click', function () {
                document.querySelectorAll('[data-review-item].is-hidden-review').forEach(function (item) {
                    item.classList.remove('is-hidden-review');
                });

                showReviewsBtn.remove();
            });
        }
    });
</script>

<style>
    .profile-hero {
        position: relative;
        min-height: 390px;
        overflow: hidden;
        background: linear-gradient(135deg, #0B1A34, #14284d);
    }

    .profile-cover {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: saturate(.92);
    }

    .profile-hero__overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: end;
        padding: 3rem 0;
        background: linear-gradient(to top, rgba(11,26,52,.96), rgba(11,26,52,.68), rgba(11,26,52,.34));
    }

    .profile-head {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: end;
        gap: 1.25rem;
        color: #fff;
    }

    @media (max-width: 900px) {
        .profile-head {
            gap: 1rem;
        }
    }

    .profile-logo {
        width: 128px;
        height: 128px;
        border-radius: 30px;
        overflow: hidden;
        border: 4px solid rgba(255,255,255,.9);
        background: #0B1A34;
        box-shadow: 0 22px 48px rgba(0,0,0,.24);
    }

    .profile-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-logo span {
        width: 100%;
        height: 100%;
        display: grid;
        place-items: center;
        color: #F1620F;
        font-size: 3rem;
        font-weight: 950;
    }

    .profile-intro h1 {
        margin: 0;
        font-size: clamp(2rem, 5vw, 3.5rem);
        line-height: 1.08;
        font-weight: 950;
        letter-spacing: -.055em;
    }

    .profile-intro p {
        margin: .45rem 0 0;
        color: rgba(255,255,255,.75);
        font-size: .95rem;
        font-weight: 750;
    }

    .profile-meta,
    .profile-rating {
        margin-top: .75rem;
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        align-items: center;
    }

    .profile-meta span {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        padding: .45rem .7rem;
        border-radius: 999px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.14);
        color: rgba(255,255,255,.86);
        font-size: .82rem;
        font-weight: 900;
    }

    .profile-meta svg {
        width: 17px;
        height: 17px;
        color: #F1620F;
    }

    .stars b,
    .review-item b {
        color: #F59E0B;
    }

    .is-muted {
        opacity: .25;
    }

    .profile-rating {
        color: rgba(255,255,255,.8);
        font-size: .88rem;
        font-weight: 850;
    }

    .profile-rating strong {
        color: #fff;
    }

    .profile-rating a {
        color: #ffb079;
        font-weight: 950;
        text-decoration: none;
    }

    .profile-jumpbar {
        position: sticky;
        top: 76px;
        z-index: 30;
        background: rgba(252,251,251,.9);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid #E7E7E7;
    }

    .profile-jumpbar nav {
        display: flex;
        gap: .55rem;
        overflow-x: auto;
        padding: .7rem 0;
    }

    .profile-jumpbar a {
        flex: 0 0 auto;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        padding: .5rem .8rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
        text-decoration: none;
        font-size: .84rem;
        font-weight: 900;
    }

    .profile-page {
        padding: 1.5rem 0 4rem;
        background: #FCFBFB;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 330px;
        gap: 1.25rem;
        align-items: start;
    }

    .profile-main {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-width: 0;
    }

    .profile-card {
        padding: 1.35rem;
        border-radius: 24px;
        background: #fff;
        border: 1px solid #E7E7E7;
        box-shadow: 0 12px 28px rgba(11,26,52,.045);
        scroll-margin-top: 140px;
    }

    .section-head {
        margin-bottom: .9rem;
    }

    .section-head.split {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
    }

    .section-head span {
        display: block;
        margin-bottom: .3rem;
        color: #F1620F;
        font-size: .8rem;
        font-weight: 950;
    }

    .section-head h2 {
        margin: 0;
        color: #0B1A34;
        font-size: 1.35rem;
        font-weight: 950;
        letter-spacing: -.035em;
    }

    .section-head small {
        color: #5D5959;
        font-size: .82rem;
        font-weight: 850;
    }

    .profile-text,
    .profile-card p {
        margin: 0;
        color: #5D5959;
        font-size: .95rem;
        line-height: 1.9;
        font-weight: 600;
    }

    .tag-list {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-bottom: 1rem;
    }

    .tag-list span {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        padding: .45rem .75rem;
        border-radius: 999px;
        background: rgba(241,98,15,.08);
        color: #F1620F;
        border: 1px solid rgba(241,98,15,.12);
        font-size: .84rem;
        font-weight: 900;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .info-grid div {
        padding: .9rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
    }

    .info-grid .wide {
        grid-column: 1 / -1;
    }

    .info-grid strong,
    .info-grid span {
        display: block;
    }

    .info-grid strong {
        color: #0B1A34;
        font-size: .9rem;
        font-weight: 950;
    }

    .info-grid span {
        margin-top: .2rem;
        color: #5D5959;
        font-size: .85rem;
        line-height: 1.7;
        font-weight: 700;
    }

    .project-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .9rem;
    }

    .project-card {
        overflow: hidden;
        border-radius: 22px;
        background: #fff;
        border: 1px solid #E7E7E7;
        box-shadow: 0 10px 24px rgba(11,26,52,.04);
    }

    .project-slider {
        position: relative;
        height: 235px;
        overflow: hidden;
        background: #0B1A34;
    }

    .project-slider img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transform: scale(1.02);
        transition: .25s ease;
    }

    .project-slider img.is-active {
        opacity: 1;
        transform: scale(1);
    }

    .project-empty {
        height: 100%;
        display: grid;
        place-items: center;
        color: rgba(255,255,255,.5);
    }

    .project-empty svg {
        width: 44px;
        height: 44px;
    }

    .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 999px;
        background: rgba(255,255,255,.92);
        color: #0B1A34;
        font-size: 1.6rem;
        line-height: 1;
        cursor: pointer;
        z-index: 2;
    }

    .slider-prev {
        inset-inline-start: .75rem;
    }

    .slider-next {
        inset-inline-end: .75rem;
    }

    .slider-count {
        position: absolute;
        bottom: .75rem;
        inset-inline-end: .75rem;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        padding: .35rem .65rem;
        border-radius: 999px;
        background: rgba(11,26,52,.72);
        color: #fff;
        font-size: .8rem;
        font-weight: 900;
        z-index: 2;
    }

    .project-body {
        padding: 1rem;
    }

    .project-body h3 {
        margin: 0 0 .45rem;
        color: #0B1A34;
        font-size: 1rem;
        line-height: 1.5;
        font-weight: 950;
    }

    .project-actions {
        margin-top: .85rem;
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .project-actions a,
    .project-actions button {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .55rem .75rem;
        border-radius: 13px;
        border: 1px solid rgba(241,98,15,.18);
        background: rgba(241,98,15,.08);
        color: #F1620F;
        font: inherit;
        font-size: .82rem;
        font-weight: 950;
        text-decoration: none;
        cursor: pointer;
    }

    .cert-strip {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(260px, 320px);
        gap: .75rem;
        overflow-x: auto;
        padding-bottom: .4rem;
        scroll-snap-type: x mandatory;
    }

    .cert-card {
        scroll-snap-align: start;
        padding: 1rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
    }

    .cert-card h3 {
        margin: 0 0 .35rem;
        color: #0B1A34;
        font-size: .95rem;
        line-height: 1.5;
        font-weight: 950;
    }

    .cert-card p {
        font-size: .84rem;
    }

    .cert-meta {
        margin-top: .6rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .6rem;
    }

    .cert-meta span,
    .cert-meta a {
        font-size: .78rem;
        font-weight: 950;
    }

    .cert-meta a {
        color: #F1620F;
        text-decoration: none;
    }

    .cert-card small {
        display: block;
        margin-top: .6rem;
        color: #5D5959;
        line-height: 1.7;
    }

    .reviews-card {
        border-color: rgba(241,98,15,.2);
    }

    .review-score {
        padding: .6rem .8rem;
        border-radius: 16px;
        background: rgba(241,98,15,.08);
        color: #F1620F;
        text-align: center;
    }

    .review-score strong,
    .review-score span {
        display: block;
    }

    .review-score strong {
        font-size: 1.25rem;
        font-weight: 950;
    }

    .review-score span {
        font-size: .75rem;
        font-weight: 900;
    }

    .review-notice,
    .review-form {
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
    }

    .review-notice div {
        margin-top: .75rem;
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .review-notice a,
    .review-form button {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .6rem .85rem;
        border-radius: 13px;
        border: 0;
        background: #F1620F;
        color: #fff;
        font: inherit;
        font-size: .84rem;
        font-weight: 950;
        text-decoration: none;
        cursor: pointer;
    }

    .review-form {
        display: grid;
        gap: .75rem;
    }

    .review-form label {
        display: block;
        margin-bottom: .35rem;
        color: #0B1A34;
        font-size: .84rem;
        font-weight: 950;
    }

    .review-form select,
    .review-form textarea {
        width: 100%;
        border: 1px solid #E7E7E7;
        border-radius: 15px;
        background: #fff;
        padding: .75rem;
        font: inherit;
        outline: none;
    }

    .reviews-list {
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    .is-hidden-review {
        display: none;
    }

    .show-all-reviews-btn {
        width: 100%;
        min-height: 44px;
        margin-top: .85rem;
        border: 1px solid rgba(241,98,15,.2);
        border-radius: 15px;
        background: rgba(241,98,15,.08);
        color: #F1620F;
        font: inherit;
        font-size: .88rem;
        font-weight: 950;
        cursor: pointer;
    }

    .show-all-reviews-btn:hover {
        background: rgba(241,98,15,.12);
    }

    .review-item {
        padding: 1rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
    }

    .review-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .45rem;
    }

    .review-top strong {
        color: #0B1A34;
        font-size: .92rem;
        font-weight: 950;
    }

    .review-item small {
        display: block;
        margin-top: .5rem;
        color: #5D5959;
        font-size: .75rem;
        font-weight: 800;
    }

    .empty-reviews {
        text-align: center;
        padding: 2rem 1rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px dashed #E7E7E7;
    }

    .empty-reviews h3 {
        margin: 0 0 .35rem;
        color: #0B1A34;
        font-size: 1.05rem;
        font-weight: 950;
    }

    .profile-sidebar {
        position: sticky;
        top: 130px;
    }

    .contact-panel {
        padding: 1.15rem;
        border-radius: 24px;
        background: #fff;
        border: 1px solid #E7E7E7;
        box-shadow: 0 16px 36px rgba(11,26,52,.07);
    }

    .contact-panel h2 {
        margin: 0;
        color: #0B1A34;
        font-size: 1.2rem;
        font-weight: 950;
        letter-spacing: -.035em;
    }

    .contact-panel p {
        margin: .4rem 0 1rem;
        color: #5D5959;
        font-size: .88rem;
        line-height: 1.7;
        font-weight: 600;
    }

    .contact-actions {
        display: flex;
        flex-direction: column;
        gap: .65rem;
    }

    .contact-actions a {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
        text-decoration: none;
        font-size: .92rem;
        font-weight: 950;
    }

    .contact-actions .is-whatsapp {
        background: #22C55E;
        border-color: #22C55E;
        color: #fff;
        box-shadow: 0 14px 28px rgba(34,197,94,.2);
    }

    .contact-actions .is-review {
        background: rgba(241,98,15,.08);
        border-color: rgba(241,98,15,.16);
        color: #F1620F;
    }

    .contact-stats {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #E7E7E7;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .7rem;
    }

    .contact-stats div {
        padding: .75rem;
        border-radius: 16px;
        background: #FCFBFB;
        text-align: center;
    }

    .contact-stats strong {
        display: block;
        color: #0B1A34;
        font-size: 1.15rem;
        font-weight: 950;
    }

    .contact-stats span {
        display: block;
        margin-top: .25rem;
        color: #5D5959;
        font-size: .75rem;
        font-weight: 850;
    }

    @media (max-width: 1080px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }

        .profile-sidebar {
            position: static;
            order: -1;
        }

        .contact-panel {
            display: grid;
            grid-template-columns: minmax(0, .8fr) minmax(0, 1.2fr);
            gap: 1rem;
            align-items: start;
        }

        .contact-stats {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 900px) {
        .profile-hero {
            min-height: 420px;
        }

        .profile-hero__overlay {
            padding: 2.5rem 0;
        }

        .profile-logo {
            width: 100px;
            height: 100px;
            border-radius: 22px;
            border-width: 3px;
        }

        .profile-intro h1 {
            font-size: clamp(1.75rem, 4vw, 2.8rem);
        }

        .profile-intro p {
            font-size: .88rem;
        }

        .profile-meta span,
        .profile-rating {
            font-size: .76rem;
            padding: .4rem .65rem;
        }
    }

    @media (max-width: 760px) {
        .profile-hero {
            min-height: 480px;
        }

        .profile-hero__overlay {
            padding: 2rem 0;
        }

        .profile-head {
            grid-template-columns: 1fr;
            text-align: center;
            justify-items: center;
            gap: .8rem;
        }

        .profile-logo {
            width: 90px;
            height: 90px;
            border-radius: 20px;
            border-width: 3px;
        }

        .profile-meta,
        .profile-rating {
            justify-content: center;
        }

        .profile-intro h1 {
            font-size: 1.5rem;
            margin-top: .3rem;
        }

        .profile-intro p {
            font-size: .82rem;
            margin-top: .25rem;
        }

        .profile-meta {
            margin-top: .5rem;
            gap: .35rem;
        }

        .profile-meta span {
            font-size: .7rem;
            padding: .35rem .6rem;
        }

        .profile-rating {
            margin-top: .5rem;
            font-size: .75rem;
            gap: .3rem;
        }

        .stars b {
            font-size: .9rem;
        }

        .profile-hero-actions {
            width: 100%;
            max-width: 360px;
        }

        .profile-card {
            padding: 1rem;
            border-radius: 22px;
        }

        .section-head.split {
            align-items: start;
            flex-direction: column;
        }

        .project-row,
        .info-grid {
            grid-template-columns: 1fr;
        }

        .project-slider {
            height: 215px;
        }

        .contact-panel {
            display: block;
        }

        .contact-stats {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .profile-jumpbar {
            top: 68px;
        }
    }

    @media (max-width: 480px) {
        .profile-hero {
            min-height: 420px;
        }

        .profile-hero__overlay {
            padding: 1.5rem 0;
        }

        .profile-head {
            gap: .6rem;
        }

        .profile-logo {
            width: 80px;
            height: 80px;
            border-radius: 18px;
            border-width: 2px;
        }

        .profile-logo span {
            font-size: 2.2rem;
        }

        .profile-intro h1 {
            font-size: 1.35rem;
            margin-top: .2rem;
        }

        .profile-intro p {
            font-size: .75rem;
        }

        .profile-meta {
            margin-top: .4rem;
            gap: .25rem;
        }

        .profile-meta span {
            font-size: .65rem;
            padding: .3rem .55rem;
            min-height: 30px;
            gap: .25rem;
        }

        .profile-meta svg {
            width: 14px;
            height: 14px;
        }

        .profile-rating {
            margin-top: .4rem;
            font-size: .7rem;
            gap: .25rem;
        }

        .profile-page {
            padding-top: 1rem;
        }

        .profile-jumpbar {
            top: 64px;
        }

        .cert-strip {
            grid-auto-columns: minmax(240px, 88vw);
        }

        .project-slider {
            height: 180px;
        }
    }
</style>
@endsection

```

## public\search.blade.php

```blade
@extends('public.layout')

@section('title', __('messages.public.search_results') . ' - ' . config('app.name'))

@section('content')
@php
    $total = $profiles?->total() ?? $profiles?->count() ?? 0;

    $activeCategory = request('category_id') ? $categories->find(request('category_id')) : null;
    $activeCity = request('city_id') ? $cities->find(request('city_id')) : null;

    $hasFilters = request()->filled('keyword')
        || request()->filled('category_id')
        || request()->filled('city_id')
        || request()->filled('provider_type')
        || request()->filled('remote')
        || request()->filled('sort');
@endphp

<div class="delni-search-container">
    {{-- Hero Header Section --}}
    <header class="search-hero">
        <div class="container">
            <div class="search-hero__badge">بحث دلني</div>
            <h1 class="search-hero__title">اعثر على مقدم الخدمة المناسب</h1>
            <p class="search-hero__subtitle">ابحث حسب الخدمة، الفئة، أو المدينة. النتائج واضحة ومرتبة بدون زحمة.</p>
        </div>
    </header>

    {{-- Search Filter Panel Section --}}
    <section class="search-panel-section">
        <div class="container">
            <form action="{{ route('public.search') }}" method="GET" class="search-card">

                {{-- Main Input Fields Grid --}}
                <div class="search-fields-grid">
                    <div class="search-field field-keyword">
                        <x-render-icon icon="heroicon-o-magnifying-glass" class="field-icon" />
                        <input
                            type="text"
                            name="keyword"
                            value="{{ request('keyword') }}"
                            placeholder="شن تحتاج؟ طبيب، محامي، مصمم..."
                            maxlength="100"
                        >
                    </div>

                    <div class="search-field">
                        <x-render-icon icon="heroicon-o-briefcase" class="field-icon" />
                        <select name="category_id">
                            <option value="">كل الفئات</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                    {{ $category->localized_name ?? $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="search-field">
                        <x-render-icon icon="heroicon-o-map-pin" class="field-icon" />
                        <select name="city_id">
                            <option value="">كل المدن</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                                    {{ $city->localized_name ?? $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(isset($providerTypes) && $providerTypes)
                        <div class="search-field">
                            <x-render-icon icon="heroicon-o-squares-2x2" class="field-icon" />
                            <select name="provider_type">
                                <option value="">كل الأنواع</option>
                                @foreach($providerTypes as $code => $name)
                                    <option value="{{ $code }}" @selected((string) request('provider_type') === (string) $code)>
                                        {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button type="submit" class="btn-search-submit">
                        <span>بحث</span>
                    </button>
                </div>

                {{-- Sub-bar Filter Controls --}}
                <div class="search-sub-bar">
                    <label class="toggle-checkbox">
                        <input type="checkbox" name="remote" value="1" @checked(request('remote') == 1)>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">يدعم العمل عن بعد</span>
                    </label>

                    <div class="sort-selector">
                        <span class="sort-label">ترتيب حسب:</span>
                        <select name="sort" aria-label="ترتيب النتائج">
                            <option value="" @selected(!request('sort'))>الأكثر صلة</option>
                            <option value="rating" @selected(request('sort') === 'rating')>الأعلى تقييماً</option>
                            <option value="reviews" @selected(request('sort') === 'reviews')>الأكثر مراجعات</option>
                            <option value="newest" @selected(request('sort') === 'newest')>الأحدث</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- Main Results Section --}}
    <main class="results-section">
        <div class="container">

            {{-- Dynamic Header Info --}}
            <div class="results-header">
                <div class="results-counter">
                    <span class="subtitle-label">مستندات العثور</span>
                    <h2 class="main-counter-title">
                        <strong>{{ number_format($total) }}</strong> مقدم خدمة متاح
                    </h2>
                </div>

                @if($hasFilters)
                    <a href="{{ route('public.search') }}" class="btn-clear-filters">
                        <x-render-icon icon="heroicon-o-trash" />
                        <span>مسح الفلاتر</span>
                    </a>
                @endif
            </div>

            {{-- Chip Tags for Applied Filters --}}
            @if($hasFilters)
                <div class="active-filter-chips">
                    @if(request('keyword'))
                        <span class="chip-tag">{{ request('keyword') }}</span>
                    @endif

                    @if($activeCategory)
                        <span class="chip-tag">{{ $activeCategory->localized_name ?? $activeCategory->name }}</span>
                    @endif

                    @if($activeCity)
                        <span class="chip-tag">{{ $activeCity->localized_name ?? $activeCity->name }}</span>
                    @endif

                    @if(request('remote'))
                        <span class="chip-tag accent">عن بعد</span>
                    @endif

                    @if(request('sort'))
                        <span class="chip-tag standard">
                            @switch(request('sort'))
                                @case('rating') الأعلى تقييماً @break
                                @case('reviews') الأكثر مراجعات @break
                                @case('newest') الأحدث @break
                                @default الأكثر صلة
                            @endswitch
                        </span>
                    @endif
                </div>
            @endif

            {{-- Providers Display Node --}}
            @if($profiles && $profiles->count() > 0)
                <div class="grid-wrapper">
                    <x-provider-grid :providers="$profiles" :columns="3" />
                </div>

                {{-- Pagination Nav Element --}}
                @if($profiles->hasPages())
                    <nav class="custom-pagination" aria-label="Pagination">
                        @if($profiles->onFirstPage())
                            <span class="pag-btn disabled">السابق</span>
                        @else
                            <a href="{{ $profiles->previousPageUrl() }}" class="pag-btn">السابق</a>
                        @endif

                        <span class="pag-status-info">
                            صفحة {{ $profiles->currentPage() }} من {{ $profiles->lastPage() }}
                        </span>

                        @if($profiles->hasMorePages())
                            <a href="{{ $profiles->nextPageUrl() }}" class="pag-btn">التالي</a>
                        @else
                            <span class="pag-btn disabled">التالي</span>
                        @endif
                    </nav>
                @endif
            @else
                {{-- Fallback Screen State --}}
                <div class="empty-state-wrapper">
                    <x-empty-state
                        icon="heroicon-o-magnifying-glass"
                        title="ما لقيناش نتائج"
                        message="جرّب كلمة أبسط، أو غيّر المدينة والفئة المحددة."
                        actionLabel="مسح فلاتر البحث"
                        actionUrl="{{ route('public.search') }}"
                    />
                </div>
            @endif
        </div>

        {{-- Provider CTA Section --}}
        <section class="provider-cta-section" style="margin-top: 3rem;">
            <div class="container">
                <div class="provider-cta-card">
                    <h2 class="cta-title">{{ __('messages.public.are_you_professional') }}</h2>
                    <p class="cta-description">{{ __('messages.public.join_marketplace_description') }}</p>
                    <a href="{{ route('contact') }}" class="cta-button">{{ __('messages.public.contact_us') }}</a>
                </div>
            </div>
        </section>
    </main>
</div>

<style>
    /* Custom Design Framework Base Tokens */
    :root {
        --brand-primary: #F1620F;
        --brand-primary-hover: #D7530A;
        --brand-dark: #0B1A34;
        --brand-dark-light: #1E2E4A;
        --bg-surface: #FFFFFF;
        --bg-canvas: #F8FAFC;
        --border-color: #E2E8F0;
        --text-main: #334155;
        --text-muted: #64748B;
        --transition-standard: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Overall Container Setting */
    .delni-search-container {
        min-height: 100vh;
        background-color: var(--bg-canvas);
        font-family: system-ui, -apple-system, sans-serif;
        color: var(--text-main);
    }

    /* Redesigned Minimalist Hero */
    .search-hero {
        background: linear-gradient(135deg, var(--brand-dark), var(--brand-dark-light));
        padding: 5rem 0 6.5rem;
        text-align: center;
        color: #FFFFFF;
    }

    .search-hero__badge {
        display: inline-block;
        background: rgba(241, 98, 15, 0.12);
        border: 1px solid rgba(241, 98, 15, 0.3);
        color: #FF9D66;
        padding: 0.35rem 1rem;
        border-radius: 100px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
    }

    .search-hero__title {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        margin: 0 0 1rem;
        line-height: 1.2;
    }

    .search-hero__subtitle {
        font-size: clamp(0.95rem, 1.5vw, 1.15rem);
        color: rgba(255, 255, 255, 0.75);
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Redesigned Floating Interface Form Card */
    .search-panel-section {
        margin-top: -3.5rem;
        position: relative;
        z-index: 10;
        padding-bottom: 2rem;
    }

    .search-card {
        background: var(--bg-surface);
        border-radius: 20px;
        box-shadow: 0 10px 30px -5px rgba(11, 26, 52, 0.08), 0 20px 40px -10px rgba(11, 26, 52, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        padding: 1.25rem;
    }

    /* Row Layout Rules for Main Filters Grid */
    .search-fields-grid {
        display: grid;
        grid-template-columns: 1.5fr repeat(auto-fit, minmax(180px, 1fr)) auto;
        gap: 0.75rem;
        align-items: center;
    }

    .search-field {
        background: var(--bg-canvas);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        height: 52px;
        display: flex;
        align-items: center;
        padding: 0 1rem;
        gap: 0.75rem;
        transition: var(--transition-standard);
    }

    .search-field:focus-within {
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 3px rgba(241, 98, 15, 0.12);
        background: #FFFFFF;
    }

    .search-field .field-icon {
        width: 20px;
        height: 20px;
        color: var(--text-muted);
        flex-shrink: 0;
    }

    .search-field input,
    .search-field select {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        color: var(--brand-dark);
        font-size: 0.95rem;
        font-weight: 500;
    }

    .search-field input::placeholder {
        color: #94A3B8;
    }

    /* Main Submit Button Refactor */
    .btn-search-submit {
        background: var(--brand-primary);
        color: #FFFFFF;
        border: none;
        height: 52px;
        padding: 0 2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-standard);
    }

    .btn-search-submit:hover {
        background: var(--brand-primary-hover);
        transform: translateY(-1px);
    }

    /* Isolated Functional Toolbar Footer Layer */
    .search-sub-bar {
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    /* Custom Toggle Switch */
    .toggle-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        user-select: none;
    }

    .toggle-checkbox input {
        display: none;
    }

    .toggle-switch {
        width: 40px;
        height: 22px;
        background: #CBD5E1;
        border-radius: 100px;
        position: relative;
        transition: var(--transition-standard);
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        background: #FFFFFF;
        border-radius: 50%;
        transition: var(--transition-standard);
    }

    .toggle-checkbox input:checked + .toggle-switch {
        background: var(--brand-primary);
    }

    .toggle-checkbox input:checked + .toggle-switch::after {
        left: calc(100% - 20px);
    }

    .toggle-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* Minimalist Sorting Layout */
    .sort-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sort-label {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .sort-selector select {
        border: 1px solid var(--border-color);
        background: #FFFFFF;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--brand-dark);
        outline: none;
        cursor: pointer;
    }

    /* Results Header Segment */
    .results-section {
        padding: 2rem 0 4rem;
    }

    .results-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        gap: 1rem;
    }

    .subtitle-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--brand-primary);
        font-weight: 700;
        display: block;
        margin-bottom: 0.25rem;
    }

    .main-counter-title {
        font-size: clamp(1.25rem, 2.5vw, 1.75rem);
        font-weight: 700;
        color: var(--brand-dark);
        margin: 0;
    }

    .main-counter-title strong {
        color: var(--brand-primary);
    }

    /* Action Filter Resets Link UI */
    .btn-clear-filters {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #EF4444;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: rgba(239, 68, 68, 0.06);
        transition: var(--transition-standard);
    }

    .btn-clear-filters:hover {
        background: rgba(239, 68, 68, 0.12);
    }

    .btn-clear-filters svg {
        width: 16px;
        height: 16px;
    }

    /* Filter Active Interactive Chips */
    .active-filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }

    .chip-tag {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        padding: 0.4rem 0.85rem;
        border-radius: 100px;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-main);
        display: inline-flex;
        align-items: center;
    }

    .chip-tag.accent {
        background: rgba(241, 98, 15, 0.06);
        border-color: rgba(241, 98, 15, 0.2);
        color: var(--brand-primary);
    }

    /* Pagination Module Element Styles */
    .custom-pagination {
        margin-top: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .pag-btn {
        height: 40px;
        padding: 0 1.25rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        color: var(--text-main);
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: var(--transition-standard);
    }

    .pag-btn:not(.disabled):hover {
        border-color: var(--brand-primary);
        color: var(--brand-primary);
        transform: translateY(-1px);
    }

    .pag-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: transparent;
    }

    .pag-status-info {
        font-size: 0.9rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Wrapper for Global Empty States components fallback context */
    .empty-state-wrapper {
        padding: 4rem 1rem;
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    /* Viewport Responsiveness Adjustments */
    @media (max-width: 1024px) {
        .search-fields-grid {
            grid-template-columns: 1fr 1fr;
        }
        .btn-search-submit {
            grid-column: span 2;
        }
    }

    @media (max-width: 640px) {
        .search-hero {
            padding: 3.5rem 0 5rem;
        }
        .search-panel-section {
            margin-top: -2.5rem;
        }
        .search-card {
            padding: 1rem;
        }
        .search-fields-grid {
            grid-template-columns: 1fr;
        }
        .btn-search-submit {
            grid-column: span 1;
        }
        .search-sub-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 1.25rem;
        }
        .sort-selector {
            width: 100%;
            justify-content: space-between;
        }
        .results-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .btn-clear-filters {
            width: 100%;
            justify-content: center;
        }
    }

    /* Provider CTA Section */
    .provider-cta-section {
        padding: 3rem 0;
        background: linear-gradient(135deg, rgba(241, 98, 15, 0.08), rgba(241, 98, 15, 0.04));
    }

    .provider-cta-card {
        background: var(--bg-surface);
        border: 2px solid var(--brand-primary);
        border-radius: 20px;
        padding: clamp(2rem, 5vw, 3rem);
        text-align: center;
        box-shadow: 0 10px 30px rgba(241, 98, 15, 0.1);
    }

    .cta-title {
        font-size: clamp(1.5rem, 4vw, 2.2rem);
        font-weight: 900;
        color: var(--brand-dark);
        margin-bottom: 1rem;
        letter-spacing: -0.03em;
    }

    .cta-description {
        font-size: clamp(0.9rem, 2vw, 1.05rem);
        color: var(--text-muted);
        margin-bottom: 1.5rem;
        line-height: 1.7;
        max-width: 500px;
        margin-inline: auto;
    }

    .cta-button {
        display: inline-block;
        background: var(--brand-primary);
        color: white;
        padding: 0.85rem 2rem;
        border-radius: 12px;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 700;
        transition: var(--transition-standard);
        border: 2px solid var(--brand-primary);
    }

    .cta-button:hover {
        background: transparent;
        color: var(--brand-primary);
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .provider-cta-section {
            padding: 2rem 0;
        }

        .provider-cta-card {
            padding: 1.5rem;
        }

        .cta-title {
            margin-bottom: 0.75rem;
        }

        .cta-description {
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
        }

        .cta-button {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
        }
    }
</style>
@endsection

```

## public\subcategory.blade.php

```blade
@extends('public.layout')

@section('title', $subcategory->localized_name . ' - ' . config('app.name'))

@section('content')
<div class="breadcrumb-nav-wrapper">
    <div class="container">
        <nav aria-label="breadcrumb" class="modern-breadcrumb">
            <a href="{{ route('home') }}" class="breadcrumb-link">{{ __('messages.public.home') }}</a>
            <span class="breadcrumb-divider">/</span>
            @if($category = $subcategory->category)
                <a href="{{ route('public.category', $category->slug) }}" class="breadcrumb-link">{{ $category->localized_name }}</a>
                <span class="breadcrumb-divider">/</span>
            @endif
            <span class="breadcrumb-current">{{ $subcategory->localized_name }}</span>
        </nav>
    </div>
</div>

<section class="archive-split-workspace">
    <div class="container">
        <div class="workspace-layout-grid">
            <aside class="workspace-sidebar-sticky">
                <x-search-filters :cities="$cities ?? null" />
            </aside>

            <main class="workspace-main-content">
                @if($profiles && $profiles->count() > 0)
                    <x-provider-grid :providers="$profiles" :columns="1" />

                    @if($profiles->hasPages())
                        <nav aria-label="pagination" class="pagination-wrapper">
                            {{ $profiles->appends(request()->query())->links('pagination::tailwind') }}
                        </nav>
                    @endif
                @else
                    <x-empty-state
                        title="{{ __('messages.public.no_providers_found') }}"
                        message="{{ __('messages.public.try_different_search') }}"
                        actionLabel="{{ __('messages.public.search') }}"
                        actionUrl="{{ route('public.search') }}"
                    />
                @endif
            </main>
        </div>
    </div>
</section>

<style>
    .breadcrumb-nav-wrapper {
        padding: 1rem 0;
        background: #FCFBFB;
        border-bottom: 1px solid #E7E7E7;
    }

    .modern-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .breadcrumb-link {
        color: #5D5959;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.18s ease;
    }

    .breadcrumb-link:hover {
        color: #F1620F;
    }

    .breadcrumb-divider {
        color: #E7E7E7;
        margin: 0 0.25rem;
    }

    .breadcrumb-current {
        color: #0B1A34;
        font-weight: 950;
    }

    .archive-split-workspace {
        padding: 2rem 0 4rem;
        background: #FCFBFB;
    }

    .workspace-layout-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    .workspace-sidebar-sticky {
        position: sticky;
        top: 100px;
    }

    .workspace-main-content {
        min-width: 0;
    }

    .pagination-wrapper {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 1024px) {
        .workspace-layout-grid {
            grid-template-columns: 1fr;
        }

        .workspace-sidebar-sticky {
            position: static;
            top: auto;
        }
    }

    @media (max-width: 768px) {
        .archive-split-workspace {
            padding: 1.5rem 0 3rem;
        }
    }

    @media (max-width: 640px) {
        .breadcrumb-nav-wrapper {
            padding: 0.75rem 0;
        }

        .modern-breadcrumb {
            font-size: 0.8rem;
            gap: 0.35rem;
        }

        .breadcrumb-divider {
            margin: 0 0.2rem;
        }
    }
</style>
@endsection

```

## public\top-rated.blade.php

```blade
@extends('public.layout')

@section('title', 'الأعلى تقييماً - ' . config('app.name'))

@section('content')
@php
    $providerCount = $providerCount ?? ($profiles?->total() ?? $profiles?->count() ?? 0);

    $activeCategory = request('category_id') ? $categories->find(request('category_id')) : null;
    $activeCity = request('city_id') ? $cities->find(request('city_id')) : null;

    $hasFilters = request()->filled('category_id') || request()->filled('city_id') || request()->filled('keyword');
@endphp

<div class="top-rated-page">
    <section class="top-rated-hero">
        <div class="container">
            <div class="top-rated-hero__inner">
                <div class="top-rated-hero__text">
                    <span class="top-rated-kicker">
                        ⭐ الأعلى ثقة في دلني
                    </span>

                    <h1>
                        مقدمو خدمات الناس
                        <span>يثقون فيهم</span>
                    </h1>

                    <p>
                        اكتشف مزودين حصلوا على تقييمات عالية من عملاء حقيقيين، وفلتر حسب المدينة أو الفئة بسرعة.
                    </p>
                </div>

                <div class="top-rated-hero__card">
                    <strong>{{ number_format($providerCount) }}</strong>
                    <span>مزود عالي التقييم</span>
                </div>
            </div>

            <form action="{{ route('public.top-rated') }}" method="GET" class="top-rated-search">
                <div class="top-rated-field top-rated-field--wide">
                    <x-render-icon icon="heroicon-o-magnifying-glass" />
                    <input
                        type="text"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        placeholder="ابحث باسم الخدمة أو المزود..."
                        maxlength="100"
                    >
                </div>

                <div class="top-rated-field">
                    <x-render-icon icon="heroicon-o-briefcase" />
                    <select name="category_id">
                        <option value="">كل الفئات</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                {{ $category->localized_name ?? $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="top-rated-field">
                    <x-render-icon icon="heroicon-o-map-pin" />
                    <select name="city_id">
                        <option value="">كل المدن</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                                {{ $city->localized_name ?? $city->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit">
                    بحث
                </button>
            </form>
        </div>
    </section>

    <section class="top-rated-body">
        <div class="container">
            <div class="top-rated-toolbar">
                <div>
                    <span>النتائج</span>
                    <h2>الأعلى تقييماً</h2>
                    <p>{{ number_format($providerCount) }} مزود مطابق</p>
                </div>

                @if($hasFilters)
                    <a href="{{ route('public.top-rated') }}">
                        مسح المرشحات
                    </a>
                @endif
            </div>

            @if($hasFilters)
                <div class="top-rated-chips">
                    @if(request('keyword'))
                        <span>{{ request('keyword') }}</span>
                    @endif

                    @if($activeCategory)
                        <span>{{ $activeCategory->localized_name ?? $activeCategory->name }}</span>
                    @endif

                    @if($activeCity)
                        <span>{{ $activeCity->localized_name ?? $activeCity->name }}</span>
                    @endif
                </div>
            @endif

            @if($profiles && $profiles->count() > 0)
                <x-provider-grid :providers="$profiles" :columns="3" />

                @if($profiles->hasPages())
                    <nav class="delni-pagination" aria-label="Pagination">
                        @if($profiles->onFirstPage())
                            <span class="delni-page-btn is-disabled">السابق</span>
                        @else
                            <a href="{{ $profiles->previousPageUrl() }}" class="delni-page-btn">السابق</a>
                        @endif

                        <span class="delni-page-info">
                            صفحة {{ $profiles->currentPage() }} من {{ $profiles->lastPage() }}
                        </span>

                        @if($profiles->hasMorePages())
                            <a href="{{ $profiles->nextPageUrl() }}" class="delni-page-btn">التالي</a>
                        @else
                            <span class="delni-page-btn is-disabled">التالي</span>
                        @endif
                    </nav>
                @endif
            @else
                <x-empty-state
                    icon="heroicon-o-star"
                    title="لا توجد نتائج"
                    message="لم نجد مزودين الأعلى تقييماً حسب المرشحات الحالية. جرّب مدينة أو فئة أخرى."
                    actionLabel="مسح المرشحات"
                    actionUrl="{{ route('public.top-rated') }}"
                />
            @endif
        </div>
    </section>
</div>

<style>
    .top-rated-page {
        background: #FCFBFB;
        min-height: 100vh;
    }

    .top-rated-hero {
        padding: clamp(2rem, 5vw, 3.5rem) 0 2rem;
        background:
            radial-gradient(circle at 15% 20%, rgba(241,98,15,.18), transparent 32%),
            linear-gradient(135deg, #0B1A34, #13264A);
        color: #fff;
    }

    .top-rated-hero__inner {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1.25rem;
    }

    .top-rated-kicker {
        display: inline-flex;
        margin-bottom: .9rem;
        padding: .42rem .8rem;
        border-radius: 999px;
        background: rgba(241,98,15,.16);
        border: 1px solid rgba(241,98,15,.26);
        color: #ffb079;
        font-size: .82rem;
        font-weight: 950;
    }

    .top-rated-hero h1 {
        max-width: 760px;
        margin: 0;
        font-size: clamp(2.1rem, 5.5vw, 4.2rem);
        line-height: 1.08;
        font-weight: 950;
        letter-spacing: -.06em;
    }

    .top-rated-hero h1 span {
        color: #F1620F;
    }

    .top-rated-hero p {
        max-width: 650px;
        margin: .9rem 0 0;
        color: rgba(255,255,255,.74);
        font-size: 1rem;
        line-height: 1.9;
        font-weight: 650;
    }

    .top-rated-hero__card {
        min-width: 170px;
        padding: 1rem;
        border-radius: 24px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.16);
        box-shadow: 0 18px 42px rgba(0,0,0,.16);
        text-align: center;
    }

    .top-rated-hero__card strong {
        display: block;
        font-size: 2rem;
        line-height: 1;
        font-weight: 950;
        color: #fff;
    }

    .top-rated-hero__card span {
        display: block;
        margin-top: .4rem;
        color: rgba(255,255,255,.72);
        font-size: .82rem;
        font-weight: 850;
    }

    .top-rated-search {
        margin-top: 1.5rem;
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(170px, .6fr) minmax(170px, .6fr) 130px;
        gap: .6rem;
        padding: .65rem;
        border-radius: 24px;
        background: rgba(255,255,255,.96);
        border: 1px solid rgba(255,255,255,.5);
        box-shadow: 0 24px 60px rgba(0,0,0,.2);
    }

    .top-rated-field {
        min-height: 54px;
        display: flex;
        align-items: center;
        gap: .65rem;
        padding-inline: .95rem;
        border-radius: 17px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
    }

    .top-rated-field svg {
        width: 20px;
        height: 20px;
        color: #F1620F;
        flex-shrink: 0;
    }

    .top-rated-field input,
    .top-rated-field select {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: #0B1A34;
        font: inherit;
        font-size: .9rem;
        font-weight: 850;
    }

    .top-rated-field input::placeholder {
        color: #9b9696;
    }

    .top-rated-search button {
        min-height: 54px;
        border: 0;
        border-radius: 17px;
        background: #F1620F;
        color: #fff;
        font: inherit;
        font-size: .92rem;
        font-weight: 950;
        cursor: pointer;
        box-shadow: 0 12px 24px rgba(241,98,15,.24);
    }

    .top-rated-body {
        padding: 1.6rem 0 3.5rem;
    }

    .top-rated-toolbar {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .top-rated-toolbar span {
        display: block;
        margin-bottom: .25rem;
        color: #F1620F;
        font-size: .8rem;
        font-weight: 950;
    }

    .top-rated-toolbar h2 {
        margin: 0;
        color: #0B1A34;
        font-size: clamp(1.35rem, 3vw, 2rem);
        line-height: 1.2;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .top-rated-toolbar p {
        margin: .3rem 0 0;
        color: #5D5959;
        font-size: .9rem;
        font-weight: 700;
    }

    .top-rated-toolbar a {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        padding: .6rem .9rem;
        border-radius: 999px;
        background: rgba(241,98,15,.08);
        border: 1px solid rgba(241,98,15,.14);
        color: #F1620F;
        text-decoration: none;
        font-size: .84rem;
        font-weight: 950;
    }

    .top-rated-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: 1rem;
    }

    .top-rated-chips span {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        padding: .45rem .75rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
        font-size: .82rem;
        font-weight: 900;
    }

    .delni-pagination {
        margin-top: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .delni-page-btn {
        min-height: 42px;
        padding: .65rem 1rem;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
        text-decoration: none;
        font-size: .9rem;
        font-weight: 900;
    }

    .delni-page-btn:hover {
        border-color: #F1620F;
        color: #F1620F;
    }

    .delni-page-btn.is-disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .delni-page-info {
        color: #5D5959;
        font-size: .9rem;
        font-weight: 850;
    }

    @media (max-width: 980px) {
        .top-rated-hero__inner {
            align-items: start;
            flex-direction: column;
        }

        .top-rated-hero__card {
            width: 100%;
            text-align: start;
        }

        .top-rated-search {
            grid-template-columns: 1fr;
        }

        .top-rated-toolbar {
            align-items: start;
            flex-direction: column;
        }
    }

    @media (max-width: 560px) {
        .top-rated-hero {
            padding: 1.75rem 0 1.3rem;
        }

        .top-rated-hero h1 {
            font-size: clamp(2rem, 11vw, 3rem);
        }

        .top-rated-search {
            border-radius: 21px;
        }

        .top-rated-field,
        .top-rated-search button {
            min-height: 50px;
            border-radius: 15px;
        }

        .top-rated-body {
            padding-top: 1rem;
        }
    }
</style>
@endsection

```

