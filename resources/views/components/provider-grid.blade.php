@props([
    'providers',
    'columns' => 2,
    'title' => null,
    'subtitle' => null,
])

@php
    $count = method_exists($providers, 'count') ? $providers->count() : count($providers);
@endphp

<section class="w-full">
    @if($title || $subtitle)
        <header class="flex items-center justify-between gap-3 mb-3.5 px-0.5">
            <div>
                @if($title) <h2 class="text-slate-900 dark:text-slate-100 text-sm md:text-base font-black m-0">{{ $title }}</h2> @endif
                @if($subtitle) <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5 font-semibold">{{ $subtitle }}</p> @endif
            </div>
        </header>
    @endif

    @if($count > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $columns === 3 ? 'lg:grid-cols-3' : '' }} gap-4.5 items-stretch justify-start w-full">
            @foreach($providers as $provider)
                <x-provider-card :provider="$provider" :showBio="false" />
            @endforeach
        </div>
    @else
        <x-empty-state />
    @endif
</section>

