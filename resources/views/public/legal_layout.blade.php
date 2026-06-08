<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - {{ $title ?? __('messages.public.legal') }}</title>

    {{-- Favicon & App Icons --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    <style>
        body { font-family: Arial, sans-serif; margin: 0; color: #111827; background: #f9fafb; }
        header, main, footer { max-width: 1120px; margin: 0 auto; padding: 16px; }
        header { display: flex; gap: 16px; align-items: center; justify-content: space-between; border-bottom: 1px solid #d1d5db; }
        a { color: #1d4ed8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .legal-page { background: #fff; padding: 24px; border-radius: 8px; }
        .legal-page h1 { margin-bottom: 8px; }
        .last-updated { color: #6b7280; font-size: 14px; }
        .legal-page h2 { margin-top: 24px; margin-bottom: 12px; font-size: 18px; }
        .legal-page p { line-height: 1.6; margin-bottom: 12px; }
        .legal-page ul { margin: 12px 0; padding-left: 24px; }
        .legal-page li { margin-bottom: 8px; }
        footer { border-top: 1px solid #d1d5db; margin-top: 48px; padding-top: 32px; }
        .footer-links { display: flex; flex-wrap: wrap; gap: 16px; }
    </style>
</head>
<body>
<header>
    <div>
        <strong><a href="/">{{ config('app.name') }}</a></strong>
    </div>
    <nav>
        <a href="{{ route('home') }}">{{ __('messages.public.home') }}</a>
        <a href="{{ route('public.search') }}">{{ __('messages.public.search') }}</a>
    </nav>
</header>

<main>
    @yield('content')
</main>

<footer>
    <div class="footer-links">
        <a href="{{ route('privacy') }}">{{ __('messages.public.privacy') }}</a>
        <a href="{{ route('terms') }}">{{ __('messages.public.terms') }}</a>
        <a href="{{ route('disclaimer') }}">{{ __('messages.public.disclaimer') }}</a>
    </div>
</footer>
</body>
</html>

