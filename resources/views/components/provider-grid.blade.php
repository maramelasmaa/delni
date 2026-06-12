@props([
    'providers',
    'columns' => 3,
    'title' => null,
    'subtitle' => null,
])

@php
    $count = method_exists($providers, 'count') ? $providers->count() : count($providers);
@endphp

<section class="pwa-grid-section">
    @if($title || $subtitle)
        <header class="pwa-grid-head">
            <div>
                @if($title) <h2 class="pwa-grid-title">{{ $title }}</h2> @endif
                @if($subtitle) <p class="pwa-grid-subtitle">{{ $subtitle }}</p> @endif
            </div>
            @if($count > 0)
                <span class="pwa-grid-count-badge">{{ $count }} مزود</span>
            @endif
        </header>
    @endif

    @if($count > 0)
        <div class="pwa-clean-grid-container">
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
    .pwa-grid-section {
        width: 100%;
        margin-top: 1rem;
    }
    .pwa-grid-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.85rem;
        padding: 0 0.25rem;
    }
    .pwa-grid-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0B1A34;
        margin: 0;
    }
    .pwa-grid-subtitle {
        font-size: 0.8rem;
        color: #64748B;
        margin: 0.2rem 0 0 0;
    }
    .pwa-grid-count-badge {
        font-size: 0.75rem;
        font-weight: 700;
        background: #F1F5F9;
        color: #475569;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
    }
    .pwa-clean-grid-container {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    /* Desktop Enhancement Override */
    @media (min-width: 768px) {
        .pwa-clean-grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
    }
</style>
@endpush
@endonce
