<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/png" href="{{ asset('images/icon-192.png') }}" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-192.png') }}">

    <meta name="theme-color" content="#0B1A34">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">

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

            /* PWA Native UI Specifications */
            --pwa-nav-height: 64px;
            --pwa-header-height: 60px;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Locks desktop scroll bounces, handles layout natively */
            background: var(--delni-bg);
            color: var(--delni-navy);
            font-family: 'Cairo', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .delni-splash {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .85rem;
            background: var(--delni-navy);
            opacity: 1;
            transition: opacity .4s ease;
        }

        .delni-splash img {
            width: 96px;
            height: 96px;
            border-radius: 24px;
            animation: delni-splash-pop .5s ease;
        }

        .delni-splash strong {
            color: #fff;
            font-size: 1.6rem;
            font-weight: 950;
            letter-spacing: -.03em;
        }

        .delni-splash.is-done {
            opacity: 0;
            pointer-events: none;
        }

        @keyframes delni-splash-pop {
            from { transform: scale(.85); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Continuous Structural Flex Framework */
        .pwa-shell {
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: -webkit-fill-available;
        }

        /* Custom Header Wrapper */
        .delni-header {
            height: calc(var(--pwa-header-height) + env(safe-area-inset-top));
            padding-top: env(safe-area-inset-top);
            background: #ffffff;
            border-bottom: 1px solid var(--delni-border);
            position: relative;
            z-index: 100;
            flex-shrink: 0;
        }

        .delni-header__inner {
            height: var(--pwa-header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
        }

        .delni-logo {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: 1.2rem;
            font-weight: 950;
            letter-spacing: -.04em;
        }

        .delni-logo__mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            overflow: hidden;
            background: var(--delni-navy);
        }

        .delni-logo__mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Dedicated App Viewport Container */
        .delni-main {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: calc(var(--pwa-nav-height) + env(safe-area-inset-bottom) + 20px);
        }

        .pwa-view-boundary {
            width: min(100% - 1.5rem, 1240px);
            margin-inline: auto;
            padding-top: .85rem;
        }

        /* Top Desktop View Navigation Items Link List Wrapper */
        .delni-nav, .delni-actions {
            display: none;
        }

        /* Persistent High-Fidelity App Bottom Navigation Bar */
        .pwa-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: calc(var(--pwa-nav-height) + env(safe-area-inset-bottom));
            padding-bottom: env(safe-area-inset-bottom);
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--delni-border);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            z-index: 999;
        }

        .pwa-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--delni-muted);
            font-size: 0.72rem;
            font-weight: 700;
            gap: 4px;
            transition: color 0.2s ease;
            text-decoration: none;
        }

        .pwa-nav-item.active {
            color: var(--delni-primary);
        }

        .pwa-nav-item svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
            transition: transform 0.2s ease;
        }

        .pwa-tab-item.is-active {
            color: var(--delni-primary);
        }

        .pwa-tab-item:active svg {
            transform: scale(0.92);
        }

        /* PWA Inline Core Footer Adjustments */
        .delni-footer {
            margin-top: 4rem;
            padding: 2rem 0;
            border-top: 1px solid var(--delni-border);
            background: #fff;
            color: var(--delni-muted);
            font-size: .85rem;
            font-weight: 600;
        }

        .delni-footer__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .delni-footer a:hover {
            color: var(--delni-primary);
        }

        /* Wide Screen Layout Desktop Enhancements */
        @media (min-width: 1025px) {
            html, body { overflow: visible; }
            .pwa-shell { height: auto; }
            .delni-main { overflow-y: visible; padding-bottom: 0; }
            .pwa-tab-bar { display: none; }
            .delni-nav { display: flex; align-items: center; gap: .35rem; }
            .delni-actions { display: flex; align-items: center; gap: .6rem; }

            .delni-nav a {
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: .55rem .9rem;
                border-radius: 999px;
                color: var(--delni-muted);
                font-size: .92rem;
                font-weight: 850;
            }

            .delni-nav a:hover,
            .delni-nav a.is-active {
                color: var(--delni-primary);
                background: rgba(241, 98, 15, .08);
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
                font-size: .9rem;
                font-weight: 900;
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
        }
    </style>
</head>

<body>
    <div class="delni-splash" id="delniSplash" aria-hidden="true">
        <img src="{{ asset('images/icon-192.png') }}" alt="" width="96" height="96">
        <strong>دلني</strong>
    </div>
    <script>
        (() => {
            const splash = document.getElementById('delniSplash');
            if (sessionStorage.getItem('delni_splash_shown')) {
                splash.remove();
                return;
            }
            sessionStorage.setItem('delni_splash_shown', '1');
            const dismiss = () => {
                splash.classList.add('is-done');
                setTimeout(() => splash.remove(), 450);
            };
            window.addEventListener('load', () => setTimeout(dismiss, 350));
            setTimeout(dismiss, 2500);
        })();
    </script>

    <div class="pwa-shell">

        <header class="delni-header">
            <div class="delni-header__inner">
                <a href="{{ route('home') }}" class="delni-logo">
                    <div class="delni-logo__mark">
                        <img src="{{ asset('images/icon-192.png') }}" alt="دلني" width="36" height="36">
                    </div>
                    <span style="color: var(--delni-navy);">دلني</span>
                </a>

                <nav class="delni-nav">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">الرئيسية</a>
                    <a href="{{ route('public.categories') }}" class="{{ request()->routeIs('public.categories') ? 'is-active' : '' }}">الفئات</a>
                    <a href="{{ route('public.top-rated') }}" class="{{ request()->routeIs('public.top-rated') ? 'is-active' : '' }}">الأعلى تقييماً</a>
                </nav>

                <div class="delni-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="delni-btn delni-btn--ghost">لوحتي</a>
                    @else
                        <a href="{{ route('login') }}" class="delni-btn delni-btn--ghost">دخول</a>
                        <a href="{{ route('register') }}" class="delni-btn delni-btn--primary">سجّل مجاناً</a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="delni-main">
            <div class="pwa-view-boundary">
                @yield('content')

                <footer class="delni-footer">
                    <div class="delni-footer__inner">
                        <span>© {{ date('Y') }} دلني. جميع الحقوق محفوظة.</span>
                        <div>
                            <a href="{{ route('privacy') }}">الخصوصية</a>
                            ·
                            <a href="{{ route('terms') }}">الشروط</a>
                        </div>
                    </div>
                </footer>
            </div>
        </main>

        <nav class="pwa-bottom-nav" aria-label="Mobile Navigation Bar">
            <a href="{{ route('home') }}" class="pwa-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                <x-render-icon icon="app-home" />
                <span>الرئيسية</span>
            </a>
            <a href="{{ route('public.top-rated') }}" class="pwa-nav-item {{ request()->routeIs('public.top-rated') ? 'active' : '' }}">
                <x-render-icon icon="app-star" />
                <span>الأعلى تقييماً</span>
            </a>
            <a href="{{ route('contact') }}" class="pwa-nav-item {{ request()->routeIs('contact') ? 'active' : '' }}">
                <x-render-icon icon="app-contact" />
                <span>اتصل بنا</span>
            </a>
            <a href="{{ route('dashboard') }}" class="pwa-nav-item {{ request()->routeIs('dashboard') || request()->routeIs('login') ? 'active' : '' }}">
                <x-render-icon icon="app-account" />
                <span>{{ Auth::check() ? 'لوحتي' : 'تسجيل' }}</span>
            </a>
        </nav>
    </div>

    @stack('scripts')

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
</body>
</html>
