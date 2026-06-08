@extends('public.layout')

@section('title', __('messages.public.home') . ' - ' . config('app.name'))

@section('content')
@php
    $categories = $categories ?? collect();
    $cities = $cities ?? collect();
    $topProviders = $topRatedProviders ?? $featuredProviders ?? collect();
    $latestProviders = $latestProviders ?? collect();
    $contact = \App\Models\ContactInfo::instance();
@endphp

<!-- Hero Section -->
<section class="home-hero">
    <div class="container">
        <div class="w-full lg:w-1/2 xl:w-3/5 relative z-10">
            <span class="hero-kicker">
                {{ __('messages.public.best_services_platform') }}
            </span>

            <h1 class="hero-title">
                {{ __('messages.public.find_trusted_professionals') }}
                <span class="text-primary-500">{{ __('messages.public.in_libya') }}</span>
            </h1>

            <p class="hero-subtitle">
                {{ __('messages.public.browse_local_professionals') }}
            </p>

            <form action="{{ route('public.search') }}" method="GET" class="hero-search">
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    placeholder="{{ __('messages.public.search_placeholder') }}"
                    value="{{ request('keyword') }}"
                    maxlength="100"
                    autocomplete="off"
                >

                <select name="city_id" class="form-select">
                    <option value="">ابحث في جميع المدن والمناطق</option>
                    @foreach($cities->take(10) as $city)
                        <option value="{{ $city->id }}">
                            {{ $city->localized_name ?? $city->name }}
                        </option>
                    @endforeach
                </select>

                <select name="category_id" class="form-select">
                    <option value="">اختر من جميع الأقسام والتخصصات</option>
                    @foreach($categories->take(10) as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->localized_name ?? $category->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    {{ __('messages.public.search') }}
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Categories Section -->
@if($categories->count() > 0)
    <section class="home-section bg-white">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">
                        {{ __('messages.public.browse_categories') }}
                    </h2>
                    <p class="section-subtitle">
                        {{ __('messages.public.explore_our_categories') }}
                    </p>
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

<!-- Cities Section -->
@if($cities->count() > 0)
    <section class="home-section bg-gray-50">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">
                        {{ __('messages.public.browse_cities') }}
                    </h2>
                    <p class="section-subtitle">
                        {{ __('messages.public.find_service_by_location') }}
                    </p>
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

<!-- Top Providers Section -->
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

<!-- Latest Providers Section -->
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

<!-- CTA Section -->
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

            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp) }}" target="_blank" class="btn btn-primary flex-shrink-0">
                {{ __('messages.public.become_provider') }}
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section-compact bg-navy-800 text-white">
    <div class="container">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div>
                <strong class="block text-3xl font-black text-orange-400 mb-2">{{ $categories->sum('discoverable_profiles_count') ?? 0 }}+</strong>
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
        @apply relative bg-cover bg-center bg-no-repeat overflow-hidden;
        min-height: 55vh;
        display: flex;
        align-items: center;
        background-image:
            linear-gradient(
                90deg,
                rgba(11, 26, 52, 0.02) 0%,
                rgba(11, 26, 52, 0.10) 30%,
                rgba(11, 26, 52, 0.38) 58%,
                rgba(11, 26, 52, 0.72) 100%
            ),
            url('{{ asset('images/herobackground.png') }}');
    }

    .home-hero .container {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .hero-search {
        @apply relative z-10 grid gap-2 max-w-2xl;
        grid-template-columns: 1.6fr 1.2fr 1.2fr 110px;
    }

    .hero-search .form-control,
    .hero-search .form-select {
        @apply min-h-12 border-0 rounded-md bg-white/96 text-sm;
    }

    .hero-search .btn {
        @apply min-h-12 rounded-md font-black text-sm;
    }

    .home-cta-box {
        @apply flex items-center justify-between gap-6 p-8 rounded-2xl text-white;
        background:
            radial-gradient(circle at top right, rgba(241, 98, 15, 0.2), transparent 35%),
            linear-gradient(135deg, rgb(11, 26, 52), rgb(17, 34, 64));
        box-shadow: 0 20px 48px rgba(11, 26, 52, 0.18);
    }

    .home-stats strong {
        @apply block font-black;
    }

    .home-stats span {
        @apply font-bold;
    }

    @media (max-width: 992px) {
        .home-hero {
            min-height: auto;
            padding-block: 2.5rem;
        }

        .hero-search {
            grid-template-columns: 1fr;
            max-width: 100%;
        }

        .section-head,
        .section-header.with-action,
        .home-cta-box {
            flex-direction: column;
            align-items: stretch;
        }
    }

    @media (max-width: 640px) {
        .hero-title {
            font-size: 1.75rem;
        }

        .hero-subtitle {
            font-size: 1rem;
        }

        .home-cta-box {
            padding: 1.5rem;
        }
    }
</style>

@endsection
