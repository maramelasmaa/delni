@extends('public.layout')

@section('title', __('messages.public.error_403_title') . ' - ' . config('app.name'))

@section('content')
    <x-app-error-state
        code="403"
        :title="__('messages.public.error_403_title')"
        :message="__('messages.public.error_403_message')"
        :primary-label="__('messages.public.back_home')"
        :primary-url="route('home')"
        :secondary-label="__('messages.public.search')"
        :secondary-url="route('public.search')"
        :note="__('messages.public.error_403_info')"
    />
@endsection
