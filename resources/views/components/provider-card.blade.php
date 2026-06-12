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

    $serviceTags = $provider->relationLoaded('subcategories')
        ? $provider->subcategories->take(3)
        : collect();

    $whatsappNumber = $provider->whatsapp ? preg_replace('/[^0-9]/', '', $provider->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وجدتك عبر دلني وأرغب بالاستفسار عن الخدمة.');
@endphp

<article class="pwa-native-card">
    {{-- Left side image/fallback circle --}}
    <a href="{{ route('public.provider', $provider->slug) }}" class="pwa-card-media-circle">
        @if($cardImage)
            <img src="{{ $cardImage }}" alt="{{ $businessName }}" loading="lazy" class="pwa-circle-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="pwa-circle-fallback" style="display:none;">{{ mb_substr($businessName, 0, 1) }}</div>
        @else
            <div class="pwa-circle-fallback">{{ mb_substr($businessName, 0, 1) }}</div>
        @endif
    </a>

    {{-- Center Body Content --}}
    <div class="pwa-card-core-content">
        <div class="pwa-card-top-row">
            <h4 class="pwa-card-title">
                <a href="{{ route('public.provider', $provider->slug) }}">{{ $businessName }}</a>
            </h4>
            <div class="pwa-card-badge-rating">
                <span class="pwa-star-mini">★</span>
                <span class="pwa-rating-val">{{ number_format($rating, 1) }}</span>
            </div>
        </div>

        <div class="pwa-card-sub-meta">
            @if($categoryName)
                <span class="pwa-meta-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 13px; height: 13px;">
                        <path d="M20.25 6.375c0-1.035-.84-1.875-1.875-1.875H5.625c-1.036 0-1.875.84-1.875 1.875v11.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V6.375Z" />
                        <path fill-rule="evenodd" d="M12 2.25c-1.036 0-1.875.84-1.875 1.875v.75c0 .414.336.75.75.75h2.25c.414 0 .75-.336.75-.75v-.75c0-1.035-.84-1.875-1.875-1.875Zm-9 6a.75.75 0 0 0-.75.75v7.5c0 .414.336.75.75.75H21a.75.75 0 0 0 .75-.75v-7.5a.75.75 0 0 0-.75-.75H3Z" clip-rule="evenodd" />
                    </svg>
                    {{ $categoryName }}
                </span>
            @endif
            @if($cityName)
                <span class="pwa-meta-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 13px; height: 13px;">
                        <path fill-rule="evenodd" d="M11.54 1.265a.75.75 0 0 1 .92 0l9 7.5a.75.75 0 1 1-.98 1.15L12 2.687l-8.48 7.078a.75.75 0 0 1-.98-1.15l9-7.5ZM3.75 12a.75.75 0 0 0-.75.75v7.5a.75.75 0 0 0 .75.75h3v-7h6v7h3a.75.75 0 0 0 .75-.75v-7.5a.75.75 0 0 0-.75-.75H3.75Zm7.5 5.25v-5h-3v5h3Z" clip-rule="evenodd" />
                    </svg>
                    {{ $cityName }}
                </span>
            @endif
        </div>

        @if($serviceTags->isNotEmpty())
            <div class="pwa-card-service-tags">
                @foreach($serviceTags as $subcategory)
                    <a href="{{ route('public.subcategory', $subcategory->slug) }}" class="pwa-service-tag">
                        {{ $subcategory->localized_name ?? $subcategory->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- High-priority Thumb Actions Container --}}
        <div class="pwa-card-actions-wrapper">
            <a href="{{ route('public.provider', $provider->slug) }}" class="pwa-btn-action pwa-btn-view">
                عرض الملف
            </a>
            @if($whatsappNumber)
                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener noreferrer" class="pwa-btn-action pwa-btn-wa">
                    واتساب مباشرة
                </a>
            @endif
        </div>
    </div>
</article>

@once
@push('styles')
<style>
    .pwa-native-card {
        display: flex;
        gap: 0.85rem;
        background: #ffffff;
        border: 1px solid #E8EDF4;
        border-radius: 18px;
        padding: 0.85rem;
        box-shadow: 0 4px 12px rgba(11, 26, 52, 0.02);
        align-items: center;
        transition: transform 0.15s ease, border-color 0.15s ease;
    }

    .pwa-native-card:hover {
        border-color: rgba(241, 98, 15, 0.25);
    }

    /* Compact Avatar Circle */
    .pwa-card-media-circle {
        flex-shrink: 0;
        width: 64px;
        height: 64px;
        border-radius: 14px;
        overflow: hidden;
        background: #0B1A34;
        position: relative;
    }
    .pwa-circle-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .pwa-circle-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0B1A34, #1E3A8A);
        color: #F1620F;
        font-weight: 900;
        font-size: 1.4rem;
    }

    /* Core Content Grid Engine */
    .pwa-card-core-content {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }
    .pwa-card-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }
    .pwa-card-title {
        margin: 0;
        font-size: 0.94rem;
        font-weight: 800;
        color: #0B1A34;
        line-height: 1.3;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pwa-card-title a {
        color: inherit;
        text-decoration: none;
    }
    .pwa-card-badge-rating {
        display: flex;
        align-items: center;
        gap: 0.15rem;
        background: #FEF3C7;
        padding: 0.15rem 0.4rem;
        border-radius: 6px;
        flex-shrink: 0;
    }
    .pwa-star-mini {
        color: #D97706;
        font-size: 0.75rem;
    }
    .pwa-rating-val {
        font-size: 0.72rem;
        font-weight: 800;
        color: #92400E;
    }

    /* Meta Details Layout */
    .pwa-card-sub-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-bottom: 0.5rem;
    }
    .pwa-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.74rem;
        color: #64748B;
        font-weight: 600;
    }
    .pwa-meta-pill svg {
        width: 13px;
        height: 13px;
        color: #94A3B8;
    }

    .pwa-card-service-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 0.65rem;
    }

    .pwa-service-tag {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        min-height: 24px;
        padding: 0.2rem 0.45rem;
        border-radius: 999px;
        background: #FFF7ED;
        color: #C2410C;
        font-size: 0.68rem;
        font-weight: 800;
        line-height: 1.2;
        text-decoration: none;
        white-space: nowrap;
    }

    .pwa-service-tag:hover {
        background: #FFEDD5;
    }

    /* Quick Action Micro Row */
    .pwa-card-actions-wrapper {
        display: flex;
        gap: 0.45rem;
    }
    .pwa-btn-action {
        flex: 1;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 800;
        text-decoration: none;
        transition: opacity 0.15s ease;
    }
    .pwa-btn-action:active {
        opacity: 0.85;
    }
    .pwa-btn-view {
        background: #F1620F;
        color: #ffffff;
    }
    .pwa-btn-wa {
        background: #DCFCE7;
        color: #15803D;
        border: 1px solid #BBF7D0;
    }

    /* Responsive Reset Override */
    @media (max-width: 640px) {
        .delni-provider-card {
            display: none !important;
        }
    }
</style>
@endpush
@endonce
