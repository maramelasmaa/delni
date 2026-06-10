@props([
    'provider',
    'showBio' => true,
])

@php
    $businessName = $provider->business_name ?? __('messages.public.provider');

    $cardImage = null;
    if ($provider->cover_image) {
        $cardImage = \Illuminate\Support\Facades\Storage::disk('public')->url($provider->cover_image);
    } elseif ($provider->logo) {
        $cardImage = \Illuminate\Support\Facades\Storage::disk('public')->url($provider->logo);
    }

    $rating = (float) ($provider->stats?->rating_avg ?? 0);
    $reviewsCount = (int) ($provider->stats?->reviews_count ?? 0);

    $categoryName = $provider->category
        ? ($provider->category->localized_name ?? $provider->category->name)
        : null;

    $cityName = $provider->city
        ? ($provider->city->localized_name ?? $provider->city->name)
        : null;

    $whatsappNumber = $provider->whatsapp ? preg_replace('/[^0-9]/', '', $provider->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وجدتك عبر دلني وأرغب بالاستفسار عن الخدمة.');
@endphp

<article class="delni-provider-card">
    <a href="{{ route('public.provider', $provider->slug) }}" class="delni-provider-card__media">
        @if($cardImage)
            <img
                src="{{ $cardImage }}"
                alt="{{ $businessName }}"
                loading="lazy"
                class="delni-provider-card__image"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            >
            <div class="delni-provider-card__fallback" style="display:none;">
                {{ mb_substr($businessName, 0, 1) }}
            </div>
        @else
            <div class="delni-provider-card__fallback">
                {{ mb_substr($businessName, 0, 1) }}
            </div>
        @endif

        @if($rating >= 4.5 && $reviewsCount >= 5)
            <span class="delni-provider-card__badge">
                الأعلى تقييماً
            </span>
        @endif
    </a>

    <div class="delni-provider-card__body">
        <div class="delni-provider-card__top">
            <h3 class="delni-provider-card__title">
                <a href="{{ route('public.provider', $provider->slug) }}">
                    {{ $businessName }}
                </a>
            </h3>

            <div class="delni-provider-card__rating">
                <span class="delni-provider-card__star">★</span>
                <strong>{{ number_format($rating, 1) }}</strong>
                <span>({{ $reviewsCount }})</span>
            </div>
        </div>

        <div class="delni-provider-card__meta">
            @if($categoryName)
                <span>
                    <x-render-icon icon="heroicon-o-briefcase" />
                    {{ $categoryName }}
                </span>
            @endif

            @if($cityName)
                <span>
                    <x-render-icon icon="heroicon-o-map-pin" />
                    {{ $cityName }}
                </span>
            @endif

            @if($provider->offers_remote_work)
                <span>
                    <x-render-icon icon="heroicon-o-globe-alt" />
                    عن بعد
                </span>
            @endif
        </div>

        @if($showBio && filled($provider->bio))
            <p class="delni-provider-card__bio">
                {{ Str::limit(strip_tags($provider->bio), 110) }}
            </p>
        @endif

        <div class="delni-provider-card__actions">
            <a href="{{ route('public.provider', $provider->slug) }}" class="delni-provider-card__primary">
                عرض الملف
            </a>

            @if($whatsappNumber)
                <a
                    href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="delni-provider-card__whatsapp"
                >
                    واتساب
                </a>
            @endif
        </div>
    </div>
</article>

@once
    @push('styles')
        <style>
            .delni-provider-card {
                min-width: 0;
                height: 100%;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                border-radius: 24px;
                background: #fff;
                border: 1px solid #E7E7E7;
                box-shadow: 0 12px 28px rgba(11, 26, 52, .06);
                transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            }

            .delni-provider-card:hover {
                transform: translateY(-3px);
                border-color: rgba(241, 98, 15, .22);
                box-shadow: 0 18px 40px rgba(11, 26, 52, .1);
            }

            .delni-provider-card__media {
                position: relative;
                display: block;
                height: 170px;
                overflow: hidden;
                background: #0B1A34;
                text-decoration: none;
            }

            .delni-provider-card__image,
            .delni-provider-card__fallback {
                width: 100%;
                height: 100%;
            }

            .delni-provider-card__image {
                display: block;
                object-fit: cover;
                transition: transform .28s ease;
            }

            .delni-provider-card:hover .delni-provider-card__image {
                transform: scale(1.035);
            }

            .delni-provider-card__fallback {
                display: flex;
                align-items: center;
                justify-content: center;
                background:
                    radial-gradient(circle at 30% 22%, rgba(241, 98, 15, .32), transparent 32%),
                    linear-gradient(135deg, #0B1A34, #13264A);
                color: #F1620F;
                font-size: 3rem;
                font-weight: 950;
            }

            .delni-provider-card__badge {
                position: absolute;
                top: .8rem;
                inset-inline-start: .8rem;
                min-height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: .45rem .75rem;
                border-radius: 999px;
                background: #22C55E;
                color: #fff;
                font-size: .78rem;
                font-weight: 900;
                box-shadow: 0 10px 20px rgba(34, 197, 94, .25);
            }

            .delni-provider-card__body {
                flex: 1;
                display: flex;
                flex-direction: column;
                padding: 1rem;
            }

            .delni-provider-card__top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: .8rem;
                margin-bottom: .85rem;
            }

            .delni-provider-card__title {
                margin: 0;
                min-width: 0;
                color: #0B1A34;
                font-size: 1.02rem;
                line-height: 1.45;
                font-weight: 950;
                letter-spacing: -.025em;
            }

            .delni-provider-card__title a {
                color: inherit;
                text-decoration: none;
            }

            .delni-provider-card__title a:hover {
                color: #F1620F;
            }

            .delni-provider-card__rating {
                flex-shrink: 0;
                display: inline-flex;
                align-items: center;
                gap: .25rem;
                color: #5D5959;
                font-size: .78rem;
                font-weight: 800;
                white-space: nowrap;
            }

            .delni-provider-card__rating strong {
                color: #0B1A34;
                font-weight: 950;
            }

            .delni-provider-card__star {
                color: #F59E0B;
            }

            .delni-provider-card__meta {
                display: flex;
                flex-wrap: wrap;
                gap: .45rem;
                margin-bottom: .85rem;
            }

            .delni-provider-card__meta span {
                min-height: 32px;
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                padding: .38rem .6rem;
                border-radius: 999px;
                background: #FCFBFB;
                border: 1px solid #E7E7E7;
                color: #5D5959;
                font-size: .76rem;
                font-weight: 850;
                max-width: 100%;
            }

            .delni-provider-card__meta svg {
                width: 15px;
                height: 15px;
                color: #F1620F;
                flex-shrink: 0;
            }

            .delni-provider-card__bio {
                margin: 0 0 1rem;
                color: #5D5959;
                font-size: .87rem;
                line-height: 1.8;
                font-weight: 500;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .delni-provider-card__actions {
                margin-top: auto;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: .55rem;
            }

            .delni-provider-card__primary,
            .delni-provider-card__whatsapp {
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
                text-decoration: none;
                font-size: .86rem;
                font-weight: 950;
                transition: .18s ease;
            }

            .delni-provider-card__primary {
                background: #F1620F;
                color: #fff;
                box-shadow: 0 10px 18px rgba(241, 98, 15, .18);
            }

            .delni-provider-card__primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 14px 26px rgba(241, 98, 15, .24);
            }

            .delni-provider-card__whatsapp {
                background: rgba(34, 197, 94, .1);
                color: #128C4A;
                border: 1px solid rgba(34, 197, 94, .18);
            }

            .delni-provider-card__whatsapp:hover {
                background: rgba(34, 197, 94, .16);
            }

            @media (max-width: 640px) {
                .delni-provider-card__media {
                    height: 164px;
                }

                .delni-provider-card__body {
                    padding: .9rem;
                }

                .delni-provider-card__top {
                    flex-direction: column;
                    gap: .35rem;
                }
            }
        </style>
    @endpush
@endonce
