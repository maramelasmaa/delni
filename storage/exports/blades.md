# Blade Files Export

**Generated:** 2026-06-09 21:08:52

## Table of Contents

- [auth\account-edit.blade.php](#auth-account-edit-blade-php)
- [auth\forgot-password.blade.php](#auth-forgot-password-blade-php)
- [auth\login.blade.php](#auth-login-blade-php)
- [auth\register.blade.php](#auth-register-blade-php)
- [auth\reset-password.blade.php](#auth-reset-password-blade-php)
- [auth\set-password.blade.php](#auth-set-password-blade-php)
- [components\category-nav.blade.php](#components-category-nav-blade-php)
- [components\city-nav.blade.php](#components-city-nav-blade-php)
- [components\contact-card.blade.php](#components-contact-card-blade-php)
- [components\empty-state.blade.php](#components-empty-state-blade-php)
- [components\footer-contact.blade.php](#components-footer-contact-blade-php)
- [components\heroicon-renderer.blade.php](#components-heroicon-renderer-blade-php)
- [components\icon.blade.php](#components-icon-blade-php)
- [components\optimized-image.blade.php](#components-optimized-image-blade-php)
- [components\provider-card.blade.php](#components-provider-card-blade-php)
- [components\provider-grid.blade.php](#components-provider-grid-blade-php)
- [components\render-icon.blade.php](#components-render-icon-blade-php)
- [components\search-filters.blade.php](#components-search-filters-blade-php)
- [components\sidebar-contact.blade.php](#components-sidebar-contact-blade-php)
- [dashboard.blade.php](#dashboard-blade-php)
- [emails\password-reset.blade.php](#emails-password-reset-blade-php)
- [emails\set-password.blade.php](#emails-set-password-blade-php)
- [errors\403.blade.php](#errors-403-blade-php)
- [errors\404.blade.php](#errors-404-blade-php)
- [errors\500.blade.php](#errors-500-blade-php)
- [errors\503.blade.php](#errors-503-blade-php)
- [errors\panel.blade.php](#errors-panel-blade-php)
- [filament\brand.blade.php](#filament-brand-blade-php)
- [icons\heroicon.blade.php](#icons-heroicon-blade-php)
- [layouts\auth.blade.php](#layouts-auth-blade-php)
- [onboarding-link.blade.php](#onboarding-link-blade-php)
- [public\category.blade.php](#public-category-blade-php)
- [public\city.blade.php](#public-city-blade-php)
- [public\home.blade.php](#public-home-blade-php)
- [public\layout.blade.php](#public-layout-blade-php)
- [public\legal\disclaimer.blade.php](#public-legal-disclaimer-blade-php)
- [public\legal\privacy.blade.php](#public-legal-privacy-blade-php)
- [public\legal\terms.blade.php](#public-legal-terms-blade-php)
- [public\legal_layout.blade.php](#public-legal_layout-blade-php)
- [public\provider.blade.php](#public-provider-blade-php)
- [public\search.blade.php](#public-search-blade-php)
- [public\subcategory.blade.php](#public-subcategory-blade-php)

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

    <!-- Success Alert -->
    @if (session('status'))
        <div class="forgot-alert forgot-alert-success">
            <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    <!-- Error Alert -->
    @if ($errors->any())
        <div class="forgot-alert forgot-alert-error">
            <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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

    <!-- Reset Form -->
    <form action="{{ route('password.email') }}" method="POST" class="forgot-form">
        @csrf

        <!-- Email Field -->
        <div class="forgot-field">
            <label for="email" class="field-label">البريد الإلكتروني</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="field-input @error('email') field-error @enderror"
                placeholder="you@example.com"
                autocomplete="email"
            />
            @error('email')
                <span class="field-error-text">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="forgot-submit">
            إرسال رابط إعادة التعيين
        </button>
    </form>

    <!-- Login Link -->
    <div class="forgot-footer">
        <p>تذكرت كلمة المرور؟</p>
        <a href="{{ route('login') }}" class="login-link">
            تسجيل الدخول
            <svg class="link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <style>
        .forgot-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        /* === FIELDS === */
        .forgot-field {
            display: flex;
            flex-direction: column;
        }

        .field-label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.9rem;
            font-weight: 800;
        }

        .field-input {
            width: 100%;
            height: 56px;
            padding: 0 1.1rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            outline: none;
            transition: 0.2s ease;
        }

        .field-input::placeholder {
            color: #94a3b8;
            font-weight: 600;
        }

        .field-input:focus {
            border-color: #ff7a1a;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(255, 122, 26, 0.14);
        }

        .field-input.field-error {
            border-color: #ef4444;
        }

        .field-input.field-error:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .field-error-text {
            display: block;
            margin-top: 0.35rem;
            color: #fca5a5;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* === ALERT === */
        .forgot-alert {
            margin-bottom: 1.8rem;
            padding: 1.2rem;
            border-radius: 16px;
            display: flex;
            gap: 1rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .forgot-alert-success {
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.24);
            color: #bbf7d0;
        }

        .forgot-alert-error {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.24);
            color: #fca5a5;
        }

        .alert-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .forgot-alert strong {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 900;
        }

        .forgot-alert-success strong {
            color: #bbf7d0;
        }

        .forgot-alert-error strong {
            color: #fca5a5;
        }

        .forgot-alert ul {
            margin: 0;
            padding-inline-start: 1.2rem;
            font-size: 0.9rem;
        }

        /* === SUBMIT BUTTON === */
        .forgot-submit {
            height: 58px;
            margin-top: 0.5rem;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 900;
            cursor: pointer;
            letter-spacing: -0.01em;
            box-shadow: 0 18px 42px rgba(255, 107, 26, 0.28);
            transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .forgot-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 56px rgba(255, 107, 26, 0.36);
        }

        .forgot-submit:active {
            transform: translateY(0);
        }

        /* === FOOTER === */
        .forgot-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        .forgot-footer p {
            margin: 0 0 0.6rem;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .login-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #ff7a1a;
            font-weight: 900;
            text-decoration: none;
            transition: 0.2s ease;
            letter-spacing: -0.01em;
        }

        .login-link:hover {
            color: #ff6b1a;
            gap: 0.6rem;
        }

        .link-icon {
            width: 18px;
            height: 18px;
            transition: 0.2s ease;
        }
    </style>
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
    أدخل بياناتك للمتابعة
@endsection

@section('content')

    <!-- Error Alert -->
    @if ($errors->any())
        <div class="login-alert login-alert-error">
            <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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

    <!-- Login Form -->
    <form action="{{ route('login') }}" method="POST" class="login-form">
        @csrf

        <!-- Email Field -->
        <div class="login-field">
            <label for="email" class="field-label">البريد الإلكتروني</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="field-input @error('email') field-error @enderror"
                placeholder="you@example.com"
                autocomplete="email"
            />
            @error('email')
                <span class="field-error-text">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password Field -->
        <div class="login-field">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label for="password" class="field-label" style="margin-bottom: 0;">كلمة المرور</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color: #ff7a1a; font-size: 0.9rem; text-decoration: none; font-weight: 600;">نسيت كلمة المرور؟</a>
                @endif
            </div>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="field-input @error('password') field-error @enderror"
                placeholder="••••••••"
                autocomplete="current-password"
            />
            @error('password')
                <span class="field-error-text">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="login-submit">
            تسجيل الدخول
        </button>
    </form>

    <!-- Register Link -->
    <div class="login-footer">
        <p>ليس لديك حساب؟</p>
        <a href="{{ route('register') }}" class="register-link">
            إنشاء حساب
            <svg class="link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <style>
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        /* === FIELDS === */
        .login-field {
            display: flex;
            flex-direction: column;
        }

        .field-label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.9rem;
            font-weight: 800;
        }

        .field-input {
            width: 100%;
            height: 56px;
            padding: 0 1.1rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            outline: none;
            transition: 0.2s ease;
        }

        .field-input::placeholder {
            color: #94a3b8;
            font-weight: 600;
        }

        .field-input:focus {
            border-color: #ff7a1a;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(255, 122, 26, 0.14);
        }

        .field-input.field-error {
            border-color: #ef4444;
        }

        .field-input.field-error:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .field-error-text {
            display: block;
            margin-top: 0.35rem;
            color: #fca5a5;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* === ALERT === */
        .login-alert {
            margin-bottom: 1.8rem;
            padding: 1.2rem;
            border-radius: 16px;
            display: flex;
            gap: 1rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-alert-error {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.24);
            color: #fca5a5;
        }

        .alert-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .login-alert strong {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 900;
            color: #fca5a5;
        }

        .login-alert ul {
            margin: 0;
            padding-inline-start: 1.2rem;
            font-size: 0.9rem;
            color: #fca5a5;
        }

        /* === SUBMIT BUTTON === */
        .login-submit {
            height: 58px;
            margin-top: 0.5rem;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 900;
            cursor: pointer;
            letter-spacing: -0.01em;
            box-shadow: 0 18px 42px rgba(255, 107, 26, 0.28);
            transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 56px rgba(255, 107, 26, 0.36);
        }

        .login-submit:active {
            transform: translateY(0);
        }

        /* === FOOTER === */
        .login-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        .login-footer p {
            margin: 0 0 0.6rem;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .register-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #ff7a1a;
            font-weight: 900;
            text-decoration: none;
            transition: 0.2s ease;
            letter-spacing: -0.01em;
        }

        .register-link:hover {
            color: #ff6b1a;
            gap: 0.6rem;
        }

        .link-icon {
            width: 18px;
            height: 18px;
            transition: 0.2s ease;
        }
    </style>
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
    ابدأ رحلتك للعثور على أفضل الخدمات في ليبيا
@endsection

@section('content')
    <div class="register-container">

        <!-- Error Alert -->
        @if ($errors->any())
            <div class="register-alert register-alert-error">
                <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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

        <!-- Registration Form -->
        <form action="{{ route('register') }}" method="POST" class="register-form">
            @csrf

            <!-- Name Field -->
            <div class="register-field">
                <label for="name" class="field-label">الاسم الكامل</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    class="field-input @error('name') field-error @enderror"
                    placeholder="أدخل اسمك الكامل"
                    autocomplete="name"
                >
                @error('name')
                    <span class="field-error-text">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="register-field">
                <label for="email" class="field-label">البريد الإلكتروني</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="field-input @error('email') field-error @enderror"
                    placeholder="you@example.com"
                    autocomplete="email"
                >
                @error('email')
                    <span class="field-error-text">{{ $message }}</span>
                @enderror
            </div>

            <!-- Phone Field -->
            <div class="register-field">
                <label for="phone" class="field-label">رقم الهاتف</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    required
                    class="field-input @error('phone') field-error @enderror"
                    placeholder="+218 91 123 4567"
                    autocomplete="tel"
                >
                @error('phone')
                    <span class="field-error-text">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password Fields Grid -->
            <div class="register-grid">
                <div class="register-field">
                    <label for="password" class="field-label">كلمة المرور</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="field-input @error('password') field-error @enderror"
                        placeholder="••••••••"
                        autocomplete="new-password"
                    >
                    @error('password')
                        <span class="field-error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="register-field">
                    <label for="password_confirmation" class="field-label">تأكيد كلمة المرور</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        class="field-input"
                        placeholder="••••••••"
                        autocomplete="new-password"
                    >
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="register-submit">
                إنشاء حساب
            </button>
        </form>

        <!-- Login Link -->
        <div class="register-footer">
            <p>هل لديك حساب بالفعل؟</p>
            <a href="{{ route('login') }}" class="login-link">
                تسجيل الدخول
                <svg class="link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <style>
        .register-container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }

        /* === HEADER (REMOVED - USE AUTH LAYOUT ONLY) === */
        .register-header {
            display: none !important;
        }

        .register-eyebrow {
            display: none !important;
        }

        .register-title {
            display: none !important;
        }

        .register-subtitle {
            display: none !important;
        }

        /* === ALERTS === */
        .register-alert {
            margin-bottom: 1.8rem;
            padding: 1.2rem;
            border-radius: 16px;
            display: flex;
            gap: 1rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-alert-error {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
        }

        .alert-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .register-alert strong {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 900;
            color: #be123c;
        }

        .register-alert ul {
            margin: 0;
            padding-inline-start: 1.2rem;
            font-size: 0.9rem;
            color: #be123c;
        }

        /* === FORM === */
        .register-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .register-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* === FIELDS === */
        .register-field {
            display: flex;
            flex-direction: column;
        }

        .field-label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.9rem;
            font-weight: 800;
        }

        .field-input {
            width: 100%;
            height: 56px;
            padding: 0 1.1rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            outline: none;
            transition: 0.2s ease;
        }

        .field-input::placeholder {
            color: #94a3b8;
            font-weight: 600;
        }

        .field-input:focus {
            border-color: #ff7a1a;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(255, 122, 26, 0.14);
        }

        .field-input.field-error {
            border-color: #ef4444;
        }

        .field-input.field-error:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .field-error-text {
            display: block;
            margin-top: 0.35rem;
            color: #dc2626;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* === SUBMIT BUTTON === */
        .register-submit {
            height: 58px;
            margin-top: 0.5rem;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 900;
            cursor: pointer;
            letter-spacing: -0.01em;
            box-shadow: 0 18px 42px rgba(255, 107, 26, 0.28);
            transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .register-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 56px rgba(255, 107, 26, 0.36);
        }

        .register-submit:active {
            transform: translateY(0);
        }

        /* === FOOTER === */
        .register-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .register-footer p {
            margin: 0 0 0.6rem;
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .login-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #ff7a1a;
            font-weight: 900;
            text-decoration: none;
            transition: 0.2s ease;
            letter-spacing: -0.01em;
        }

        .login-link:hover {
            color: #ff6b1a;
            gap: 0.6rem;
        }

        .link-icon {
            width: 18px;
            height: 18px;
            transition: 0.2s ease;
        }

        /* === RESPONSIVE === */
        @media (max-width: 640px) {
            .register-container {
                max-width: 100%;
                padding: 0 1rem;
            }

            .register-title {
                font-size: 1.5rem;
            }

            .register-grid {
                grid-template-columns: 1fr;
            }

            .register-header {
                margin-bottom: 2rem;
            }

            .register-form {
                gap: 0.9rem;
                margin-bottom: 2rem;
            }
        }
    </style>
@endsection

```

## auth\reset-password.blade.php

```blade
@extends('layouts.auth')

@section('title', __('auth.reset_password_title') . ' - ' . config('app.name'))

@section('auth_title')
    إنشاء<br/><span class="text-primary-500">كلمة مرور جديدة</span>
@endsection

@section('auth_subtitle')
    أدخل كلمة مرور جديدة قوية لحساب دلني الخاص بك.
@endsection

@section('content')
    <!-- Header -->
    <div class="mb-10">
        <h2 class="text-4xl font-black text-navy-800 mb-2">{{ __('auth.reset_password_title') }}</h2>
        <p class="text-gray-600 text-base leading-relaxed">{{ __('auth.reset_password_subtitle') }}</p>
    </div>

    <!-- Error Alert -->
    @if ($errors->any())
        <div class="bg-danger-50 border border-danger-200 rounded-lg p-4 mb-8">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-danger-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-danger-700">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Reset Password Form -->
    <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Hidden Token -->
        <input type="hidden" name="token" value="{{ $token }}">

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.email') }}
            </label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $email) }}"
                required
                class="input @error('email') border-danger-500 @enderror"
                placeholder="you@example.com"
                autocomplete="email"
                readonly
            />
            @error('email')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.new_password') }}
            </label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="input @error('password') border-danger-500 @enderror"
                placeholder="••••••••"
                autocomplete="new-password"
            />
            <p class="text-gray-500 text-xs mt-2">
                {{ __('auth.password_requirements') }}
            </p>
            @error('password')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Confirmation Field -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.confirm_password') }}
            </label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                class="input @error('password_confirmation') border-danger-500 @enderror"
                placeholder="••••••••"
                autocomplete="new-password"
            />
            @error('password_confirmation')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="btn btn-primary w-full justify-center text-base font-semibold py-3 mt-8"
        >
            {{ __('auth.reset_password_button') }}
        </button>
    </form>

    <!-- Back to Login Link -->
    <div class="mt-10 pt-8 border-t border-gray-200 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-primary-600 font-semibold hover:text-primary-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('auth.back_to_login') }}
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
        <div class="setpwd-alert setpwd-alert-error" role="alert">
            <svg class="alert-icon" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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

    <form method="POST" action="{{ route('onboarding.set-password') }}" class="setpwd-form" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email --}}
        <div class="setpwd-field">
            <div class="field-label-row">
                <label for="email" class="field-label">{{ __('auth.email') }}</label>

                <span class="verified-badge">
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.415 0L3.296 9.217a1 1 0 111.415-1.414l4.035 4.035 6.543-6.543a1 1 0 011.415-.005z" clip-rule="evenodd"/>
                    </svg>
                    موثق
                </span>
            </div>

            <div class="readonly-email-shell">
                <svg class="readonly-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M7 10V8a5 5 0 0110 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M6.5 10h11A1.5 1.5 0 0119 11.5v7A1.5 1.5 0 0117.5 20h-11A1.5 1.5 0 015 18.5v-7A1.5 1.5 0 016.5 10z" stroke="currentColor" stroke-width="1.8"/>
                </svg>

                <input
                    type="email"
                    id="email"
                    class="field-input readonly-email"
                    value="{{ $email }}"
                    readonly
                    tabindex="-1"
                    aria-readonly="true"
                />
            </div>

            <small class="setpwd-hint secure-hint">
                هذا البريد مرتبط بحسابك ولا يمكن تعديله من هذه الصفحة.
            </small>
        </div>

        {{-- Password --}}
        <div class="setpwd-field">
            <label for="password" class="field-label">{{ __('auth.new_password') }}</label>

            <input
                type="password"
                id="password"
                name="password"
                required
                class="field-input @error('password') field-error @enderror"
                placeholder="••••••••"
                autocomplete="new-password"
                minlength="8"
            />

            <small class="setpwd-hint">
                {{ __('auth.password_requirements') }}
            </small>

            @error('password')
                <span class="field-error-text">{{ $message }}</span>
            @enderror
        </div>

        {{-- Password Confirmation --}}
        <div class="setpwd-field">
            <label for="password_confirmation" class="field-label">{{ __('auth.confirm_password') }}</label>

            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                class="field-input @error('password_confirmation') field-error @enderror"
                placeholder="••••••••"
                autocomplete="new-password"
                minlength="8"
            />

            @error('password_confirmation')
                <span class="field-error-text">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="setpwd-submit">
            <span>{{ __('auth.set_password_button') }}</span>

            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </form>

    <style>
        /* Hide back link on onboarding page */
        .auth-card-top {
            display: none !important;
        }

        .setpwd-alert {
            margin-bottom: 1.5rem;
            padding: 1rem 1.1rem;
            border-radius: 18px;
            display: flex;
            gap: 0.85rem;
            animation: slideDown 0.25s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .setpwd-alert-error {
            background: rgba(248, 113, 113, 0.11);
            border: 1px solid rgba(248, 113, 113, 0.26);
            color: #fca5a5;
        }

        .alert-icon {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .setpwd-alert strong {
            display: block;
            margin-bottom: 0.35rem;
            font-weight: 900;
            color: #fecaca;
        }

        .setpwd-alert ul {
            margin: 0;
            padding-inline-start: 1.1rem;
            font-size: 0.86rem;
            line-height: 1.7;
            color: #fca5a5;
        }

        .setpwd-form {
            display: flex;
            flex-direction: column;
            gap: 1.05rem;
            margin-bottom: 2rem;
        }

        .setpwd-field {
            display: flex;
            flex-direction: column;
        }

        .field-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .field-label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.88rem;
            font-weight: 800;
        }

        .field-label-row .field-label {
            margin-bottom: 0;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.28rem;
            padding: 0.25rem 0.55rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.22);
            color: #86efac;
            font-size: 0.72rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .verified-badge svg {
            width: 0.85rem;
            height: 0.85rem;
        }

        .readonly-email-shell {
            position: relative;
            display: flex;
            align-items: center;
        }

        .readonly-icon {
            position: absolute;
            inset-inline-start: 1rem;
            width: 1.15rem;
            height: 1.15rem;
            color: rgba(255, 255, 255, 0.54);
            pointer-events: none;
            z-index: 1;
        }

        .field-input {
            width: 100%;
            height: 56px;
            padding: 0 1rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.085);
            color: rgba(255, 255, 255, 0.94);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            outline: none;
            transition:
                border-color 0.18s ease,
                background 0.18s ease,
                box-shadow 0.18s ease,
                transform 0.18s ease;
        }

        .field-input::placeholder {
            color: rgba(255, 255, 255, 0.36);
            font-weight: 600;
        }

        .field-input:hover:not(:read-only) {
            border-color: rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.105);
        }

        .field-input:focus {
            border-color: rgba(255, 122, 26, 0.9);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 4px rgba(255, 122, 26, 0.15);
        }

        .readonly-email {
            padding-inline-start: 3rem;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.075), rgba(255, 255, 255, 0.045));
            border-color: rgba(255, 255, 255, 0.11);
            color: rgba(255, 255, 255, 0.72);
            cursor: default;
            user-select: text;
        }

        .readonly-email:focus {
            border-color: rgba(255, 255, 255, 0.11);
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.075), rgba(255, 255, 255, 0.045));
            box-shadow: none;
        }

        .field-input.field-error {
            border-color: rgba(239, 68, 68, 0.8);
            background: rgba(239, 68, 68, 0.06);
        }

        .field-input.field-error:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
        }

        .setpwd-hint {
            display: block;
            margin-top: 0.42rem;
            color: rgba(255, 255, 255, 0.52);
            font-size: 0.78rem;
            font-weight: 500;
            line-height: 1.7;
        }

        .secure-hint {
            color: rgba(255, 255, 255, 0.58);
        }

        .field-error-text {
            display: block;
            margin-top: 0.35rem;
            color: #fca5a5;
            font-size: 0.8rem;
            font-weight: 700;
            line-height: 1.6;
        }

        .setpwd-submit {
            height: 56px;
            margin-top: 0.65rem;
            border: 0;
            border-radius: 15px;
            background: linear-gradient(135deg, #ff8a3d 0%, #ff681f 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 0.94rem;
            font-weight: 900;
            cursor: pointer;
            letter-spacing: -0.01em;
            box-shadow: 0 16px 36px rgba(255, 107, 26, 0.24);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                filter 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
        }

        .setpwd-submit svg {
            width: 1.05rem;
            height: 1.05rem;
            transform: scaleX(-1);
        }

        .setpwd-submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
            box-shadow: 0 20px 46px rgba(255, 107, 26, 0.3);
        }

        .setpwd-submit:focus-visible {
            outline: none;
            box-shadow:
                0 16px 36px rgba(255, 107, 26, 0.24),
                0 0 0 4px rgba(255, 122, 26, 0.2);
        }

        .setpwd-submit:active {
            transform: translateY(0);
            box-shadow: 0 12px 30px rgba(255, 107, 26, 0.22);
        }

        @media (max-width: 480px) {
            .setpwd-form {
                gap: 0.95rem;
            }

            .field-input,
            .setpwd-submit {
                height: 54px;
                border-radius: 14px;
            }

            .field-label {
                font-size: 0.84rem;
            }

            .setpwd-hint {
                font-size: 0.74rem;
            }

            .verified-badge {
                font-size: 0.68rem;
            }
        }
    </style>

@endsection

```

## components\category-nav.blade.php

```blade
@props(['categories' => collect(), 'active' => null])

<div class="category-nav-section">
    <div class="category-nav-container">
        <div class="category-nav-scroll">
            @forelse($categories as $category)
                <a
                    href="{{ route('public.category', $category->slug) }}"
                    class="category-nav-item {{ $active === $category->id ? 'is-active' : '' }}"
                    title="{{ $category->localized_name ?? $category->name }}"
                >
                    <span class="category-nav-icon">
                        @if($category->icon)
                            <x-render-icon :icon="$category->icon" class="w-5 h-5" />
                        @else
                            <span class="icon-placeholder">📁</span>
                        @endif
                    </span>
                    <span class="category-nav-text">
                        <span class="category-nav-name">{{ $category->localized_name ?? $category->name }}</span>
                        <span class="category-nav-count">{{ $category->discoverable_profiles_count ?? 0 }}</span>
                    </span>
                </a>
            @empty
                <div class="category-nav-empty">
                    {{ __('messages.public.no_categories') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .category-nav-section {
                background: #f8fafc;
                border-bottom: 1px solid #e5e7eb;
                padding: 2rem 0;
                overflow-x: auto;
            }

            .category-nav-container {
                max-width: 1320px;
                margin: 0 auto;
                padding: 0 1rem;
            }

            .category-nav-scroll {
                display: flex;
                gap: 1rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
            }

            /* Hide scrollbar but keep functionality */
            .category-nav-scroll::-webkit-scrollbar {
                height: 4px;
            }

            .category-nav-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .category-nav-scroll::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 2px;
            }

            .category-nav-item {
                display: inline-flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.75rem 1.25rem;
                background: #ffffff;
                border: 1.5px solid #e5e7eb;
                border-radius: 999px;
                text-decoration: none;
                color: #475569;
                font-weight: 600;
                font-size: 0.9rem;
                transition: all 0.2s ease;
                white-space: nowrap;
                flex-shrink: 0;
                cursor: pointer;
            }

            .category-nav-item:hover {
                background: #f1f5f9;
                border-color: #ff7a1a;
                color: #ff7a1a;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(255, 122, 26, 0.12);
            }

            .category-nav-item.is-active {
                background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
                border-color: #ff6b1a;
                color: #ffffff;
                box-shadow: 0 6px 16px rgba(255, 107, 26, 0.25);
            }

            .category-nav-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
                flex-shrink: 0;
            }

            .category-nav-icon svg {
                width: 100%;
                height: 100%;
                display: block;
            }

            .icon-placeholder {
                font-size: 1rem;
                line-height: 1;
            }

            .category-nav-text {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.15rem;
            }

            .category-nav-name {
                display: block;
                font-weight: 600;
                font-size: 0.9rem;
            }

            .category-nav-count {
                display: block;
                font-size: 0.75rem;
                opacity: 0.7;
                font-weight: 500;
            }

            .category-nav-empty {
                padding: 2rem;
                text-align: center;
                color: #94a3b8;
            }

            @media (max-width: 768px) {
                .category-nav-section {
                    padding: 1.5rem 0;
                }

                .category-nav-item {
                    padding: 0.6rem 1rem;
                    font-size: 0.85rem;
                }

                .category-nav-name {
                    font-size: 0.85rem;
                }

                .category-nav-count {
                    font-size: 0.7rem;
                }
            }

            /* RTL Support */
            [dir="rtl"] .category-nav-text {
                align-items: flex-end;
            }

            [dir="rtl"] .category-nav-scroll {
                flex-direction: row-reverse;
            }
        </style>
    @endpush
@endonce

```

## components\city-nav.blade.php

```blade
@props(['cities' => collect(), 'active' => null])

<div class="city-nav-section">
    <div class="city-nav-container">
        <h3 class="city-nav-title">{{ __('messages.public.cities') }}</h3>
        <div class="city-nav-scroll">
            @forelse($cities as $city)
                <a
                    href="{{ route('public.city', $city->slug) }}"
                    class="city-nav-item {{ $active === $city->id ? 'is-active' : '' }}"
                    title="{{ $city->localized_name ?? $city->name }}"
                >
                    <span class="city-nav-icon">
                        @if($city->icon)
                            <x-render-icon :icon="$city->icon" class="w-5 h-5" />
                        @else
                            <span class="icon-placeholder">📍</span>
                        @endif
                    </span>
                    <span class="city-nav-text">
                        <span class="city-nav-name">{{ $city->localized_name ?? $city->name }}</span>
                        <span class="city-nav-count">{{ $city->discoverable_profiles_count ?? 0 }}</span>
                    </span>
                </a>
            @empty
                <div class="city-nav-empty">
                    {{ __('messages.public.no_cities') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .city-nav-section {
                background: #ffffff;
                border-bottom: 1px solid #e5e7eb;
                padding: 2rem 0;
                overflow-x: auto;
            }

            .city-nav-container {
                max-width: 1320px;
                margin: 0 auto;
                padding: 0 1rem;
            }

            .city-nav-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: #0b1a34;
                margin: 0 0 1.5rem;
                letter-spacing: -0.01em;
            }

            .city-nav-scroll {
                display: flex;
                gap: 0.75rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
            }

            /* Hide scrollbar but keep functionality */
            .city-nav-scroll::-webkit-scrollbar {
                height: 4px;
            }

            .city-nav-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .city-nav-scroll::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 2px;
            }

            .city-nav-item {
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.6rem;
                padding: 1rem;
                background: #f8fafc;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                text-decoration: none;
                color: #475569;
                font-weight: 600;
                font-size: 0.85rem;
                transition: all 0.2s ease;
                white-space: nowrap;
                flex-shrink: 0;
                cursor: pointer;
                min-width: 90px;
                text-align: center;
            }

            .city-nav-item:hover {
                background: #f1f5f9;
                border-color: #ff7a1a;
                color: #ff7a1a;
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(255, 122, 26, 0.12);
            }

            .city-nav-item.is-active {
                background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
                border-color: #ff6b1a;
                color: #ffffff;
                box-shadow: 0 8px 20px rgba(255, 107, 26, 0.25);
            }

            .city-nav-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 24px;
                height: 24px;
                flex-shrink: 0;
            }

            .city-nav-icon svg {
                width: 100%;
                height: 100%;
                display: block;
            }

            .icon-placeholder {
                font-size: 1.2rem;
                line-height: 1;
            }

            .city-nav-text {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.2rem;
            }

            .city-nav-name {
                display: block;
                font-weight: 600;
                font-size: 0.85rem;
            }

            .city-nav-count {
                display: block;
                font-size: 0.7rem;
                opacity: 0.7;
                font-weight: 500;
            }

            .city-nav-empty {
                padding: 2rem;
                text-align: center;
                color: #94a3b8;
            }

            @media (max-width: 768px) {
                .city-nav-section {
                    padding: 1.5rem 0;
                }

                .city-nav-title {
                    font-size: 1rem;
                    margin-bottom: 1rem;
                }

                .city-nav-item {
                    padding: 0.8rem;
                    font-size: 0.8rem;
                    min-width: 80px;
                }

                .city-nav-icon {
                    width: 20px;
                    height: 20px;
                }

                .city-nav-name {
                    font-size: 0.8rem;
                }
            }

            @media (max-width: 480px) {
                .city-nav-item {
                    min-width: 70px;
                    padding: 0.7rem;
                }
            }
        </style>
    @endpush
@endonce

```

## components\contact-card.blade.php

```blade
@props(['provider'])

<style>
    .sticky-contact {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        position: sticky;
        top: 2rem;
    }

    .sticky-contact .card-body {
        padding: 1.5rem;
    }

    .contact-row {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
    }

    .contact-row:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .contact-row svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        display: block;
    }

    .contact-row-label {
        font-size: 0.875rem;
        color: #1e293b;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0b1a34;
    }

    .rating-section {
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
        margin-top: 1rem;
    }

    .rating-stars {
        color: #f59e0b;
        letter-spacing: 1px;
        font-size: 0.9rem;
    }
</style>

<div class="card sticky-contact">
    <div class="card-body">
        <h5 class="card-title mb-3">{{ __('messages.public.contact') }}</h5>

        @if($provider->phone)
            <a href="tel:{{ preg_replace('/\s+/', '', $provider->phone) }}" class="contact-row">
                <x-render-icon icon="heroicon-o-phone" />
                <span class="contact-row-label">{{ $provider->phone }}</span>
            </a>
        @endif

        @if($provider->whatsapp)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $provider->whatsapp) }}" target="_blank" class="contact-row" style="background: #dcfce7; border-color: #86efac;">
                <x-render-icon icon="heroicon-o-chat-bubble-left" />
                <span class="contact-row-label">{{ __('messages.public.whatsapp') }}</span>
            </a>
        @endif

        @if($provider->map_url)
            <a href="{{ $provider->map_url }}" target="_blank" class="contact-row">
                <x-render-icon icon="heroicon-o-map-pin" />
                <span class="contact-row-label">{{ __('messages.public.location') }}</span>
            </a>
        @endif

        @if($provider->stats)
            <div class="rating-section">
                <div class="rating-stars mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <span style="{{ $i <= floor($provider->stats->rating_avg) ? '' : 'opacity: 0.28;' }}">★</span>
                    @endfor
                </div>
                <small class="text-muted d-block">
                    <strong class="text-dark">{{ number_format($provider->stats->rating_avg, 1) }}</strong>
                    ({{ $provider->stats->reviews_count }})
                </small>
            </div>
        @endif
    </div>
</div>

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

<div class="empty-state">
    <div class="empty-state-icon">
        <x-render-icon :icon="$icon" />
    </div>

    <h3 class="empty-state-title">
        {{ $title }}
    </h3>

    @if($message)
        <p class="empty-state-message">
            {{ $message }}
        </p>
    @endif

    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary mt-4">
            {{ $actionLabel }}
        </a>
    @endif
</div>

@once
    @push('styles')
        <style>
            .empty-state {
                text-align: center;
                padding: 3.5rem 2rem;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 1rem;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            }

            .empty-state-icon {
                width: 56px;
                height: 56px;
                margin: 0 auto 1.5rem;
                border-radius: 0.75rem;
                background: #f1f5f9;
                color: #94a3b8;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #e2e8f0;
            }

            .empty-state-icon svg {
                width: 26px;
                height: 26px;
                display: block;
            }

            .empty-state-title {
                font-size: 1.1rem;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 0.6rem;
            }

            .empty-state-message {
                color: #64748b;
                max-width: 380px;
                margin: 0 auto;
                line-height: 1.6;
                font-size: 0.95rem;
            }
        </style>
    @endpush
@endonce

```

## components\footer-contact.blade.php

```blade
@php
    $contact = \App\Models\ContactInfo::instance();
@endphp

<style>
    .footer-contact-card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .footer-contact-card .card-body {
        padding: 1.5rem;
    }

    .footer-contact-row {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 0.75rem;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
        font-size: 0.875rem;
        color: #1e293b;
        font-weight: 500;
    }

    .footer-contact-row:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0b1a34;
    }

    .footer-contact-row svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        display: block;
    }

    .footer-contact-row.whatsapp-row {
        background: #dcfce7;
        border-color: #86efac;
    }

    .footer-contact-row.whatsapp-row:hover {
        background: #c6f6d5;
    }

    .footer-contact-address {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        color: #64748b;
    }

    .footer-contact-address svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        display: block;
        margin-top: 0.1rem;
    }
</style>

@if($contact->whatsapp || $contact->email)
    <div class="card footer-contact-card">
        <div class="card-body">
            <h5 class="card-title mb-3">{{ __('messages.public.contact_information') }}</h5>

            <div class="d-flex flex-column gap-2">
                @if($contact->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp) }}" target="_blank" class="footer-contact-row whatsapp-row">
                        <x-render-icon icon="heroicon-o-chat-bubble-left" />
                        <span>{{ __('messages.public.contact_us') }}</span>
                    </a>
                @endif

                @if($contact->email)
                    <a href="mailto:{{ $contact->email }}" class="footer-contact-row">
                        <x-render-icon icon="heroicon-o-envelope" />
                        <span>{{ $contact->email }}</span>
                    </a>
                @endif

                @if($contact->address)
                    <div class="footer-contact-address">
                        <x-render-icon icon="heroicon-o-map-pin" />
                        <span>{{ $contact->address }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif


```

## components\heroicon-renderer.blade.php

```blade
@props(['icon' => '', 'class' => ''])

@if($icon)
    @try
        <x-dynamic-component :component="$icon" :class="$class" />
    @catch(\Exception $e)
        <span class="{{ $class }}">📦</span>
    @endcatch
@else
    <span class="{{ $class }}">📦</span>
@endif

```

## components\icon.blade.php

```blade
@props(['icon' => null, 'size' => 'w-5 h-5', 'color' => 'text-gray-700'])

@if($icon)
    @php
        $isValid = \App\Services\IconSystem::isValidHeroicon($icon);
    @endphp

    @if($isValid)
        <x-dynamic-component :component="$icon" :class="$size . ' ' . $color" />
    @else
        <!-- Fallback for invalid icon -->
        <x-dynamic-component component="heroicon-o-square-3-stack-3d" :class="$size . ' ' . $color" />
    @endif
@else
    <!-- Fallback for null icon -->
    <x-dynamic-component component="heroicon-o-square-3-stack-3d" :class="$size . ' ' . $color" />
@endif

```

## components\optimized-image.blade.php

```blade
@props([
    'src' => '',
    'alt' => '',
    'width' => null,
    'height' => null,
    'lazy' => true,
    'class' => '',
    'sizes' => null,
])

<img
    src="{{ asset($src) }}"
    alt="{{ $alt }}"
    {{ $attributes->merge([
        'class' => $class,
        'loading' => $lazy ? 'lazy' : 'eager',
        'width' => $width,
        'height' => $height,
        'sizes' => $sizes,
        'decoding' => 'async',
    ]) }}
/>

```

## components\provider-card.blade.php

```blade
@props(['provider', 'showBio' => true])

@php
    $businessName = $provider->business_name ?? __('messages.public.provider');
    $logo = $provider->logo_url ?? ($provider->logo ? asset('storage/' . $provider->logo) : null);
    $rating = (float) ($provider->stats?->rating_avg ?? 0);
    $reviewsCount = (int) ($provider->stats?->reviews_count ?? 0);

    $whatsappNumber = $provider->whatsapp ? preg_replace('/[^0-9]/', '', $provider->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وجدتك عبر دلني وأرغب بالاستفسار عن الخدمة.');
@endphp

<article class="provider-card card h-100 border-0">
    <div class="provider-card__media">
        <a href="{{ route('public.provider', $provider->slug) }}" class="d-block">
            @if($logo)
                <img
                    src="{{ $logo }}"
                    alt="{{ $businessName }}"
                    class="provider-card__image"
                    loading="lazy"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                >
                <div class="provider-card__fallback" style="display:none;">
                    {{ mb_substr($businessName, 0, 1) }}
                </div>
            @else
                <div class="provider-card__fallback">
                    {{ mb_substr($businessName, 0, 1) }}
                </div>
            @endif
        </a>

        @if($rating >= 4.5 && $reviewsCount >= 5)
            <span class="provider-card__badge">
                {{ __('messages.public.top_rated') }}
            </span>
        @endif
    </div>

    <div class="card-body provider-card__body">
        <!-- Title -->
        <h3 class="provider-card__title mb-3">
            <a href="{{ route('public.provider', $provider->slug) }}">
                {{ $businessName }}
            </a>
        </h3>

        <!-- Features Section -->
        <div class="provider-card__features mb-4">
            @if($provider->category)
                <div class="provider-card__feature">
                    <span class="provider-card__feature-icon">
                        <x-render-icon icon="heroicon-o-briefcase" class="w-5 h-5" />
                    </span>
                    <span class="provider-card__feature-label">
                        {{ $provider->category->localized_name ?? $provider->category->name }}
                    </span>
                </div>
            @endif

            @if($provider->city)
                <div class="provider-card__feature">
                    <span class="provider-card__feature-icon">
                        <x-render-icon :icon="$provider->city->icon" class="w-5 h-5" />
                    </span>
                    <span class="provider-card__feature-label">
                        {{ $provider->city->localized_name ?? $provider->city->name }}
                    </span>
                </div>
            @endif

            @if($provider->offers_remote_work)
                <div class="provider-card__feature">
                    <span class="provider-card__feature-icon">
                        <x-render-icon icon="heroicon-o-globe-alt" class="w-5 h-5" />
                    </span>
                    <span class="provider-card__feature-label">
                        {{ __('messages.public.remote_work') }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Rating -->
        <div class="provider-card__rating mb-3">
            <span class="rating-stars">
                @for($i = 1; $i <= 5; $i++)
                    <span class="{{ $i <= round($rating) ? '' : 'is-muted' }}">★</span>
                @endfor
            </span>

            <span class="provider-card__rating-text">
                {{ number_format($rating, 1) }}
                <span>({{ $reviewsCount }})</span>
            </span>
        </div>

        <!-- Bio -->
        @if($showBio && filled($provider->bio))
            <p class="provider-card__bio">
                {{ Str::limit(strip_tags($provider->bio), 115) }}
            </p>
        @endif

        <!-- Actions -->
        <div class="provider-card__actions">
            <a href="{{ route('public.provider', $provider->slug) }}" class="btn btn-primary btn-sm">
                {{ __('messages.public.view_profile') }}
            </a>

            @if($whatsappNumber)
                <a
                    href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="whatsapp-btn btn-sm"
                >
                    {{ __('messages.public.whatsapp') }}
                </a>
            @endif
        </div>
    </div>
</article>

@once
    @push('styles')
        <style>
            .provider-card {
                border-radius: 22px;
                overflow: hidden;
                background: #fff;
                box-shadow: 0 10px 28px rgba(11, 26, 52, 0.08);
                transition: 0.22s ease;
                max-width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .provider-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 18px 42px rgba(11, 26, 52, 0.13);
            }

            .provider-card__media {
                position: relative;
                height: 188px;
                background: #0B1A34;
                overflow: hidden;
            }

            .provider-card__image,
            .provider-card__fallback {
                width: 100%;
                height: 188px;
                object-fit: cover;
            }

            .provider-card__image {
                display: block;
                transition: transform 0.3s ease;
            }

            .provider-card:hover .provider-card__image {
                transform: scale(1.04);
            }

            .provider-card__fallback {
                display: flex;
                align-items: center;
                justify-content: center;
                background:
                    radial-gradient(circle at 30% 20%, rgba(241, 98, 15, 0.35), transparent 28%),
                    linear-gradient(135deg, #0B1A34, #112240);
                color: #F1620F;
                font-size: 3rem;
                font-weight: 800;
            }

            .provider-card__badge {
                position: absolute;
                top: 12px;
                inset-inline-start: 12px;
                background: #22C55E;
                color: #fff;
                border-radius: 999px;
                padding: 0.35rem 0.7rem;
                font-size: 0.78rem;
                font-weight: 800;
                box-shadow: 0 8px 18px rgba(34, 197, 94, 0.25);
            }

            .provider-card__body {
                padding: 1.3rem;
                display: flex;
                flex-direction: column;
                flex: 1;
                justify-content: space-between;
            }

            .provider-card__title {
                font-size: 1rem;
                font-weight: 800;
                line-height: 1.35;
            }

            .provider-card__title a {
                color: #0B1A34;
                text-decoration: none;
            }

            .provider-card__title a:hover {
                color: #F1620F;
            }

            /* Feature Circles Section */
            .provider-card__features {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.7rem;
                justify-items: center;
            }

            .provider-card__feature {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.4rem;
                width: 100%;
                text-align: center;
            }

            .provider-card__feature-icon {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #F8FAFC;
                border: 1.5px solid #EEF2F7;
                color: #0B1A34;
                flex: 0 0 auto;
            }

            .provider-card__feature-label {
                font-size: 0.75rem;
                font-weight: 600;
                color: #374151;
                line-height: 1.2;
                word-break: break-word;
            }

            .provider-card__rating {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.6rem;
                margin-top: auto;
            }

            .provider-card__rating .rating-stars {
                color: #F59E0B;
                letter-spacing: 2px;
                font-size: 0.85rem;
            }

            .provider-card__rating .rating-stars .is-muted {
                opacity: 0.25;
            }

            .provider-card__rating-text {
                color: #0B1A34;
                font-weight: 700;
                font-size: 0.8rem;
            }

            .provider-card__rating-text span {
                color: #6B7280;
                font-weight: 500;
                font-size: 0.75rem;
            }

            .provider-card__bio {
                color: #6B7280;
                font-size: 0.85rem;
                line-height: 1.6;
                margin-bottom: 1rem;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .provider-card__actions {
                display: flex;
                gap: 0.5rem;
                margin-top: auto;
            }

            .provider-card__actions .btn,
            .provider-card__actions .whatsapp-btn {
                flex: 1;
                height: 38px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
                font-weight: 600;
            }

            @media (max-width: 575px) {
                .provider-card__media,
                .provider-card__image,
                .provider-card__fallback {
                    height: 180px;
                }

                .provider-card__body {
                    padding: 1rem;
                }

                .provider-card__title {
                    font-size: 0.95rem;
                }

                .provider-card__features {
                    grid-template-columns: repeat(3, 1fr);
                    gap: 0.5rem;
                    margin-bottom: 0.8rem;
                }

                .provider-card__feature-icon {
                    width: 44px;
                    height: 44px;
                }

                .provider-card__feature-label {
                    font-size: 0.7rem;
                }

                .provider-card__bio {
                    font-size: 0.8rem;
                    margin-bottom: 0.8rem;
                }

                .provider-card__rating {
                    gap: 0.4rem;
                    margin-bottom: 0.8rem;
                }

                .provider-card__actions .btn,
                .provider-card__actions .whatsapp-btn {
                    height: 36px;
                    font-size: 0.8rem;
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
    'columns' => 4,
    'title' => null,
    'subtitle' => null,
])

@php
    $colClass = match($columns) {
        1 => 'col-12',
        2 => 'col-xl-6 col-lg-6 col-md-6',
        3 => 'col-xl-4 col-lg-4 col-md-6',
        4 => 'col-lg-3 col-md-6 col-sm-12',
        default => 'col-lg-3 col-md-6 col-sm-12',
    };
@endphp

<section class="provider-grid-section">
    @if($title || $subtitle)
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-2 mb-4">
            <div>
                @if($title)
                    <h2 class="section-title mb-1">
                        {{ $title }}
                    </h2>
                @endif

                @if($subtitle)
                    <p class="section-subtitle mb-0">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            @if($providers->count() > 0)
                <div class="provider-grid-count">
                    {{ $providers->count() }}
                    {{ __('messages.public.providers') }}
                </div>
            @endif
        </div>
    @endif

    @if($providers->count() > 0)
        <div class="row g-4">
            @foreach($providers as $provider)
                <div class="{{ $colClass }}">
                    <x-provider-card :provider="$provider" />
                </div>
            @endforeach
        </div>
    @else
        <div class="provider-grid-empty">
            <x-empty-state
                title="{{ __('messages.public.no_providers_found') }}"
                message="{{ __('messages.public.try_different_search') }}"
            />
        </div>
    @endif
</section>

@once
    @push('styles')
        <style>
            .provider-grid-section {
                position: relative;
            }

            .provider-grid-section .row {
                margin: 0 -0.5rem;
            }

            .provider-grid-section .row > [class*="col-"] {
                padding: 0 0.5rem;
                margin-bottom: 1.5rem;
            }

            .provider-grid-count {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.35rem;
                padding: 0.65rem 1rem;
                background: rgba(241, 98, 15, 0.08);
                color: #F1620F;
                border-radius: 999px;
                font-size: 0.88rem;
                font-weight: 800;
                border: 1px solid rgba(241, 98, 15, 0.12);
                white-space: nowrap;
            }

            .provider-grid-empty {
                margin-top: 1rem;
            }

            @media (max-width: 768px) {
                .provider-grid-count {
                    align-self: flex-start;
                }

                .provider-grid-section .row {
                    margin: 0 -0.25rem;
                }

                .provider-grid-section .row > [class*="col-"] {
                    padding: 0 0.25rem;
                    margin-bottom: 1rem;
                }
            }

            @media (max-width: 480px) {
                .provider-grid-section .row {
                    margin: 0;
                }

                .provider-grid-section .row > [class*="col-"] {
                    padding: 0;
                    margin-bottom: 1rem;
                }
            }
        </style>
    @endpush
@endonce

```

## components\render-icon.blade.php

```blade
@props(['icon' => null, 'class' => ''])

@php
    use App\Services\IconSystem;

    $isValid = !empty($icon) && IconSystem::isValidHeroicon($icon);

    // Since blade-heroicons isn't installed for public views, we render a safe fallback
    // The icon name is stored in DB and admin can select it properly
    // Public just shows a symbol to indicate icon position
    if ($isValid) {
        $display = '◆'; // Diamond for valid icon (system tried to render it)
    } else {
        $display = '■'; // Square for invalid/empty (fallback)
    }
@endphp

<span class="{{ $class }}" title="{{ $icon ?? 'no-icon' }}">{{ $display }}</span>

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

<div class="search-filters">
    <form method="GET" action="{{ route('public.search') }}" class="filters-form">
        <div class="filters-header">
            <div>
                <h3 class="filters-title">
                    {{ __('messages.public.search_filters') }}
                </h3>
                <p class="filters-subtitle">
                    {{ __('messages.public.search_filters_hint') }}
                </p>
            </div>
        </div>

        <!-- Keyword -->
        <div class="filter-field">
            <label for="keyword" class="filter-label">
                {{ __('messages.public.search_keyword') }}
            </label>
            <input
                type="text"
                id="keyword"
                name="keyword"
                class="filter-input"
                placeholder="{{ __('messages.public.search_placeholder') }}"
                value="{{ request('keyword') }}"
                maxlength="100"
            >
        </div>

        <!-- Category -->
        @if($categories)
            <div class="filter-field">
                <label for="category_id" class="filter-label">
                    {{ __('messages.public.category') }}
                </label>
                <select id="category_id" name="category_id" class="filter-select">
                    <option value="">{{ __('messages.public.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                            {{ $category->localized_name ?? $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- City -->
        @if($cities)
            <div class="filter-field">
                <label for="city_id" class="filter-label">
                    {{ __('messages.public.city') }}
                </label>
                <select id="city_id" name="city_id" class="filter-select">
                    <option value="">{{ __('messages.public.all_cities') }}</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                            {{ $city->localized_name ?? $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Provider Type -->
        @if($providerTypes)
            <div class="filter-field">
                <label for="provider_type" class="filter-label">
                    {{ __('messages.public.provider_type') }}
                </label>
                <select id="provider_type" name="provider_type" class="filter-select">
                    <option value="">{{ __('messages.public.all_types') }}</option>
                    @foreach($providerTypes as $code => $name)
                        <option value="{{ $code }}" @selected((string) request('provider_type') === (string) $code)>
                            {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Remote Toggle -->
        <div class="filter-field filter-checkbox">
            <input
                type="checkbox"
                id="remote"
                name="remote"
                class="filter-checkbox-input"
                value="1"
                @checked(request('remote') == 1)
            >
            <label class="filter-checkbox-label" for="remote">
                <x-render-icon icon="heroicon-o-globe-alt" class="w-4 h-4 inline-block me-1" />
                {{ __('messages.public.remote_work') }}
            </label>
        </div>

        <!-- Sort -->
        <div class="filter-field">
            <label for="sort" class="filter-label">
                {{ __('messages.public.sort_by') }}
            </label>
            <select id="sort" name="sort" class="filter-select">
                <option value="" @selected(!request('sort'))>
                    {{ __('messages.public.relevance') }}
                </option>
                <option value="rating" @selected(request('sort') === 'rating')>
                    {{ __('messages.public.highest_rated') }}
                </option>
                <option value="reviews" @selected(request('sort') === 'reviews')>
                    {{ __('messages.public.most_reviewed') }}
                </option>
                <option value="newest" @selected(request('sort') === 'newest')>
                    {{ __('messages.public.newest') }}
                </option>
            </select>
        </div>

        <!-- Actions -->
        <div class="filter-actions">
            <button type="submit" class="filter-btn filter-btn-primary">
                {{ __('messages.public.search') }}
            </button>

            @if($hasFilters)
                <a href="{{ route('public.search') }}" class="filter-link-clear">
                    {{ __('messages.public.clear_filters') }}
                </a>
            @endif
        </div>
    </form>
</div>

@once
    @push('styles')
        <style>
            .search-filters {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                padding: 1rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            .filters-form {
                display: flex;
                flex-direction: column;
                gap: 0.8rem;
            }

            .filters-header {
                margin-bottom: 0.4rem;
            }

            .filters-title {
                margin: 0 0 0.3rem;
                font-size: 1rem;
                font-weight: 900;
                color: #0f172a;
                letter-spacing: -0.01em;
            }

            .filters-subtitle {
                margin: 0;
                color: #64748b;
                font-size: 0.8rem;
                font-weight: 500;
            }

            .filter-field {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }

            .filter-label {
                display: block;
                color: #0f172a;
                font-size: 0.85rem;
                font-weight: 800;
                letter-spacing: -0.01em;
            }

            .filter-input,
            .filter-select {
                height: 40px;
                padding: 0 0.9rem;
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                background: #ffffff;
                color: #0f172a;
                font-family: inherit;
                font-size: 0.9rem;
                font-weight: 600;
                outline: none;
                transition: 0.15s ease;
            }

            .filter-input::placeholder {
                color: #94a3b8;
                font-weight: 500;
            }

            .filter-input:focus,
            .filter-select:focus {
                border-color: #ff7a1a;
                box-shadow: 0 0 0 3px rgba(255, 122, 26, 0.08);
            }

            .filter-checkbox {
                flex-direction: row;
                align-items: center;
                gap: 0.5rem;
            }

            .filter-checkbox-input {
                width: 18px;
                height: 18px;
                border: 1.5px solid #d1d5db;
                border-radius: 5px;
                cursor: pointer;
                accent-color: #ff7a1a;
                transition: 0.15s ease;
                flex-shrink: 0;
                margin: 0;
            }

            .filter-checkbox-input:checked {
                background: #ff7a1a;
                border-color: #ff7a1a;
            }

            .filter-checkbox-label {
                color: #0f172a;
                font-size: 0.9rem;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 0.35rem;
                margin: 0;
            }

            .filter-actions {
                display: flex;
                flex-direction: column;
                gap: 0.6rem;
                margin-top: 0.4rem;
            }

            .filter-btn {
                height: 40px;
                padding: 0 1.2rem;
                border-radius: 10px;
                font-family: inherit;
                font-size: 0.9rem;
                font-weight: 700;
                border: none;
                cursor: pointer;
                transition: 0.15s ease;
                letter-spacing: -0.01em;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .filter-btn-primary {
                background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
                color: #ffffff;
                box-shadow: 0 8px 16px rgba(255, 107, 26, 0.16);
            }

            .filter-btn-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 10px 24px rgba(255, 107, 26, 0.24);
            }

            .filter-link-clear {
                color: #ff7a1a;
                text-decoration: none;
                font-weight: 700;
                font-size: 0.85rem;
                text-align: center;
                transition: 0.15s ease;
                padding: 0.5rem;
            }

            .filter-link-clear:hover {
                color: #ff6b1a;
            }

            @media (max-width: 768px) {
                .search-filters {
                    padding: 0.9rem;
                }

                .filter-input,
                .filter-select {
                    height: 44px;
                    font-size: 0.9rem;
                }

                .filter-btn {
                    height: 44px;
                    font-size: 0.9rem;
                }
            }
        </style>
    @endpush
@endonce

```

## components\sidebar-contact.blade.php

```blade
@php
    $contact = \App\Models\ContactInfo::instance();
@endphp

@if($contact->whatsapp || $contact->phone || $contact->email)
    <div class="card border-0 bg-light mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">{{ __('messages.public.contact_information') }}</h5>
            </div>

            <div class="d-flex flex-column gap-2">
                @if($contact->whatsapp)
                    <div class="small">
                        <div class="text-muted mb-1">{{ __('messages.public.whatsapp') }}</div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp) }}" target="_blank" class="text-decoration-none text-primary fw-500">
                            {{ $contact->whatsapp }}
                        </a>
                    </div>
                @endif

                @if($contact->phone)
                    <div class="small">
                        <div class="text-muted mb-1">{{ __('messages.public.phone') }}</div>
                        <a href="tel:{{ preg_replace('/\s+/', '', $contact->phone) }}" class="text-decoration-none text-primary fw-500">
                            {{ $contact->phone }}
                        </a>
                    </div>
                @endif

                @if($contact->email)
                    <div class="small">
                        <div class="text-muted mb-1">{{ __('messages.public.email') }}</div>
                        <a href="mailto:{{ $contact->email }}" class="text-decoration-none text-primary fw-500">
                            {{ $contact->email }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif


```

## dashboard.blade.php

```blade
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.dashboard') }}</title>
</head>
<body>
    <h1>{{ __('messages.dashboard') }}</h1>
    <p>{{ __('messages.welcome') }}, {{ auth()->user()->name }}</p>
    <p><a href="{{ route('home') }}">{{ __('messages.public.home') }}</a></p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">{{ __('messages.logout') }}</button>
    </form>
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
<section class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-danger-100 mb-6">
                <svg class="w-16 h-16 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-4">
            <h1 class="text-6xl md:text-7xl font-black text-navy-800 mb-2">403</h1>
            <div class="w-16 h-1 bg-danger-500 rounded-full mx-auto"></div>
        </div>

        <!-- Title -->
        <h2 class="text-3xl font-black text-navy-800 mb-4">
            {{ __('messages.public.error_403_title') }}
        </h2>

        <!-- Description -->
        <p class="text-gray-600 text-lg leading-relaxed mb-10">
            {{ __('messages.public.error_403_message') }}
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="btn btn-primary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l4-4m0 0l4 4m-4-4v4"/>
                </svg>
                {{ __('messages.public.back_home') }}
            </a>
            <a href="{{ route('public.search') }}" class="btn btn-outline flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{ __('messages.public.search') }}
            </a>
        </div>

        <!-- Info Message -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 text-left">
                <p class="text-sm text-warning-800">
                    <strong>ملاحظة:</strong> ليس لديك إذن للوصول إلى هذه الصفحة. إذا كنت تعتقد أن هذا خطأ، يرجى الاتصال بالدعم.
                </p>
            </div>
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
<section class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-primary-100 mb-6">
                <svg class="w-16 h-16 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-4">
            <h1 class="text-6xl md:text-7xl font-black text-navy-800 mb-2">404</h1>
            <div class="w-16 h-1 bg-primary-500 rounded-full mx-auto"></div>
        </div>

        <!-- Title -->
        <h2 class="text-3xl font-black text-navy-800 mb-4">
            {{ __('messages.public.error_404_title') }}
        </h2>

        <!-- Description -->
        <p class="text-gray-600 text-lg leading-relaxed mb-10">
            {{ __('messages.public.error_404_message') }}
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="btn btn-primary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l4-4m0 0l4 4m-4-4v4"/>
                </svg>
                {{ __('messages.public.back_home') }}
            </a>
            <a href="{{ route('public.search') }}" class="btn btn-outline flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{ __('messages.public.search') }}
            </a>
        </div>

        <!-- Suggestions -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-4 font-medium">{{ __('messages.public.suggestions') ?? 'اقتراحات' }}</p>
            <ul class="text-sm text-gray-600 space-y-2">
                <li>• تحقق من صحة الرابط</li>
                <li>• قد تم حذف الصفحة أو نقلها</li>
                <li>• جرب البحث عن ما تبحث عنه</li>
            </ul>
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
<section class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-danger-100 mb-6">
                <svg class="w-16 h-16 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-4">
            <h1 class="text-6xl md:text-7xl font-black text-navy-800 mb-2">500</h1>
            <div class="w-16 h-1 bg-danger-500 rounded-full mx-auto"></div>
        </div>

        <!-- Title -->
        <h2 class="text-3xl font-black text-navy-800 mb-4">
            {{ __('messages.public.error_500_title', ['default' => 'Server Error']) }}
        </h2>

        <!-- Description -->
        <p class="text-gray-600 text-lg leading-relaxed mb-10">
            {{ __('messages.public.error_500_message', ['default' => 'حدث خطأ في الخادم. يرجى المحاولة لاحقًا.']) }}
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="btn btn-primary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l4-4m0 0l4 4m-4-4v4"/>
                </svg>
                {{ __('messages.public.back_home') }}
            </a>
            @if (Route::has('contact'))
                <a href="{{ route('contact') }}" class="btn btn-outline flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('messages.public.contact_support') }}
                </a>
            @endif
        </div>

        <!-- Status Info -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="bg-danger-50 border border-danger-200 rounded-lg p-4">
                <p class="text-sm text-danger-800">
                    <strong>{{ __('messages.public.error_500_code', ['default' => 'رمز الخطأ']) }}:</strong> Server Error 500
                </p>
                <p class="text-xs text-danger-700 mt-2">{{ __('messages.public.error_please_try_later', ['default' => 'يرجى محاولة الوصول مرة أخرى بعد قليل.']) }}</p>
            </div>
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
<section class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-warning-100 mb-6">
                <svg class="w-16 h-16 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-4">
            <h1 class="text-6xl md:text-7xl font-black text-navy-800 mb-2">503</h1>
            <div class="w-16 h-1 bg-warning-500 rounded-full mx-auto"></div>
        </div>

        <!-- Title -->
        <h2 class="text-3xl font-black text-navy-800 mb-4">
            {{ __('messages.public.error_503_title', ['default' => 'Service Unavailable']) }}
        </h2>

        <!-- Description -->
        <p class="text-gray-600 text-lg leading-relaxed mb-10">
            {{ __('messages.public.error_503_message', ['default' => 'الخدمة غير متاحة حاليًا. نحن نعمل على إصلاح المشكلة.']) }}
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="btn btn-primary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l4-4m0 0l4 4m-4-4v4"/>
                </svg>
                {{ __('messages.public.back_home') }}
            </a>
        </div>

        <!-- Maintenance Message -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 text-left">
                <p class="text-sm font-semibold text-warning-800 mb-2">
                    {{ __('messages.public.maintenance', ['default' => 'جاري الصيانة']) }}
                </p>
                <p class="text-xs text-warning-700">
                    {{ __('messages.public.maintenance_message', ['default' => 'نعتذر عن عدم توفر الخدمة. نعمل بجد لإعادة الخدمة قريبًا.']) }}
                </p>
                <p class="text-xs text-warning-600 mt-3">
                    ⏱️ {{ __('messages.public.check_back_soon', ['default' => 'يرجى التحقق لاحقًا']) }}
                </p>
            </div>
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #0B1A34 0%, #112240 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: rgba(17, 34, 64, 0.8);
            border: 1px solid rgba(241, 98, 15, 0.3);
            border-radius: 8px;
            padding: 40px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .error-code {
            font-size: 48px;
            font-weight: bold;
            color: #F1620F;
            margin-bottom: 16px;
        }

        .error-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .error-message {
            color: #ccc;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .back-button {
            display: inline-block;
            background: #F1620F;
            color: white;
            padding: 10px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .back-button:hover {
            background: #D9550C;
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
            <div style="text-align: left; background: rgba(0,0,0,0.3); padding: 20px; border-radius: 4px; margin-top: 30px; font-family: monospace; font-size: 12px;">
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

## icons\heroicon.blade.php

```blade
@php
    // Dynamically render Heroicon using blade-icons
    // Usage: @include('icons.heroicon', ['icon' => 'heroicon-o-star', 'class' => 'w-6 h-6'])
@endphp

@switch($icon ?? null)
    @case('heroicon-o-building-office-2')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V9.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636m0 0h9m-9 0L2.25 9m0 0V6.504c0-1.341 1.084-2.436 2.424-2.436h15.152c1.34 0 2.424 1.095 2.424 2.436V9m-21 0V3.75A2.25 2.25 0 005.25 1.5h13.5A2.25 2.25 0 0021 3.75V9" /></svg>
        @break
    @case('heroicon-s-building-office-2')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M6.819 1.5a2.25 2.25 0 00-2.119 1.375.75.75 0 00.798 1.048c.34-.066.68.149.854.56L7.5 9v9.375A2.25 2.25 0 009.75 21h10.5A2.25 2.25 0 0022.5 18.75V9l1.148-4.087a.75.75 0 00.798-1.048A2.25 2.25 0 0022.181 1.5H6.819z" /><path fill-rule="evenodd" d="M9.75 9a.75.75 0 01.75.75v7.5a.75.75 0 01-1.5 0v-7.5A.75.75 0 019.75 9zm2.25 0a.75.75 0 01.75.75v7.5a.75.75 0 01-1.5 0v-7.5A.75.75 0 0112 9zm2.25 0a.75.75 0 01.75.75v7.5a.75.75 0 01-1.5 0v-7.5a.75.75 0 01.75-.75z" /></svg>
        @break
    @case('heroicon-o-wrench')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.632a2.25 2.25 0 01-2.25 2.25H5.25a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5A2.25 2.25 0 002.25 6.75m19.5 0v-1.5A2.25 2.25 0 0019.5 3H4.5A2.25 2.25 0 002.25 5.25v1.5m19.5 0h-19.5m0 0A2.25 2.25 0 012.25 8.25h19.5A2.25 2.25 0 0121.75 6.75z" /></svg>
        @break
    @case('heroicon-o-star')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25l.840 2.615a.75.75 0 00.712.515h2.743l-2.22 1.612a.75.75 0 00-.27.824l.84 2.616-2.22-1.612a.75.75 0 00-.882 0l-2.22 1.612.84-2.616a.75.75 0 00-.27-.824l-2.22-1.612h2.743a.75.75 0 00.712-.515L12 8.25z" /></svg>
        @break
    @case('heroicon-s-star')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.007 1.564 5.694c.27 1.065-.910 1.900-1.838 1.335L12 18.338l-4.856 2.676c-.927.566-2.108-.27-1.838-1.335l1.563-5.694-4.117-3.007c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.006z" /></svg>
        @break
    @default
        📦
@endswitch

```

## layouts\auth.blade.php

```blade
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield('title', config('app.name'))
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>

        :root {
            --auth-orange: #ff7a1a;
            --auth-orange-hover: #ff6b1a;

            --auth-bg: #06101d;

            --auth-card:
                rgba(8, 16, 30, 0.74);

            --auth-border:
                rgba(255,255,255,0.08);

            --auth-text:
                rgba(255,255,255,0.92);

            --auth-muted:
                rgba(255,255,255,0.58);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;

            width: 100%;
            min-height: 100%;
        }

        body {
            font-family: 'Cairo', sans-serif;

            background: var(--auth-bg);

            color: white;

            overflow-x: hidden;
        }

        .auth-page {
            position: relative;

            min-height: 100dvh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding:
                20px
                16px;

            overflow: hidden;

            isolation: isolate;

            background-image:
                linear-gradient(
                    rgba(4, 10, 24, 0.68),
                    rgba(4, 10, 24, 0.74)
                ),
                url('{{ asset('images/registernlogin.png') }}');

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .auth-page::before {
            content: '';

            position: absolute;
            inset: 0;

            background:
                radial-gradient(
                    circle at top right,
                    rgba(255,122,26,0.08),
                    transparent 22%
                ),
                radial-gradient(
                    circle at bottom left,
                    rgba(59,130,246,0.05),
                    transparent 26%
                );

            pointer-events: none;

            z-index: -1;
        }

        .auth-shell {
            width: 100%;
            max-width: 500px;
        }

        .auth-card {
            position: relative;

            width: 100%;

            padding:
                24px
                24px;

            border-radius: 24px;

            background: var(--auth-card);

            border:
                1px solid var(--auth-border);

            backdrop-filter: blur(14px);

            box-shadow:
                0 8px 28px rgba(0,0,0,0.24);

            overflow: hidden;
        }

        .auth-card::before {
            content: '';

            position: absolute;
            inset: 0;

            background:
                linear-gradient(
                    180deg,
                    rgba(255,255,255,0.025),
                    transparent
                );

            pointer-events: none;
        }

        .auth-card-top {
            margin-bottom: 1.1rem;
        }

        .auth-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;

            color: var(--auth-orange);

            font-size: 0.82rem;
            font-weight: 700;

            text-decoration: none;

            transition:
                opacity 0.2s ease,
                transform 0.2s ease;
        }

        .auth-back-link:hover {
            opacity: 0.92;

            transform: translateX(-2px);
        }

        .auth-top {
            text-align: center;

            margin-bottom: 1.35rem;
        }

        .auth-logo-link {
            display: inline-flex;

            text-decoration: none;
        }

        .auth-logo {
            width: 54px;
            height: 54px;

            border-radius: 16px;

            object-fit: cover;

            margin-bottom: 0.95rem;

            box-shadow:
                0 8px 24px rgba(0,0,0,0.24);
        }

        .auth-title {
            margin: 0;

            color: white;

            font-size: 1.85rem;
            font-weight: 900;

            line-height: 1.15;

            letter-spacing: -0.03em;
        }

        .auth-subtitle {
            margin:
                0.75rem auto 0;

            max-width: 340px;

            color: var(--auth-muted);

            font-size: 0.88rem;

            line-height: 1.8;

            font-weight: 600;
        }

        @media (max-width: 640px) {

            .auth-page {
                padding:
                    14px
                    12px;
            }

            .auth-shell {
                max-width: 100%;
            }

            .auth-card {
                padding:
                    20px
                    16px;

                border-radius: 20px;

                backdrop-filter: blur(10px);
            }

            .auth-title {
                font-size: 1.55rem;
            }

            .auth-subtitle {
                font-size: 0.82rem;
                line-height: 1.7;
            }

            .auth-logo {
                width: 48px;
                height: 48px;

                border-radius: 14px;
            }

            .auth-back-link {
                font-size: 0.78rem;
            }
        }

    </style>

</head>

<body>

    <main class="auth-page">

        <div class="auth-shell">

            <section class="auth-card">

                <div class="auth-card-top">

                    <a
                        href="{{ route('home') }}"
                        class="auth-back-link"
                    >
                        ← العودة إلى الصفحة الرئيسية
                    </a>

                </div>

                <div class="auth-top">

                    <a
                        href="{{ route('home') }}"
                        class="auth-logo-link"
                    >

                        <img
                            src="{{ asset('images/logo.jpg') }}"
                            alt="دلني"
                            class="auth-logo"
                        >

                    </a>

                    <h1 class="auth-title">
                        @yield('auth_title')
                    </h1>

                    <p class="auth-subtitle">
                        @yield('auth_subtitle')
                    </p>

                </div>

                @yield('content')

            </section>

        </div>

    </main>

</body>
</html>
```

```

## onboarding-link.blade.php

```blade
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رابط الإعداد</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        h1 {
            color: #003366;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .link-box {
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 40px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .copy-btn {
            background: #f1620f;
            padding: 10px 20px;
            font-size: 14px;
            margin-top: 15px;
        }
        .copy-btn:hover {
            background: #e0540a;
        }
        .success {
            display: none;
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
            border: 1px solid #c3e6cb;
        }
        .info {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            text-align: right;
            border-radius: 4px;
            color: #0c5394;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 رابط الإعداد</h1>
        <p class="subtitle">انسخ الرابط أدناه أو اضغط على الزر لتعيين كلمة مرورك</p>

        <div class="link-box" id="linkBox">
            {{ $onboardingUrl }}
        </div>

        <button class="button copy-btn" onclick="copyToClipboard()">
            📋 انسخ الرابط
        </button>
        <div class="success" id="success">✓ تم النسخ بنجاح!</div>

        <div class="info">
            ⏰ انتبه: هذا الرابط صالح لمدة محدودة فقط. تأكد من استخدامه قبل انتهاء الصلاحية.
        </div>

        <a href="{{ $onboardingUrl }}" class="button" style="margin-top: 20px;">
            إكمال الإعداد →
        </a>

        <p style="margin-top: 30px; color: #999; font-size: 13px;">
            إذا واجهت مشاكل، يرجى نسخ الرابط أعلاه والصقه في عنوان المتصفح.
        </p>
    </div>

    <script>
        function copyToClipboard() {
            const text = document.getElementById('linkBox').innerText;
            navigator.clipboard.writeText(text).then(() => {
                const successMsg = document.getElementById('success');
                successMsg.style.display = 'block';
                setTimeout(() => {
                    successMsg.style.display = 'none';
                }, 3000);
            }).catch(() => {
                alert('فشل النسخ. يرجى محاولة يدويًا.');
            });
        }
    </script>
</body>
</html>

```

## public\category.blade.php

```blade
@extends('public.layout')

@section('title', $category->localized_name . ' - ' . config('app.name'))

@section('content')

<!-- Breadcrumb -->
<div class="container pt-3">
    <nav aria-label="breadcrumb" class="breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-primary-500">{{ __('messages.public.home') }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-600">{{ $category->localized_name }}</span>
    </nav>
</div>

<!-- Hero Section -->
<section class="bg-navy-800 text-white section-compact">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2">
                <h1 class="text-4xl font-black mb-4">
                    {{ $category->localized_name }}
                </h1>
                @if($category->description)
                    <p class="text-lg text-white/75 mb-3">{{ $category->description }}</p>
                @endif
                <p class="text-white/70">
                    {{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}
                </p>
            </div>
            <div class="flex items-center justify-center h-32 text-white/80">
                <x-render-icon :icon="$category->icon ?: 'heroicon-o-briefcase'" class="w-24 h-24" />
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
                        @if(isset($cities))
                            <div>
                                <label for="city_id" class="form-label">{{ __('messages.public.city') }}</label>
                                <select id="city_id" name="city_id" class="form-select">
                                    <option value="">{{ __('messages.public.all_cities') }}</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" @selected(request('city_id') == $city->id)>
                                            {{ $city->localized_name }}
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

                        <button type="submit" class="btn btn-primary btn-sm w-full">
                            <x-render-icon icon="heroicon-o-funnel" class="w-4 h-4 inline-block mr-2" />
                            {{ __('messages.public.filter') }}
                        </button>
                    </form>
                </div>

                @if(request()->anyFilled(['city_id', 'sort']))
                    <div class="mt-4">
                        <a href="{{ route('public.category', $category->slug) }}" class="btn btn-outline btn-sm w-full">
                            <x-render-icon icon="heroicon-o-arrow-path" class="w-4 h-4 inline-block mr-2" />
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
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <x-render-icon icon="heroicon-o-magnifying-glass" class="w-16 h-16" />
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-2">{{ __('messages.public.no_providers_found') }}</h5>
                    <p class="text-gray-600 mb-6">
                        {{ __('messages.public.no_providers_in_category') }}
                    </p>
                    <a href="{{ route('public.search') }}" class="btn btn-primary">
                        {{ __('messages.public.browse_all') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>

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
                <h1 class="text-4xl font-black mb-4 flex items-center gap-3">
                    <x-render-icon icon="heroicon-o-map-pin" class="w-10 h-10" />
                    {{ $city->localized_name }}
                </h1>
                @if($city->description)
                    <p class="text-lg text-white/75 mb-3">{{ $city->description }}</p>
                @endif
                <p class="text-white/70">
                    {{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}
                </p>
            </div>
            <div class="flex items-center justify-center h-32 text-white/80">
                <x-render-icon icon="heroicon-o-building-office-2" class="w-24 h-24" />
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

## public\home.blade.php

```blade
@extends('public.layout')

@section('title', __('messages.public.home') . ' - ' . config('app.name'))

@section('content')

@php
    $categories = $categories ?? collect();
    $cities = $cities ?? collect();

    $professionalsCount = $categories->sum(
        fn ($category) => (int) ($category->discoverable_profiles_count ?? 0)
    );
@endphp

<section class="home-hero">

    <div class="hero-gradient hero-gradient-1"></div>
    <div class="hero-gradient hero-gradient-2"></div>
    <div class="hero-grid"></div>

    <div class="container">
        <div class="hero-inner">
            <h1 class="hero-title">
                {{ __('messages.public.find_trusted_professionals') }}

                <span>
                    {{ __('messages.public.in_libya') }}
                </span>
            </h1>

            <p class="hero-text">
                {{ __('messages.public.browse_local_professionals') }}
            </p>

            <form
                action="{{ route('public.search') }}"
                method="GET"
                class="premium-search"
            >

                <div class="premium-field premium-keyword">

                    <svg
                        class="field-svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>

                    <input
                        type="text"
                        name="keyword"
                        placeholder="{{ __('messages.public.search_placeholder') }}"
                        value="{{ request('keyword') }}"
                        maxlength="100"
                        autocomplete="off"
                    >
                </div>

                <div class="premium-field">

                    <svg
                        class="field-svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>

                    <select name="city_id">

                        <option value="">
                            {{ __('messages.public.all_cities') }}
                        </option>

                        @foreach($cities->take(15) as $city)
                            <option value="{{ $city->id }}">
                                {{ $city->localized_name ?? $city->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="premium-field">

                    <svg
                        class="field-svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 2v20"></path>
                        <path d="M2 12h20"></path>
                    </svg>

                    <select name="category_id">

                        <option value="">
                            {{ __('messages.public.all_categories') }}
                        </option>

                        @foreach($categories->take(15) as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->localized_name ?? $category->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <button
                    type="submit"
                    class="premium-search-btn"
                >
                    {{ __('messages.public.search') }}
                </button>

            </form>

            <div class="hero-stats">

                <div class="hero-stat">
                    <strong>{{ $professionalsCount }}+</strong>
                    <span>{{ __('messages.public.professionals') }}</span>
                </div>

                <div class="hero-stat">
                    <strong>{{ $cities->count() }}+</strong>
                    <span>{{ __('messages.public.cities') }}</span>
                </div>

                <div class="hero-stat">
                    <strong>{{ $categories->count() }}+</strong>
                    <span>{{ __('messages.public.categories') }}</span>
                </div>

            </div>

        </div>

    </div>

</section>

<style>
/* ===== PREMIUM HERO SECTION ===== */

.home-hero {
    position: relative;
    overflow: hidden;
    min-height: 92vh;
    display: flex;
    align-items: center;
    background-image: url('/images/herobackground.png');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    padding: 4rem 0;
}

.home-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(135deg, rgba(7,20,43,0.6) 0%, rgba(13,34,72,0.5) 50%, rgba(5,11,24,0.6) 100%);
    z-index: 1;
    pointer-events: none;
}

.hero-gradient {
    position: absolute;
    border-radius: 999px;
    filter: blur(120px);
    opacity: 0.06;
    z-index: 2;
}

.hero-gradient-1 {
    width: 500px;
    height: 500px;
    background: #ff8533;
    top: -200px;
    right: -100px;
}

.hero-gradient-2 {
    width: 450px;
    height: 450px;
    background: #2f5abb;
    bottom: -200px;
    left: -150px;
}

.hero-grid {
    position: absolute;
    inset: 0;
    opacity: 0.03;
    z-index: 3;
    background-image:
        linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 80px 80px;
}

.hero-inner {
    position: relative;
    z-index: 10;
    max-width: 1320px;
    margin: 0 auto;
    text-align: center;
    padding: 6rem 2rem 4rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* === TYPOGRAPHY === */
.hero-title {
    margin: 0 auto;
    max-width: 90%;
    color: #ffffff;
    font-size: clamp(2.8rem, 6vw, 5rem);
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -0.03em;
    text-align: center;
    display: block;
    word-break: break-word;
}

.hero-title span {
    display: block;
    color: #ff7a1a;
    font-size: 0.58em;
    margin-top: 0.6rem;
    font-weight: 900;
    letter-spacing: -0.02em;
}

.hero-text {
    margin: 1.2rem auto 2.5rem;
    max-width: 720px;
    color: rgba(255,255,255,0.75);
    font-size: clamp(1rem, 2.2vw, 1.3rem);
    font-weight: 500;
    line-height: 1.8;
    letter-spacing: -0.01em;
    text-align: center;
}

/* === SEARCH BAR (HERO CENTERPIECE) === */
.premium-search {
    display: grid;
    grid-template-columns: 1.5fr 0.95fr 0.95fr 0.85fr;
    gap: 0.75rem;
    max-width: 1050px;
    margin: 0 auto;
    padding: 0.9rem;
    border-radius: 24px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    backdrop-filter: blur(24px);
    box-shadow:
        0 32px 96px rgba(0,0,0,0.35),
        inset 0 1px 2px rgba(255,255,255,0.08);
    transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.premium-search:focus-within {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.25);
    box-shadow:
        0 40px 120px rgba(0,0,0,0.4),
        inset 0 1px 2px rgba(255,255,255,0.12);
}

.premium-field {
    height: 72px;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0 1.2rem;
    border-radius: 18px;
    background: #ffffff;
    transition: 0.2s ease;
}

.premium-field:focus-within {
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgba(241,98,15,0.18);
}

.field-svg {
    width: 22px;
    height: 22px;
    color: #ff7a1a;
    flex-shrink: 0;
    transition: 0.2s ease;
}

.premium-field:focus-within .field-svg {
    color: #ff7a1a;
}

.premium-field input,
.premium-field select {
    width: 100%;
    border: 0;
    outline: none;
    background: transparent;
    color: #0f172a;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 700;
}

.premium-field input::placeholder {
    color: #cbd5e1;
    font-weight: 500;
}

.premium-field select {
    appearance: none;
    cursor: pointer;
}

.premium-search-btn {
    height: 72px;
    padding: 0 2.4rem;
    border: 0;
    border-radius: 18px;
    background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
    color: #ffffff;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 900;
    cursor: pointer;
    transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 20px 48px rgba(255,107,26,0.32);
}

.premium-search-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 28px 64px rgba(255,107,26,0.42);
}

.premium-search-btn:active {
    transform: translateY(-1px);
}

/* === STATS SECTION === */
.hero-stats {
    display: flex;
    justify-content: center;
    gap: 4rem;
    margin-top: 3rem;
    padding-top: 3rem;
    border-top: 1px solid rgba(255,255,255,0.12);
}

.hero-stat {
    min-width: 160px;
}

.hero-stat strong {
    display: block;
    color: #ff8533;
    font-size: 2.8rem;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 0.4rem;
    letter-spacing: -0.02em;
}

.hero-stat span {
    color: rgba(255,255,255,0.64);
    font-weight: 600;
    font-size: 0.95rem;
    letter-spacing: 0.01em;
}

/* === RESPONSIVE === */
@media (max-width: 1024px) {
    .premium-search {
        grid-template-columns: 1.2fr 0.9fr 0.9fr 0.8fr;
        max-width: 90%;
        gap: 0.6rem;
        padding: 0.75rem;
    }

    .hero-inner {
        padding: 5rem 1.5rem 3rem;
    }

    .hero-stats {
        gap: 2.5rem;
    }
}

@media (max-width: 768px) {
    .premium-search {
        grid-template-columns: 1fr 0.9fr;
        gap: 0.5rem;
    }

    .premium-search-btn {
        grid-column: 1 / -1;
    }

    .hero-stats {
        gap: 2rem;
        flex-wrap: wrap;
    }

    .hero-text {
        margin: 1rem auto 2rem;
    }
}

@media (max-width: 640px) {
    .home-hero {
        min-height: auto;
        padding: 3rem 0;
    }

    .hero-inner {
        padding: 4rem 1rem 2.5rem;
    }

    .hero-title {
        font-size: 2.2rem;
        line-height: 1;
    }

    .hero-title span {
        font-size: 1.3rem;
        margin-top: 0.4rem;
    }

    .hero-text {
        font-size: 1rem;
        margin: 0.8rem auto 1.5rem;
    }

    .premium-search {
        grid-template-columns: 1fr;
        gap: 0.4rem;
        padding: 0.6rem;
        max-width: 100%;
    }

    .premium-field,
    .premium-search-btn {
        height: 64px;
    }

    .premium-field {
        padding: 0 1rem;
    }

    .hero-stats {
        gap: 1.5rem;
        margin-top: 2rem;
        padding-top: 2rem;
    }

    .hero-stat strong {
        font-size: 2rem;
    }
}

</style>

<!-- Category Navigation -->
<x-category-nav :categories="$categories" />

<!-- City Navigation -->
<x-city-nav :cities="$cities" />

<!-- Featured Providers -->
@if($featuredProviders->count() > 0)
    <section class="home-section">
        <div class="container">
            <x-provider-grid
                :providers="$featuredProviders"
                :columns="4"
                title="{{ __('messages.public.featured_professionals') }}"
                subtitle="{{ __('messages.public.top_professionals_in_your_area') }}"
            />
        </div>
    </section>
@endif

<!-- Top Rated Providers -->
@if($topRatedProviders->count() > 0)
    <section class="home-section">
        <div class="container">
            <x-provider-grid
                :providers="$topRatedProviders"
                :columns="4"
                title="{{ __('messages.public.highest_rated') }}"
                subtitle="{{ __('messages.public.trusted_professionals') }}"
            />
        </div>
    </section>
@endif

<!-- Latest Providers -->
@if($latestProviders->count() > 0)
    <section class="home-section">
        <div class="container">
            <x-provider-grid
                :providers="$latestProviders"
                :columns="4"
                title="{{ __('messages.public.newest_professionals') }}"
                subtitle="{{ __('messages.public.recently_joined') }}"
            />
        </div>
    </section>
@endif

<style>
    .home-section {
        padding: 3rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .home-section:last-of-type {
        border-bottom: none;
    }
</style>

@endsection

```

## public\layout.blade.php

```blade
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'دلني'))</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    <link rel="dns-prefetch" href="//images.unsplash.com">
    <link rel="preconnect" href="//images.unsplash.com" crossorigin>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="referrer" content="strict-origin-when-cross-origin">

    @stack('styles')

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
            font-family: 'Cairo', sans-serif;
            background: #ffffff;
        }

        body {
            overflow-x: hidden;
        }

        .site-navbar {
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 100;
            background: transparent;
            border-bottom: none;
            padding: 1.1rem 0;
        }

        body.page-search .site-navbar {
            position: static;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.75rem 0;
        }

        .site-navbar .container {
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }

        [dir="rtl"] .navbar-menu {
            margin-right: auto;
            margin-left: 0;
        }

        [dir="ltr"] .navbar-menu {
            margin-left: auto;
            margin-right: 0;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #ffffff;
            font-weight: 900;
            font-size: 1.2rem;
            line-height: 1.1;
            flex-shrink: 0;
        }

        body.page-search .navbar-brand {
            color: #0f172a;
        }

        .navbar-brand img {
            width: 38px;
            height: 38px;
            border-radius: 8px;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 1.8rem;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-direction: row;
        }

        .navbar-menu a {
            padding: 0.4rem 0.6rem;
            text-decoration: none;
            color: rgba(255,255,255,0.75);
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            transition: 0.2s ease;
            line-height: 1.3;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .navbar-menu a:hover {
            color: #ffffff;
            background: rgba(255,255,255,0.1);
        }

        .navbar-menu a.active {
            color: #ff7a1a;
        }

        body.page-search .navbar-menu a {
            color: #475569;
        }

        body.page-search .navbar-menu a:hover {
            color: #0f172a;
            background: #f3f4f6;
        }

        body.page-search .navbar-menu a.active {
            color: #ff7a1a;
        }

        .btn-text {
            color: rgba(255,255,255,0.75) !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%) !important;
            color: #ffffff !important;
            border: none !important;
            padding: 0.5rem 1.2rem !important;
            border-radius: 8px !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            box-shadow: 0 6px 14px rgba(255, 107, 26, 0.14) !important;
            transition: 0.2s ease !important;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(255, 107, 26, 0.22) !important;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            white-space: nowrap;
        }

        .btn-logout {
            padding: 0.4rem 0.6rem;
            background: transparent;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            color: #4b5563;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn-logout:hover {
            border-color: #f1620f;
            color: #f1620f;
        }

        main {
            margin: 0;
            padding: 0;
        }

        main > *:first-child {
            margin-top: 0 !important;
        }

        footer.footer {
            background: #07142b;
            color: rgba(255, 255, 255, 0.72);
            padding: 4rem 0 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3rem;
            margin-bottom: 2.5rem;
            padding-bottom: 2.5rem;
        }

        .footer-column {
            display: flex;
            flex-direction: column;
        }

        .footer-brand-column {
            grid-column: 1;
        }

        .footer-logo-group {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .footer-logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .footer-brand-title {
            margin: 0;
            color: #ffffff;
            font-size: 1.3rem;
            font-weight: 900;
            letter-spacing: -0.01em;
        }

        .footer-description {
            margin: 0;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.6;
        }

        .footer-heading {
            margin: 0 0 1.5rem;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 900;
            letter-spacing: -0.01em;
        }

        .footer-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.72);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: 0.2s ease;
            display: inline-block;
        }

        .footer-links a:hover {
            color: #ff7a1a;
            padding-inline-start: 0.3rem;
        }

        .footer-help-column {
            grid-column: 4;
        }

        .footer-help-text {
            margin: 0 0 1.5rem;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.6;
        }

        .footer-cta-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 900;
            text-align: center;
            transition: 0.2s ease;
            box-shadow: 0 12px 28px rgba(255, 107, 26, 0.22);
            width: fit-content;
        }

        .footer-cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(255, 107, 26, 0.32);
        }

        .footer-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 0 0 2rem;
        }

        .footer-bottom {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            color: rgba(255, 255, 255, 0.64);
            font-size: 0.92rem;
            font-weight: 600;
        }

        /* === RESPONSIVE === */
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2.5rem;
                margin-bottom: 2rem;
                padding-bottom: 2rem;
            }

            .footer-brand-column {
                grid-column: 1 / -1;
            }

            .footer-help-column {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
                margin-bottom: 1.5rem;
                padding-bottom: 1.5rem;
            }

            .footer-brand-column,
            .footer-help-column {
                grid-column: 1;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        .brand-logo {
            border-radius: 12px;
            object-fit: cover;
        }
    </style>
</head>

<body @class(['page-search' => request()->routeIs('public.search')])>
    <nav class="site-navbar">
        <div class="container">
            <a href="{{ route('home') }}" class="navbar-brand">
                <img src="{{ asset('images/logo.jpg') }}" alt="logo">
                <span>{{ config('app.name', 'دلني') }}</span>
            </a>

            <ul class="navbar-menu">
                <li><a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>{{ __('messages.public.home') }}</a></li>
                <li><a href="{{ route('public.search') }}" @class(['active' => request()->routeIs('public.search')])>{{ __('messages.public.search') }}</a></li>
                @guest
                    <li><a href="{{ route('login') }}" class="btn-text">{{ __('messages.login') }}</a></li>
                    <li><a href="{{ route('register') }}" class="btn-primary">{{ __('messages.register') }}</a></li>
                @else
                    <li class="navbar-user">
                        <a href="{{ route('dashboard') }}">{{ auth()->user()->name }}</a>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-logout">{{ __('messages.logout') }}</button>
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </nav>

    <main>
        @if ($errors->any())
            <div class="container mt-4">
                <div class="alert delni-alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ __('messages.error') }}</strong>

                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.close') }}"></button>
                </div>
            </div>
        @endif

        @foreach (['success' => 'alert-success', 'warning' => 'alert-warning', 'error' => 'alert-danger'] as $key => $class)
            @if (session($key))
                <div class="container mt-4">
                    <div class="alert delni-alert {{ $class }} alert-dismissible fade show" role="alert">
                        {{ session($key) }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.close') }}"></button>
                    </div>
                </div>
            @endif
        @endforeach

        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-column footer-brand-column">
                    <div class="footer-logo-group">
                        <img src="{{ asset('images/logo.jpg') }}" alt="logo" class="footer-logo">
                        <h3 class="footer-brand-title">{{ config('app.name', 'دلني') }}</h3>
                    </div>
                    <p class="footer-description">
                        {{ __('messages.public.marketplace_description') }}
                    </p>
                </div>

                <!-- Quick Links Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">{{ __('messages.public.quick_links') }}</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}">{{ __('messages.public.home') }}</a></li>
                        <li><a href="{{ route('public.search') }}">{{ __('messages.public.search') }}</a></li>
                        <li><a href="{{ route('register') }}">{{ __('messages.register') }}</a></li>
                    </ul>
                </div>

                <!-- Legal Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">{{ __('messages.public.legal') }}</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('privacy') }}">{{ __('messages.public.privacy') }}</a></li>
                        <li><a href="{{ route('terms') }}">{{ __('messages.public.terms') }}</a></li>
                        <li><a href="{{ route('disclaimer') }}">{{ __('messages.public.disclaimer') }}</a></li>
                    </ul>
                </div>

                <!-- Help Column -->
                <div class="footer-column footer-help-column">
                    <h4 class="footer-heading">{{ __('messages.public.need_help') }}</h4>
                    <p class="footer-help-text">
                        {{ __('messages.public.need_help_text') }}
                    </p>
                    <a href="{{ route('public.search') }}" class="footer-cta-btn">
                        {{ __('messages.public.start_search') }}
                    </a>
                </div>
            </div>

            <div class="footer-divider"></div>

            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'دلني') }}. {{ __('messages.public.all_rights_reserved') }}</span>
                <span>{{ __('messages.public.built_for_libya') }}</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

```

## public\legal\disclaimer.blade.php

```blade
@extends('public.legal_layout')

@section('content')
    <div class="legal-page">
        <h1>إخلاء المسؤولية</h1>
        <p class="last-updated">آخر تحديث: {{ now()->format('Y-m-d') }}</p>

        <h2>1. عدم المسؤولية</h2>
        <p>
            منصة دلني هي منصة وسيطة تربط بين العملاء ومقدمي الخدمات.
            نحن لا نقدم الخدمات بشكل مباشر، بل نوفر فقط منصة للاتصال والتعاقد.
        </p>

        <h2>2. المسؤولية عن الخدمات</h2>
        <p>
            مقدمو الخدمات مسؤولون بالكامل عن جودة وسلامة الخدمات المقدمة.
            المنصة لا تضمن جودة الخدمات أو تحقق من كفاءة مقدمي الخدمات.
        </p>

        <h2>3. المعلومات والمحتوى</h2>
        <p>
            البيانات والمعلومات المنشورة على المنصة مقدمة "كما هي" دون ضمانات من أي نوع.
            لا نضمن دقة أو اكتمال أو صحة أي معلومات على المنصة.
        </p>

        <h2>4. تقييمات المستخدمين</h2>
        <p>
            التقييمات والآراء المنشورة من قبل المستخدمين لا تعكس بالضرورة آراء المنصة.
            لا نتحمل المسؤولية عن دقة أو صحة التقييمات المكتوبة.
        </p>

        <h2>5. عدم الضمان</h2>
        <p>
            المنصة توفر خدماتها "كما هي" و "كما هي متاحة" دون أي ضمانات صريحة أو ضمنية.
            لا نضمن:
        </p>
        <ul>
            <li>عدم انقطاع الخدمة</li>
            <li>خالية من الأخطاء</li>
            <li>خالية من الفيروسات</li>
            <li>نتائج معينة من استخدام الخدمة</li>
        </ul>

        <h2>6. تحديد المسؤولية</h2>
        <p>
            في أي حال من الأحوال، لن تكون المنصة مسؤولة عن:
        </p>
        <ul>
            <li>الأضرار غير المباشرة أو التبعية</li>
            <li>خسارة البيانات أو الأرباح</li>
            <li>انقطاع العمل</li>
            <li>أضرار السمعة</li>
        </ul>

        <h2>7. روابط الطرف الثالث</h2>
        <p>
            قد تحتوي المنصة على روابط لمواقع طرف ثالث. نحن لا نتحمل مسؤولية محتوى هذه المواقع أو سياساتها.
        </p>

        <h2>8. التعديلات على الخدمة</h2>
        <p>
            نحتفظ بحق تعديل أو إيقاف أي جزء من الخدمة في أي وقت دون إشعار مسبق.
        </p>

        <h2>9. المسؤولية القانونية</h2>
        <p>
            أنت وحدك المسؤول عن امتثالك للقوانين والأنظمة المعمول بها.
            نحن لا نقدم نصائح قانونية أو مالية.
        </p>

        <h2>10. عدم التنازل عن الحقوق</h2>
        <p>
            عدم ممارسة المنصة لأي حق بموجب هذا الإخلاء لا يشكل تنازلاً عن هذا الحق.
        </p>

        <h2>11. الفصل</h2>
        <p>
            إذا تم اعتبار أي جزء من هذا الإخلاء غير صالح أو غير قابل للتنفيذ،
            فسيستمر الجزء المتبقي في الصلاحية والنفاذ.
        </p>

        <h2>12. تاريخ السريان</h2>
        <p>
            هذا الإخلاء ساري من تاريخ آخر تحديث أعلاه ويستمر حتى إشعار آخر.
        </p>

        <h2>13. الاتصال</h2>
        <p>
            للاستفسارات عن هذا الإخلاء، يرجى الاتصال بفريق الدعم لدينا.
        </p>
    </div>
@endsection

```

## public\legal\privacy.blade.php

```blade
@extends('public.legal_layout')

@section('content')
    <div class="legal-page">
        <h1>سياسة الخصوصية</h1>
        <p class="last-updated">آخر تحديث: {{ now()->format('Y-m-d') }}</p>

        <h2>1. المقدمة</h2>
        <p>
            يلتزم تطبيق دلني ("نحن"، "الخدمة") بحماية خصوصيتك وسرية بياناتك الشخصية.
            تشرح هذه السياسة كيفية جمعنا واستخدامنا لمعلوماتك.
        </p>

        <h2>2. البيانات التي نجمعها</h2>
        <ul>
            <li><strong>بيانات التسجيل:</strong> الاسم، البريد الإلكتروني، رقم الهاتف، كلمة المرور</li>
            <li><strong>بيانات الملف الشخصي:</strong> صورة الملف الشخصي، السيرة الذاتية، الفئة، المدينة</li>
            <li><strong>بيانات المراجعات:</strong> التقييمات والتعليقات المكتوبة من قبل المستخدمين</li>
            <li><strong>بيانات الاستخدام:</strong> سجل النشاط والبحث على المنصة</li>
        </ul>

        <h2>3. كيفية استخدام بياناتك</h2>
        <p>نستخدم البيانات الشخصية الخاصة بك من أجل:</p>
        <ul>
            <li>تقديم الخدمات والمنتجات المطلوبة</li>
            <li>تحسين تجربتك على المنصة</li>
            <li>الاتصال بك بشأن الحسابات والخدمات</li>
            <li>مكافحة الاحتيال والنشاط غير القانوني</li>
            <li>الامتثال للقوانين واللوائح المعمول بها</li>
        </ul>

        <h2>4. حماية البيانات</h2>
        <p>
            نطبق إجراءات أمان صارمة لحماية بياناتك من الوصول غير المصرح به والتعديل والحذف والكشف.
            البيانات مشفرة أثناء النقل وفي حالة السكون.
        </p>

        <h2>5. مشاركة البيانات</h2>
        <p>
            لن نشارك بياناتك الشخصية مع أطراف ثالثة دون موافقتك، باستثناء:
        </p>
        <ul>
            <li>عند الامتثال للقوانين القانونية والنظامية</li>
            <li>مع مقدمي الخدمات الموثوقين الذين يساعدوننا في تشغيل المنصة</li>
            <li>عند نقل الأعمال التجارية (الاندماج أو الاستحواذ)</li>
        </ul>

        <h2>6. حقوقك</h2>
        <p>لديك الحق في:</p>
        <ul>
            <li>الوصول إلى بياناتك الشخصية</li>
            <li>تصحيح المعلومات غير الدقيقة</li>
            <li>حذف بياناتك (الحق في أن تنسى)</li>
            <li>الاعتراض على معالجة بياناتك</li>
            <li>نقل بياناتك إلى خدمة أخرى</li>
        </ul>

        <h2>7. ملفات تعريف الارتباط</h2>
        <p>
            نستخدم ملفات تعريف الارتباط لتحسين تجربتك. يمكنك تعطيل ملفات تعريف الارتباط من خلال إعدادات متصفحك،
            لكن قد يؤثر ذلك على وظائف المنصة.
        </p>

        <h2>8. التغييرات على هذه السياسة</h2>
        <p>
            قد نحدث هذه السياسة من وقت لآخر. سيتم إخطارك بأي تغييرات جوهرية عبر البريد الإلكتروني أو على المنصة.
        </p>

        <h2>9. اتصل بنا</h2>
        <p>
            إذا كان لديك أسئلة حول سياسة الخصوصية هذه، يرجى الاتصال بنا عبر البريد الإلكتروني أو نموذج الاتصال.
        </p>
    </div>
@endsection

```

## public\legal\terms.blade.php

```blade
@extends('public.legal_layout')

@section('content')
    <div class="legal-page">
        <h1>شروط الاستخدام</h1>
        <p class="last-updated">آخر تحديث: {{ now()->format('Y-m-d') }}</p>

        <h2>1. قبول الشروط</h2>
        <p>
            بالوصول واستخدام منصة دلني، فإنك توافق على الالتزام بهذه الشروط والأحكام.
            إذا كنت لا توافق على أي جزء من هذه الشروط، فرجاء عدم استخدام المنصة.
        </p>

        <h2>2. حساب المستخدم</h2>
        <p>
            عند إنشاء حساب، فأنت تتعهد بتقديم معلومات دقيقة وتحديثها بانتظام.
            أنت مسؤول عن الحفاظ على سرية كلمة المرور الخاصة بك.
        </p>

        <h2>3. السلوك المرفوض</h2>
        <p>
            لا يجوز لك استخدام المنصة من أجل:
        </p>
        <ul>
            <li>نشر محتوى مسيء أو إباحي أو غير قانوني</li>
            <li>الاحتيال أو الخداع أو الابتزاز</li>
            <li>انتهاك حقوق الملكية الفكرية</li>
            <li>التحرش أو المضايقة أو التمييز</li>
            <li>محاولة الوصول غير المصرح به إلى الأنظمة</li>
            <li>نشر البرامج الضارة أو الفيروسات</li>
        </ul>

        <h2>4. محتوى المستخدم</h2>
        <p>
            أنت تحتفظ بجميع حقوق الملكية على محتواك. بنشر محتوى على المنصة، تمنحنا ترخيصًا لاستخدامه وتعديله وتوزيعه.
        </p>

        <h2>5. التزامات مقدمي الخدمات</h2>
        <ul>
            <li>تقديم خدمات عالية الجودة</li>
            <li>الامتثال للقوانين والأنظمة السارية</li>
            <li>احترام سرية العملاء</li>
            <li>عدم استخدام بيانات العملاء بشكل غير شرعي</li>
        </ul>

        <h2>6. التزامات المستخدمين</h2>
        <ul>
            <li>الدفع في الوقت المناسب</li>
            <li>احترام حقوق مقدمي الخدمات</li>
            <li>عدم استخدام الخدمات بطريقة غير قانونية</li>
            <li>الإبلاغ عن أي مشاكل أو انتهاكات</li>
        </ul>

        <h2>7. الرسوم والدفع</h2>
        <p>
            تحتفظ المنصة بحق تغيير الرسوم أو إضافة رسوم جديدة بإشعار مسبق.
            قد يتم إيقاف الخدمات عند عدم الدفع في المواعيد المحددة.
        </p>

        <h2>8. المسؤوليات الضارة</h2>
        <p>
            لن تكون المنصة مسؤولة عن أي أضرار مباشرة أو غير مباشرة ناشئة عن استخدام الخدمة أو عدم القدرة على استخدامها.
        </p>

        <h2>9. إنهاء الحساب</h2>
        <p>
            يمكننا إنهاء أو تعليق حسابك دون سابق إنذار إذا انتهكت هذه الشروط أو تصرفت بطريقة غير قانونية.
        </p>

        <h2>10. التغييرات على الشروط</h2>
        <p>
            نحتفظ بحق تعديل هذه الشروط في أي وقت. سيتم إخطارك بأي تغييرات جوهرية.
        </p>

        <h2>11. القانون الحاكم</h2>
        <p>
            تخضع هذه الشروط لقوانين دولة ليبيا.
        </p>

        <h2>12. اتصل بنا</h2>
        <p>
            لأية استفسارات حول هذه الشروط، يرجى الاتصال بفريق الدعم.
        </p>
    </div>
@endsection

```

## public\legal_layout.blade.php

```blade
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - {{ $title ?? __('messages.public.legal') }}</title>

    {{-- Favicon & App Icons --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    <style>
        body { font-family: Arial, sans-serif; margin: 0; color: #111827; background: #f9fafb; }
        header, main, footer { max-width: 1120px; margin: 0 auto; padding: 16px; }
        header { display: flex; gap: 16px; align-items: center; justify-content: space-between; border-bottom: 1px solid #d1d5db; }
        a { color: #1d4ed8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .legal-page { background: #fff; padding: 24px; border-radius: 8px; }
        .legal-page h1 { margin-bottom: 8px; }
        .last-updated { color: #6b7280; font-size: 14px; }
        .legal-page h2 { margin-top: 24px; margin-bottom: 12px; font-size: 18px; }
        .legal-page p { line-height: 1.6; margin-bottom: 12px; }
        .legal-page ul { margin: 12px 0; padding-left: 24px; }
        .legal-page li { margin-bottom: 8px; }
        footer { border-top: 1px solid #d1d5db; margin-top: 48px; padding-top: 32px; }
        .footer-links { display: flex; flex-wrap: wrap; gap: 16px; }
    </style>
</head>
<body>
<header>
    <div>
        <strong><a href="/">{{ config('app.name') }}</a></strong>
    </div>
    <nav>
        <a href="{{ route('home') }}">{{ __('messages.public.home') }}</a>
        <a href="{{ route('public.search') }}">{{ __('messages.public.search') }}</a>
    </nav>
</header>

<main>
    @yield('content')
</main>

<footer>
    <div class="footer-links">
        <a href="{{ route('privacy') }}">{{ __('messages.public.privacy') }}</a>
        <a href="{{ route('terms') }}">{{ __('messages.public.terms') }}</a>
        <a href="{{ route('disclaimer') }}">{{ __('messages.public.disclaimer') }}</a>
    </div>
</footer>
</body>
</html>


```

## public\provider.blade.php

```blade
@extends('public.layout')

@section('title', $profile->business_name . ' - ' . config('app.name'))

@section('content')
<!-- Inline styles moved to components.css -->

<div class="provider-hero">
    @if($profile->cover_image)
        <img src="{{ asset('storage/' . $profile->cover_image) }}" alt="{{ $profile->business_name }}">
    @endif
</div>

<div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">

            {{-- Provider Header --}}
            <div class="card provider-header-card mb-4">
                <div class="card-body p-4">
                    <div class="provider-header-body d-flex gap-4 align-items-start">

                        @if($profile->logo)
                            <img src="{{ asset('storage/' . $profile->logo) }}" alt="{{ $profile->business_name }}" class="provider-logo">
                        @else
                            <div class="provider-logo-fallback">
                                {{ mb_substr($profile->business_name, 0, 1) }}
                            </div>
                        @endif

                        <div class="flex-grow-1">
                            <h1 class="h3 fw-bold mb-1">{{ $profile->business_name }}</h1>

                            @if($profile->user?->name)
                                <p class="text-muted mb-3">{{ $profile->user->name }}</p>
                            @endif

                            <div class="meta-pills mb-3">
                                @if($profile->category)
                                    <span class="meta-pill">
                                        <x-render-icon icon="heroicon-o-briefcase" />
                                        {{ $profile->category->localized_name }}
                                    </span>
                                @endif

                                @if($profile->city)
                                    <span class="meta-pill">
                                        <x-render-icon :icon="$profile->city->icon ?: 'heroicon-o-map-pin'" />
                                        {{ $profile->city->localized_name }}
                                    </span>
                                @endif

                                @if($profile->offers_remote_work)
                                    <span class="meta-pill">
                                        <x-render-icon icon="heroicon-o-globe-alt" />
                                        {{ __('messages.public.remote_work') }}
                                    </span>
                                @endif
                            </div>

                            @if($profile->stats)
                                <div class="rating-line">
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span style="{{ $i <= floor($profile->stats->rating_avg) ? '' : 'opacity:.28;' }}">★</span>
                                        @endfor
                                    </div>

                                    <strong class="text-dark">{{ number_format($profile->stats->rating_avg, 1) }}</strong>

                                    <span>
                                        ({{ $profile->stats->reviews_count }} {{ __('messages.public.reviews') }})
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bio --}}
            @if($profile->bio)
                <div class="card provider-section-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">{{ __('messages.public.bio') }}</h3>
                        <p class="text-muted mb-0">{{ $profile->bio }}</p>
                    </div>
                </div>
            @endif

            {{-- Service Area --}}
            @if($profile->service_area_note)
                <div class="card provider-section-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">{{ __('messages.public.service_area') }}</h3>
                        <p class="text-muted mb-0">{{ $profile->service_area_note }}</p>
                    </div>
                </div>
            @endif

            {{-- Details --}}
            <div class="card provider-section-card mb-4">
                <div class="card-body p-4">
                    <h3 class="h5 fw-bold mb-3">{{ __('messages.public.details') }}</h3>

                    <div class="row g-3">
                        @if($profile->category)
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">{{ __('messages.public.category') }}</small>
                                <strong>{{ $profile->category->localized_name }}</strong>
                            </div>
                        @endif

                        @if($profile->city)
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">{{ __('messages.public.city') }}</small>
                                <strong>{{ $profile->city->localized_name }}</strong>
                            </div>
                        @endif

                        @if($profile->subcategories->isNotEmpty())
                            <div class="col-12">
                                <small class="text-muted d-block mb-2">{{ __('messages.public.subcategories') }}</small>
                                <div class="meta-pills">
                                    @foreach($profile->subcategories as $subcategory)
                                        <span class="meta-pill">{{ $subcategory->localized_name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Portfolio --}}
            @if($portfolioItems->isNotEmpty())
                <section class="mb-4">
                    <h2 class="h4 fw-bold mb-3 section-title-icon d-flex align-items-center gap-2">
                        <x-render-icon icon="heroicon-o-photo" />
                        {{ __('messages.public.portfolio') }}
                    </h2>

                    <small class="text-muted d-block mb-3">اضغط على أي عمل لمشاهدة جميع الصور</small>

                    <div class="row g-4">
                        @foreach($portfolioItems as $item)
                            <div class="col-md-6">
                                <div class="card h-100 provider-section-card" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#portfolio-modal-{{ $item->id }}">
                                    @if($item->images->isNotEmpty())
                                        <img src="{{ asset('storage/' . $item->images->first()->path) }}" alt="{{ $item->title }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top d-flex align-items-center justify-content-center" style="height:200px;background:#f8fafc;color:#cbd5e1;">
                                            <x-render-icon icon="heroicon-o-photo" style="width: 48px; height: 48px;" />
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <h5 class="card-title">{{ $item->title }}</h5>

                                        @if($item->short_description)
                                            <p class="card-text text-muted small">{{ $item->short_description }}</p>
                                        @endif

                                        @if($item->images->count() > 1)
                                            <small class="text-muted">
                                                {{ $item->images->count() }} صور
                                            </small>
                                        @endif

                                        @if($item->main_url || $item->link)
                                            <div class="mt-2">
                                                <a href="{{ $item->main_url ?: $item->link }}" target="_blank" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();">
                                                    {{ __('messages.public.view_link') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="portfolio-modal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content border-0">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title">{{ $item->title }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body p-0">
                                            @if($item->images->isNotEmpty())
                                                <div id="portfolio-carousel-{{ $item->id }}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500">
                                                    <div class="carousel-inner">
                                                        @foreach($item->images as $index => $image)
                                                            <div class="carousel-item @if($index === 0) active @endif">
                                                                <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->alt ?: $item->title }}" class="d-block w-100" style="max-height:500px;object-fit:contain;background:#f8fafc;">
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    @if($item->images->count() > 1)
                                                        <button class="carousel-control-prev" type="button" data-bs-target="#portfolio-carousel-{{ $item->id }}" data-bs-slide="prev">
                                                            <span class="carousel-control-prev-icon"></span>
                                                        </button>

                                                        <button class="carousel-control-next" type="button" data-bs-target="#portfolio-carousel-{{ $item->id }}" data-bs-slide="next">
                                                            <span class="carousel-control-next-icon"></span>
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center justify-content-center" style="height:400px;background:#f8fafc;color:#cbd5e1;">
                                                    <x-render-icon icon="heroicon-o-photo" style="width: 64px; height: 64px;" />
                                                </div>
                                            @endif
                                        </div>

                                        @if($item->description || $item->main_url || $item->link)
                                            <div class="modal-body border-top">
                                                @if($item->description)
                                                    <p class="text-muted mb-3">{{ $item->description }}</p>
                                                @endif

                                                @if($item->main_url || $item->link)
                                                    <a href="{{ $item->main_url ?: $item->link }}" target="_blank" class="btn btn-primary">
                                                        {{ __('messages.public.view_link') }} ↗
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Links --}}
            @if($links->isNotEmpty())
                <section class="mb-4">
                    <h2 class="h4 fw-bold mb-3">{{ __('messages.public.links') }}</h2>

                    <div class="row g-3">
                        @foreach($links as $link)
                            <div class="col-12">
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary w-100 text-start">
                                    {{ $link->label ?: $link->url }}
                                    <span class="float-end">↗</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Credentials --}}
            @if($credentials->isNotEmpty())
                <section class="mb-4">
                    <h2 class="h4 fw-bold mb-3">{{ __('messages.public.credentials') }}</h2>

                    <div class="row g-3">
                        @foreach($credentials as $credential)
                            <div class="col-12">
                                <div class="card provider-section-card">
                                    <div class="card-body">
                                        @if($credential->title)
                                            <h5 class="card-title">{{ $credential->title }}</h5>
                                        @endif

                                        @if($credential->issuer)
                                            <small class="text-muted d-block">{{ $credential->issuer }}</small>
                                        @endif

                                        @if($credential->issue_date)
                                            <small class="text-muted d-block">{{ $credential->issue_date->toDateString() }}</small>
                                        @endif

                                        @if($credential->notes)
                                            <p class="card-text text-muted mt-2 mb-0">{{ $credential->notes }}</p>
                                        @endif

                                        @if($credential->verification_url)
                                            <a href="{{ $credential->verification_url }}" target="_blank" class="btn btn-sm btn-link p-0 mt-2">
                                                {{ __('messages.public.verify') }} ↗
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Reviews --}}
            <section class="mb-4">
                <h2 class="h4 fw-bold mb-3">{{ __('messages.public.reviews') }} ({{ $reviews->count() }})</h2>

                <div class="card provider-section-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-3">{{ __('messages.public.leave_review') }}</h3>

                        @if(!auth()->check())
                            <div class="alert alert-info mb-0">
                                <p class="mb-2">{{ __('messages.public.login_to_review') }}</p>
                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">{{ __('messages.login') }}</a>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm">{{ __('messages.register') }}</a>
                            </div>
                        @elseif(!auth()->user()->hasRole('user'))
                            <div class="alert alert-warning mb-0">
                                {{ __('messages.public.providers_cannot_review') }}
                            </div>
                        @elseif($profile->user_id === auth()->id())
                            <div class="alert alert-warning mb-0">
                                {{ __('messages.public.cannot_review_own') }}
                            </div>
                        @else
                            <form method="POST" action="{{ route('review.store', $profile) }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="rating" class="form-label">{{ __('messages.public.rating') }}</label>
                                    <select id="rating" name="rating" class="form-select" required>
                                        <option value="">{{ __('messages.public.select') }}</option>
                                        @for($rating = 5; $rating >= 1; $rating--)
                                            <option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} / 5.0</option>
                                        @endfor
                                    </select>

                                    @error('rating')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="comment" class="form-label">{{ __('messages.public.review_comment') }}</label>
                                    <textarea id="comment" name="comment" class="form-control" rows="4" maxlength="2000" placeholder="{{ __('messages.public.share_your_experience') }}">{{ old('comment') }}</textarea>

                                    @error('comment')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    {{ __('messages.public.submit_review') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @forelse($reviews as $review)
                    <div class="card provider-section-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2 gap-3">
                                <strong>{{ $review->user?->name ?? __('messages.public.anonymous') }}</strong>

                                <div class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                            </div>

                            @if($review->comment)
                                <p class="text-muted mb-2">{{ $review->comment }}</p>
                            @endif

                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>

                            @can('flag', $review)
                                <form method="POST" action="{{ route('reviews.flag', $review) }}" class="mt-3">
                                    @csrf

                                    <label for="flag-reason-{{ $review->id }}" class="form-label small text-muted">
                                        {{ __('messages.public.flag_review') }}
                                    </label>

                                    <textarea id="flag-reason-{{ $review->id }}" name="reason" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" required></textarea>

                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ __('messages.public.submit_flag') }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <x-empty-state
                        icon="heroicon-o-chat-bubble-left-right"
                        title="{{ __('messages.public.no_reviews') }}"
                        message="{{ __('messages.public.be_first_review') }}"
                    />
                @endforelse
            </section>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <x-contact-card :provider="$profile" />
        </div>
    </div>
</div>
@endsection

```

## public\search.blade.php

```blade
@extends('public.layout')

@section('title', __('messages.public.search_results') . ' - ' . config('app.name'))

@section('content')

<div class="search-page">
    <!-- Page Header -->
    <div class="search-header">
        <div class="container">
            <h1>{{ __('messages.public.search_results') }}</h1>
            <p>{{ __('messages.public.find_trusted_professionals') }}</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="search-main">
        <div class="container">
            <div class="search-container">
                <!-- Sidebar -->
                <aside class="search-sidebar">
                    <div class="filter-box">
                        <x-search-filters
                            :categories="$categories"
                            :cities="$cities"
                            :providerTypes="$providerTypes ?? null"
                        />
                    </div>
                </aside>

                <!-- Results -->
                <main class="search-results">
                    <!-- Results Summary -->
                    <div class="results-summary">
                        <h2>{{ $profiles->total() }} {{ __('messages.public.professionals') }}</h2>
                        @if(request('keyword'))
                            <p>{{ __('messages.public.for') }} <strong>"{{ request('keyword') }}"</strong></p>
                        @endif
                    </div>

                    <!-- Active Filters -->
                    @if(request()->anyFilled(['category_id', 'city_id', 'provider_type', 'remote']))
                        <div class="filter-chips">
                            @if(request('category_id') && $categories->find(request('category_id')))
                                <span class="chip">
                                    {{ $categories->find(request('category_id'))->localized_name ?? $categories->find(request('category_id'))->name }}
                                    <a href="{{ request()->fullUrlWithQuery(['category_id' => null]) }}">×</a>
                                </span>
                            @endif

                            @if(request('city_id') && $cities->find(request('city_id')))
                                <span class="chip">
                                    {{ $cities->find(request('city_id'))->localized_name ?? $cities->find(request('city_id'))->name }}
                                    <a href="{{ request()->fullUrlWithQuery(['city_id' => null]) }}">×</a>
                                </span>
                            @endif

                            @if(request('provider_type') && isset($providerTypes))
                                @php $selectedType = request('provider_type'); @endphp
                                @if(isset($providerTypes[$selectedType]))
                                    <span class="chip">
                                        {{ $providerTypes[$selectedType] }}
                                        <a href="{{ request()->fullUrlWithQuery(['provider_type' => null]) }}">×</a>
                                    </span>
                                @endif
                            @endif

                            @if(request('remote') == 1)
                                <span class="chip">
                                    {{ __('messages.public.remote_work') }}
                                    <a href="{{ request()->fullUrlWithQuery(['remote' => null]) }}">×</a>
                                </span>
                            @endif
                        </div>
                    @endif

                    <!-- Results List -->
                    @if($profiles->count() > 0)
                        <div class="providers-list">
                            <x-provider-grid :providers="$profiles" :columns="1" />
                        </div>

                        @if($profiles->hasPages())
                            <div class="pagination-area">
                                {{ $profiles->links('pagination::tailwind') }}
                            </div>
                        @endif
                    @else
                        <!-- Empty State -->
                        <div class="empty-state">
                            <div class="empty-icon"></div>
                            <h3>{{ __('messages.public.no_results') }}</h3>
                            <p>
                                @if(request('keyword'))
                                    {{ __('messages.public.no_results_for_keyword', ['keyword' => request('keyword')]) }}
                                @else
                                    {{ __('messages.public.no_results_found') }}
                                @endif
                            </p>
                            <a href="{{ route('home') }}" class="empty-btn">
                                {{ __('messages.public.back_to_home') }}
                            </a>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </div>
</div>

<style>
    .search-page {
        background: #ffffff;
        min-height: 100vh;
    }

    .search-header {
        padding: 2rem 0 1.2rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .search-header h1 {
        margin: 0 0 0.4rem;
        color: #0f172a;
        font-size: 1.7rem;
        font-weight: 900;
        line-height: 1.1;
        letter-spacing: -0.01em;
    }

    .search-header p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .search-main {
        padding: 1.5rem 0 2.5rem;
    }

    .search-container {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 1.8rem;
        align-items: start;
    }

    [dir="rtl"] .search-container {
        grid-template-columns: 1fr 300px;
    }

    .search-sidebar {
        /* Compact sidebar */
    }

    .filter-box {
        padding: 1rem;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        top: 5.5rem;
    }

    .search-results {
        min-width: 0;
    }

    .results-summary {
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .results-summary h2 {
        margin: 0;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .results-summary p {
        margin: 0.3rem 0 0;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .results-summary strong {
        color: #ff7a1a;
        font-weight: 700;
    }

    .filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.35rem 0.6rem;
        border-radius: 20px;
        background: #fef3e2;
        color: #d97706;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .chip a {
        color: inherit;
        text-decoration: none;
        opacity: 0.7;
        margin-left: 0.15rem;
    }

    .chip a:hover {
        opacity: 1;
    }

    .providers-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .pagination-area {
        margin-top: 1.5rem;
    }

    .empty-state {
        padding: 2rem;
        border-radius: 14px;
        background: #f9fafb;
        text-align: center;
    }

    .empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        border-radius: 14px;
        background: rgba(255, 122, 26, 0.08);
    }

    .empty-state h3 {
        margin: 0 0 0.5rem;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .empty-state p {
        max-width: 340px;
        margin: 0 auto 1rem;
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.5;
        font-weight: 500;
    }

    .empty-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 40px;
        padding: 0 1.2rem;
        border-radius: 10px;
        background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 900;
        text-decoration: none;
        box-shadow: 0 6px 16px rgba(255, 107, 26, 0.16);
        transition: 0.15s ease;
    }

    .empty-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(255, 107, 26, 0.24);
    }

    /* === RESPONSIVE === */
    @media (max-width: 1024px) {
        .search-container {
            grid-template-columns: 260px 1fr;
            gap: 1.5rem;
        }

        [dir="rtl"] .search-container {
            grid-template-columns: 1fr 260px;
        }
    }

    @media (max-width: 768px) {
        .search-page {
            padding-top: 0;
        }

        .search-header {
            padding: 1.2rem 0 0.8rem;
        }

        .search-header h1 {
            font-size: 1.4rem;
        }

        .search-main {
            padding: 1rem 0 2rem;
        }

        .search-container {
            grid-template-columns: 1fr;
            gap: 1.2rem;
        }

        [dir="rtl"] .search-container {
            grid-template-columns: 1fr;
        }

        .filter-box {
            position: static;
            padding: 0.9rem;
        }

        .empty-state {
            padding: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1rem;
        }

        .empty-state p {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .search-page {
            padding-top: 0;
        }

        .search-header {
            padding: 0.8rem 0 0.6rem;
        }

        .search-header h1 {
            font-size: 1.2rem;
        }

        .search-main {
            padding: 0.8rem 0 1.5rem;
        }

        .search-container {
            gap: 1rem;
        }

        .filter-box {
            padding: 0.8rem;
        }

        .results-summary h2 {
            font-size: 1rem;
        }

        .empty-state {
            padding: 1.2rem;
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

<!-- Breadcrumb -->
<div class="container pt-3">
    <nav aria-label="breadcrumb" class="breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-primary-500">{{ __('messages.public.home') }}</a>
        <span class="mx-2 text-gray-400">/</span>
        @if($category = $subcategory->category)
            <a href="{{ route('public.category', $category->slug) }}" class="hover:text-primary-500">{{ $category->localized_name }}</a>
            <span class="mx-2 text-gray-400">/</span>
        @endif
        <span class="text-gray-600">{{ $subcategory->localized_name }}</span>
    </nav>
</div>

<!-- Hero Section -->
<section class="bg-navy-800 text-white section-compact">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2">
                <h1 class="text-4xl font-black mb-4">
                    {{ $subcategory->localized_name }}
                </h1>
                @if($subcategory->description)
                    <p class="text-lg text-white/75 mb-3">{{ $subcategory->description }}</p>
                @endif
                <p class="text-white/70">
                    {{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}
                </p>
            </div>
            <div class="flex items-center justify-center h-32 text-white/80">
                <x-render-icon :icon="$subcategory->icon ?: 'heroicon-o-document-text'" class="w-24 h-24" />
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
                        @if(isset($cities))
                            <div>
                                <label for="city_id" class="form-label">{{ __('messages.public.city') }}</label>
                                <select id="city_id" name="city_id" class="form-select">
                                    <option value="">{{ __('messages.public.all_cities') }}</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" @selected(request('city_id') == $city->id)>
                                            {{ $city->localized_name }}
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

                @if(request()->anyFilled(['city_id', 'sort']))
                    <div class="mt-4">
                        <a href="{{ route('public.subcategory', $subcategory->slug) }}" class="btn btn-outline btn-sm w-full flex items-center justify-center gap-2">
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
                    message="{{ __('messages.public.no_providers_in_category') }}"
                    action-label="{{ __('messages.public.browse_all') }}"
                    action-url="{{ route('public.search') }}"
                />
            @endif
        </div>
    </div>
</section>

@endsection


```

