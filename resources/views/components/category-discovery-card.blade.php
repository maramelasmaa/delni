@props(['category'])

@php
    $categoryName = $category->localized_name ?? $category->name;

    $iconModel  = $category->icon;
    $iconString = $iconModel ? null : $category->getRawOriginal('icon');
@endphp

<article class="group relative bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-4 shadow-3xs hover:shadow-xs transition-all duration-300">
    <!-- Main Category Link covering the whole card -->
    <a href="{{ route('public.category', $category->slug) }}" class="absolute inset-0 z-1" aria-label="{{ $categoryName }}"></a>

    <!-- Content wrapper -->
    <div class="flex items-center gap-4">
        <!-- 1. Icon container (Right in RTL) -->
        <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-orange-50 dark:bg-orange-950/20 text-primary flex-none [&_svg]:w-6 [&_svg]:h-6">
            @if($iconModel)
                <x-svg-icon :icon="$iconModel" size="24" />
            @elseif($iconString)
                <x-render-icon :icon="$iconString" />
            @else
                <x-render-icon icon="heroicon-o-briefcase" />
            @endif
        </span>

        <!-- 2. Text Info (Middle) -->
        <div class="flex-1 min-w-0 text-right">
            <h2 class="text-[#0B1A34] dark:text-slate-100 text-sm md:text-base font-black leading-snug">
                {{ $categoryName }}
            </h2>
        </div>

        <!-- 3. Chevron Left (Left in RTL) -->
        <div class="flex-none flex items-center justify-center text-slate-450 dark:text-slate-600 group-hover:text-primary transition-colors">
            <x-render-icon icon="heroicon-o-chevron-left" class="w-5 h-5" />
        </div>
    </div>
</article>

