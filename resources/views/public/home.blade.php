@extends('public.layout')

@section('title', __('messages.public.home') . ' - ' . config('app.name'))

@section('content')

@php
    $categories = $categories ?? collect();
    $cities = $cities ?? collect();

    $professionalsCount = $categories->sum(
        fn ($category) => (int) ($category->discoverable_profiles_count ?? 0)
    );
@endphp

<section class="home-hero">

    <div class="hero-gradient hero-gradient-1"></div>
    <div class="hero-gradient hero-gradient-2"></div>
    <div class="hero-grid"></div>

    <div class="container">
        <div class="hero-inner">
            <h1 class="hero-title">
                {{ __('messages.public.find_trusted_professionals') }}

                <span>
                    {{ __('messages.public.in_libya') }}
                </span>
            </h1>

            <p class="hero-text">
                {{ __('messages.public.browse_local_professionals') }}
            </p>

            <form
                action="{{ route('public.search') }}"
                method="GET"
                class="premium-search"
            >

                <div class="premium-field premium-keyword">

                    <svg
                        class="field-svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>

                    <input
                        type="text"
                        name="keyword"
                        placeholder="{{ __('messages.public.search_placeholder') }}"
                        value="{{ request('keyword') }}"
                        maxlength="100"
                        autocomplete="off"
                    >
                </div>

                <div class="premium-field">

                    <svg
                        class="field-svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>

                    <select name="city_id">

                        <option value="">
                            {{ __('messages.public.all_cities') }}
                        </option>

                        @foreach($cities->take(15) as $city)
                            <option value="{{ $city->id }}">
                                {{ $city->localized_name ?? $city->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="premium-field">

                    <svg
                        class="field-svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 2v20"></path>
                        <path d="M2 12h20"></path>
                    </svg>

                    <select name="category_id">

                        <option value="">
                            {{ __('messages.public.all_categories') }}
                        </option>

                        @foreach($categories->take(15) as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->localized_name ?? $category->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <button
                    type="submit"
                    class="premium-search-btn"
                >
                    {{ __('messages.public.search') }}
                </button>

            </form>

            <div class="hero-stats">

                <div class="hero-stat">
                    <strong>{{ $professionalsCount }}+</strong>
                    <span>{{ __('messages.public.professionals') }}</span>
                </div>

                <div class="hero-stat">
                    <strong>{{ $cities->count() }}+</strong>
                    <span>{{ __('messages.public.cities') }}</span>
                </div>

                <div class="hero-stat">
                    <strong>{{ $categories->count() }}+</strong>
                    <span>{{ __('messages.public.categories') }}</span>
                </div>

            </div>

        </div>

    </div>

</section>

<style>
/* ===== PREMIUM HERO SECTION ===== */

.home-hero {
    position: relative;
    overflow: hidden;
    min-height: 92vh;
    display: flex;
    align-items: center;
    background-image: url('/images/herobackground.png');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    padding: 4rem 0;
}

.home-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(135deg, rgba(7,20,43,0.6) 0%, rgba(13,34,72,0.5) 50%, rgba(5,11,24,0.6) 100%);
    z-index: 1;
    pointer-events: none;
}

.hero-gradient {
    position: absolute;
    border-radius: 999px;
    filter: blur(120px);
    opacity: 0.06;
    z-index: 2;
}

.hero-gradient-1 {
    width: 500px;
    height: 500px;
    background: #ff8533;
    top: -200px;
    right: -100px;
}

.hero-gradient-2 {
    width: 450px;
    height: 450px;
    background: #2f5abb;
    bottom: -200px;
    left: -150px;
}

.hero-grid {
    position: absolute;
    inset: 0;
    opacity: 0.03;
    z-index: 3;
    background-image:
        linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 80px 80px;
}

.hero-inner {
    position: relative;
    z-index: 10;
    max-width: 1320px;
    margin: 0 auto;
    text-align: center;
    padding: 6rem 2rem 4rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* === TYPOGRAPHY === */
.hero-title {
    margin: 0 auto;
    max-width: 90%;
    color: #ffffff;
    font-size: clamp(2.8rem, 6vw, 5rem);
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -0.03em;
    text-align: center;
    display: block;
    word-break: break-word;
}

.hero-title span {
    display: block;
    color: #ff7a1a;
    font-size: 0.58em;
    margin-top: 0.6rem;
    font-weight: 900;
    letter-spacing: -0.02em;
}

.hero-text {
    margin: 1.2rem auto 2.5rem;
    max-width: 720px;
    color: rgba(255,255,255,0.75);
    font-size: clamp(1rem, 2.2vw, 1.3rem);
    font-weight: 500;
    line-height: 1.8;
    letter-spacing: -0.01em;
    text-align: center;
}

/* === SEARCH BAR (HERO CENTERPIECE) === */
.premium-search {
    display: grid;
    grid-template-columns: 1.5fr 0.95fr 0.95fr 0.85fr;
    gap: 0.75rem;
    max-width: 1050px;
    margin: 0 auto;
    padding: 0.9rem;
    border-radius: 24px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    backdrop-filter: blur(24px);
    box-shadow:
        0 32px 96px rgba(0,0,0,0.35),
        inset 0 1px 2px rgba(255,255,255,0.08);
    transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.premium-search:focus-within {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.25);
    box-shadow:
        0 40px 120px rgba(0,0,0,0.4),
        inset 0 1px 2px rgba(255,255,255,0.12);
}

.premium-field {
    height: 72px;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0 1.2rem;
    border-radius: 18px;
    background: #ffffff;
    transition: 0.2s ease;
}

.premium-field:focus-within {
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgba(241,98,15,0.18);
}

.field-svg {
    width: 22px;
    height: 22px;
    color: #ff7a1a;
    flex-shrink: 0;
    transition: 0.2s ease;
}

.premium-field:focus-within .field-svg {
    color: #ff7a1a;
}

.premium-field input,
.premium-field select {
    width: 100%;
    border: 0;
    outline: none;
    background: transparent;
    color: #0f172a;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 700;
}

.premium-field input::placeholder {
    color: #cbd5e1;
    font-weight: 500;
}

.premium-field select {
    appearance: none;
    cursor: pointer;
}

.premium-search-btn {
    height: 72px;
    padding: 0 2.4rem;
    border: 0;
    border-radius: 18px;
    background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
    color: #ffffff;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 900;
    cursor: pointer;
    transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 20px 48px rgba(255,107,26,0.32);
}

.premium-search-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 28px 64px rgba(255,107,26,0.42);
}

.premium-search-btn:active {
    transform: translateY(-1px);
}

/* === STATS SECTION === */
.hero-stats {
    display: flex;
    justify-content: center;
    gap: 4rem;
    margin-top: 3rem;
    padding-top: 3rem;
    border-top: 1px solid rgba(255,255,255,0.12);
}

.hero-stat {
    min-width: 160px;
}

.hero-stat strong {
    display: block;
    color: #ff8533;
    font-size: 2.8rem;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 0.4rem;
    letter-spacing: -0.02em;
}

.hero-stat span {
    color: rgba(255,255,255,0.64);
    font-weight: 600;
    font-size: 0.95rem;
    letter-spacing: 0.01em;
}

/* === RESPONSIVE === */
@media (max-width: 1024px) {
    .premium-search {
        grid-template-columns: 1.2fr 0.9fr 0.9fr 0.8fr;
        max-width: 90%;
        gap: 0.6rem;
        padding: 0.75rem;
    }

    .hero-inner {
        padding: 5rem 1.5rem 3rem;
    }

    .hero-stats {
        gap: 2.5rem;
    }
}

@media (max-width: 768px) {
    .premium-search {
        grid-template-columns: 1fr 0.9fr;
        gap: 0.5rem;
    }

    .premium-search-btn {
        grid-column: 1 / -1;
    }

    .hero-stats {
        gap: 2rem;
        flex-wrap: wrap;
    }

    .hero-text {
        margin: 1rem auto 2rem;
    }
}

@media (max-width: 640px) {
    .home-hero {
        min-height: auto;
        padding: 3rem 0;
    }

    .hero-inner {
        padding: 4rem 1rem 2.5rem;
    }

    .hero-title {
        font-size: 2.2rem;
        line-height: 1;
    }

    .hero-title span {
        font-size: 1.3rem;
        margin-top: 0.4rem;
    }

    .hero-text {
        font-size: 1rem;
        margin: 0.8rem auto 1.5rem;
    }

    .premium-search {
        grid-template-columns: 1fr;
        gap: 0.4rem;
        padding: 0.6rem;
        max-width: 100%;
    }

    .premium-field,
    .premium-search-btn {
        height: 64px;
    }

    .premium-field {
        padding: 0 1rem;
    }

    .hero-stats {
        gap: 1.5rem;
        margin-top: 2rem;
        padding-top: 2rem;
    }

    .hero-stat strong {
        font-size: 2rem;
    }
}

</style>

<!-- Category Navigation -->
<x-category-nav :categories="$categories" />

<!-- City Navigation -->
<x-city-nav :cities="$cities" />

<!-- Featured Providers -->
@if($featuredProviders->count() > 0)
    <section class="home-section">
        <div class="container">
            <x-provider-grid
                :providers="$featuredProviders"
                :columns="4"
                title="{{ __('messages.public.featured_professionals') }}"
                subtitle="{{ __('messages.public.top_professionals_in_your_area') }}"
            />
        </div>
    </section>
@endif

<!-- Top Rated Providers -->
@if($topRatedProviders->count() > 0)
    <section class="home-section">
        <div class="container">
            <x-provider-grid
                :providers="$topRatedProviders"
                :columns="4"
                title="{{ __('messages.public.highest_rated') }}"
                subtitle="{{ __('messages.public.trusted_professionals') }}"
            />
        </div>
    </section>
@endif

<!-- Latest Providers -->
@if($latestProviders->count() > 0)
    <section class="home-section">
        <div class="container">
            <x-provider-grid
                :providers="$latestProviders"
                :columns="4"
                title="{{ __('messages.public.newest_professionals') }}"
                subtitle="{{ __('messages.public.recently_joined') }}"
            />
        </div>
    </section>
@endif

<style>
    .home-section {
        padding: 3rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .home-section:last-of-type {
        border-bottom: none;
    }
</style>

@endsection
