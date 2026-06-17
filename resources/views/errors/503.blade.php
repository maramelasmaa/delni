@extends('public.layout')

@section('title', __('messages.public.error_503_title') . ' - ' . config('app.name'))

@section('content')
    <x-app-error-state
        code="503"
        :title="__('messages.public.error_503_title')"
        :message="__('messages.public.error_503_message')"
        :primary-label="__('messages.public.back_home')"
        :primary-url="route('home')"
        :note="__('messages.public.check_back_soon')"
    />
@endsection
