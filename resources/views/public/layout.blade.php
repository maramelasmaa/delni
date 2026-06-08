<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'دلني'))</title>

    {{-- Favicon & App Icons --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    {{-- DNS Prefetch for faster asset loading --}}
    <link rel="dns-prefetch" href="//images.unsplash.com">
    <link rel="preconnect" href="//images.unsplash.com" crossorigin>

    {{-- Fonts with optimized loading --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet"></noscript>

    {{-- Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles are consolidated in resources/css/components.css via app.css -->

    {{-- Performance: Disable Resource Hints in Production for CSP Compliance --}}
    <meta name="referrer" content="strict-origin-when-cross-origin">

    @stack('styles')

    <style>
        .site-navbar {
            background: #0b1a34 !important;
            border-bottom: 2px solid #f1620f !important;
            backdrop-filter: none !important;
            padding: 0.5rem 0 !important;
        }

        .site-navbar .container {
            max-width: 1200px;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .site-navbar .brand-logo {
            width: 40px !important;
            height: 40px !important;
        }

        .site-navbar .brand-title {
            font-size: 1.1rem !important;
        }

        .site-navbar .navbar-nav {
            gap: 0.5rem !important;
        }

        .site-navbar .nav-link {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.9rem !important;
        }

        .site-navbar .nav-outline,
        .site-navbar .nav-cta {
            padding: 0.4rem 0.75rem !important;
            font-size: 0.85rem !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg site-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}" aria-label="{{ config('app.name', 'دلني') }}">
                <img src="{{ asset('images/logo.jpg') }}" alt="logo" class="brand-logo" style="width: 50px; height: auto;">
                <span class="brand-title" style="color: #fff; font-size: 22px; font-weight: 800;">{{ config('app.name', 'دلني') }}</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="{{ __('messages.public.toggle_navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            {{ __('messages.public.home') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('public.search') ? 'active' : '' }}" href="{{ route('public.search') }}">
                            {{ __('messages.public.search') }}
                        </a>
                    </li>

                    @if(Route::has('public.categories'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('category*') ? 'active' : '' }}" href="{{ route('public.categories') }}">
                                {{ __('messages.public.categories') }}
                            </a>
                        </li>
                    @endif

                    @if(Route::has('public.cities'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('city*') ? 'active' : '' }}" href="{{ route('public.cities') }}">
                                {{ __('messages.public.cities') }}
                            </a>
                        </li>
                    @endif
                </ul>

                <ul class="navbar-nav align-items-lg-center gap-2">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link nav-outline px-3" href="{{ route('login') }}">
                                {{ __('messages.login') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-cta px-3" href="{{ route('register') }}">
                                {{ __('messages.register') }}
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle nav-outline px-3" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu {{ app()->getLocale() === 'ar' ? 'dropdown-menu-start' : 'dropdown-menu-end' }}">
                                <li>
                                    <a class="dropdown-item" href="{{ route('dashboard') }}">
                                        {{ __('messages.dashboard') }}
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            {{ __('messages.logout') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
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
                        <img src="{{ asset('images/logo.jpg') }}" alt="logo" class="brand-logo" style="width: 50px; height: auto;">
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
                    <h6 class="mb-3">{{ __('messages.public.quick_links') }}</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('home') }}">{{ __('messages.public.home') }}</a>
                        <a href="{{ route('public.search') }}">{{ __('messages.public.search') }}</a>
                        <a href="{{ route('register') }}">{{ __('messages.register') }}</a>
                    </div>
                </div>

                <div class="col-6 col-lg-2">
                    <h6 class="mb-3">{{ __('messages.public.legal') }}</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('privacy') }}">{{ __('messages.public.privacy') }}</a>
                        <a href="{{ route('terms') }}">{{ __('messages.public.terms') }}</a>
                        <a href="{{ route('disclaimer') }}">{{ __('messages.public.disclaimer') }}</a>
                    </div>
                </div>

                <div class="col-lg-3">
                    <h6 class="mb-3">{{ __('messages.public.need_help') }}</h6>
                    <p class="mb-3">{{ __('messages.public.need_help_text') }}</p>
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
