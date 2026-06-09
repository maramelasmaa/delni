@props(['provider', 'showBio' => true])

@php
    $businessName = $provider->business_name ?? __('messages.public.provider');
    $logo = $provider->logo_url ?? ($provider->logo ? asset('storage/' . $provider->logo) : null);
    $rating = (float) ($provider->stats?->rating_avg ?? 0);
    $reviewsCount = (int) ($provider->stats?->reviews_count ?? 0);

    $whatsappNumber = $provider->whatsapp ? preg_replace('/[^0-9]/', '', $provider->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وجدتك عبر دلني وأرغب بالاستفسار عن الخدمة.');
@endphp

<article class="provider-card card h-100 border-0">
    <div class="provider-card__media">
        <a href="{{ route('public.provider', $provider->slug) }}" class="d-block">
            @if($logo)
                <img
                    src="{{ $logo }}"
                    alt="{{ $businessName }}"
                    class="provider-card__image"
                    loading="lazy"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                >
                <div class="provider-card__fallback" style="display:none;">
                    {{ mb_substr($businessName, 0, 1) }}
                </div>
            @else
                <div class="provider-card__fallback">
                    {{ mb_substr($businessName, 0, 1) }}
                </div>
            @endif
        </a>

        @if($rating >= 4.5 && $reviewsCount >= 5)
            <span class="provider-card__badge">
                {{ __('messages.public.top_rated') }}
            </span>
        @endif
    </div>

    <div class="card-body provider-card__body">
        <!-- Title -->
        <h3 class="provider-card__title mb-3">
            <a href="{{ route('public.provider', $provider->slug) }}">
                {{ $businessName }}
            </a>
        </h3>

        <!-- Features Section -->
        <div class="provider-card__features mb-4">
            @if($provider->category)
                <div class="provider-card__feature">
                    <span class="provider-card__feature-icon">
                        <x-render-icon icon="heroicon-o-briefcase" class="w-5 h-5" />
                    </span>
                    <span class="provider-card__feature-label">
                        {{ $provider->category->localized_name ?? $provider->category->name }}
                    </span>
                </div>
            @endif

            @if($provider->city)
                <div class="provider-card__feature">
                    <span class="provider-card__feature-icon">
                        <x-render-icon :icon="$provider->city->icon" class="w-5 h-5" />
                    </span>
                    <span class="provider-card__feature-label">
                        {{ $provider->city->localized_name ?? $provider->city->name }}
                    </span>
                </div>
            @endif

            @if($provider->offers_remote_work)
                <div class="provider-card__feature">
                    <span class="provider-card__feature-icon">
                        <x-render-icon icon="heroicon-o-globe-alt" class="w-5 h-5" />
                    </span>
                    <span class="provider-card__feature-label">
                        {{ __('messages.public.remote_work') }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Rating -->
        <div class="provider-card__rating mb-3">
            <span class="rating-stars">
                @for($i = 1; $i <= 5; $i++)
                    <span class="{{ $i <= round($rating) ? '' : 'is-muted' }}">★</span>
                @endfor
            </span>

            <span class="provider-card__rating-text">
                {{ number_format($rating, 1) }}
                <span>({{ $reviewsCount }})</span>
            </span>
        </div>

        <!-- Bio -->
        @if($showBio && filled($provider->bio))
            <p class="provider-card__bio">
                {{ Str::limit(strip_tags($provider->bio), 115) }}
            </p>
        @endif

        <!-- Actions -->
        <div class="provider-card__actions">
            <a href="{{ route('public.provider', $provider->slug) }}" class="btn btn-primary btn-sm">
                {{ __('messages.public.view_profile') }}
            </a>

            @if($whatsappNumber)
                <a
                    href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="whatsapp-btn btn-sm"
                >
                    {{ __('messages.public.whatsapp') }}
                </a>
            @endif
        </div>
    </div>
</article>

@once
    @push('styles')
        <style>
            .provider-card {
                border-radius: 22px;
                overflow: hidden;
                background: #fff;
                box-shadow: 0 10px 28px rgba(11, 26, 52, 0.08);
                transition: 0.22s ease;
                max-width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .provider-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 18px 42px rgba(11, 26, 52, 0.13);
            }

            .provider-card__media {
                position: relative;
                height: 188px;
                background: #0B1A34;
                overflow: hidden;
            }

            .provider-card__image,
            .provider-card__fallback {
                width: 100%;
                height: 188px;
                object-fit: cover;
            }

            .provider-card__image {
                display: block;
                transition: transform 0.3s ease;
            }

            .provider-card:hover .provider-card__image {
                transform: scale(1.04);
            }

            .provider-card__fallback {
                display: flex;
                align-items: center;
                justify-content: center;
                background:
                    radial-gradient(circle at 30% 20%, rgba(241, 98, 15, 0.35), transparent 28%),
                    linear-gradient(135deg, #0B1A34, #112240);
                color: #F1620F;
                font-size: 3rem;
                font-weight: 800;
            }

            .provider-card__badge {
                position: absolute;
                top: 12px;
                inset-inline-start: 12px;
                background: #22C55E;
                color: #fff;
                border-radius: 999px;
                padding: 0.35rem 0.7rem;
                font-size: 0.78rem;
                font-weight: 800;
                box-shadow: 0 8px 18px rgba(34, 197, 94, 0.25);
            }

            .provider-card__body {
                padding: 1.3rem;
                display: flex;
                flex-direction: column;
                flex: 1;
                justify-content: space-between;
            }

            .provider-card__title {
                font-size: 1rem;
                font-weight: 800;
                line-height: 1.35;
            }

            .provider-card__title a {
                color: #0B1A34;
                text-decoration: none;
            }

            .provider-card__title a:hover {
                color: #F1620F;
            }

            /* Feature Circles Section */
            .provider-card__features {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.7rem;
                justify-items: center;
            }

            .provider-card__feature {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.4rem;
                width: 100%;
                text-align: center;
            }

            .provider-card__feature-icon {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #F8FAFC;
                border: 1.5px solid #EEF2F7;
                color: #0B1A34;
                flex: 0 0 auto;
            }

            .provider-card__feature-label {
                font-size: 0.75rem;
                font-weight: 600;
                color: #374151;
                line-height: 1.2;
                word-break: break-word;
            }

            .provider-card__rating {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.6rem;
                margin-top: auto;
            }

            .provider-card__rating .rating-stars {
                color: #F59E0B;
                letter-spacing: 2px;
                font-size: 0.85rem;
            }

            .provider-card__rating .rating-stars .is-muted {
                opacity: 0.25;
            }

            .provider-card__rating-text {
                color: #0B1A34;
                font-weight: 700;
                font-size: 0.8rem;
            }

            .provider-card__rating-text span {
                color: #6B7280;
                font-weight: 500;
                font-size: 0.75rem;
            }

            .provider-card__bio {
                color: #6B7280;
                font-size: 0.85rem;
                line-height: 1.6;
                margin-bottom: 1rem;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .provider-card__actions {
                display: flex;
                gap: 0.5rem;
                margin-top: auto;
            }

            .provider-card__actions .btn,
            .provider-card__actions .whatsapp-btn {
                flex: 1;
                height: 38px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
                font-weight: 600;
            }

            @media (max-width: 575px) {
                .provider-card__media,
                .provider-card__image,
                .provider-card__fallback {
                    height: 180px;
                }

                .provider-card__body {
                    padding: 1rem;
                }

                .provider-card__title {
                    font-size: 0.95rem;
                }

                .provider-card__features {
                    grid-template-columns: repeat(3, 1fr);
                    gap: 0.5rem;
                    margin-bottom: 0.8rem;
                }

                .provider-card__feature-icon {
                    width: 44px;
                    height: 44px;
                }

                .provider-card__feature-label {
                    font-size: 0.7rem;
                }

                .provider-card__bio {
                    font-size: 0.8rem;
                    margin-bottom: 0.8rem;
                }

                .provider-card__rating {
                    gap: 0.4rem;
                    margin-bottom: 0.8rem;
                }

                .provider-card__actions .btn,
                .provider-card__actions .whatsapp-btn {
                    height: 36px;
                    font-size: 0.8rem;
                }
            }
        </style>
    @endpush
@endonce
