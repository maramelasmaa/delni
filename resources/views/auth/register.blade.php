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
