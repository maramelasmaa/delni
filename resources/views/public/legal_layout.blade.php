<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', '')">

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #F1620F;
            --navy: #0B1A34;
            --bg: #FCFBFB;
            --surface: #FFFFFF;
            --border: #E7E7E7;
            --muted: #5D5959;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Cairo', system-ui, sans-serif;
            background: var(--bg);
            color: var(--navy);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; }
        img, svg { max-width: 100%; }

        .container {
            width: min(100% - 2rem, 1240px);
            margin-inline: auto;
        }

        /* Header */
        .legal-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .legal-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 70px;
            gap: 1.5rem;
        }

        .legal-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 900;
            color: var(--navy);
            letter-spacing: -0.02em;
            flex-shrink: 0;
        }

        .legal-logo img {
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }

        .legal-tabs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legal-tab {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .legal-tab:hover,
        .legal-tab.active {
            color: var(--primary);
            background: rgba(241, 98, 15, 0.08);
        }

        /* Main Content */
        .legal-main {
            padding: 2.5rem 0;
        }

        .legal-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
        }

        .legal-card h1 {
            font-size: clamp(1.75rem, 4vw, 2.2rem);
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .legal-card-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 600;
        }

        /* Content Sections */
        .legal-section {
            margin-bottom: 2rem;
        }

        .legal-section:last-child {
            margin-bottom: 0;
        }

        .legal-section h2 {
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .legal-section p {
            font-size: 0.95rem;
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .legal-section p:last-child {
            margin-bottom: 0;
        }

        .legal-section ul,
        .legal-section ol {
            padding-inline-start: 1.5rem;
            margin: 1rem 0;
        }

        .legal-section li {
            font-size: 0.95rem;
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 0.6rem;
            font-weight: 500;
        }

        .legal-section strong {
            color: var(--navy);
            font-weight: 700;
        }

        .legal-section a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .legal-section a:hover {
            text-decoration: underline;
        }

        /* Footer */
        .legal-footer {
            padding: 2rem 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--border);
            margin-top: 3rem;
        }

        .legal-footer a {
            color: var(--muted);
            text-decoration: none;
            font-weight: 700;
        }

        .legal-footer a:hover {
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .legal-header__inner {
                min-height: 64px;
            }

            .legal-logo {
                font-size: 1rem;
            }

            .legal-logo img {
                width: 32px;
                height: 32px;
            }

            .legal-tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                flex: 1;
                gap: 0.3rem;
            }

            .legal-tab {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .legal-card {
                padding: 1.25rem;
            }

            .legal-card h1 {
                font-size: 1.5rem;
            }

            .legal-card-meta {
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                gap: 0.75rem;
                flex-wrap: wrap;
            }

            .legal-section {
                margin-bottom: 1.5rem;
            }

            .legal-section h2 {
                font-size: 1.05rem;
                margin-bottom: 0.75rem;
            }

            .legal-section p,
            .legal-section li {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                width: min(100% - 1rem, 1240px);
            }

            .legal-header__inner {
                min-height: 60px;
                gap: 1rem;
            }

            .legal-logo {
                font-size: 0.95rem;
            }

            .legal-logo img {
                width: 30px;
                height: 30px;
            }

            .legal-tab {
                padding: 0.35rem 0.7rem;
                font-size: 0.75rem;
            }

            .legal-card {
                padding: 1rem;
            }

            .legal-card h1 {
                font-size: 1.35rem;
                margin-bottom: 0.4rem;
            }

            .legal-card-meta {
                font-size: 0.8rem;
                margin-bottom: 1.25rem;
                padding-bottom: 0.75rem;
            }

            .legal-section {
                margin-bottom: 1.25rem;
            }

            .legal-section h2 {
                font-size: 1rem;
                margin-bottom: 0.6rem;
            }

            .legal-section p,
            .legal-section li {
                font-size: 0.88rem;
                line-height: 1.7;
            }
        }
    </style>
</head>
<body>

<header class="legal-header">
    <div class="container">
        <div class="legal-header__inner">
            <a href="{{ route('home') }}" class="legal-logo">
                <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('app.name') }}">
                <span>دلني</span>
            </a>

            <nav class="legal-tabs">
                <a href="{{ route('privacy') }}"
                   class="legal-tab {{ request()->routeIs('privacy') ? 'active' : '' }}">
                    الخصوصية
                </a>
                <a href="{{ route('terms') }}"
                   class="legal-tab {{ request()->routeIs('terms') ? 'active' : '' }}">
                    الشروط
                </a>
                <a href="{{ route('disclaimer') }}"
                   class="legal-tab {{ request()->routeIs('disclaimer') ? 'active' : '' }}">
                    إخلاء
                </a>
            </nav>
        </div>
    </div>
</header>

<main class="legal-main">
    <div class="container">
        <article class="legal-card">
            @yield('content')
        </article>
    </div>
</main>

<footer class="legal-footer">
    <div class="container">
        © {{ date('Y') }} دلني
        <span style="opacity: 0.3; margin: 0 0.5rem;">•</span>
        <a href="{{ route('privacy') }}">الخصوصية</a>
        <span style="opacity: 0.3; margin: 0 0.5rem;">•</span>
        <a href="{{ route('terms') }}">الشروط</a>
        <span style="opacity: 0.3; margin: 0 0.5rem;">•</span>
        <a href="{{ route('disclaimer') }}">إخلاء</a>
    </div>
</footer>

@stack('scripts')
</body>
</html>
