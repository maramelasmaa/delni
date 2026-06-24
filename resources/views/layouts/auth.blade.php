<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', config('app.name'))</title>

    <meta name="theme-color" content="#1E40AF">

    <script>
        (function(){
            var t = localStorage.getItem('delni-theme');
            if (t === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="relative flex min-h-screen items-center justify-center overflow-x-hidden bg-[#F6F8FF] px-4 py-12 font-sans text-[#0F172A] transition-colors duration-300 sm:px-6 lg:px-8 dark:bg-[#0B1120] dark:text-[#F1F5F9]">
    <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-72 bg-[radial-gradient(circle_at_top,rgba(30,64,175,0.18),transparent_60%)] dark:bg-[radial-gradient(circle_at_top,rgba(96,165,250,0.2),transparent_58%)]"></div>
        <div class="absolute -top-24 end-[-5rem] h-72 w-72 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#1E40AF]/30"></div>
        <div class="absolute bottom-[-7rem] start-[-4rem] h-72 w-72 rounded-full bg-[#DBEAFE]/80 blur-3xl dark:bg-[#1B2740]/70"></div>
    </div>

    <main class="relative z-10 w-full max-w-md">
        <div class="relative overflow-hidden rounded-[28px] border border-[#E8EEF8] bg-white/92 p-6 shadow-[0_24px_60px_rgba(30,64,175,0.14)] ring-1 ring-[#E8EEF8]/80 backdrop-blur-xl transition-all duration-300 sm:p-8 md:p-10 dark:border-[#243149] dark:bg-[#131C2E]/94 dark:ring-[#33425E]/70 dark:shadow-[0_24px_70px_rgba(2,6,23,0.45)]">

            @unless(View::hasSection('hide_home_back'))
                <a
                    href="@if(Route::has('home')){{ route('home') }}@else{{ url('/') }}@endif"
                    class="absolute start-6 top-6 flex h-10 w-10 items-center justify-center rounded-full border border-[#CBD5E1] bg-white text-[#1E40AF] shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-[#1E40AF]/30 hover:bg-[#EFF6FF] dark:border-[#33425E] dark:bg-[#16203A] dark:text-[#60A5FA] dark:hover:border-[#60A5FA]/40 dark:hover:bg-[#1B2740]"
                    aria-label="الرجوع للرئيسية"
                >
                    <x-render-icon icon="app-back" class="h-5 w-5 rtl:scale-x-[-1]" />
                </a>
            @endunless

            <div class="mb-6 flex justify-center">
                <a
                    href="@if(Route::has('home')){{ route('home') }}@else{{ url('/') }}@endif"
                    aria-label="{{ config('app.name') }}"
                    class="inline-flex items-center justify-center overflow-hidden rounded-[22px] border border-[#E8EEF8] bg-[#F8FAFF] p-1.5 shadow-[0_12px_30px_rgba(30,64,175,0.12)] transition-all duration-200 hover:scale-105 hover:shadow-[0_16px_36px_rgba(30,64,175,0.18)] dark:border-[#33425E] dark:bg-[#16203A]"
                    style="width: 76px; height: 76px;"
                >
                    <img src="{{ asset('images/photo_2026-06-22_23-21-55.jpg') }}" alt="{{ config('app.name') }}" class="h-full w-full rounded-[16px] object-cover">
                </a>
            </div>

            <header class="mb-8 text-center">
                @hasSection('auth_eyebrow')
                    <div class="mb-3 inline-flex items-center justify-center rounded-full border border-[#BFDBFE] bg-[#DBEAFE] px-3 py-1 text-xs font-extrabold tracking-wide text-[#1E40AF] dark:border-[#33425E] dark:bg-[#1E3A8A]/25 dark:text-[#93C5FD]">
                        @yield('auth_eyebrow')
                    </div>
                @endif

                <h1 class="text-2xl font-black leading-tight text-[#0F172A] sm:text-3xl dark:text-[#F1F5F9]">
                    @yield('auth_title')
                </h1>

                @hasSection('auth_subtitle')
                    <p class="mt-2 text-sm font-semibold leading-relaxed text-[#475569] sm:text-base dark:text-[#A8B4C8]">
                        @yield('auth_subtitle')
                    </p>
                @endif
            </header>

            @yield('content')
        </div>
    </main>
</body>
</html>
