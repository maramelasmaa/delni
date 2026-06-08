@extends('layouts.auth')

@section('title', __('auth.forgot_password_title') . ' - ' . config('app.name'))

@section('auth_title')
    استعادة<br/><span class="text-primary-500">كلمة المرور</span>
@endsection

@section('auth_subtitle')
    أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة تعيين كلمة المرور.
@endsection

@section('content')
    <!-- Header -->
    <div class="mb-10">
        <h2 class="text-4xl font-black text-navy-800 mb-2">{{ __('auth.forgot_password_title') }}</h2>
        <p class="text-gray-600 text-base leading-relaxed">{{ __('auth.forgot_password_subtitle') }}</p>
    </div>

    <!-- Success Alert -->
    @if (session('status'))
        <div class="bg-success-50 border border-success-200 rounded-lg p-4 mb-8">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-success-700">
                    {{ session('status') }}
                </div>
            </div>
        </div>
    @endif

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

    <!-- Forgot Password Form -->
    <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
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

        <!-- Submit Button -->
        <button
            type="submit"
            class="btn btn-primary w-full justify-center text-base font-semibold py-3 mt-8"
        >
            {{ __('auth.send_reset_link') }}
        </button>
    </form>

    <!-- Back to Login Link -->
    <div class="mt-10 pt-8 border-t border-gray-200 text-center">
        <p class="text-gray-600 mb-3">
            {{ __('auth.remember_password') }}
        </p>
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-primary-600 font-semibold hover:text-primary-700 transition">
            {{ __('auth.back_to_login') }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
@endsection
