@props([
    'providers',
    'columns' => 2,
    'title' => null,
    'subtitle' => null,
])

@php
    $count = method_exists($providers, 'count') ? $providers->count() : count($providers);
@endphp

<section class="pg-section">
    @if($title || $subtitle)
        <header class="pg-head">
            <div>
                @if($title) <h2 class="pg-title">{{ $title }}</h2> @endif
                @if($subtitle) <p class="pg-subtitle">{{ $subtitle }}</p> @endif
            </div>
            @if($count > 0)
                <span class="pg-count">{{ $count }} مزود</span>
            @endif
        </header>
    @endif

    @if($count > 0)
        <div class="pg-grid" data-columns="{{ $columns }}">
            @foreach($providers as $provider)
                <x-provider-card :provider="$provider" :showBio="false" />
            @endforeach
        </div>
    @else
        <x-empty-state />
    @endif
</section>

@once
@push('styles')
<style>
    .pg-section { width: 100%; }

    .pg-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .85rem;
        padding: 0 .2rem;
    }

    .pg-title {
        font-size: 1.05rem;
        font-weight: 900;
        color: #0B1A34;
        margin: 0;
    }

    .pg-subtitle {
        font-size: .78rem;
        color: #64748B;
        margin: .15rem 0 0;
    }

    .pg-count {
        font-size: .73rem;
        font-weight: 700;
        background: #F1F5F9;
        color: #475569;
        padding: .25rem .6rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .pg-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    @media (min-width: 900px) {
        .pg-grid[data-columns="3"] {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 380px) {
        .pg-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
@endonce
