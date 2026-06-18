@extends('public.layout')

@section('title', 'المفضلة - ' . config('app.name'))

@section('content')
<div class="mx-auto grid w-full max-w-2xl gap-4 px-1 py-2.5 pb-8">
    <section class="grid gap-5 rounded-[2rem] border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 grid gap-4">
                <x-browse-trail :items="[
                    ['label' => 'الرئيسية', 'url' => route('home')],
                    ['label' => 'المفضلة', 'active' => true],
                ]" />

                <div class="flex items-start gap-3">
                    <div class="flex h-16 w-16 flex-none items-center justify-center rounded-[1.4rem] bg-orange-50/50 text-primary shadow-xs ring-1 ring-orange-100/50 dark:bg-slate-950 dark:ring-slate-800">
                        <x-render-icon icon="app-heart-filled" class="h-8 w-8" />
                    </div>

                    <div class="min-w-0 text-right">
                        <h1 class="text-xl font-black leading-tight text-slate-950 dark:text-white md:text-2xl">المفضلة</h1>
                    </div>
                </div>
            </div>

            <a href="{{ route('home') }}" class="inline-flex h-11 w-11 flex-none items-center justify-center rounded-2xl bg-slate-50 text-slate-700 shadow-xs ring-1 ring-slate-200 transition hover:bg-slate-100 hover:text-primary dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800 dark:hover:text-orange-300" aria-label="الرجوع إلى الرئيسية">
                <x-render-icon icon="heroicon-o-arrow-left" class="h-5 w-5" />
            </a>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800/60 my-1"></div>

        @guest
            <div class="fv-guest flex flex-col items-center text-center py-12 px-6 gap-4">
                <div class="fv-guest__icon w-16 h-16 rounded-full bg-orange-50 dark:bg-orange-950/20 flex items-center justify-center text-primary">
                    <x-render-icon icon="app-heart" class="w-8 h-8" />
                </div>
                <h2 class="fv-guest__title text-lg font-black text-slate-900 dark:text-slate-100">احفظ مقدمي الخدمات المفضلين لديك</h2>
                <p class="fv-guest__desc text-sm text-slate-500 dark:text-slate-400 max-w-xs leading-relaxed">سجّل دخولك لتتمكن من حفظ مقدمي الخدمات ومراجعتهم لاحقاً بسهولة.</p>
                <a href="{{ route('login') }}" class="fv-guest__btn inline-flex items-center justify-center bg-primary hover:bg-orange-600 text-white font-black text-sm px-6 py-2.5 rounded-full transition-colors">تسجيل الدخول</a>
            </div>
        @endguest

        @auth
            @if($favorites->count() > 0)
                <div class="grid min-w-0 gap-3">
                    <x-provider-grid :providers="$favorites" :favorite-profile-ids="$favorites->pluck('id')->all()" />
                    
                    @if(method_exists($favorites, 'hasPages') && $favorites->hasPages())
                        <x-marketplace-pagination :paginator="$favorites" />
                    @endif
                </div>
            @else
                <div class="fv-empty flex flex-col items-center text-center py-12 px-6 gap-4">
                    <div class="fv-empty__icon w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                        <x-render-icon icon="app-heart" class="w-8 h-8" />
                    </div>
                    <h2 class="fv-empty__title text-lg font-black text-slate-900 dark:text-slate-100">لا توجد مفضلة بعد</h2>
                    <p class="fv-empty__desc text-sm text-slate-500 dark:text-slate-400 max-w-xs leading-relaxed">اضغط على أيقونة القلب في صفحة أي مقدم خدمة لإضافته إلى مفضلتك.</p>
                    <a href="{{ route('home') }}" class="fv-empty__btn inline-flex items-center justify-center bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-black text-xs px-5 py-2.5 rounded-full transition-colors">استكشف مقدمي الخدمات</a>
                </div>
            @endif
        @endauth
    </section>
</div>
@endsection
