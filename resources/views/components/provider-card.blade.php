@props([
    'provider',
    'showBio' => true,
])

@php
    $businessName = $provider->business_name ?? __('messages.public.provider');

    $coverImage = $provider->cover_image
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($provider->cover_image)
        : null;

    $logoImage = $provider->logo
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($provider->logo)
        : null;

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

    $initials = mb_substr($businessName, 0, 1);
@endphp

<article class="pc-card">
    {{-- Image banner with logo badge --}}
    <a href="{{ route('public.provider', $provider->slug) }}" class="pc-banner" aria-label="{{ $businessName }}">
        @if($coverImage)
            <img src="{{ $coverImage }}" alt="{{ $businessName }}" loading="lazy" class="pc-banner-img">
        @elseif($logoImage)
            <img src="{{ $logoImage }}" alt="{{ $businessName }}" loading="lazy" class="pc-banner-img pc-banner-img--logo">
        @else
            <div class="pc-banner-empty">
                <span>{{ $initials }}</span>
            </div>
        @endif

        {{-- Logo badge floating at bottom-left of banner --}}
        @if($coverImage && $logoImage)
            <div class="pc-logo-badge">
                <img src="{{ $logoImage }}" alt="{{ $businessName }}">
            </div>
        @endif

        {{-- Rating badge floating top-right --}}
        @if($rating > 0)
            <div class="pc-rating-badge">
                <span class="pc-star">★</span>
                <span>{{ number_format($rating, 1) }}</span>
            </div>
        @endif
    </a>

    {{-- Card body --}}
    <div class="pc-body">
        <h4 class="pc-name">
            <a href="{{ route('public.provider', $provider->slug) }}">{{ $businessName }}</a>
        </h4>

        <div class="pc-meta">
            @if($categoryName)
                <span class="pc-meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" /><path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" /></svg>
                    {{ $categoryName }}
                </span>
            @endif
            @if($cityName)
                <span class="pc-meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                    {{ $cityName }}
                </span>
            @endif
        </div>

        @if($serviceTags->isNotEmpty())
            <div class="pc-tags">
                @foreach($serviceTags as $subcategory)
                    <a href="{{ route('public.subcategory', $subcategory->slug) }}" class="pc-tag">
                        {{ $subcategory->localized_name ?? $subcategory->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="pc-actions">
            <a href="{{ route('public.provider', $provider->slug) }}" class="pc-btn pc-btn--primary">
                عرض الملف
            </a>
            @if($whatsappNumber)
                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener noreferrer" class="pc-btn pc-btn--wa">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.125.557 4.122 1.529 5.857L0 24l6.335-1.507A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.788 9.788 0 01-5.027-1.384l-.36-.214-3.762.895.952-3.659-.235-.375A9.786 9.786 0 012.182 12C2.182 6.57 6.57 2.182 12 2.182S21.818 6.57 21.818 12 17.43 21.818 12 21.818z"/></svg>
                    واتساب
                </a>
            @endif
        </div>
    </div>
</article>

@once
@push('styles')
<style>
    .pc-card {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border: 1px solid #E8EDF4;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(11,26,52,.05);
        transition: box-shadow .2s ease, border-color .2s ease;
    }

    .pc-card:hover {
        box-shadow: 0 8px 28px rgba(11,26,52,.1);
        border-color: rgba(241,98,15,.2);
    }

    /* Banner */
    .pc-banner {
        position: relative;
        display: block;
        height: 160px;
        background: linear-gradient(135deg, #0B1A34 0%, #1a3259 100%);
        overflow: hidden;
        flex-shrink: 0;
    }

    .pc-banner-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .35s ease;
    }

    .pc-card:hover .pc-banner-img {
        transform: scale(1.04);
    }

    .pc-banner-img--logo {
        object-fit: contain;
        padding: 1.5rem;
        background: linear-gradient(135deg, #0B1A34 0%, #1a3259 100%);
    }

    .pc-banner-empty {
        width: 100%;
        height: 100%;
        display: grid;
        place-items: center;
    }

    .pc-banner-empty span {
        font-size: 3rem;
        font-weight: 950;
        color: #F1620F;
        opacity: .6;
    }

    /* Logo badge */
    .pc-logo-badge {
        position: absolute;
        bottom: .75rem;
        inset-inline-start: .75rem;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        overflow: hidden;
        border: 2.5px solid rgba(255,255,255,.9);
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,.18);
    }

    .pc-logo-badge img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Rating badge */
    .pc-rating-badge {
        position: absolute;
        top: .65rem;
        inset-inline-end: .65rem;
        display: inline-flex;
        align-items: center;
        gap: .2rem;
        padding: .3rem .55rem;
        border-radius: 999px;
        background: rgba(11,26,52,.72);
        backdrop-filter: blur(8px);
        color: #fff;
        font-size: .75rem;
        font-weight: 900;
    }

    .pc-star {
        color: #F59E0B;
        font-size: .8rem;
    }

    /* Body */
    .pc-body {
        padding: .9rem;
        display: flex;
        flex-direction: column;
        gap: .55rem;
        flex: 1;
    }

    .pc-name {
        margin: 0;
        font-size: .94rem;
        font-weight: 900;
        color: #0B1A34;
        line-height: 1.35;
    }

    .pc-name a {
        color: inherit;
        text-decoration: none;
    }

    /* Meta row */
    .pc-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
    }

    .pc-meta-item {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        color: #64748B;
        font-size: .74rem;
        font-weight: 700;
    }

    .pc-meta-item svg {
        width: 13px;
        height: 13px;
        flex-shrink: 0;
        color: #94A3B8;
    }

    /* Tags */
    .pc-tags {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }

    .pc-tag {
        display: inline-flex;
        align-items: center;
        padding: .22rem .55rem;
        border-radius: 999px;
        background: #FFF7ED;
        color: #C2410C;
        font-size: .7rem;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s ease;
    }

    .pc-tag:hover {
        background: #FFEDD5;
    }

    /* Actions */
    .pc-actions {
        display: flex;
        gap: .5rem;
        margin-top: auto;
        padding-top: .35rem;
    }

    .pc-btn {
        flex: 1;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        border-radius: 12px;
        font-size: .78rem;
        font-weight: 900;
        text-decoration: none;
        transition: opacity .15s ease, transform .15s ease;
    }

    .pc-btn:active {
        opacity: .85;
        transform: scale(.98);
    }

    .pc-btn--primary {
        background: #F1620F;
        color: #fff;
    }

    .pc-btn--wa {
        background: #DCFCE7;
        color: #15803D;
        border: 1px solid #BBF7D0;
    }

    .pc-btn--wa svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }
</style>
@endpush
@endonce
