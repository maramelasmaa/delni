@extends('layouts.auth')

@section('title', __('auth.register_title') . ' - ' . config('app.name'))

@section('auth_title')
    انضم إلى <span class="text-primary-500">دلني</span>
@endsection

@section('auth_subtitle')
    أنشئ حسابك للوصول إلى أفضل الخدمات والمزودين في ليبيا بسهولة.
@endsection

@section('content')
    <div class="auth-form-head">
        <span class="auth-eyebrow">حساب جديد</span>

        <h2>
            {{ __('auth.register_title') }}
        </h2>

        <p>
            {{ __('auth.register_subtitle') }}
        </p>
    </div>

    @if ($errors->any())
        <div class="auth-alert">
            <strong>حدث خطأ</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="name">{{ __('auth.name') }}</label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                class="@error('name') is-invalid @enderror"
                placeholder="اسمك الكامل"
                autocomplete="name"
            >

            @error('name')
                <small>{{ $message }}</small>
            @enderror
        </div>

        <div class="auth-field">
            <label for="email">{{ __('auth.email') }}</label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="@error('email') is-invalid @enderror"
                placeholder="you@example.com"
                autocomplete="email"
            >

            @error('email')
                <small>{{ $message }}</small>
            @enderror
        </div>

        <div class="auth-field">
            <label for="phone">{{ __('auth.phone') }}</label>

            <input
                type="tel"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                required
                class="@error('phone') is-invalid @enderror"
                placeholder="+218 91 123 4567"
                autocomplete="tel"
            >

            @error('phone')
                <small>{{ $message }}</small>
            @enderror
        </div>

        <div class="auth-grid">
            <div class="auth-field">
                <label for="password">{{ __('auth.password') }}</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="@error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    autocomplete="new-password"
                >

                @error('password')
                    <small>{{ $message }}</small>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password_confirmation">{{ __('auth.confirm_password') }}</label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    placeholder="••••••••"
                    autocomplete="new-password"
                >
            </div>
        </div>

        <button type="submit" class="auth-submit">
            {{ __('auth.register_button') }}
        </button>
    </form>

    <div class="auth-switch">
        <p>{{ __('auth.already_account') }}</p>

        <a href="{{ route('login') }}">
            {{ __('auth.login_link') }}
        </a>
    </div>

    <style>
        .auth-form-head {
            margin-bottom: 2rem;
        }

        .auth-eyebrow {
            display: inline-flex;
            margin-bottom: 0.8rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(241, 98, 15, 0.1);
            color: #f1620f;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .auth-form-head h2 {
            margin: 0;
            color: #081427;
            font-size: 2.2rem;
            font-weight: 900;
            line-height: 1.15;
        }

        .auth-form-head p {
            margin: 0.7rem 0 0;
            color: #64748b;
            font-size: 0.98rem;
            line-height: 1.8;
        }

        .auth-alert {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 18px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            font-size: 0.9rem;
        }

        .auth-alert strong {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 900;
        }

        .auth-alert ul {
            margin: 0;
            padding-inline-start: 1.2rem;
        }

        .auth-form {
            display: grid;
            gap: 1rem;
        }

        .auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .auth-field label {
            display: block;
            margin-bottom: 0.45rem;
            color: #0f172a;
            font-size: 0.9rem;
            font-weight: 800;
        }

        .auth-field input {
            width: 100%;
            height: 54px;
            padding: 0 1rem;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            outline: none;
            transition: 0.2s ease;
        }

        .auth-field input:focus {
            border-color: rgba(241, 98, 15, 0.45);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(241, 98, 15, 0.09);
        }

        .auth-field input.is-invalid {
            border-color: #ef4444;
        }

        .auth-field small {
            display: block;
            margin-top: 0.4rem;
            color: #dc2626;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .auth-submit {
            width: 100%;
            height: 56px;
            margin-top: 0.6rem;
            border: 0;
            border-radius: 18px;
            background: linear-gradient(135deg, #ff7a1a, #f1620f);
            color: white;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 18px 38px rgba(241, 98, 15, 0.28);
            transition: 0.22s ease;
        }

        .auth-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 44px rgba(241, 98, 15, 0.36);
        }

        .auth-switch {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }

        .auth-switch p {
            margin: 0 0 0.55rem;
            color: #64748b;
            font-size: 0.95rem;
        }

        .auth-switch a {
            color: #f1620f;
            font-weight: 900;
            text-decoration: none;
        }

        .auth-switch a:hover {
            color: #d9530d;
        }

        @media (max-width: 640px) {
            .auth-grid {
                grid-template-columns: 1fr;
            }

            .auth-form-head h2 {
                font-size: 1.8rem;
            }
        }
    </style>
@endsection