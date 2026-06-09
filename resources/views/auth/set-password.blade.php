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
