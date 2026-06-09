@props(['cities' => collect(), 'active' => null])

<div class="city-nav-section">
    <div class="city-nav-container">
        <h3 class="city-nav-title">{{ __('messages.public.cities') }}</h3>
        <div class="city-nav-scroll">
            @forelse($cities as $city)
                <a
                    href="{{ route('public.city', $city->slug) }}"
                    class="city-nav-item {{ $active === $city->id ? 'is-active' : '' }}"
                    title="{{ $city->localized_name ?? $city->name }}"
                >
                    <span class="city-nav-icon">
                        @if($city->icon)
                            <x-render-icon :icon="$city->icon" class="w-5 h-5" />
                        @else
                            <span class="icon-placeholder">📍</span>
                        @endif
                    </span>
                    <span class="city-nav-text">
                        <span class="city-nav-name">{{ $city->localized_name ?? $city->name }}</span>
                        <span class="city-nav-count">{{ $city->discoverable_profiles_count ?? 0 }}</span>
                    </span>
                </a>
            @empty
                <div class="city-nav-empty">
                    {{ __('messages.public.no_cities') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .city-nav-section {
                background: #ffffff;
                border-bottom: 1px solid #e5e7eb;
                padding: 2rem 0;
                overflow-x: auto;
            }

            .city-nav-container {
                max-width: 1320px;
                margin: 0 auto;
                padding: 0 1rem;
            }

            .city-nav-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: #0b1a34;
                margin: 0 0 1.5rem;
                letter-spacing: -0.01em;
            }

            .city-nav-scroll {
                display: flex;
                gap: 0.75rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
            }

            /* Hide scrollbar but keep functionality */
            .city-nav-scroll::-webkit-scrollbar {
                height: 4px;
            }

            .city-nav-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .city-nav-scroll::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 2px;
            }

            .city-nav-item {
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.6rem;
                padding: 1rem;
                background: #f8fafc;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                text-decoration: none;
                color: #475569;
                font-weight: 600;
                font-size: 0.85rem;
                transition: all 0.2s ease;
                white-space: nowrap;
                flex-shrink: 0;
                cursor: pointer;
                min-width: 90px;
                text-align: center;
            }

            .city-nav-item:hover {
                background: #f1f5f9;
                border-color: #ff7a1a;
                color: #ff7a1a;
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(255, 122, 26, 0.12);
            }

            .city-nav-item.is-active {
                background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
                border-color: #ff6b1a;
                color: #ffffff;
                box-shadow: 0 8px 20px rgba(255, 107, 26, 0.25);
            }

            .city-nav-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 24px;
                height: 24px;
                flex-shrink: 0;
            }

            .city-nav-icon svg {
                width: 100%;
                height: 100%;
                display: block;
            }

            .icon-placeholder {
                font-size: 1.2rem;
                line-height: 1;
            }

            .city-nav-text {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.2rem;
            }

            .city-nav-name {
                display: block;
                font-weight: 600;
                font-size: 0.85rem;
            }

            .city-nav-count {
                display: block;
                font-size: 0.7rem;
                opacity: 0.7;
                font-weight: 500;
            }

            .city-nav-empty {
                padding: 2rem;
                text-align: center;
                color: #94a3b8;
            }

            @media (max-width: 768px) {
                .city-nav-section {
                    padding: 1.5rem 0;
                }

                .city-nav-title {
                    font-size: 1rem;
                    margin-bottom: 1rem;
                }

                .city-nav-item {
                    padding: 0.8rem;
                    font-size: 0.8rem;
                    min-width: 80px;
                }

                .city-nav-icon {
                    width: 20px;
                    height: 20px;
                }

                .city-nav-name {
                    font-size: 0.8rem;
                }
            }

            @media (max-width: 480px) {
                .city-nav-item {
                    min-width: 70px;
                    padding: 0.7rem;
                }
            }
        </style>
    @endpush
@endonce
