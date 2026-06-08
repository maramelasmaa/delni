<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.dashboard') }}</title>
</head>
<body>
    <h1>{{ __('messages.dashboard') }}</h1>
    <p>{{ __('messages.welcome') }}, {{ auth()->user()->name }}</p>
    <p><a href="{{ route('home') }}">{{ __('messages.public.home') }}</a></p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">{{ __('messages.logout') }}</button>
    </form>
</body>
</html>
