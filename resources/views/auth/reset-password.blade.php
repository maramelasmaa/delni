@extends('layouts.auth')

@section('title', __('auth.reset_password_title') . ' - ' . config('app.name'))

@section('auth_title')
    إنشاء<br/><span class="text-primary-500">كلمة مرور جديدة</span>
@endsection

@section('auth_subtitle')
    أدخل كلمة مرور جديدة قوية لحساب دلني الخاص بك.
@endsection

@section('content')
    <!-- Header -->
    <div class="mb-10">
        <h2 class="text-4xl font-black text-navy-800 mb-2">{{ __('auth.reset_password_title') }}</h2>
        <p class="text-gray-600 text-base leading-relaxed">{{ __('auth.reset_password_subtitle') }}</p>
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

    <!-- Reset Password Form -->
    <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Hidden Token -->
        <input type="hidden" name="token" value="{{ $token }}">

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.email') }}
            </label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $email) }}"
                required
                class="input @error('email') border-danger-500 @enderror"
                placeholder="you@example.com"
                autocomplete="email"
                readonly
            />
            @error('email')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-sm font-semibold text-navy-800 mb-2">
                {{ __('auth.new_password') }}
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
            <p class="text-gray-500 text-xs mt-2">
                {{ __('auth.password_requirements') }}
            </p>
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
                class="input @error('password_confirmation') border-danger-500 @enderror"
                placeholder="••••••••"
                autocomplete="new-password"
            />
            @error('password_confirmation')
                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="btn btn-primary w-full justify-center text-base font-semibold py-3 mt-8"
        >
            {{ __('auth.reset_password_button') }}
        </button>
    </form>

    <!-- Back to Login Link -->
    <div class="mt-10 pt-8 border-t border-gray-200 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-primary-600 font-semibold hover:text-primary-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('auth.back_to_login') }}
        </a>
    </div>
@endsection
