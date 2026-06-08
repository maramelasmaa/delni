@extends('public.layout')

@section('title', __('messages.public.error_403_title') . ' - ' . config('app.name'))

@section('content')
<section class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-danger-100 mb-6">
                <svg class="w-16 h-16 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-4">
            <h1 class="text-6xl md:text-7xl font-black text-navy-800 mb-2">403</h1>
            <div class="w-16 h-1 bg-danger-500 rounded-full mx-auto"></div>
        </div>

        <!-- Title -->
        <h2 class="text-3xl font-black text-navy-800 mb-4">
            {{ __('messages.public.error_403_title') }}
        </h2>

        <!-- Description -->
        <p class="text-gray-600 text-lg leading-relaxed mb-10">
            {{ __('messages.public.error_403_message') }}
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="btn btn-primary flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l4-4m0 0l4 4m-4-4v4"/>
                </svg>
                {{ __('messages.public.back_home') }}
            </a>
            <a href="{{ route('public.search') }}" class="btn btn-outline flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{ __('messages.public.search') }}
            </a>
        </div>

        <!-- Info Message -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 text-left">
                <p class="text-sm text-warning-800">
                    <strong>ملاحظة:</strong> ليس لديك إذن للوصول إلى هذه الصفحة. إذا كنت تعتقد أن هذا خطأ، يرجى الاتصال بالدعم.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
