@props([
    'icon' => 'heroicon-o-magnifying-glass',
    'title' => __('messages.public.no_results'),
    'message' => __('messages.public.try_again_later'),
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="text-center p-8 md:p-14 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 rounded-3xl shadow-xs">
    <div class="w-14 h-14 md:w-16 md:h-16 mx-auto mb-5 rounded-2xl bg-orange-50 dark:bg-orange-950/20 text-primary flex items-center justify-center border border-orange-500/10 [&>svg]:w-7 [&>svg]:h-7">
        <x-render-icon :icon="$icon" />
    </div>

    <h3 class="m-0 mb-2 text-slate-900 dark:text-slate-100 text-base md:text-lg font-black tracking-tight">
        {{ $title }}
    </h3>

    @if($message)
        <p class="max-w-[420px] mx-auto text-slate-500 dark:text-slate-400 text-xs md:text-sm font-semibold leading-relaxed">
            {{ $message }}
        </p>
    @endif

    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="inline-flex items-center justify-center min-h-[44px] px-5 py-2 mt-5 rounded-2xl bg-primary text-white text-xs md:text-sm font-black shadow-sm shadow-orange-500/10 hover:shadow-orange-500/25 hover:translate-y-[-1px] transition-all cursor-pointer text-decoration-none">
            {{ $actionLabel }}
        </a>
    @endif
</div>

