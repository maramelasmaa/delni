@props([
    'stats' => [],
])

@php
    $profiles = (int) ($stats['profiles_count'] ?? 0);
    $reviews = (int) ($stats['reviews_count'] ?? 0);
    $categories = (int) ($stats['categories_count'] ?? 0);
    $cities = (int) ($stats['cities_count'] ?? 0);
@endphp

<section class="mt-8 overflow-hidden">
    <div class="mb-4 px-1">
        <span class="block text-primary text-[10px] md:text-xs font-black uppercase tracking-wider mb-0.5">الإحصائيات</span>
        <h2 class="m-0 text-slate-900 dark:text-slate-100 text-sm md:text-base font-black">دلني بالأرقام</h2>
    </div>

    <div class="py-1">
        {{-- Static grid layout: 2 columns on mobile, 4 columns on desktop --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
            {{-- Providers stat --}}
            <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs select-none">
                <span class="w-8.5 h-8.5 rounded-xl bg-orange-50 dark:bg-orange-950/20 text-primary flex items-center justify-center flex-none">
                    <x-render-icon icon="heroicon-o-users" class="w-4.5 h-4.5" />
                </span>
                <div class="text-right">
                    <strong class="block text-slate-900 dark:text-white text-sm md:text-base font-black leading-none">{{ number_format($profiles) }}</strong>
                    <span class="block text-slate-400 dark:text-slate-500 text-[10px] font-black mt-1">مقدم خدمة</span>
                </div>
            </div>

            {{-- Reviews stat --}}
            <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs select-none">
                <span class="w-8.5 h-8.5 rounded-xl bg-orange-50 dark:bg-orange-950/20 text-primary flex items-center justify-center flex-none">
                    <x-render-icon icon="heroicon-o-star" class="w-4.5 h-4.5" />
                </span>
                <div class="text-right">
                    <strong class="block text-slate-900 dark:text-white text-sm md:text-base font-black leading-none">{{ number_format($reviews) }}</strong>
                    <span class="block text-slate-400 dark:text-slate-500 text-[10px] font-black mt-1">تقييم</span>
                </div>
            </div>

            {{-- Categories stat --}}
            <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs select-none">
                <span class="w-8.5 h-8.5 rounded-xl bg-orange-50 dark:bg-orange-950/20 text-primary flex items-center justify-center flex-none">
                    <x-render-icon icon="heroicon-o-briefcase" class="w-4.5 h-4.5" />
                </span>
                <div class="text-right">
                    <strong class="block text-slate-900 dark:text-white text-sm md:text-base font-black leading-none">{{ number_format($categories) }}</strong>
                    <span class="block text-slate-400 dark:text-slate-500 text-[10px] font-black mt-1">تخصص</span>
                </div>
            </div>

            {{-- Cities stat --}}
            <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs select-none">
                <span class="w-8.5 h-8.5 rounded-xl bg-orange-50 dark:bg-orange-950/20 text-primary flex items-center justify-center flex-none">
                    <x-render-icon icon="heroicon-o-map-pin" class="w-4.5 h-4.5" />
                </span>
                <div class="text-right">
                    <strong class="block text-slate-900 dark:text-white text-sm md:text-base font-black leading-none">{{ number_format($cities) }}</strong>
                    <span class="block text-slate-400 dark:text-slate-500 text-[10px] font-black mt-1">مدينة</span>
                </div>
            </div>
        </div>
    </div>
</section>
