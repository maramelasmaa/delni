<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.dashboard') }} - {{ config('app.name') }}</title>

    <style>
        /* Design System Variables */
        :root {
            --brand-primary: #F1620F;
            --brand-primary-hover: #D7530A;
            --brand-dark: #0B1A34;
            --brand-dark-light: #14284D;
            --bg-canvas: #F8FAFC;
            --bg-surface: #FFFFFF;
            --text-primary: #0B1A34;
            --text-secondary: #475569;
            --border-color: #E2E8F0;
            --transition-smooth: all 0.2s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-canvas);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* Clean Dashboard Core Panel Card */
        .dashboard-container {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(11, 26, 52, 0.05), 0 8px 10px -6px rgba(11, 26, 52, 0.05);
            max-width: 540px;
            width: 100%;
            padding: 2.25rem 2rem;
        }

        /* Header Presentation Elements */
        .dashboard-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .dashboard-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--brand-dark);
            margin-bottom: 0.35rem;
        }

        .welcome-message {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .user-highlight {
            color: var(--brand-dark);
            font-weight: 700;
        }

        /* Core Navigation Stack Panel */
        .action-workspace-panel {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        /* Button & Hyperlink Framework Normatives */
        .nav-action-btn {
            width: 100%;
            height: 46px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: var(--transition-smooth);
        }

        .btn-link-public {
            background-color: var(--bg-canvas);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-link-public:hover {
            background-color: #EDF2F7;
            border-color: #CBD5E1;
        }

        .btn-logout-trigger {
            background-color: #FEF2F2;
            border: 1px solid #FEE2E2;
            color: #DC2626;
        }

        .btn-logout-trigger:hover {
            background-color: #FEE2E2;
            border-color: #FCA5A5;
        }

        /* Multi-directional Alignment Tuning fixes */
        html[dir="rtl"] .dashboard-header {
            text-align: right;
        }
        html[dir="ltr"] .dashboard-header {
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        {{-- Structural Header Component Block --}}
        <header class="dashboard-header">
            <h1 class="dashboard-title">{{ __('messages.dashboard') }}</h1>
            <p class="welcome-message">
                {{ __('messages.welcome') }}, <span class="user-highlight">{{ auth()->user()->name }}</span>
            </p>
        </header>

        {{-- Interactive Operations Panel Matrix --}}
        <main class="action-workspace-panel">
            <a href="{{ route('home') }}" class="nav-action-btn btn-link-public">
                <span>{{ __('messages.public.home') }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
                @csrf
                <button type="submit" class="nav-action-btn btn-logout-trigger">
                    <span>{{ __('messages.logout') }}</span>
                </button>
            </form>
        </main>
    </div>

</body>
</html>
