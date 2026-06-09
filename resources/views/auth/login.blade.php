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
