@extends('public.layout')

@section('title', __('messages.public.error_500_title') . ' - ' . config('app.name'))

@section('content')
    <x-app-error-state
        code="500"
        :title="__('messages.public.error_500_title')"
        :message="__('messages.public.error_500_message')"
        :primary-label="__('messages.public.back_home')"
        :primary-url="route('home')"
        :secondary-label="Route::has('contact') ? __('messages.public.contact_support') : null"
        :secondary-url="Route::has('contact') ? route('contact') : null"
        :note="__('messages.public.error_please_try_later')"
    />
@endsection
