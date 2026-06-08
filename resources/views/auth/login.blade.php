@extends('layouts.auth')

@section('title', __('auth.login_title') . ' - ' . config('app.name'))

@section('auth_title')
    دلني لأفضل<br/><span class="text-primary-500">الخدمات والمزودين</span>
@endsection

@section('auth_subtitle')
    ابحث، قارن، واتصل مع أفضل المزودين في منطقتك بسهولة وثقة.
@endsection

@section('content')
    <!-- Header -->
    <div class="mb-10">
        <h2 class="text-4xl font-black text-navy-800 mb-2">{{ __('auth.login_title') }}</h2>
        <p class="text-gray-600 text-base leading-relaxed">{{ __('auth.login_subtitle') }}</p>
    </div>

    <!-- Error Alert -->
    @if ($errors->any())
        <div class="bg-danger-50 border border-danger-200 rounded-lg p-4 mb-8">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-danger-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-danger-700">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Login Form -->
    <form action="{{ route('login') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.email') }}
            </label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="input @error('email') border-danger-500 @enderror"
                placeholder="you@example.com"
                autocomplete="email"
            />
            @error('email')
                <p class="text-danger-600 text-sm mt-2 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.25-1.502l-3.905 3.088-1.652-1.652a1 1 0 10-1.414 1.414l2.359 2.359a1 1 0 001.563-.163l4.899-6.138z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password Field -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-sm font-semibold text-navy-800">
                    {{ __('auth.password') }}
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 transition">
                        {{ __('auth.forgot_password') }}
                    </a>
                @endif
            </div>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="input @error('password') border-danger-500 @enderror"
                placeholder="••••••••"
                autocomplete="current-password"
            />
            @error('password')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input
                type="checkbox"
                id="remember"
                name="remember"
                class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-2 focus:ring-primary-500 cursor-pointer"
            />
            <label for="remember" class="text-sm text-gray-600 cursor-pointer ml-2 rtl:ml-0 rtl:mr-2">
                {{ __('auth.remember_me') }}
            </label>
        </div>

        <!-- Account Suspended Alert -->
        @error('account_suspended')
            <div class="bg-danger-50 border border-danger-200 rounded-lg p-4 text-danger-800 text-sm">
                {{ $message }}
            </div>
        @enderror

        <!-- Submit Button -->
        <button
            type="submit"
            class="btn btn-primary w-full justify-center text-base font-semibold py-3 mt-8"
        >
            {{ __('auth.login_button') }}
        </button>
    </form>

    <!-- Register Link -->
    <div class="mt-10 pt-8 border-t border-gray-200 text-center">
        <p class="text-gray-600 mb-3">
            {{ __('auth.no_account') }}
        </p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 text-primary-600 font-semibold hover:text-primary-700 transition">
            {{ __('auth.create_account') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
@endsection
