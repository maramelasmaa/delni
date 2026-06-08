@extends('public.layout')

@section('title', $category->localized_name . ' - ' . config('app.name'))

@section('content')

<!-- Breadcrumb -->
<div class="container pt-3">
    <nav aria-label="breadcrumb" class="breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-primary-500">{{ __('messages.public.home') }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-600">{{ $category->localized_name }}</span>
    </nav>
</div>

<!-- Hero Section -->
<section class="bg-navy-800 text-white section-compact">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2">
                <h1 class="text-4xl font-black mb-4">
                    {{ $category->localized_name }}
                </h1>
                @if($category->description)
                    <p class="text-lg text-white/75 mb-3">{{ $category->description }}</p>
                @endif
                <p class="text-white/70">
                    {{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}
                </p>
            </div>
            <div class="flex items-center justify-center h-32 text-white/80">
                <x-render-icon :icon="$category->icon ?: 'heroicon-o-briefcase'" class="w-24 h-24" />
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <div class="search-filters">
                    <form method="GET" class="space-y-4">
                        @if(isset($cities))
                            <div>
                                <label for="city_id" class="form-label">{{ __('messages.public.city') }}</label>
                                <select id="city_id" name="city_id" class="form-select">
                                    <option value="">{{ __('messages.public.all_cities') }}</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" @selected(request('city_id') == $city->id)>
                                            {{ $city->localized_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label for="sort" class="form-label">{{ __('messages.public.sort_by') }}</label>
                            <select id="sort" name="sort" class="form-select">
                                <option value="" @selected(!request('sort'))>{{ __('messages.public.relevance') }}</option>
                                <option value="rating" @selected(request('sort') === 'rating')>{{ __('messages.public.highest_rated') }}</option>
                                <option value="reviews" @selected(request('sort') === 'reviews')>{{ __('messages.public.most_reviewed') }}</option>
                                <option value="newest" @selected(request('sort') === 'newest')>{{ __('messages.public.newest') }}</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-full">
                            <x-render-icon icon="heroicon-o-funnel" class="w-4 h-4 inline-block mr-2" />
                            {{ __('messages.public.filter') }}
                        </button>
                    </form>
                </div>

                @if(request()->anyFilled(['city_id', 'sort']))
                    <div class="mt-4">
                        <a href="{{ route('public.category', $category->slug) }}" class="btn btn-outline btn-sm w-full">
                            <x-render-icon icon="heroicon-o-arrow-path" class="w-4 h-4 inline-block mr-2" />
                            {{ __('messages.public.clear_filters') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Results Section -->
        <div class="lg:col-span-3">
            @if($profiles && $profiles->count() > 0)
                <x-provider-grid :providers="$profiles" :columns="1" />

                @if($profiles->hasPages())
                    <nav aria-label="Page navigation" class="mt-8">
                        {{ $profiles->links('pagination::tailwind') }}
                    </nav>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <x-render-icon icon="heroicon-o-magnifying-glass" class="w-16 h-16" />
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-2">{{ __('messages.public.no_providers_found') }}</h5>
                    <p class="text-gray-600 mb-6">
                        {{ __('messages.public.no_providers_in_category') }}
                    </p>
                    <a href="{{ route('public.search') }}" class="btn btn-primary">
                        {{ __('messages.public.browse_all') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>

@endsection


