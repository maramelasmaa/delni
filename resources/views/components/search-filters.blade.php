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

<div class="search-filters">
    <form method="GET" action="{{ route('public.search') }}" class="filters-form">
        <div class="filters-header">
            <div>
                <h3 class="filters-title">
                    {{ __('messages.public.search_filters') }}
                </h3>
                <p class="filters-subtitle">
                    {{ __('messages.public.search_filters_hint') }}
                </p>
            </div>
        </div>

        <!-- Keyword -->
        <div class="filter-field">
            <label for="keyword" class="filter-label">
                {{ __('messages.public.search_keyword') }}
            </label>
            <input
                type="text"
                id="keyword"
                name="keyword"
                class="filter-input"
                placeholder="{{ __('messages.public.search_placeholder') }}"
                value="{{ request('keyword') }}"
                maxlength="100"
            >
        </div>

        <!-- Category -->
        @if($categories)
            <div class="filter-field">
                <label for="category_id" class="filter-label">
                    {{ __('messages.public.category') }}
                </label>
                <select id="category_id" name="category_id" class="filter-select">
                    <option value="">{{ __('messages.public.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                            {{ $category->localized_name ?? $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- City -->
        @if($cities)
            <div class="filter-field">
                <label for="city_id" class="filter-label">
                    {{ __('messages.public.city') }}
                </label>
                <select id="city_id" name="city_id" class="filter-select">
                    <option value="">{{ __('messages.public.all_cities') }}</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                            {{ $city->localized_name ?? $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Provider Type -->
        @if($providerTypes)
            <div class="filter-field">
                <label for="provider_type" class="filter-label">
                    {{ __('messages.public.provider_type') }}
                </label>
                <select id="provider_type" name="provider_type" class="filter-select">
                    <option value="">{{ __('messages.public.all_types') }}</option>
                    @foreach($providerTypes as $code => $name)
                        <option value="{{ $code }}" @selected((string) request('provider_type') === (string) $code)>
                            {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Remote Toggle -->
        <div class="filter-field filter-checkbox">
            <input
                type="checkbox"
                id="remote"
                name="remote"
                class="filter-checkbox-input"
                value="1"
                @checked(request('remote') == 1)
            >
            <label class="filter-checkbox-label" for="remote">
                <x-render-icon icon="heroicon-o-globe-alt" class="w-4 h-4 inline-block me-1" />
                {{ __('messages.public.remote_work') }}
            </label>
        </div>

        <!-- Sort -->
        <div class="filter-field">
            <label for="sort" class="filter-label">
                {{ __('messages.public.sort_by') }}
            </label>
            <select id="sort" name="sort" class="filter-select">
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

        <!-- Actions -->
        <div class="filter-actions">
            <button type="submit" class="filter-btn filter-btn-primary">
                {{ __('messages.public.search') }}
            </button>

            @if($hasFilters)
                <a href="{{ route('public.search') }}" class="filter-link-clear">
                    {{ __('messages.public.clear_filters') }}
                </a>
            @endif
        </div>
    </form>
</div>

@once
    @push('styles')
        <style>
            .search-filters {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 14px;
                padding: 1rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            .filters-form {
                display: flex;
                flex-direction: column;
                gap: 0.8rem;
            }

            .filters-header {
                margin-bottom: 0.4rem;
            }

            .filters-title {
                margin: 0 0 0.3rem;
                font-size: 1rem;
                font-weight: 900;
                color: #0f172a;
                letter-spacing: -0.01em;
            }

            .filters-subtitle {
                margin: 0;
                color: #64748b;
                font-size: 0.8rem;
                font-weight: 500;
            }

            .filter-field {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }

            .filter-label {
                display: block;
                color: #0f172a;
                font-size: 0.85rem;
                font-weight: 800;
                letter-spacing: -0.01em;
            }

            .filter-input,
            .filter-select {
                height: 40px;
                padding: 0 0.9rem;
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                background: #ffffff;
                color: #0f172a;
                font-family: inherit;
                font-size: 0.9rem;
                font-weight: 600;
                outline: none;
                transition: 0.15s ease;
            }

            .filter-input::placeholder {
                color: #94a3b8;
                font-weight: 500;
            }

            .filter-input:focus,
            .filter-select:focus {
                border-color: #ff7a1a;
                box-shadow: 0 0 0 3px rgba(255, 122, 26, 0.08);
            }

            .filter-checkbox {
                flex-direction: row;
                align-items: center;
                gap: 0.5rem;
            }

            .filter-checkbox-input {
                width: 18px;
                height: 18px;
                border: 1.5px solid #d1d5db;
                border-radius: 5px;
                cursor: pointer;
                accent-color: #ff7a1a;
                transition: 0.15s ease;
                flex-shrink: 0;
                margin: 0;
            }

            .filter-checkbox-input:checked {
                background: #ff7a1a;
                border-color: #ff7a1a;
            }

            .filter-checkbox-label {
                color: #0f172a;
                font-size: 0.9rem;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 0.35rem;
                margin: 0;
            }

            .filter-actions {
                display: flex;
                flex-direction: column;
                gap: 0.6rem;
                margin-top: 0.4rem;
            }

            .filter-btn {
                height: 40px;
                padding: 0 1.2rem;
                border-radius: 10px;
                font-family: inherit;
                font-size: 0.9rem;
                font-weight: 700;
                border: none;
                cursor: pointer;
                transition: 0.15s ease;
                letter-spacing: -0.01em;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .filter-btn-primary {
                background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
                color: #ffffff;
                box-shadow: 0 8px 16px rgba(255, 107, 26, 0.16);
            }

            .filter-btn-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 10px 24px rgba(255, 107, 26, 0.24);
            }

            .filter-link-clear {
                color: #ff7a1a;
                text-decoration: none;
                font-weight: 700;
                font-size: 0.85rem;
                text-align: center;
                transition: 0.15s ease;
                padding: 0.5rem;
            }

            .filter-link-clear:hover {
                color: #ff6b1a;
            }

            @media (max-width: 768px) {
                .search-filters {
                    padding: 0.9rem;
                }

                .filter-input,
                .filter-select {
                    height: 44px;
                    font-size: 0.9rem;
                }

                .filter-btn {
                    height: 44px;
                    font-size: 0.9rem;
                }
            }
        </style>
    @endpush
@endonce
