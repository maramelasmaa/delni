@extends('layouts.auth')

@section('title', __('auth.set_password_title') . ' - ' . config('app.name'))
@section('hide_home_back', true)

@section('auth_title')
    {{ __('auth.set_password_title') }}
@endsection

@section('auth_subtitle')
    {{ __('auth.set_password_subtitle') }}
@endsection

@section('content')
    <div class="mb-6 flex items-center gap-3 rounded-2xl border border-[#E8EEF8] bg-[#F8FAFF] px-4 py-3 shadow-sm dark:border-[#243149] dark:bg-[#16203A]">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white p-1 shadow-sm dark:bg-[#1B2740]">
            <img src="{{ asset('images/photo_2026-06-22_23-21-55.jpg') }}" alt="{{ config('app.name') }}" class="h-full w-full rounded-lg object-cover">
        </div>

        <div class="min-w-0">
            <p class="text-sm font-black text-[#0F172A] dark:text-[#F1F5F9]">{{ config('app.name') }}</p>
            <p class="text-xs font-semibold text-[#475569] dark:text-[#A8B4C8]">{{ __('auth.password_secure_note') }}</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 flex gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300" role="alert">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4l1.3-1.3 1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/>
            </svg>

            <div>
                <strong class="mb-1 block font-bold text-red-900 dark:text-red-200">حدث خطأ</strong>

                <ul class="list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('onboarding.set-password') }}" class="flex flex-col gap-5" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <div class="mb-1.5 flex items-center justify-between text-xs font-bold">
                <label for="email" class="text-[#475569] dark:text-[#A8B4C8]">{{ __('auth.email') }}</label>
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20">
                    موثق
                </span>
            </div>

            <input
                type="email"
                id="email"
                class="w-full cursor-not-allowed rounded-2xl border border-[#E8EEF8] bg-[#F1F5F9] px-4 py-3 font-semibold text-[#475569] select-none focus:outline-none dark:border-[#243149] dark:bg-[#1B2740] dark:text-[#A8B4C8]"
                value="{{ $email }}"
                readonly
                tabindex="-1"
                aria-readonly="true"
            >

            <span class="mt-1.5 block text-[11px] font-semibold text-[#475569] dark:text-[#7C8AA5]">
                هذا البريد مرتبط بحسابك ولا يمكن تعديله من هذه الصفحة.
            </span>
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-xs font-bold text-[#475569] dark:text-[#A8B4C8]">{{ __('auth.new_password') }}</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="w-full rounded-2xl border @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-[#E8EEF8] focus:border-[#1E40AF] focus:ring-[#1E40AF]/15 dark:border-[#243149] dark:focus:border-[#60A5FA] dark:focus:ring-[#60A5FA]/20 @enderror bg-white px-4 py-3 font-semibold text-[#0F172A] placeholder-[#94A3B8] shadow-sm transition-all duration-200 focus:ring-4 dark:bg-[#131C2E] dark:text-[#F1F5F9]"
                placeholder="••••••••"
                autocomplete="new-password"
                minlength="8"
            >
            <span class="mt-1.5 block text-[11px] font-semibold text-[#475569] dark:text-[#7C8AA5]">{{ __('auth.password_requirements') }}</span>
            @error('password')
                <span class="mt-1 block text-xs font-bold text-red-600 dark:text-red-300">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-xs font-bold text-[#475569] dark:text-[#A8B4C8]">{{ __('auth.confirm_password') }}</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                class="w-full rounded-2xl border @error('password_confirmation') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-[#E8EEF8] focus:border-[#1E40AF] focus:ring-[#1E40AF]/15 dark:border-[#243149] dark:focus:border-[#60A5FA] dark:focus:ring-[#60A5FA]/20 @enderror bg-white px-4 py-3 font-semibold text-[#0F172A] placeholder-[#94A3B8] shadow-sm transition-all duration-200 focus:ring-4 dark:bg-[#131C2E] dark:text-[#F1F5F9]"
                placeholder="••••••••"
                autocomplete="new-password"
                minlength="8"
            >
            @error('password_confirmation')
                <span class="mt-1 block text-xs font-bold text-red-600 dark:text-red-300">{{ $message }}</span>
            @enderror
        </div>

        <button
            type="submit"
            class="mt-2 w-full rounded-2xl bg-[#1E40AF] px-4 py-3.5 font-bold text-white shadow-[0_16px_30px_rgba(30,64,175,0.24)] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#1D4ED8] hover:shadow-[0_20px_36px_rgba(30,64,175,0.3)] dark:bg-[#60A5FA] dark:text-[#0B1120] dark:hover:bg-[#93C5FD]"
        >
            {{ __('auth.set_password_button') }}
        </button>
    </form>
@endsection
