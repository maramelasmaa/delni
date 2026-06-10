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

        /* Structural Card Footer Rules */
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
