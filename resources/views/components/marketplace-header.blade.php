@props([
    'eyebrow' => null,
    'title',
    'count' => null,
    'backUrl' => null,
    'backLabel' => 'رجوع',
    'icon' => null,
    'description' => null,
])

<header class="grid grid-cols-[auto_minmax(0,_1fr)_auto] items-center gap-3 p-3 md:p-4.5 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 shadow-sm">
    @if($backUrl)
        <a href="{{ $backUrl }}" class="flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition-colors" aria-label="{{ $backLabel }}">
            <x-render-icon icon="heroicon-o-arrow-right" class="w-5 h-5" />
        </a>
    @endif

    <div class="min-w-0">
        @if($eyebrow)
            <span class="block text-primary text-[10px] md:text-xs font-black tracking-wider mb-0.5 uppercase">{{ $eyebrow }}</span>
        @endif

        <h1 class="m-0 text-slate-900 dark:text-slate-100 text-[1.05rem] md:text-xl font-black leading-tight truncate">{{ $title }}</h1>

        @if($description)
            <p class="hidden md:block text-slate-500 dark:text-slate-400 text-xs md:text-sm font-semibold leading-relaxed mt-1.5 max-w-2xl">{{ $description }}</p>
        @endif

        @if($count !== null)
            <span class="inline-flex text-slate-500 dark:text-slate-400 text-[11px] md:text-xs font-bold mt-1">{{ $count }}</span>
        @endif
    </div>

    @if($icon)
        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-950/20 text-primary [&_svg]:w-5 [&_svg]:h-5">
            {{ $icon }}
        </div>
    @endif
</header>

