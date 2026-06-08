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

<section class="home-hero">
    <div class="hero-bg-glow hero-glow-1"></div>
    <div class="hero-bg-glow hero-glow-2"></div>
    <div class="hero-grid"></div>

    <div class="container">
        <div class="hero-inner">

            <div class="hero-badge">
                <span>✨</span>
                <span>{{ __('messages.public.best_services_platform') }}</span>
            </div>

            <h1 class="hero-title">
                {{ __('messages.public.find_trusted_professionals') }}
                <span>{{ __('messages.public.in_libya') }}</span>
            </h1>

            <p class="hero-text">
                {{ __('messages.public.browse_local_professionals') }}
            </p>

            <form action="{{ route('public.search') }}" method="GET" class="premium-search">
                <div class="premium-field premium-keyword">
                    <span class="field-icon">⌕</span>
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
                    <span class="field-icon">⌖</span>
                    <select name="city_id">
                        <option value="">{{ __('messages.public.all_cities') }}</option>
                        @foreach($cities->take(15) as $city)
                            <option value="{{ $city->id }}">
                                {{ $city->localized_name ?? $city->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="premium-field">
                    <span class="field-icon">＋</span>
                    <select name="category_id">
                        <option value="">{{ __('messages.public.all_categories') }}</option>
                        @foreach($categories->take(15) as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->localized_name ?? $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="premium-search-btn">
                    {{ __('messages.public.search') }}
                </button>
            </form>

            <div class="hero-stats">
                <div>
                    <strong>{{ $professionalsCount ?? 0 }}+</strong>
                    <span>{{ __('messages.public.professionals') }}</span>
                </div>

                <div>
                    <strong>{{ $cities->count() }}+</strong>
                    <span>{{ __('messages.public.cities') }}</span>
                </div>

                <div>
                    <strong>{{ $categories->count() }}+</strong>
                    <span>{{ __('messages.public.categories') }}</span>
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
        min-height: calc(100vh - 72px);
        overflow: hidden;
        display: flex;
        align-items: center;
        background:
            radial-gradient(circle at 20% 20%, rgba(241, 98, 15, 0.18), transparent 28%),
            radial-gradient(circle at 80% 35%, rgba(56, 94, 170, 0.32), transparent 34%),
            linear-gradient(135deg, #07142b 0%, #10244a 48%, #090f22 100%);
    }

    .hero-inner {
        position: relative;
        z-index: 5;
        max-width: 1050px;
        margin: 0 auto;
        text-align: center;
        padding: 6rem 1rem;
    }

    .hero-bg-glow {
        position: absolute;
        width: 420px;
        height: 420px;
        border-radius: 999px;
        filter: blur(90px);
        opacity: 0.35;
    }

    .hero-glow-1 {
        background: #f1620f;
        top: -120px;
        right: 8%;
    }

    .hero-glow-2 {
        background: #3459b8;
        bottom: -140px;
        left: 8%;
    }

    .hero-grid {
        position: absolute;
        inset: 0;
        opacity: 0.06;
        background-image:
            linear-gradient(rgba(255,255,255,0.18) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.18) 1px, transparent 1px);
        background-size: 64px 64px;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        padding: 0.65rem 1.1rem;
        border-radius: 999px;
        color: #ff7a2a;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.14);
        backdrop-filter: blur(14px);
        font-weight: 700;
    }

    .hero-title {
        margin: 0;
        color: #fff;
        font-size: clamp(3rem, 7vw, 6.8rem);
        font-weight: 900;
        line-height: 1.08;
        letter-spacing: -0.04em;
    }

    .hero-title span {
        display: block;
        color: #ff6b1a;
    }

    .hero-text {
        margin: 1.5rem auto 2.6rem;
        max-width: 620px;
        color: rgba(255,255,255,0.68);
        font-size: 1.25rem;
        font-weight: 600;
    }

    .premium-search {
        display: grid;
        grid-template-columns: 1.4fr 0.9fr 0.9fr auto;
        gap: 0.75rem;
        max-width: 980px;
        margin: 0 auto;
        padding: 0.75rem;
        border-radius: 26px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.16);
        backdrop-filter: blur(18px);
        box-shadow: 0 24px 70px rgba(0,0,0,0.28);
    }

    .premium-field {
        height: 64px;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0 1rem;
        border-radius: 18px;
        background: rgba(255,255,255,0.94);
    }

    .field-icon {
        color: #9aa3b2;
        font-size: 1.4rem;
        line-height: 1;
    }

    .premium-field input,
    .premium-field select {
        width: 100%;
        border: 0;
        outline: none;
        background: transparent;
        color: #152033;
        font-family: inherit;
        font-size: 0.98rem;
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
        height: 64px;
        padding: 0 2rem;
        border: 0;
        border-radius: 18px;
        background: linear-gradient(135deg, #ff7a1a, #f1620f);
        color: #fff;
        font-family: inherit;
        font-size: 1rem;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 18px 38px rgba(241, 98, 15, 0.34);
        transition: 0.25s ease;
    }

    .premium-search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 48px rgba(241, 98, 15, 0.44);
    }

    .hero-stats {
        display: flex;
        justify-content: center;
        gap: 3rem;
        margin-top: 2.5rem;
        color: #fff;
    }

    .hero-stats div {
        min-width: 120px;
    }

    .hero-stats strong {
        display: block;
        color: #ff7a1a;
        font-size: 2.1rem;
        font-weight: 900;
    }

    .hero-stats span {
        color: rgba(255,255,255,0.64);
        font-weight: 700;
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

    @media (max-width: 991px) {
        .premium-search {
            grid-template-columns: 1fr;
        }

        .premium-search-btn {
            width: 100%;
        }

        .hero-stats {
            gap: 1.5rem;
            flex-wrap: wrap;
        }
    }

    @media (max-width: 640px) {
        .home-hero {
            min-height: auto;
        }

        .hero-inner {
            padding: 4rem 1rem;
        }

        .hero-title {
            font-size: 3rem;
        }

        .hero-text {
            font-size: 1rem;
        }

        .premium-field,
        .premium-search-btn {
            height: 58px;
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
</style>

@endsection
