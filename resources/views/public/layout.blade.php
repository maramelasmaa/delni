<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')

    <style>
        :root {
            --delni-primary: #F1620F;
            --delni-navy: #0B1A34;
            --delni-bg: #FCFBFB;
            --delni-gray: #C7C3C3;
            --delni-muted: #5D5959;
            --delni-border: #E7E7E7;
            --delni-success: #22C55E;
            --delni-warning: #F59E0B;

            --delni-radius-sm: 12px;
            --delni-radius-md: 18px;
            --delni-radius-lg: 26px;

            --delni-shadow-sm: 0 8px 20px rgba(11, 26, 52, .05);
            --delni-shadow-md: 0 16px 36px rgba(11, 26, 52, .08);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--delni-bg);
            color: var(--delni-navy);
            font-family: 'Cairo', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            text-align: start;
        }

        a {
            color: inherit;
        }

        img,
        svg {
            max-width: 100%;
            max-height: 100%;
        }

        .container {
            width: min(100% - 2rem, 1240px);
            margin-inline: auto;
        }

        .delni-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(252, 251, 251, .88);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--delni-border);
        }

        .delni-header__inner {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .delni-logo {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            color: var(--delni-navy);
            text-decoration: none;
            font-size: 1.45rem;
            font-weight: 950;
            letter-spacing: -.04em;
        }

        .delni-logo__mark {
            width: 46px;
            height: 46px;
            border-radius: 15px;
            overflow: hidden;
            background: var(--delni-navy);
            box-shadow: var(--delni-shadow-sm);
        }

        .delni-logo__mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .delni-nav {
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .delni-nav a {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .55rem .9rem;
            border-radius: 999px;
            color: var(--delni-muted);
            text-decoration: none;
            font-size: .92rem;
            font-weight: 850;
        }

        .delni-nav a:hover,
        .delni-nav a.is-active {
            color: var(--delni-primary);
            background: rgba(241, 98, 15, .08);
        }

        .delni-actions {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .delni-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            padding: .7rem 1rem;
            border-radius: 14px;
            border: 1px solid transparent;
            font-family: inherit;
            font-size: .9rem;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition: .18s ease;
        }

        .delni-btn--primary {
            background: var(--delni-primary);
            color: #fff;
            box-shadow: 0 12px 24px rgba(241, 98, 15, .22);
        }

        .delni-btn--primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(241, 98, 15, .28);
        }

        .delni-btn--ghost {
            background: #fff;
            color: var(--delni-navy);
            border-color: var(--delni-border);
        }

        .delni-btn--ghost:hover {
            border-color: rgba(241, 98, 15, .28);
            color: var(--delni-primary);
        }

        .delni-main {
            min-height: calc(100vh - 76px);
        }

        .delni-footer {
            margin-top: 4rem;
            padding: 2rem 0;
            border-top: 1px solid var(--delni-border);
            background: #fff;
            color: var(--delni-muted);
            font-size: .9rem;
            font-weight: 600;
        }

        .delni-footer__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .delni-footer a {
            color: var(--delni-muted);
            text-decoration: none;
            font-weight: 800;
        }

        .delni-footer a:hover {
            color: var(--delni-primary);
        }

        @media (max-width: 760px) {
            .container {
                width: min(100% - 1.25rem, 1240px);
            }

            .delni-header__inner {
                min-height: 68px;
                gap: .4rem;
            }

            .delni-logo {
                font-size: 1.2rem;
            }

            .delni-logo__mark {
                width: 40px;
                height: 40px;
                border-radius: 13px;
            }

            .delni-nav {
                gap: .25rem;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                flex-shrink: 0;
            }

            .delni-nav a {
                min-height: 40px;
                padding: .5rem .7rem;
                font-size: .85rem;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .delni-btn {
                min-height: 40px;
                padding: .6rem .8rem;
                font-size: .84rem;
            }
        }
    </style>
</head>

<body>
    <header class="delni-header">
        <div class="container">
            <div class="delni-header__inner">
                <a href="{{ route('home') }}" class="delni-logo">
                    <span class="delni-logo__mark">
                        <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('app.name') }}">
                    </span>
                    <span>دلني</span>
                </a>

                <nav class="delni-nav" aria-label="Main navigation">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">
                        الرئيسية
                    </a>
                    <a href="{{ route('public.top-rated') }}" class="{{ request()->routeIs('public.top-rated') ? 'is-active' : '' }}">
                        الأعلى تقييماً
                    </a>
                    <a href="{{ route('public.search') }}" class="{{ request()->routeIs('public.search') ? 'is-active' : '' }}">
                        بحث
                    </a>
                    <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'is-active' : '' }}">
                        {{ __('messages.public.contact_us') }}
                    </a>
                </nav>

                <div class="delni-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="delni-btn delni-btn--ghost">لوحتي</a>
                    @else
                        <a href="{{ route('login') }}" class="delni-btn delni-btn--primary">تسجيل</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="delni-main">
        @yield('content')
    </main>

    <footer class="delni-footer">
        <div class="container">
            <div class="delni-footer__inner">
                <span>© {{ date('Y') }} دلني. جميع الحقوق محفوظة.</span>
                <div>
                    <a href="{{ route('privacy') }}">الخصوصية</a>
                    ·
                    <a href="{{ route('terms') }}">الشروط</a>
                </div>
            </div>
        </div>
    </footer>

    <x-chatbot-widget />

    @stack('scripts')
</body>
</html>
