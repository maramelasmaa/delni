@extends('public.layout')

@section('title', __('messages.public.error_404_title') . ' - ' . config('app.name'))

@section('content')
<section class="min-h-[calc(100vh-200px)] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-primary-100 mb-6">
                <svg class="w-16 h-16 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-4">
            <h1 class="text-6xl md:text-7xl font-black text-navy-800 mb-2">404</h1>
            <div class="w-16 h-1 bg-primary-500 rounded-full mx-auto"></div>
        </div>

        <!-- Title -->
        <h2 class="text-3xl font-black text-navy-800 mb-4">
            {{ __('messages.public.error_404_title') }}
        </h2>

        <!-- Description -->
        <p class="text-gray-600 text-lg leading-relaxed mb-10">
            {{ __('messages.public.error_404_message') }}
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{ __('messages.public.search') }}
            </a>
        </div>

        <!-- Suggestions -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-4 font-medium">{{ __('messages.public.suggestions') ?? 'اقتراحات' }}</p>
            <ul class="text-sm text-gray-600 space-y-2">
                <li>• تحقق من صحة الرابط</li>
                <li>• قد تم حذف الصفحة أو نقلها</li>
                <li>• جرب البحث عن ما تبحث عنه</li>
            </ul>
        </div>
    </div>
</section>
@endsection
