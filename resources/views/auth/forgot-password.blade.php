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
