<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ __('messages.dashboard') }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --dash-primary: #F1620F;
            --dash-navy: #0B1A34;
            --dash-bg: #FCFBFB;
            --dash-muted: #5D5959;
            --dash-border: #E7E7E7;
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 1rem;
            background: var(--dash-bg);
            color: var(--dash-navy);
            font-family: Cairo, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .dashboard-card {
            width: min(100%, 440px);
            padding: clamp(1.25rem, 4vw, 1.75rem);
            border: 1px solid var(--dash-border);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 16px 36px rgba(11, 26, 52, .08);
        }
        .dashboard-brand {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .dashboard-brand img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .dashboard-title {
            margin: 0;
            font-size: 1.25rem;
            line-height: 1.4;
            font-weight: 950;
        }
        .dashboard-message {
            margin: .35rem 0 1.1rem;
            color: var(--dash-muted);
            font-size: .92rem;
            line-height: 1.8;
            font-weight: 650;
        }
        .dashboard-actions {
            display: grid;
            gap: .65rem;
        }
        .dashboard-actions form {
            margin: 0;
        }
        .dashboard-action {
            width: 100%;
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            border: 1px solid var(--dash-border);
            background: #F8FAFC;
            color: var(--dash-navy);
            font: inherit;
            font-size: .9rem;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }
        .dashboard-action--primary {
            border-color: var(--dash-primary);
            background: var(--dash-primary);
            color: #fff;
        }
        .dashboard-action--danger {
            background: #FEF2F2;
            border-color: #FECACA;
            color: #B91C1C;
        }
    </style>
</head>
<body>
    <main class="dashboard-card">
        <div class="dashboard-brand">
            <img src="{{ asset('images/icon-192.png') }}" alt="">
        </div>

        <h1 class="dashboard-title">{{ __('messages.dashboard') }}</h1>
        <p class="dashboard-message">
            {{ __('messages.welcome') }}, <strong>{{ auth()->user()->name }}</strong>
        </p>

        <div class="dashboard-actions">
            <a href="{{ route('home') }}" class="dashboard-action dashboard-action--primary">
                {{ __('messages.public.home') }}
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dashboard-action dashboard-action--danger">
                    {{ __('messages.logout') }}
                </button>
            </form>
        </div>
    </main>
</body>
</html>
