<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', config('app.name'))</title>

    <meta name="theme-color" content="#0B1A34">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">

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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen font-['Cairo',system-ui,-apple-system,sans-serif] bg-slate-50 dark:bg-[#0D1117] text-slate-900 dark:text-slate-50 transition-colors duration-300 relative overflow-x-hidden flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    {{-- High Fidelity Radial Background Glows --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-primary/12 dark:bg-primary/18 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-sky-400/10 dark:bg-orange-500/10 rounded-full blur-3xl"></div>
    </div>

    <main class="relative z-10 w-full max-w-[440px]">
        <div class="relative w-full rounded-3xl bg-white/78 dark:bg-[#131A22]/92 border border-slate-200/60 dark:border-[#334155] p-6 sm:p-8 md:p-10 shadow-2xl backdrop-blur-xl transition-all duration-300">
            
            {{-- Back Button --}}
            @unless(View::hasSection('hide_home_back'))
                <a
                    href="@if(Route::has('home')){{ route('home') }}@else{{ url('/') }}@endif"
                    class="absolute top-6 start-6 flex h-10 w-10 items-center justify-center rounded-full border border-slate-200/80 dark:border-[#334155] bg-white/85 dark:bg-[#0D1117] text-primary dark:text-orange-300 hover:border-primary/50 hover:text-primary-dark dark:hover:text-orange-200 hover:bg-orange-50/50 dark:hover:bg-[#1B2430] transition-all duration-200 shadow-sm hover:-translate-y-0.5"
                    aria-label="الرجوع للرئيسية"
                >
                    <x-render-icon icon="app-back" class="w-5 h-5 rtl:scale-x-[-1]" />
                </a>
            @endunless

            {{-- Brand Logo --}}
            <div class="flex justify-center mb-6">
                <a 
                    href="@if(Route::has('home')){{ route('home') }}@else{{ url('/') }}@endif" 
                    aria-label="{{ config('app.name') }}"
                    class="inline-flex h-[72px] w-[72px] items-center justify-center overflow-hidden rounded-2xl border border-slate-200/80 dark:border-[#334155] bg-white dark:bg-[#0D1117] p-1.5 shadow-md hover:scale-105 hover:shadow-lg transition-all duration-200"
                >
                    <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('app.name') }}" class="w-full h-full object-cover rounded-xl">
                </a>
            </div>

            {{-- Header Title & Subtitle --}}
            <header class="text-center mb-8">
                @hasSection('auth_eyebrow')
                    <div class="inline-flex items-center justify-center px-3 py-1 text-xs font-extrabold tracking-wide text-primary dark:text-orange-300 bg-orange-500/10 dark:bg-orange-500/12 border border-primary/20 dark:border-orange-400/20 rounded-full mb-3">
                        @yield('auth_eyebrow')
                    </div>
                @endif

                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-50 leading-tight">
                    @yield('auth_title')
                </h1>

                @hasSection('auth_subtitle')
                    <p class="text-sm sm:text-base text-slate-500 dark:text-[#CBD5E1] font-semibold leading-relaxed mt-2">
                        @yield('auth_subtitle')
                    </p>
                @endif
            </header>

            {{-- Dynamic Content --}}
            @yield('content')

        </div>
    </main>
</body>
</html>
