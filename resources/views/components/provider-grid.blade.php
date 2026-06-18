@props([
    'providers',
    'columns' => 2,
    'mobileColumns' => 1,
    'title' => null,
    'subtitle' => null,
    'favoriteProfileIds' => [],
    'cardVariant' => 'row',
])

@php
    $count = method_exists($providers, 'count') ? $providers->count() : count($providers);
    $gridClasses = match (true) {
        $cardVariant === 'grid' && $columns >= 4 => 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 w-full',
        $cardVariant === 'grid' && $columns >= 3 => 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 w-full',
        $cardVariant === 'grid' && $mobileColumns >= 2 => 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 w-full',
        $cardVariant === 'grid' => 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 w-full',
        default => 'grid gap-3 grid-cols-1 w-full',
    };

    $cardComponent = $cardVariant === 'grid' ? 'provider-card' : 'provider-row-card';
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
        <div class="{{ $gridClasses }}">
            @foreach($providers as $provider)
                <x-dynamic-component
                    :component="$cardComponent"
                    :provider="$provider"
                    :favorite-profile-ids="$favoriteProfileIds"
                />
            @endforeach
        </div>
    @else
        <x-empty-state />
    @endif
</section>
