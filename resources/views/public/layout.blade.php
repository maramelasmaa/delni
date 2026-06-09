<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'دلني'))</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    <link rel="dns-prefetch" href="//images.unsplash.com">
    <link rel="preconnect" href="//images.unsplash.com" crossorigin>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <meta name="referrer" content="strict-origin-when-cross-origin">

    @stack('styles')

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
            font-family: 'Cairo', sans-serif;
            background: #ffffff;
        }

        body {
            overflow-x: hidden;
        }

        .site-navbar {
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 100;
            background: transparent;
            border-bottom: none;
            padding: 1.1rem 0;
        }

        body.page-search .site-navbar {
            position: static;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.75rem 0;
        }

        .site-navbar .container {
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }

        [dir="rtl"] .navbar-menu {
            margin-right: auto;
            margin-left: 0;
        }

        [dir="ltr"] .navbar-menu {
            margin-left: auto;
            margin-right: 0;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #ffffff;
            font-weight: 900;
            font-size: 1.2rem;
            line-height: 1.1;
            flex-shrink: 0;
        }

        body.page-search .navbar-brand {
            color: #0f172a;
        }

        .navbar-brand img {
            width: 38px;
            height: 38px;
            border-radius: 8px;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 1.8rem;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-direction: row;
        }

        .navbar-menu a {
            padding: 0.4rem 0.6rem;
            text-decoration: none;
            color: rgba(255,255,255,0.75);
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            transition: 0.2s ease;
            line-height: 1.3;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .navbar-menu a:hover {
            color: #ffffff;
            background: rgba(255,255,255,0.1);
        }

        .navbar-menu a.active {
            color: #ff7a1a;
        }

        body.page-search .navbar-menu a {
            color: #475569;
        }

        body.page-search .navbar-menu a:hover {
            color: #0f172a;
            background: #f3f4f6;
        }

        body.page-search .navbar-menu a.active {
            color: #ff7a1a;
        }

        .btn-text {
            color: rgba(255,255,255,0.75) !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%) !important;
            color: #ffffff !important;
            border: none !important;
            padding: 0.5rem 1.2rem !important;
            border-radius: 8px !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            box-shadow: 0 6px 14px rgba(255, 107, 26, 0.14) !important;
            transition: 0.2s ease !important;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(255, 107, 26, 0.22) !important;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            white-space: nowrap;
        }

        .btn-logout {
            padding: 0.4rem 0.6rem;
            background: transparent;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            color: #4b5563;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn-logout:hover {
            border-color: #f1620f;
            color: #f1620f;
        }

        main {
            margin: 0;
            padding: 0;
        }

        main > *:first-child {
            margin-top: 0 !important;
        }

        footer.footer {
            background: #07142b;
            color: rgba(255, 255, 255, 0.72);
            padding: 4rem 0 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3rem;
            margin-bottom: 2.5rem;
            padding-bottom: 2.5rem;
        }

        .footer-column {
            display: flex;
            flex-direction: column;
        }

        .footer-brand-column {
            grid-column: 1;
        }

        .footer-logo-group {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .footer-logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .footer-brand-title {
            margin: 0;
            color: #ffffff;
            font-size: 1.3rem;
            font-weight: 900;
            letter-spacing: -0.01em;
        }

        .footer-description {
            margin: 0;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.6;
        }

        .footer-heading {
            margin: 0 0 1.5rem;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 900;
            letter-spacing: -0.01em;
        }

        .footer-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.72);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: 0.2s ease;
            display: inline-block;
        }

        .footer-links a:hover {
            color: #ff7a1a;
            padding-inline-start: 0.3rem;
        }

        .footer-help-column {
            grid-column: 4;
        }

        .footer-help-text {
            margin: 0 0 1.5rem;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.6;
        }

        .footer-cta-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 900;
            text-align: center;
            transition: 0.2s ease;
            box-shadow: 0 12px 28px rgba(255, 107, 26, 0.22);
            width: fit-content;
        }

        .footer-cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(255, 107, 26, 0.32);
        }

        .footer-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 0 0 2rem;
        }

        .footer-bottom {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            color: rgba(255, 255, 255, 0.64);
            font-size: 0.92rem;
            font-weight: 600;
        }

        /* === RESPONSIVE === */
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2.5rem;
                margin-bottom: 2rem;
                padding-bottom: 2rem;
            }

            .footer-brand-column {
                grid-column: 1 / -1;
            }

            .footer-help-column {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
                margin-bottom: 1.5rem;
                padding-bottom: 1.5rem;
            }

            .footer-brand-column,
            .footer-help-column {
                grid-column: 1;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        .brand-logo {
            border-radius: 12px;
            object-fit: cover;
        }
    </style>
</head>

<body @class(['page-search' => request()->routeIs('public.search')])>
    <nav class="site-navbar">
        <div class="container">
            <a href="{{ route('home') }}" class="navbar-brand">
                <img src="{{ asset('images/logo.jpg') }}" alt="logo">
                <span>{{ config('app.name', 'دلني') }}</span>
            </a>

            <ul class="navbar-menu">
                <li><a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>{{ __('messages.public.home') }}</a></li>
                <li><a href="{{ route('public.search') }}" @class(['active' => request()->routeIs('public.search')])>{{ __('messages.public.search') }}</a></li>
                @guest
                    <li><a href="{{ route('login') }}" class="btn-text">{{ __('messages.login') }}</a></li>
                    <li><a href="{{ route('register') }}" class="btn-primary">{{ __('messages.register') }}</a></li>
                @else
                    <li class="navbar-user">
                        <a href="{{ route('dashboard') }}">{{ auth()->user()->name }}</a>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-logout">{{ __('messages.logout') }}</button>
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </nav>

    <main>
        @if ($errors->any())
            <div class="container mt-4">
                <div class="alert delni-alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ __('messages.error') }}</strong>

                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.close') }}"></button>
                </div>
            </div>
        @endif

        @foreach (['success' => 'alert-success', 'warning' => 'alert-warning', 'error' => 'alert-danger'] as $key => $class)
            @if (session($key))
                <div class="container mt-4">
                    <div class="alert delni-alert {{ $class }} alert-dismissible fade show" role="alert">
                        {{ session($key) }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('messages.close') }}"></button>
                    </div>
                </div>
            @endif
        @endforeach

        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-column footer-brand-column">
                    <div class="footer-logo-group">
                        <img src="{{ asset('images/logo.jpg') }}" alt="logo" class="footer-logo">
                        <h3 class="footer-brand-title">{{ config('app.name', 'دلني') }}</h3>
                    </div>
                    <p class="footer-description">
                        {{ __('messages.public.marketplace_description') }}
                    </p>
                </div>

                <!-- Quick Links Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">{{ __('messages.public.quick_links') }}</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}">{{ __('messages.public.home') }}</a></li>
                        <li><a href="{{ route('public.search') }}">{{ __('messages.public.search') }}</a></li>
                        <li><a href="{{ route('register') }}">{{ __('messages.register') }}</a></li>
                    </ul>
                </div>

                <!-- Legal Column -->
                <div class="footer-column">
                    <h4 class="footer-heading">{{ __('messages.public.legal') }}</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('privacy') }}">{{ __('messages.public.privacy') }}</a></li>
                        <li><a href="{{ route('terms') }}">{{ __('messages.public.terms') }}</a></li>
                        <li><a href="{{ route('disclaimer') }}">{{ __('messages.public.disclaimer') }}</a></li>
                    </ul>
                </div>

                <!-- Help Column -->
                <div class="footer-column footer-help-column">
                    <h4 class="footer-heading">{{ __('messages.public.need_help') }}</h4>
                    <p class="footer-help-text">
                        {{ __('messages.public.need_help_text') }}
                    </p>
                    <a href="{{ route('public.search') }}" class="footer-cta-btn">
                        {{ __('messages.public.start_search') }}
                    </a>
                </div>
            </div>

            <div class="footer-divider"></div>

            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'دلني') }}. {{ __('messages.public.all_rights_reserved') }}</span>
                <span>{{ __('messages.public.built_for_libya') }}</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
