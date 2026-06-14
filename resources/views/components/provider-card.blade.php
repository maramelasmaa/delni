@props([
    'provider',
    'showBio' => true,
    'favoriteProfileIds' => [],
])

@php
    $businessName = $provider->business_name ?? __('messages.public.provider');

    $coverImage = $provider->cover_image
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($provider->cover_image)
        : null;

    $logoImage = $provider->logo
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($provider->logo)
        : null;

    $reviewsCount = (int) ($provider->getAttribute('approved_reviews_count') ?? 0);
    $rating = $reviewsCount > 0
        ? (float) ($provider->getAttribute('approved_reviews_avg_rating') ?? 0)
        : 0.0;

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

    $isFavorited = in_array($provider->id, $favoriteProfileIds, true);
@endphp

<article class="pc-card">
    {{-- Image banner with logo badge --}}
    <div class="pc-media">
    <a href="{{ route('public.provider', $provider->slug) }}" class="pc-banner" aria-label="{{ $businessName }}">
        @if($coverImage)
            <img src="{{ $coverImage }}" alt="{{ $businessName }}" loading="lazy" decoding="async" class="pc-banner-img">
        @elseif($logoImage)
            <img src="{{ $logoImage }}" alt="{{ $businessName }}" loading="lazy" decoding="async" class="pc-banner-img pc-banner-img--logo">
        @else
            <div class="pc-banner-empty">
                <span>{{ $initials }}</span>
            </div>
        @endif

        {{-- Logo badge floating at bottom-left of banner --}}
        @if($coverImage && $logoImage)
            <div class="pc-logo-badge">
                <img src="{{ $logoImage }}" alt="{{ $businessName }}" loading="lazy" decoding="async">
            </div>
        @endif

        {{-- Rating badge floating top-right --}}
        @if($rating > 0)
            <div class="pc-rating-badge" aria-label="متوسط التقييم {{ number_format($rating, 1) }} من 5">
                <span class="pc-star">★</span>
                <span>{{ number_format($rating, 1) }}</span>
            </div>
        @endif

    </a>

        {{-- Favorite toggle top-left --}}
        @auth
            <button
                class="pc-fav-btn {{ $isFavorited ? 'is-favorited' : '' }}"
                data-toggle-url="{{ route('favorites.toggle', $provider) }}"
                aria-label="{{ $isFavorited ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة' }}"
                type="button"
            >
                <x-render-icon icon="app-heart" class="pc-fav-outline" />
                <x-render-icon icon="app-heart-filled" class="pc-fav-filled" />
            </button>
        @else
            <button class="pc-fav-btn pc-fav-guest" type="button" aria-label="أضف إلى المفضلة">
                <x-render-icon icon="app-heart" class="pc-fav-outline" />
            </button>
        @endauth
    </div>

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
@push('scripts')
<script>
(function () {
    var toast = null;

    function showLoginToast() {
        if (window.DelniAuthToast) {
            window.DelniAuthToast.show('سجّل دخولك لإضافة المزود إلى المفضلة', 'دخول', '{{ route('login') }}');
            return;
        }

        if (toast) { return; }
        toast = document.createElement('div');
        toast.className = 'pc-fav-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.innerHTML = '<span>سجّل دخولك لإضافة مزودين إلى المفضلة</span><a href="{{ route('login') }}">دخول</a>';
        document.body.appendChild(toast);
        requestAnimationFrame(function () { toast.classList.add('is-visible'); });
        setTimeout(function () {
            toast.classList.remove('is-visible');
            setTimeout(function () { toast && toast.remove(); toast = null; }, 300);
        }, 5000);
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.pc-fav-guest')) {
            e.preventDefault();
            e.stopPropagation();
            showLoginToast();
            return;
        }

        const btn = e.target.closest('.pc-fav-btn[data-toggle-url]');
        if (!btn) { return; }

        e.preventDefault();
        e.stopPropagation();

        const url = btn.dataset.toggleUrl;
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        btn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            btn.classList.toggle('is-favorited', data.favorited);
            btn.setAttribute('aria-label', data.favorited ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة');
        })
        .catch(function () {})
        .finally(function () { btn.disabled = false; });
    });
}());
</script>
@endpush
@push('styles')
<style>
    .pc-card {
        height: 100%;
        min-height: 360px;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border: 1px solid #E8EDF4;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(11,26,52,.05);
        transition: box-shadow .2s ease, border-color .2s ease;
    }

    .pc-card:hover {
        box-shadow: 0 8px 28px rgba(11,26,52,.1);
        border-color: rgba(241,98,15,.2);
    }

    .pc-media {
        position: relative;
        flex: 0 0 auto;
        min-height: 0;
    }

    /* Banner */
    .pc-banner {
        display: block;
        aspect-ratio: 16 / 10;
        height: 100%;
        min-height: 128px;
        max-height: 180px;
        background: linear-gradient(135deg, #0B1A34 0%, #1a3259 100%);
        overflow: hidden;
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
        gap: .22rem;
        min-height: 28px;
        padding: .28rem .52rem;
        border: 1px solid rgba(255,255,255,.32);
        border-radius: 999px;
        background: rgba(255,255,255,.9);
        backdrop-filter: blur(8px);
        color: #0B1A34;
        font-size: .74rem;
        font-weight: 900;
        box-shadow: 0 8px 20px rgba(15,23,42,.12);
    }

    .pc-star {
        color: #B45309;
        font-size: .76rem;
    }

    /* Body */
    .pc-body {
        padding: .9rem;
        display: flex;
        flex-direction: column;
        gap: .55rem;
        flex: 1;
        min-height: 0;
    }

    .pc-name {
        margin: 0;
        min-height: 2.54em;
        font-size: .94rem;
        font-weight: 900;
        color: #0B1A34;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .pc-name a {
        color: inherit;
        text-decoration: none;
    }

    /* Meta row */
    .pc-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        min-height: 26px;
    }

    .pc-meta-item {
        display: inline-flex;
        align-items: center;
        gap: .24rem;
        min-height: 26px;
        padding: .22rem .48rem;
        border: 1px solid #E8EDF4;
        border-radius: 999px;
        background: #F8FAFC;
        color: #475569;
        font-size: .7rem;
        font-weight: 850;
        max-width: 100%;
    }

    .pc-meta-item svg {
        width: 12px;
        height: 12px;
        flex-shrink: 0;
        color: #F1620F;
    }

    /* Tags */
    .pc-tags {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        min-height: 26px;
        max-height: 57px;
        overflow: hidden;
    }

    .pc-tag {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: .22rem .48rem;
        border: 1px solid #E8EDF4;
        border-radius: 999px;
        background: #F8FAFC;
        color: #475569;
        font-size: .7rem;
        font-weight: 850;
        text-decoration: none;
        white-space: nowrap;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color .15s ease;
    }

    .pc-tag:hover {
        border-color: rgba(241,98,15,.22);
        background: rgba(241,98,15,.06);
        color: #F1620F;
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
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        border-radius: 12px;
        font-size: .8rem;
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

    /* Favorite button */
    .pc-fav-btn {
        position: absolute;
        top: .6rem;
        inset-inline-start: .6rem;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(8px);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, .8);
        transition: transform .2s ease, background .2s ease;
        text-decoration: none;
        padding: 0;
    }

    .pc-fav-btn:active { transform: scale(.88); }
    .pc-fav-btn svg { width: 20px; height: 20px; }
    .pc-fav-btn .pc-fav-filled { display: none; }
    .pc-fav-btn .pc-fav-outline { display: block; }
    .pc-fav-btn.is-favorited { color: #F1620F; background: rgba(241, 98, 15, .25); }
    .pc-fav-btn.is-favorited .pc-fav-filled { display: block; fill: #F1620F; }
    .pc-fav-btn.is-favorited .pc-fav-outline { display: none; }

    /* Guest login toast */
    .pc-fav-toast {
        position: fixed;
        bottom: calc(72px + env(safe-area-inset-bottom, 0px) + .75rem);
        inset-inline-start: 50%;
        transform: translateX(50%) translateY(1rem);
        background: #0B1A34;
        color: #fff;
        padding: .65rem 1rem;
        border-radius: 14px;
        font-size: .85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: .75rem;
        box-shadow: 0 8px 24px rgba(0,0,0,.25);
        opacity: 0;
        transition: opacity .25s ease, transform .25s ease;
        z-index: 9999;
        white-space: nowrap;
        pointer-events: none;
    }

    .pc-fav-toast.is-visible {
        opacity: 1;
        transform: translateX(50%) translateY(0);
        pointer-events: auto;
    }

    .pc-fav-toast a {
        color: #F1620F;
        font-weight: 800;
        text-decoration: none;
        flex-shrink: 0;
    }

    /* Dark mode */
    [data-theme="dark"] .pc-card {
        background: #1E293B;
        border-color: #334155;
    }
    [data-theme="dark"] .pc-name { color: #F1F5F9; }
    [data-theme="dark"] .pc-rating-badge {
        background: rgba(15,23,42,.82);
        border-color: rgba(255,255,255,.14);
        color: #F1F5F9;
    }
    [data-theme="dark"] .pc-meta-item {
        background: #0F172A;
        border-color: #334155;
        color: #CBD5E1;
    }
    [data-theme="dark"] .pc-tag {
        background: #0F172A;
        border-color: #334155;
        color: #CBD5E1;
    }
    [data-theme="dark"] .pc-tag:hover {
        background: rgba(241,98,15,.12);
        border-color: rgba(241,98,15,.25);
        color: #FB923C;
    }
    [data-theme="dark"] .pc-btn--wa {
        background: #0D2B1D;
        border-color: rgba(37,211,102,.2);
        color: #4ADE80;
    }
</style>
@endpush
@endonce
