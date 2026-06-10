@props([
    'providers',
    'columns' => 3,
    'title' => null,
    'subtitle' => null,
])

@php
    $count = method_exists($providers, 'count') ? $providers->count() : count($providers);

    $gridClass = match((int) $columns) {
        1 => 'delni-provider-grid--one',
        2 => 'delni-provider-grid--two',
        4 => 'delni-provider-grid--four',
        default => 'delni-provider-grid--three',
    };
@endphp

<section class="delni-provider-section">
    @if($title || $subtitle)
        <header class="delni-section-head">
            <div>
                @if($title)
                    <h2 class="delni-section-title">{{ $title }}</h2>
                @endif

                @if($subtitle)
                    <p class="delni-section-subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @if($count > 0)
                <span class="delni-section-count">
                    {{ $count }} {{ __('messages.public.providers') }}
                </span>
            @endif
        </header>
    @endif

    @if($count > 0)
        <div class="delni-provider-grid {{ $gridClass }}">
            @foreach($providers as $provider)
                <x-provider-card :provider="$provider" />
            @endforeach
        </div>
    @else
        <x-empty-state
            title="{{ __('messages.public.no_providers_found') }}"
            message="{{ __('messages.public.try_different_search') }}"
            actionLabel="{{ __('messages.public.search') }}"
            actionUrl="{{ route('public.search') }}"
        />
    @endif
</section>

@once
    @push('styles')
        <style>
            .delni-provider-section {
                width: 100%;
            }

            .delni-section-head {
                display: flex;
                align-items: end;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1.25rem;
            }

            .delni-section-title {
                margin: 0;
                color: #0B1A34;
                font-size: clamp(1.35rem, 3vw, 1.9rem);
                line-height: 1.2;
                font-weight: 950;
                letter-spacing: -.04em;
            }

            .delni-section-subtitle {
                margin: .45rem 0 0;
                color: #5D5959;
                font-size: .95rem;
                line-height: 1.8;
                font-weight: 600;
            }

            .delni-section-count {
                flex-shrink: 0;
                min-height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: .55rem .85rem;
                border-radius: 999px;
                background: rgba(241, 98, 15, .08);
                color: #F1620F;
                border: 1px solid rgba(241, 98, 15, .12);
                font-size: .82rem;
                font-weight: 900;
            }

            .delni-provider-grid {
                display: grid;
                gap: 1rem;
            }

            .delni-provider-grid--one {
                grid-template-columns: 1fr;
            }

            .delni-provider-grid--two {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .delni-provider-grid--three {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .delni-provider-grid--four {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            @media (max-width: 1160px) {
                .delni-provider-grid--four {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }

            @media (max-width: 920px) {
                .delni-provider-grid--four,
                .delni-provider-grid--three {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .delni-section-head {
                    align-items: start;
                    flex-direction: column;
                    margin-bottom: 1rem;
                }

                .delni-provider-grid,
                .delni-provider-grid--four,
                .delni-provider-grid--three,
                .delni-provider-grid--two {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush
@endonce
