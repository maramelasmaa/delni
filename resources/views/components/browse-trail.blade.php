@props(['items' => []])

@php
    $trailItems = collect($items)->filter(fn ($item) => filled($item['label'] ?? null))->values();
@endphp

@if($trailItems->isNotEmpty())
    <nav class="flex items-center gap-1.5 text-[10px] md:text-xs text-slate-400 dark:text-slate-500 font-bold px-1.5 py-1" aria-label="مسار التصفح">
        @foreach($trailItems as $index => $item)
            @if($index > 0)
                <x-render-icon icon="heroicon-o-chevron-left" class="w-3 h-3 text-slate-400 dark:text-slate-600 flex-none" />
            @endif
            @if(! empty($item['url']) && empty($item['active']))
                <a href="{{ $item['url'] }}" class="hover:text-primary transition-colors text-slate-500 dark:text-slate-400">{{ $item['label'] }}</a>
            @else
                <span class="text-slate-800 dark:text-slate-200 font-extrabold">{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif

