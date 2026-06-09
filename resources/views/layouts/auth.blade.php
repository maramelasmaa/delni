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
