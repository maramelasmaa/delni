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
