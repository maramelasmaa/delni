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
