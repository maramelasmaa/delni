@extends('public.layout')

@section('title', __('messages.public.home') . ' - ' . config('app.name'))

@section('content')
@php
    $categories = $categories ?? collect();
    $cities = $cities ?? collect();
    $topProviders = $topRatedProviders ?? $featuredProviders ?? collect();
    $latestProviders = $latestProviders ?? collect();
    $contact = \App\Models\ContactInfo::instance();

    $professionalsCount = $categories->sum(fn ($category) => (int) ($category->discoverable_profiles_count ?? 0));
    $whatsappNumber = $contact?->whatsapp ? preg_replace('/[^0-9]/', '', $contact->whatsapp) : null;
@endphp

<!-- Hero Section -->
<section class="home-hero">
    <div class="hero-decorative-blob hero-blob-1"></div>
    <div class="hero-decorative-blob hero-blob-2"></div>
    <div class="hero-decorative-grid"></div>

    <div class="container relative z-10">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-icon">✨</span>
                <span>{{ __('messages.public.best_services_platform') }}</span>
            </div>

            <h1 class="hero-headline">
                {{ __('messages.public.find_trusted_professionals') }}
                <span class="hero-highlight">{{ __('messages.public.in_libya') }}</span>
            </h1>

            <p class="hero-description">
                {{ __('messages.public.browse_local_professionals') }}
            </p>

            <form action="{{ route('public.search') }}" method="GET" class="hero-search-form">
                <div class="search-field search-field-main">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

                <div class="search-field">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>

                    <select name="city_id">
                        <option value="">{{ __('messages.public.all_cities') }}</option>
                        @foreach($cities->take(15) as $city)
                            <option value="{{ $city->id }}">
                                {{ $city->localized_name ?? $city->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="search-field">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M2 12h20"></path>
                    </svg>

                    <select name="category_id">
                        <option value="">{{ __('messages.public.all_categories') }}</option>
                        @foreach($categories->take(15) as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->localized_name ?? $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="search-button">
                    <span>{{ __('messages.public.search') }}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"></path>
                    </svg>
                </button>
            </form>

            <div class="hero-trust">
                <div class="trust-item">
                    <span class="trust-number">{{ $professionalsCount }}+</span>
                    <span class="trust-label">{{ __('messages.public.professionals') }}</span>
                </div>

                <div class="trust-divider"></div>

                <div class="trust-item">
                    <span class="trust-number">{{ $cities->count() }}+</span>
                    <span class="trust-label">{{ __('messages.public.cities') }}</span>
                </div>

                <div class="trust-divider"></div>

                <div class="trust-item">
                    <span class="trust-number">{{ $categories->count() }}+</span>
                    <span class="trust-label">{{ __('messages.public.categories') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

@if($categories->count() > 0)
    <section class="home-section bg-white">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">{{ __('messages.public.browse_categories') }}</h2>
                    <p class="section-subtitle">{{ __('messages.public.explore_our_categories') }}</p>
                </div>

                <a href="{{ route('public.search') }}" class="btn btn-outline-primary btn-sm whitespace-nowrap">
                    {{ __('messages.public.view_all') }}
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($categories->take(8) as $category)
                    <a href="{{ route('public.category', $category->slug) }}" class="category-tile">
                        <span class="category-icon flex-shrink-0">
                            <x-render-icon :icon="$category->icon" class="w-6 h-6 text-primary-500" />
                        </span>

                        <span class="category-info flex-1">
                            <strong class="block text-gray-900">{{ $category->localized_name ?? $category->name }}</strong>
                            <small class="block text-gray-600">
                                {{ $category->discoverable_profiles_count ?? 0 }}
                                {{ __('messages.public.professionals') }}
                            </small>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($cities->count() > 0)
    <section class="home-section bg-gray-50">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">{{ __('messages.public.browse_cities') }}</h2>
                    <p class="section-subtitle">{{ __('messages.public.find_service_by_location') }}</p>
                </div>

                <a href="{{ route('public.search') }}" class="btn btn-outline-primary btn-sm whitespace-nowrap">
                    {{ __('messages.public.view_all') }}
                </a>
            </div>

            <div class="city-strip">
                @foreach($cities->take(10) as $city)
                    <a href="{{ route('public.city', $city->slug) }}" class="city-pill">
                        <span class="city-dot"></span>
                        <strong>{{ $city->localized_name ?? $city->name }}</strong>
                        <small>{{ $city->discoverable_profiles_count ?? 0 }}</small>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($topProviders->count() > 0)
    <section class="home-section bg-white">
        <div class="container">
            <x-provider-grid
                :providers="$topProviders"
                :columns="3"
                :title="__('messages.public.top_rated_providers')"
                :subtitle="__('messages.public.most_trusted_professionals')"
            />

            <div class="text-center mt-8">
                <a href="{{ route('public.search', ['sort' => 'rating']) }}" class="btn btn-primary">
                    {{ __('messages.public.view_all_providers') }}
                </a>
            </div>
        </div>
    </section>
@endif

@if($latestProviders->count() > 0)
    <section class="home-section bg-gray-50">
        <div class="container">
            <x-provider-grid
                :providers="$latestProviders"
                :columns="3"
                :title="__('messages.public.latest_providers')"
                :subtitle="__('messages.public.recently_joined')"
            />
        </div>
    </section>
@endif

<section class="section bg-white">
    <div class="container">
        <div class="home-cta-box">
            <div class="flex-1">
                <span class="hero-kicker">
                    {{ __('messages.public.become_provider') }}
                </span>

                <h2 class="text-3xl font-black text-white mb-3">
                    {{ __('messages.public.are_you_professional') }}
                </h2>

                <p class="text-white/70">
                    {{ __('messages.public.join_marketplace_description') }}
                </p>
            </div>

            @if($whatsappNumber)
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary flex-shrink-0">
                    {{ __('messages.public.become_provider') }}
                </a>
            @endif
        </div>
    </div>
</section>

<section class="section-compact bg-navy-800 text-white">
    <div class="container">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div>
                <strong class="block text-3xl font-black text-orange-400 mb-2">{{ $professionalsCount }}+</strong>
                <span class="text-white/70 font-bold">{{ __('messages.public.professionals') }}</span>
            </div>

            <div>
                <strong class="block text-3xl font-black text-orange-400 mb-2">{{ $cities->count() }}+</strong>
                <span class="text-white/70 font-bold">{{ __('messages.public.cities') }}</span>
            </div>

            <div>
                <strong class="block text-3xl font-black text-orange-400 mb-2">{{ $categories->count() }}+</strong>
                <span class="text-white/70 font-bold">{{ __('messages.public.categories') }}</span>
            </div>
        </div>
    </div>
</section>

<style>
    .home-hero {
        position: relative;
        overflow: hidden;
        min-height: calc(100vh - 90px);
        padding-top: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-image: url('/images/herobackground.png');
        background-size: cover;
        background-position: center;
        background-attachment: scroll;
    }

    .home-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(11, 26, 52, 0.7) 0%, rgba(26, 47, 94, 0.7) 50%, rgba(15, 30, 61, 0.7) 100%);
        pointer-events: none;
        z-index: 1;
    }

    .hero-decorative-blob {
        position: absolute;
        border-radius: 9999px;
        opacity: 0.1;
        filter: blur(80px);
        animation: blob-drift 15s infinite ease-in-out;
        z-index: 2;
    }

    .hero-blob-1 {
        width: 24rem;
        height: 24rem;
        background: #f1620f;
        top: -50px;
        right: -100px;
    }

    .hero-blob-2 {
        width: 24rem;
        height: 24rem;
        background: #ff8533;
        bottom: -50px;
        left: -100px;
        animation-delay: 7.5s;
        animation-direction: reverse;
    }

    @keyframes blob-drift {
        0%, 100% {
            transform: translate(0, 0);
        }

        50% {
            transform: translate(30px, -30px);
        }
    }

    .hero-decorative-grid {
        position: absolute;
        inset: 0;
        opacity: 0.05;
        background-image:
            linear-gradient(rgba(241, 98, 15, 0.2) 1px, transparent 1px),
            linear-gradient(90deg, rgba(241, 98, 15, 0.2) 1px, transparent 1px);
        background-size: 50px 50px;
        z-index: 2;
    }

    .hero-content {
        text-align: center;
        color: #fff;
        max-width: 56rem;
        margin-left: auto;
        margin-right: auto;
        padding-left: 1rem;
        padding-right: 1rem;
        position: relative;
        z-index: 10;
        animation: fade-up 0.8s ease-out;
    }

    @keyframes fade-up {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #f1620f;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        animation: fade-up 0.8s ease-out 0.1s both;
    }

    .badge-icon {
        font-size: 1.125rem;
        line-height: 1;
    }

    .hero-headline {
        font-size: 3rem;
        line-height: 1.1;
        font-weight: 900;
        margin-bottom: 1.5rem;
        animation: fade-up 0.8s ease-out 0.2s both;
    }

    .hero-highlight {
        display: block;
        color: #f1620f;
        background: linear-gradient(135deg, #f1620f, #ff8533);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-description {
        font-size: 1.125rem;
        color: rgba(255, 255, 255, 0.7);
        max-width: 42rem;
        margin: 0 auto 2.5rem;
        animation: fade-up 0.8s ease-out 0.3s both;
    }

    .hero-search-form {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin: 0 auto 3rem;
        max-width: 56rem;
        animation: fade-up 0.8s ease-out 0.4s both;
    }

    .search-field {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 0.75rem;
        backdrop-filter: blur(8px);
        transition: all 0.3s ease;
        flex: 1;
    }

    .search-field-main {
        flex: 1.5;
    }

    .search-field:focus-within {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        outline: 2px solid #f1620f;
        outline-offset: 0;
        transform: translateY(-2px);
    }

    .search-icon {
        width: 1.25rem;
        height: 1.25rem;
        color: #9ca3af;
        flex-shrink: 0;
    }

    .search-field input,
    .search-field select {
        flex: 1;
        background: transparent;
        border: 0;
        outline: none;
        color: #1f2937;
        font-size: 0.875rem;
        font-family: inherit;
        min-width: 0;
    }

    .search-field input::placeholder {
        color: #9ca3af;
    }

    .search-field select {
        appearance: none;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239CA3AF' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        padding-right: 1.75rem;
    }

    [dir="rtl"] .search-field select {
        background-position: left 0.5rem center;
        padding-right: 0;
        padding-left: 1.75rem;
    }

    .search-button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 2rem;
        background: linear-gradient(to right, #f1620f, #d9540d);
        color: #fff;
        font-weight: 700;
        border-radius: 0.75rem;
        white-space: nowrap;
        border: 0;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(241, 98, 15, 0.3);
    }

    .search-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(241, 98, 15, 0.4);
    }

    .search-button svg {
        width: 1rem;
        height: 1rem;
    }

    .hero-trust {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
        animation: fade-up 0.8s ease-out 0.5s both;
    }

    .trust-item {
        text-align: center;
    }

    .trust-number {
        display: block;
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        color: #f1620f;
        margin-bottom: 0.25rem;
    }

    .trust-label {
        display: block;
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.875rem;
        font-weight: 600;
    }

    .trust-divider {
        width: 1px;
        height: 3rem;
        background: rgba(255, 255, 255, 0.2);
    }

    .home-cta-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        padding: 2rem;
        border-radius: 1rem;
        color: #fff;
        background:
            radial-gradient(circle at top right, rgba(241, 98, 15, 0.2), transparent 35%),
            linear-gradient(135deg, rgb(11, 26, 52), rgb(17, 34, 64));
        box-shadow: 0 20px 48px rgba(11, 26, 52, 0.18);
    }

    .home-stats strong {
        display: block;
        font-weight: 900;
    }

    .home-stats span {
        font-weight: 700;
    }

    @media (min-width: 1024px) {
        .hero-search-form {
            flex-direction: row;
        }

        .hero-headline {
            font-size: 3.75rem;
        }

        .trust-number {
            font-size: 2.25rem;
        }
    }

    @media (max-width: 1024px) {
        .home-hero {
            min-height: 60vh;
        }

        .hero-headline {
            font-size: 2.25rem;
        }

        .search-button {
            width: 100%;
        }
    }

    @media (max-width: 992px) {
        .section-head,
        .section-header.with-action,
        .home-cta-box {
            flex-direction: column;
            align-items: stretch;
        }
    }

    @media (max-width: 640px) {
        .home-hero {
            min-height: 50vh;
            padding: 2rem 0;
        }

        .hero-headline {
            font-size: 1.875rem;
        }

        .hero-description {
            font-size: 1rem;
        }

        .hero-badge {
            font-size: 0.75rem;
        }

        .search-field {
            padding: 0.625rem 0.75rem;
        }

        .search-icon {
            width: 1rem;
            height: 1rem;
        }

        .trust-divider {
            display: none;
        }

        .hero-trust {
            gap: 1rem;
        }

        .trust-number {
            font-size: 1.5rem;
        }

        .trust-label {
            font-size: 0.75rem;
        }

        .home-cta-box {
            padding: 1.5rem;
        }
    }
</style>

@endsection
