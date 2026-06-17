@extends('layouts.auth')

@section('title', __('auth.login_title') . ' - ' . config('app.name'))

@section('auth_title')
    {{ __('auth.login_title') }}
@endsection

@section('content')
    @if ($errors->any())
        <div class="flex gap-3 bg-red-500/10 border border-red-500/20 text-red-800 dark:text-red-400 p-4 rounded-2xl text-sm mb-6 font-semibold" role="alert">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4l1.3-1.3 1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/>
            </svg>
            <div>
                <strong class="block mb-1 font-bold text-red-900 dark:text-red-300">{{ __('auth.login_failed_title') }}</strong>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <a href="{{ route('auth.google') }}" class="flex items-center justify-center gap-3 w-full py-3.5 px-4 bg-white dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-2xl font-bold shadow-sm hover:shadow-md hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 select-none hover:-translate-y-0.5">
        <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        <span>{{ __('auth.continue_with_google') }}</span>
    </a>

    <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800/80 text-center text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">
        <p>
            {!! __('auth.terms_and_privacy_agreement', [
                'terms' => '<a href="'.route('terms').'" class="text-primary hover:underline hover:text-primary-dark dark:text-primary dark:hover:text-primary-dark font-bold transition-colors duration-150">'.__('messages.public.terms').'</a>',
                'privacy' => '<a href="'.route('privacy').'" class="text-primary hover:underline hover:text-primary-dark dark:text-primary dark:hover:text-primary-dark font-bold transition-colors duration-150">'.__('messages.public.privacy').'</a>',
            ]) !!}
        </p>
    </div>
@endsection
