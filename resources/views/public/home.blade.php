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
            <div class="hero-badge">
                <span>✨</span>
                <span>{{ __('messages.public.best_services_platform') }}</span>
            </div>

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

.home-hero {
    position: relative;
    overflow: hidden;

    min-height: 92vh;

    display: flex;
    align-items: center;

    background:
        radial-gradient(circle at top right, rgba(241,98,15,0.18), transparent 28%),
        radial-gradient(circle at bottom left, rgba(67,97,238,0.16), transparent 32%),
        linear-gradient(
            135deg,
            #07142b 0%,
            #0d2248 48%,
            #050b18 100%
        );

    padding: 3rem 0;
}

.hero-gradient {
    position: absolute;
    border-radius: 999px;
    filter: blur(100px);
    opacity: 0.08;
}

.hero-gradient-1 {
    width: 420px;
    height: 420px;

    background: #f1620f;

    top: -180px;
    right: -80px;
}

.hero-gradient-2 {
    width: 380px;
    height: 380px;

    background: #3459b8;

    bottom: -180px;
    left: -120px;
}

.hero-grid {
    position: absolute;
    inset: 0;

    opacity: 0.05;

    background-image:
        linear-gradient(rgba(255,255,255,0.14) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.14) 1px, transparent 1px);

    background-size: 64px 64px;
}

.hero-inner {
    position: relative;
    z-index: 5;

    max-width: 1300px;

    margin: 0 auto;

    text-align: center;

    padding: 7rem 1rem 3rem;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;

    padding: 0.7rem 1.2rem;

    border-radius: 999px;

    background: rgba(255,255,255,0.08);

    border: 1px solid rgba(255,255,255,0.12);

    color: #ff7a1a;

    backdrop-filter: blur(16px);

    margin-bottom: 1rem;

    font-size: 0.95rem;
    font-weight: 800;
}

.hero-title {
    margin: 0;

    color: #ffffff;

    font-size: clamp(2.5rem, 5vw, 4.5rem);

    font-weight: 900;

    line-height: 0.95;

    letter-spacing: -0.04em;
}

.hero-title span {
    display: block;

    color: #ff6b1a;

    font-size: 0.62em;

    margin-top: 0.1rem;
}

.hero-text {
    margin:
        1rem auto
        1.5rem;

    max-width: 700px;

    color: rgba(255,255,255,0.72);

    font-size: 1.25rem;
    font-weight: 600;

    line-height: 1.8;
}

.premium-search {
    display: grid;

    grid-template-columns:
        1.4fr
        0.9fr
        0.9fr
        auto;

    gap: 0.85rem;

    max-width: 1000px;

    margin: 0 auto;

    padding: 0.85rem;

    border-radius: 22px;

    background: rgba(255,255,255,0.10);

    border: 1px solid rgba(255,255,255,0.12);

    backdrop-filter: blur(20px);

    box-shadow:
        0 28px 80px rgba(0,0,0,0.25);
}

.premium-field {
    height: 68px;

    display: flex;
    align-items: center;
    gap: 0.75rem;

    padding: 0 1.1rem;

    border-radius: 20px;

    background: rgba(255,255,255,0.96);

    transition: 0.2s ease;
}

.premium-field:focus-within {
    transform: translateY(-1px);

    box-shadow:
        0 14px 32px rgba(241,98,15,0.12);
}

.field-svg {
    width: 20px;
    height: 20px;

    color: #9aa3b2;

    flex-shrink: 0;
}

.premium-field input,
.premium-field select {
    width: 100%;

    border: 0;
    outline: none;

    background: transparent;

    color: #142033;

    font-family: inherit;

    font-size: 1rem;
    font-weight: 700;
}

.premium-field input::placeholder {
    color: #9aa3b2;
}

.premium-field select {
    appearance: none;
    cursor: pointer;
}

.premium-search-btn {
    height: 68px;

    padding: 0 2.2rem;

    border: 0;

    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #ff7a1a,
            #f1620f
        );

    color: #ffffff;

    font-family: inherit;

    font-size: 1rem;
    font-weight: 900;

    cursor: pointer;

    transition: 0.25s ease;

    box-shadow:
        0 18px 40px rgba(241,98,15,0.28);
}

.premium-search-btn:hover {
    transform: translateY(-2px);

    box-shadow:
        0 24px 48px rgba(241,98,15,0.42);
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;

    margin-top: 2rem;
}

.hero-stat {
    min-width: 140px;
}

.hero-stat strong {
    display: block;

    color: #ff7a1a;

    font-size: 2.4rem;
    font-weight: 900;
}

.hero-stat span {
    color: rgba(255,255,255,0.66);

    font-weight: 700;
}

@media (max-width: 992px) {

    .premium-search {
        grid-template-columns: 1fr;
    }

    .premium-search-btn {
        width: 100%;
    }

    .hero-stats {
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .hero-logo {
        top: 1rem;
        right: 1rem;
    }

    .hero-logo img {
        width: 58px;
        height: 58px;
    }
}

@media (max-width: 640px) {

    .home-hero {
        min-height: auto;
    }

    .hero-inner {
        padding:
            5rem 1rem
            3rem;
    }

    .hero-title {
        font-size: 3rem;
        line-height: 1.15;
    }

    .hero-text {
        font-size: 1rem;
    }

    .premium-search {
        padding: 0.75rem;
        border-radius: 24px;
    }

    .premium-field,
    .premium-search-btn {
        height: 60px;
    }

    .hero-stat strong {
        font-size: 2rem;
    }
}

</style>

@endsection
