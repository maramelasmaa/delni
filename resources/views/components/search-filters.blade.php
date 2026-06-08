@props([
    'categories' => null,
    'cities' => null,
    'providerTypes' => null,
])

@php
    $hasFilters = request()->filled('keyword')
        || request()->filled('category_id')
        || request()->filled('city_id')
        || request()->filled('provider_type')
        || request()->filled('remote')
        || request()->filled('sort');
@endphp

<div class="search-filters search-filters-panel">
    <form method="GET" action="{{ route('public.search') }}">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h3 class="search-filters-title mb-1">
                    {{ __('messages.public.search_filters') }}
                </h3>
                <p class="search-filters-subtitle mb-0">
                    {{ __('messages.public.search_filters_hint') }}
                </p>
            </div>

            @if($hasFilters)
                <a href="{{ route('public.search') }}" class="search-filters-clear">
                    {{ __('messages.public.clear') }}
                </a>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-12">
                <label for="keyword" class="form-label">
                    {{ __('messages.public.search_keyword') }}
                </label>

                <div class="search-input-wrap">
                    <span class="search-input-icon">🔍</span>
                    <input
                        type="text"
                        id="keyword"
                        name="keyword"
                        class="form-control search-input"
                        placeholder="{{ __('messages.public.search_placeholder') }}"
                        value="{{ request('keyword') }}"
                        maxlength="100"
                    >
                </div>
            </div>

            @if($categories)
                <div class="col-md-6 col-lg-3">
                    <label for="category_id" class="form-label">
                        {{ __('messages.public.category') }}
                    </label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">{{ __('messages.public.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                {{ $category->localized_name ?? $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($cities)
                <div class="col-md-6 col-lg-3">
                    <label for="city_id" class="form-label">
                        {{ __('messages.public.city') }}
                    </label>
                    <select id="city_id" name="city_id" class="form-select">
                        <option value="">{{ __('messages.public.all_cities') }}</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                                {{ $city->localized_name ?? $city->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($providerTypes)
                <div class="col-md-6 col-lg-3">
                    <label for="provider_type" class="form-label">
                        {{ __('messages.public.provider_type') }}
                    </label>
                    <select id="provider_type" name="provider_type" class="form-select">
                        <option value="">{{ __('messages.public.all_types') }}</option>
                        @foreach($providerTypes as $code => $name)
                            <option value="{{ $code }}" @selected((string) request('provider_type') === (string) $code)>
                                {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-12">
                <div class="form-check">
                    <input
                        type="checkbox"
                        id="remote"
                        name="remote"
                        class="form-check-input"
                        value="1"
                        @checked(request('remote') == 1)
                    >
                    <label class="form-check-label" for="remote">
                        <x-render-icon icon="heroicon-o-globe-alt" class="w-4 h-4 inline-block me-1" />
                        {{ __('messages.public.remote_work') }}
                    </label>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <label for="sort" class="form-label">
                    {{ __('messages.public.sort_by') }}
                </label>
                <select id="sort" name="sort" class="form-select">
                    <option value="" @selected(!request('sort'))>
                        {{ __('messages.public.relevance') }}
                    </option>
                    <option value="rating" @selected(request('sort') === 'rating')>
                        {{ __('messages.public.highest_rated') }}
                    </option>
                    <option value="reviews" @selected(request('sort') === 'reviews')>
                        {{ __('messages.public.most_reviewed') }}
                    </option>
                    <option value="newest" @selected(request('sort') === 'newest')>
                        {{ __('messages.public.newest') }}
                    </option>
                </select>
            </div>

            <div class="col-12">
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-2">
                    @if($hasFilters)
                        <a href="{{ route('public.search') }}" class="btn btn-outline-primary">
                            {{ __('messages.public.clear') }}
                        </a>
                    @endif

                    <button type="submit" class="btn btn-primary">
                        {{ __('messages.public.search') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@once
    @push('styles')
        <style>
            .search-filters-panel {
                background:
                    radial-gradient(circle at top right, rgba(241, 98, 15, 0.07), transparent 28%),
                    #fff;
                border: 1px solid #F1F1F1;
                border-radius: 28px;
                padding: 1.4rem;
                box-shadow: 0 12px 34px rgba(11, 26, 52, 0.07);
            }

            .search-filters-title {
                font-size: 1.15rem;
                font-weight: 800;
                color: #0B1A34;
            }

            .search-filters-subtitle {
                color: #6B7280;
                font-size: 0.9rem;
            }

            .search-filters-clear {
                color: #F1620F;
                text-decoration: none;
                font-weight: 800;
                font-size: 0.9rem;
                white-space: nowrap;
            }

            .search-filters-clear:hover {
                color: #D9550C;
            }

            .search-input-wrap {
                position: relative;
            }

            .search-input-icon {
                position: absolute;
                inset-inline-start: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: #9CA3AF;
                z-index: 2;
            }

            .search-input {
                padding-inline-start: 2.75rem;
            }

            html[dir="rtl"] .search-input {
                padding-right: 2.75rem;
                padding-left: 0.95rem;
            }

            html[dir="ltr"] .search-input {
                padding-left: 2.75rem;
                padding-right: 0.95rem;
            }

            @media (max-width: 575px) {
                .search-filters-panel {
                    border-radius: 22px;
                    padding: 1rem;
                }
            }
        </style>
    @endpush
@endonce
