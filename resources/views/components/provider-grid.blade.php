@props([
    'providers',
    'columns' => 3,
    'title' => null,
    'subtitle' => null,
])

@php
    $colClass = match($columns) {
        1 => 'col-12',
        2 => 'col-xl-6 col-lg-6',
        3 => 'col-xl-4 col-md-6',
        4 => 'col-xl-3 col-lg-4 col-md-6',
        default => 'col-xl-4 col-md-6',
    };
@endphp

<section class="provider-grid-section">
    @if($title || $subtitle)
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-2 mb-4">
            <div>
                @if($title)
                    <h2 class="section-title mb-1">
                        {{ $title }}
                    </h2>
                @endif

                @if($subtitle)
                    <p class="section-subtitle mb-0">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            @if($providers->count() > 0)
                <div class="provider-grid-count">
                    {{ $providers->count() }}
                    {{ __('messages.public.providers') }}
                </div>
            @endif
        </div>
    @endif

    @if($providers->count() > 0)
        <div class="row g-4">
            @foreach($providers as $provider)
                <div class="{{ $colClass }}">
                    <x-provider-card :provider="$provider" />
                </div>
            @endforeach
        </div>
    @else
        <div class="provider-grid-empty">
            <x-empty-state
                icon="🔍"
                title="{{ __('messages.public.no_providers_found') }}"
                message="{{ __('messages.public.try_different_search') }}"
            />
        </div>
    @endif
</section>

@once
    @push('styles')
        <style>
            .provider-grid-section {
                position: relative;
            }

            .provider-grid-count {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.35rem;
                padding: 0.65rem 1rem;
                background: rgba(241, 98, 15, 0.08);
                color: #F1620F;
                border-radius: 999px;
                font-size: 0.88rem;
                font-weight: 800;
                border: 1px solid rgba(241, 98, 15, 0.12);
                white-space: nowrap;
            }

            .provider-grid-empty {
                margin-top: 1rem;
            }

            @media (max-width: 768px) {
                .provider-grid-count {
                    align-self: flex-start;
                }
            }
        </style>
    @endpush
@endonce
