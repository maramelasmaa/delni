<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .auth-brand-svg {
            width: 40px;
            height: 40px;
            stroke: #F1620F;
            stroke-width: 2;
        }
        .auth-brand-path {
            fill: #F1620F;
        }
        .auth-decorative-path {
            stroke: #F1620F;
            stroke-width: 60;
            fill: none;
            stroke-linecap: round;
        }
    </style>
</head>
<body class="antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Brand Panel - Hidden on Mobile, Left on Desktop -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-navy-800 via-navy-750 to-navy-800 flex-col justify-between p-12 relative overflow-hidden">
            <!-- Decorative Animated Background -->
            <div class="absolute inset-0 opacity-20">
                <svg class="w-full h-full" viewBox="0 0 400 600" preserveAspectRatio="xMidYMid slice">
                    <path class="auth-decorative-path" d="M200 50 Q220 150, 210 250 Q200 350, 215 450 Q220 550, 200 600"/>
                </svg>
            </div>

            <!-- Brand Content -->
            <div class="relative z-10">
                <!-- Logo -->
                <div class="flex items-center gap-3 mb-16">
                    <svg class="auth-brand-svg" viewBox="0 0 40 40" fill="none">
                        <circle cx="20" cy="20" r="18"/>
                        <path class="auth-brand-path" d="M20 8v24M20 32l-3-4h6l-3 4"/>
                    </svg>
                    <span class="text-white text-2xl font-bold tracking-tight">دلني</span>
                </div>

                <!-- Main Message -->
                <h1 class="text-white text-5xl font-black leading-tight mb-8">
                    @yield('auth_title', 'دلني لأفضل<br/><span class="text-primary-500">الخدمات والمزودين</span>')
                </h1>

                <!-- Description -->
                <p class="text-gray-300 text-lg leading-relaxed max-w-lg">
                    @yield('auth_subtitle', 'ابحث، قارن، واتصل مع أفضل المزودين في منطقتك بسهولة وثقة.')
                </p>
            </div>

            <!-- Trust Indicators -->
            <div class="relative z-10 flex gap-6 text-gray-400 text-sm">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    آمن وموثوق
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    تقييمات حقيقية
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    خدمة سريعة
                </span>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 py-12 lg:px-12">
            <!-- Mobile Brand Header -->
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <svg class="w-8 h-8" viewBox="0 0 40 40" fill="none" stroke="#F1620F" stroke-width="2">
                    <circle cx="20" cy="20" r="18"/>
                    <path fill="#F1620F" d="M20 8v24M20 32l-3-4h6l-3 4"/>
                </svg>
                <span class="text-navy-800 text-xl font-bold">دلني</span>
            </div>

            <!-- Form Container -->
            <div class="w-full max-w-md mx-auto">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
