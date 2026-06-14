@extends('public.layout')

@section('title', __('messages.public.error_503_title', ['default' => 'Service Unavailable']) . ' - ' . config('app.name'))

@section('content')
    <x-app-error-state
        code="503"
        :title="__('messages.public.error_503_title', ['default' => 'Service Unavailable'])"
        :message="__('messages.public.error_503_message', ['default' => 'الخدمة غير متاحة حاليًا. نحن نعمل على إصلاح المشكلة.'])"
        :primary-label="__('messages.public.back_home')"
        :primary-url="route('home')"
        :note="__('messages.public.check_back_soon', ['default' => 'يرجى التحقق لاحقًا'])"
    />
@endsection
