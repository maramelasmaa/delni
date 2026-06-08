@extends('layouts.auth')

@section('title', __('auth.register_title') . ' - ' . config('app.name'))

@section('auth_title')
    دلني لأفضل<br/><span class="text-primary-500">الخدمات والمزودين</span>
@endsection

@section('auth_subtitle')
    شارك تجاربك وساعد الآخرين في إيجاد أفضل الخدمات في منطقتك.
@endsection

@section('content')
    <!-- Header -->
    <div class="mb-10">
        <h2 class="text-4xl font-black text-navy-800 mb-2">{{ __('auth.register_title') }}</h2>
        <p class="text-gray-600 text-base leading-relaxed">{{ __('auth.register_subtitle') }}</p>
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

    <!-- Registration Form -->
    <form action="{{ route('register') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Name Field -->
        <div>
            <label for="name" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.name') }}
            </label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                class="input @error('name') border-danger-500 @enderror"
                placeholder="اسمك الكامل"
                autocomplete="name"
            />
            @error('name')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

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
                class="input @error('email') border-danger-500 @enderror"
                placeholder="you@example.com"
                autocomplete="email"
            />
            @error('email')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Phone Field -->
        <div>
            <label for="phone" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.phone') }}
            </label>
            <input
                type="tel"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                required
                class="input @error('phone') border-danger-500 @enderror"
                placeholder="+218 91 123 4567"
                autocomplete="tel"
            />
            @error('phone')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.password') }}
            </label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="input @error('password') border-danger-500 @enderror"
                placeholder="••••••••"
                autocomplete="new-password"
            />
            @error('password')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Confirmation Field -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.confirm_password') }}
            </label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                class="input"
                placeholder="••••••••"
                autocomplete="new-password"
            />
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="btn btn-primary w-full justify-center text-base font-semibold py-3 mt-8"
        >
            {{ __('auth.register_button') }}
        </button>
    </form>

    <!-- Login Link -->
    <div class="mt-10 pt-8 border-t border-gray-200 text-center">
        <p class="text-gray-600 mb-3">
            {{ __('auth.already_account') }}
        </p>
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-primary-600 font-semibold hover:text-primary-700 transition">
            {{ __('auth.login_link') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
@endsection
