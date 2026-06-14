@extends('public.layout')

@section('title', __('messages.public.error_500_title', ['default' => 'Server Error']) . ' - ' . config('app.name'))

@section('content')
    <x-app-error-state
        code="500"
        :title="__('messages.public.error_500_title', ['default' => 'Server Error'])"
        :message="__('messages.public.error_500_message', ['default' => 'حدث خطأ في الخادم. يرجى المحاولة لاحقًا.'])"
        :primary-label="__('messages.public.back_home')"
        :primary-url="route('home')"
        :secondary-label="Route::has('contact') ? __('messages.public.contact_support') : null"
        :secondary-url="Route::has('contact') ? route('contact') : null"
        :note="__('messages.public.error_please_try_later', ['default' => 'يرجى محاولة الوصول مرة أخرى بعد قليل.'])"
    />
@endsection
