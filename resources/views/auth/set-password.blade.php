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
    @if ($errors->any())
        <div class="flex gap-3 bg-red-500/10 border border-red-500/20 text-red-800 dark:text-red-400 p-4 rounded-2xl text-sm mb-6 font-semibold" role="alert">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4l1.3-1.3 1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/>
            </svg>
            <div>
                <strong class="block mb-1 font-bold text-red-900 dark:text-red-300">حدث خطأ</strong>
                <ul class="list-disc list-inside space-y-0.5">
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

        {{-- Email Field (Readonly) --}}
        <div>
            <div class="flex justify-between items-center text-xs font-bold mb-1.5">
                <label for="email" class="text-slate-600 dark:text-slate-300">{{ __('auth.email') }}</label>
                <span class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/35 px-2 py-0.5 rounded-full text-[10px]">موثق</span>
            </div>

            <input
                type="email"
                id="email"
                class="w-full px-4 py-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl font-semibold text-slate-500 dark:text-slate-400 cursor-not-allowed select-none focus:outline-none"
                value="{{ $email }}"
                readonly
                tabindex="-1"
                aria-readonly="true"
            >

            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 font-semibold block">
                هذا البريد مرتبط بحسابك ولا يمكن تعديله من هذه الصفحة.
            </span>
        </div>

        {{-- Password Field --}}
        <div>
            <label for="password" class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1.5">{{ __('auth.new_password') }}</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="w-full px-4 py-3 bg-white dark:bg-slate-900 border @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 dark:border-slate-800 focus:border-primary focus:ring-primary/15 @enderror rounded-2xl font-semibold text-slate-900 dark:text-white placeholder-slate-400 focus:ring-4 focus:ring-primary/15 transition-all duration-200"
                placeholder="••••••••"
                autocomplete="new-password"
                minlength="8"
            >
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 font-semibold block">{{ __('auth.password_requirements') }}</span>
            @error('password')
                <span class="text-xs font-bold text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Confirm Password Field --}}
        <div>
            <label for="password_confirmation" class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1.5">{{ __('auth.confirm_password') }}</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                class="w-full px-4 py-3 bg-white dark:bg-slate-900 border @error('password_confirmation') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 dark:border-slate-800 focus:border-primary focus:ring-primary/15 @enderror rounded-2xl font-semibold text-slate-900 dark:text-white placeholder-slate-400 focus:ring-4 focus:ring-primary/15 transition-all duration-200"
                placeholder="••••••••"
                autocomplete="new-password"
                minlength="8"
            >
            @error('password_confirmation')
                <span class="text-xs font-bold text-red-600 dark:text-red-400 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="w-full py-3.5 px-4 bg-primary hover:bg-primary-dark text-white rounded-2xl font-bold shadow-md hover:shadow-lg transition-all duration-200 select-none hover:-translate-y-0.5 mt-2">
            {{ __('auth.set_password_button') }}
        </button>
    </form>
@endsection
