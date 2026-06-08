@extends('public.layout')

@section('title', __('messages.public.error_500_title', ['default' => 'Server Error']) . ' - ' . config('app.name'))

@section('content')
<section class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-danger-100 mb-6">
                <svg class="w-16 h-16 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-4">
            <h1 class="text-6xl md:text-7xl font-black text-navy-800 mb-2">500</h1>
            <div class="w-16 h-1 bg-danger-500 rounded-full mx-auto"></div>
        </div>

        <!-- Title -->
        <h2 class="text-3xl font-black text-navy-800 mb-4">
            {{ __('messages.public.error_500_title', ['default' => 'Server Error']) }}
        </h2>

        <!-- Description -->
        <p class="text-gray-600 text-lg leading-relaxed mb-10">
            {{ __('messages.public.error_500_message', ['default' => 'حدث خطأ في الخادم. يرجى المحاولة لاحقًا.']) }}
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="btn btn-primary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l4-4m0 0l4 4m-4-4v4"/>
                </svg>
                {{ __('messages.public.back_home') }}
            </a>
            @if (Route::has('contact'))
                <a href="{{ route('contact') }}" class="btn btn-outline flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('messages.public.contact_support') }}
                </a>
            @endif
        </div>

        <!-- Status Info -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="bg-danger-50 border border-danger-200 rounded-lg p-4">
                <p class="text-sm text-danger-800">
                    <strong>{{ __('messages.public.error_500_code', ['default' => 'رمز الخطأ']) }}:</strong> Server Error 500
                </p>
                <p class="text-xs text-danger-700 mt-2">{{ __('messages.public.error_please_try_later', ['default' => 'يرجى محاولة الوصول مرة أخرى بعد قليل.']) }}</p>
            </div>
        </div>
    </div>
</section>
@endsection
