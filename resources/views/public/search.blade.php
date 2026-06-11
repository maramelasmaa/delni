@extends('public.layout')

@section('title', __('messages.public.search_results') . ' - ' . config('app.name'))

@section('content')
@php
    $total = $profiles?->total() ?? $profiles?->count() ?? 0;

    $activeCategory = request('category_id') ? $categories->find(request('category_id')) : null;
    $activeCity = request('city_id') ? $cities->find(request('city_id')) : null;

    $hasFilters = request()->filled('keyword')
        || request()->filled('category_id')
        || request()->filled('city_id')
        || request()->filled('provider_type')
        || request()->filled('remote')
        || request()->filled('sort');
@endphp

<div class="delni-search-container">
    {{-- Hero Header Section --}}
    <header class="search-hero">
        <div class="container">
            <div class="search-hero__badge">بحث دلني</div>
            <h1 class="search-hero__title">اعثر على مقدم الخدمة المناسب</h1>
            <p class="search-hero__subtitle">ابحث حسب الخدمة، الفئة، أو المدينة. النتائج واضحة ومرتبة بدون زحمة.</p>
        </div>
    </header>

    {{-- Search Filter Panel Section --}}
    <section class="search-panel-section">
        <div class="container">
            <form action="{{ route('public.search') }}" method="GET" class="search-card">

                {{-- Main Input Fields Grid --}}
                <div class="search-fields-grid">
                    <div class="search-field field-keyword">
                        <x-render-icon icon="heroicon-o-magnifying-glass" class="field-icon" />
                        <input
                            type="text"
                            name="keyword"
                            value="{{ request('keyword') }}"
                            placeholder="شن تحتاج؟ طبيب، محامي، مصمم..."
                            maxlength="100"
                        >
                    </div>

                    <div class="search-field">
                        <x-render-icon icon="heroicon-o-briefcase" class="field-icon" />
                        <select name="category_id">
                            <option value="">كل الفئات</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                    {{ $category->localized_name ?? $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="search-field">
                        <x-render-icon icon="heroicon-o-map-pin" class="field-icon" />
                        <select name="city_id">
                            <option value="">كل المدن</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                                    {{ $city->localized_name ?? $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(isset($providerTypes) && $providerTypes)
                        <div class="search-field">
                            <x-render-icon icon="heroicon-o-squares-2x2" class="field-icon" />
                            <select name="provider_type">
                                <option value="">كل الأنواع</option>
                                @foreach($providerTypes as $code => $name)
                                    <option value="{{ $code }}" @selected((string) request('provider_type') === (string) $code)>
                                        {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button type="submit" class="btn-search-submit">
                        <span>بحث</span>
                    </button>
                </div>

                {{-- Sub-bar Filter Controls --}}
                <div class="search-sub-bar">
                    <label class="toggle-checkbox">
                        <input type="checkbox" name="remote" value="1" @checked(request('remote') == 1)>
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">يدعم العمل عن بعد</span>
                    </label>

                    <div class="sort-selector">
                        <span class="sort-label">ترتيب حسب:</span>
                        <select name="sort" aria-label="ترتيب النتائج">
                            <option value="" @selected(!request('sort'))>الأكثر صلة</option>
                            <option value="rating" @selected(request('sort') === 'rating')>الأعلى تقييماً</option>
                            <option value="reviews" @selected(request('sort') === 'reviews')>الأكثر مراجعات</option>
                            <option value="newest" @selected(request('sort') === 'newest')>الأحدث</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- Main Results Section --}}
    <main class="results-section">
        <div class="container">

            {{-- Dynamic Header Info --}}
            <div class="results-header">
                <div class="results-counter">
                    <span class="subtitle-label">مستندات العثور</span>
                    <h2 class="main-counter-title">
                        <strong>{{ number_format($total) }}</strong> مقدم خدمة متاح
                    </h2>
                </div>

                @if($hasFilters)
                    <a href="{{ route('public.search') }}" class="btn-clear-filters">
                        <x-render-icon icon="heroicon-o-trash" />
                        <span>مسح الفلاتر</span>
                    </a>
                @endif
            </div>

            {{-- Chip Tags for Applied Filters --}}
            @if($hasFilters)
                <div class="active-filter-chips">
                    @if(request('keyword'))
                        <span class="chip-tag">{{ request('keyword') }}</span>
                    @endif

                    @if($activeCategory)
                        <span class="chip-tag">{{ $activeCategory->localized_name ?? $activeCategory->name }}</span>
                    @endif

                    @if($activeCity)
                        <span class="chip-tag">{{ $activeCity->localized_name ?? $activeCity->name }}</span>
                    @endif

                    @if(request('remote'))
                        <span class="chip-tag accent">عن بعد</span>
                    @endif

                    @if(request('sort'))
                        <span class="chip-tag standard">
                            @switch(request('sort'))
                                @case('rating') الأعلى تقييماً @break
                                @case('reviews') الأكثر مراجعات @break
                                @case('newest') الأحدث @break
                                @default الأكثر صلة
                            @endswitch
                        </span>
                    @endif
                </div>
            @endif

            {{-- Providers Display Node --}}
            @if($profiles && $profiles->count() > 0)
                <div class="grid-wrapper">
                    <x-provider-grid :providers="$profiles" :columns="3" />
                </div>

                {{-- Pagination Nav Element --}}
                @if($profiles->hasPages())
                    <nav class="custom-pagination" aria-label="Pagination">
                        @if($profiles->onFirstPage())
                            <span class="pag-btn disabled">السابق</span>
                        @else
                            <a href="{{ $profiles->previousPageUrl() }}" class="pag-btn">السابق</a>
                        @endif

                        <span class="pag-status-info">
                            صفحة {{ $profiles->currentPage() }} من {{ $profiles->lastPage() }}
                        </span>

                        @if($profiles->hasMorePages())
                            <a href="{{ $profiles->nextPageUrl() }}" class="pag-btn">التالي</a>
                        @else
                            <span class="pag-btn disabled">التالي</span>
                        @endif
                    </nav>
                @endif
            @else
                {{-- Fallback Screen State --}}
                <div class="empty-state-wrapper">
                    <x-empty-state
                        icon="heroicon-o-magnifying-glass"
                        title="ما لقيناش نتائج"
                        message="جرّب كلمة أبسط، أو غيّر المدينة والفئة المحددة."
                        actionLabel="مسح فلاتر البحث"
                        actionUrl="{{ route('public.search') }}"
                    />
                </div>
            @endif
        </div>

        {{-- Provider CTA Section --}}
        <section class="provider-cta-section" style="margin-top: 3rem;">
            <div class="container">
                <div class="provider-cta-card">
                    <h2 class="cta-title">{{ __('messages.public.are_you_professional') }}</h2>
                    <p class="cta-description">{{ __('messages.public.join_marketplace_description') }}</p>
                    <a href="{{ route('contact') }}" class="cta-button">{{ __('messages.public.contact_us') }}</a>
                </div>
            </div>
        </section>
    </main>
</div>

<style>
    /* Custom Design Framework Base Tokens */
    :root {
        --brand-primary: #F1620F;
        --brand-primary-hover: #D7530A;
        --brand-dark: #0B1A34;
        --brand-dark-light: #1E2E4A;
        --bg-surface: #FFFFFF;
        --bg-canvas: #F8FAFC;
        --border-color: #E2E8F0;
        --text-main: #334155;
        --text-muted: #64748B;
        --transition-standard: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Overall Container Setting */
    .delni-search-container {
        min-height: 100vh;
        background-color: var(--bg-canvas);
        font-family: system-ui, -apple-system, sans-serif;
        color: var(--text-main);
    }

    /* Redesigned Minimalist Hero */
    .search-hero {
        background: linear-gradient(135deg, var(--brand-dark), var(--brand-dark-light));
        padding: 5rem 0 6.5rem;
        text-align: center;
        color: #FFFFFF;
    }

    .search-hero__badge {
        display: inline-block;
        background: rgba(241, 98, 15, 0.12);
        border: 1px solid rgba(241, 98, 15, 0.3);
        color: #FF9D66;
        padding: 0.35rem 1rem;
        border-radius: 100px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
    }

    .search-hero__title {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        margin: 0 0 1rem;
        line-height: 1.2;
    }

    .search-hero__subtitle {
        font-size: clamp(0.95rem, 1.5vw, 1.15rem);
        color: rgba(255, 255, 255, 0.75);
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Redesigned Floating Interface Form Card */
    .search-panel-section {
        margin-top: -3.5rem;
        position: relative;
        z-index: 10;
        padding-bottom: 2rem;
    }

    .search-card {
        background: var(--bg-surface);
        border-radius: 20px;
        box-shadow: 0 10px 30px -5px rgba(11, 26, 52, 0.08), 0 20px 40px -10px rgba(11, 26, 52, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        padding: 1.25rem;
    }

    /* Row Layout Rules for Main Filters Grid */
    .search-fields-grid {
        display: grid;
        grid-template-columns: 1.5fr repeat(auto-fit, minmax(180px, 1fr)) auto;
        gap: 0.75rem;
        align-items: center;
    }

    .search-field {
        background: var(--bg-canvas);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        height: 52px;
        display: flex;
        align-items: center;
        padding: 0 1rem;
        gap: 0.75rem;
        transition: var(--transition-standard);
    }

    .search-field:focus-within {
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 3px rgba(241, 98, 15, 0.12);
        background: #FFFFFF;
    }

    .search-field .field-icon {
        width: 20px;
        height: 20px;
        color: var(--text-muted);
        flex-shrink: 0;
    }

    .search-field input,
    .search-field select {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        color: var(--brand-dark);
        font-size: 0.95rem;
        font-weight: 500;
    }

    .search-field input::placeholder {
        color: #94A3B8;
    }

    /* Main Submit Button Refactor */
    .btn-search-submit {
        background: var(--brand-primary);
        color: #FFFFFF;
        border: none;
        height: 52px;
        padding: 0 2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-standard);
    }

    .btn-search-submit:hover {
        background: var(--brand-primary-hover);
        transform: translateY(-1px);
    }

    /* Isolated Functional Toolbar Footer Layer */
    .search-sub-bar {
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    /* Custom Toggle Switch */
    .toggle-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        user-select: none;
    }

    .toggle-checkbox input {
        display: none;
    }

    .toggle-switch {
        width: 40px;
        height: 22px;
        background: #CBD5E1;
        border-radius: 100px;
        position: relative;
        transition: var(--transition-standard);
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        background: #FFFFFF;
        border-radius: 50%;
        transition: var(--transition-standard);
    }

    .toggle-checkbox input:checked + .toggle-switch {
        background: var(--brand-primary);
    }

    .toggle-checkbox input:checked + .toggle-switch::after {
        left: calc(100% - 20px);
    }

    .toggle-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* Minimalist Sorting Layout */
    .sort-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sort-label {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .sort-selector select {
        border: 1px solid var(--border-color);
        background: #FFFFFF;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--brand-dark);
        outline: none;
        cursor: pointer;
    }

    /* Results Header Segment */
    .results-section {
        padding: 2rem 0 4rem;
    }

    .results-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        gap: 1rem;
    }

    .subtitle-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--brand-primary);
        font-weight: 700;
        display: block;
        margin-bottom: 0.25rem;
    }

    .main-counter-title {
        font-size: clamp(1.25rem, 2.5vw, 1.75rem);
        font-weight: 700;
        color: var(--brand-dark);
        margin: 0;
    }

    .main-counter-title strong {
        color: var(--brand-primary);
    }

    /* Action Filter Resets Link UI */
    .btn-clear-filters {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #EF4444;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: rgba(239, 68, 68, 0.06);
        transition: var(--transition-standard);
    }

    .btn-clear-filters:hover {
        background: rgba(239, 68, 68, 0.12);
    }

    .btn-clear-filters svg {
        width: 16px;
        height: 16px;
    }

    /* Filter Active Interactive Chips */
    .active-filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }

    .chip-tag {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        padding: 0.4rem 0.85rem;
        border-radius: 100px;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-main);
        display: inline-flex;
        align-items: center;
    }

    .chip-tag.accent {
        background: rgba(241, 98, 15, 0.06);
        border-color: rgba(241, 98, 15, 0.2);
        color: var(--brand-primary);
    }

    /* Pagination Module Element Styles */
    .custom-pagination {
        margin-top: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .pag-btn {
        height: 40px;
        padding: 0 1.25rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        color: var(--text-main);
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: var(--transition-standard);
    }

    .pag-btn:not(.disabled):hover {
        border-color: var(--brand-primary);
        color: var(--brand-primary);
        transform: translateY(-1px);
    }

    .pag-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: transparent;
    }

    .pag-status-info {
        font-size: 0.9rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* Wrapper for Global Empty States components fallback context */
    .empty-state-wrapper {
        padding: 4rem 1rem;
        background: var(--bg-surface);
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    /* Viewport Responsiveness Adjustments */
    @media (max-width: 1024px) {
        .search-fields-grid {
            grid-template-columns: 1fr 1fr;
        }
        .btn-search-submit {
            grid-column: span 2;
        }
    }

    @media (max-width: 640px) {
        .search-hero {
            padding: 3.5rem 0 5rem;
        }
        .search-panel-section {
            margin-top: -2.5rem;
        }
        .search-card {
            padding: 1rem;
        }
        .search-fields-grid {
            grid-template-columns: 1fr;
        }
        .btn-search-submit {
            grid-column: span 1;
        }
        .search-sub-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 1.25rem;
        }
        .sort-selector {
            width: 100%;
            justify-content: space-between;
        }
        .results-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .btn-clear-filters {
            width: 100%;
            justify-content: center;
        }
    }

    /* Provider CTA Section */
    .provider-cta-section {
        padding: 3rem 0;
        background: linear-gradient(135deg, rgba(241, 98, 15, 0.08), rgba(241, 98, 15, 0.04));
    }

    .provider-cta-card {
        background: var(--bg-surface);
        border: 2px solid var(--brand-primary);
        border-radius: 20px;
        padding: clamp(2rem, 5vw, 3rem);
        text-align: center;
        box-shadow: 0 10px 30px rgba(241, 98, 15, 0.1);
    }

    .cta-title {
        font-size: clamp(1.5rem, 4vw, 2.2rem);
        font-weight: 900;
        color: var(--brand-dark);
        margin-bottom: 1rem;
        letter-spacing: -0.03em;
    }

    .cta-description {
        font-size: clamp(0.9rem, 2vw, 1.05rem);
        color: var(--text-muted);
        margin-bottom: 1.5rem;
        line-height: 1.7;
        max-width: 500px;
        margin-inline: auto;
    }

    .cta-button {
        display: inline-block;
        background: var(--brand-primary);
        color: white;
        padding: 0.85rem 2rem;
        border-radius: 12px;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 700;
        transition: var(--transition-standard);
        border: 2px solid var(--brand-primary);
    }

    .cta-button:hover {
        background: transparent;
        color: var(--brand-primary);
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .provider-cta-section {
            padding: 2rem 0;
        }

        .provider-cta-card {
            padding: 1.5rem;
        }

        .cta-title {
            margin-bottom: 0.75rem;
        }

        .cta-description {
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
        }

        .cta-button {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
        }
    }
</style>
@endsection
