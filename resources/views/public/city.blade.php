@extends('public.layout')

@section('title', $city->localized_name . ' - ' . config('app.name'))

@section('content')

<!-- Breadcrumb -->
<div class="container pt-3">
    <nav aria-label="breadcrumb" class="breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-primary-500">{{ __('messages.public.home') }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-600">{{ $city->localized_name }}</span>
    </nav>
</div>

<!-- Hero Section -->
<section class="bg-navy-800 text-white section-compact">
    <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2">
                <h1 class="text-4xl font-black mb-4">
                    {{ $city->localized_name }}
                </h1>
                @if($city->description)
                    <p class="text-lg text-white/75 mb-3">{{ $city->description }}</p>
                @endif
                <p class="text-white/70">
                    {{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}
                </p>
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
                        @if(isset($categories))
                            <div>
                                <label for="category_id" class="form-label">{{ __('messages.public.category') }}</label>
                                <select id="category_id" name="category_id" class="form-select">
                                    <option value="">{{ __('messages.public.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                            {{ $category->localized_name }}
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

                        <button type="submit" class="btn btn-primary btn-sm w-full flex items-center justify-center gap-2">
                            <x-render-icon icon="heroicon-o-funnel" class="w-4 h-4" />
                            {{ __('messages.public.filter') }}
                        </button>
                    </form>
                </div>

                @if(request()->anyFilled(['category_id', 'sort']))
                    <div class="mt-4">
                        <a href="{{ route('public.city', $city->slug) }}" class="btn btn-outline btn-sm w-full flex items-center justify-center gap-2">
                            <x-render-icon icon="heroicon-o-arrow-path" class="w-4 h-4" />
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
                <x-empty-state
                    icon="heroicon-o-magnifying-glass"
                    title="{{ __('messages.public.no_providers_found') }}"
                    message="{{ __('messages.public.no_providers_in_city') }}"
                    action-label="{{ __('messages.public.browse_all') }}"
                    action-url="{{ route('public.search') }}"
                />
            @endif
        </div>
    </div>
</section>

@endsection

