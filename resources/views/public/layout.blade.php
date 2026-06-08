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
            padding: 1.25rem 0;
        }

        .site-navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #ffffff;
            font-weight: 900;
            font-size: 1rem;
        }

        .navbar-brand img {
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .navbar-menu a {
            padding: 0.5rem 0.75rem;
            text-decoration: none;
            color: rgba(255,255,255,0.75);
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 6px;
            transition: 0.2s ease;
        }

        .navbar-menu a:hover {
            color: #ffffff;
            background: rgba(255,255,255,0.1);
        }

        .navbar-menu a.active {
            color: #ff7a1a;
        }

        .btn-text {
            color: rgba(255,255,255,0.75) !important;
        }

        .btn-primary {
            background: #f1620f !important;
            color: #ffffff !important;
            border: none !important;
        }

        .btn-primary:hover {
            background: #d9540d !important;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        footer {
            background: #07142b;
            color: rgba(255, 255, 255, 0.72);
            padding: 4rem 0 2rem;
        }

        footer a {
            color: rgba(255, 255, 255, 0.72);
            text-decoration: none;
            transition: 0.2s ease;
        }

        footer a:hover {
            color: #f1620f;
        }

        .footer-brand {
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .footer-bottom {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-logo {
            border-radius: 12px;
            object-fit: cover;
        }
    </style>
</head>

<body>
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

    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="{{ asset('images/logo.jpg') }}" alt="logo" class="brand-logo" style="width: 50px; height: 50px;">
                        <div>
                            <div class="footer-brand">{{ config('app.name', 'دلني') }}</div>
                            <small>{{ __('messages.public.tagline') }}</small>
                        </div>
                    </div>

                    <p class="mb-0">
                        {{ __('messages.public.marketplace_description') }}
                    </p>
                </div>

                <div class="col-6 col-lg-2">
                    <h6 class="mb-3 text-white">{{ __('messages.public.quick_links') }}</h6>

                    <div class="d-grid gap-2">
                        <a href="{{ route('home') }}">{{ __('messages.public.home') }}</a>
                        <a href="{{ route('public.search') }}">{{ __('messages.public.search') }}</a>
                        <a href="{{ route('register') }}">{{ __('messages.register') }}</a>
                    </div>
                </div>

                <div class="col-6 col-lg-2">
                    <h6 class="mb-3 text-white">{{ __('messages.public.legal') }}</h6>

                    <div class="d-grid gap-2">
                        <a href="{{ route('privacy') }}">{{ __('messages.public.privacy') }}</a>
                        <a href="{{ route('terms') }}">{{ __('messages.public.terms') }}</a>
                        <a href="{{ route('disclaimer') }}">{{ __('messages.public.disclaimer') }}</a>
                    </div>
                </div>

                <div class="col-lg-3">
                    <h6 class="mb-3 text-white">{{ __('messages.public.need_help') }}</h6>

                    <p class="mb-3">
                        {{ __('messages.public.need_help_text') }}
                    </p>

                    <a href="{{ route('public.search') }}" class="btn btn-primary btn-sm">
                        {{ __('messages.public.start_search') }}
                    </a>
                </div>
            </div>

            <div class="footer-bottom d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'دلني') }}. {{ __('messages.public.all_rights_reserved') }}</span>
                <span>{{ __('messages.public.built_for_libya') }}</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
