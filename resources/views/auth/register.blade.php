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

        <label class="auth-terms-label">
            <input
                type="checkbox"
                name="terms_accepted"
                class="auth-terms-check @error('terms_accepted') is-invalid @enderror"
                {{ old('terms_accepted') ? 'checked' : '' }}
            >
            <span>
                أوافق على
                <a href="{{ route('terms') }}" target="_blank" rel="noopener">شروط الاستخدام</a>
                و
                <a href="{{ route('privacy') }}" target="_blank" rel="noopener">سياسة الخصوصية</a>
            </span>
        </label>
        @error('terms_accepted')
            <span class="auth-error-text" style="margin-top: -.5rem;">{{ $message }}</span>
        @enderror

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
