@extends('public.layout')

@section('title', $category->localized_name . ' - ' . config('app.name'))

@section('content')

{{-- Breadcrumbs Navigation Node --}}
<div class="breadcrumb-nav-wrapper">
    <div class="container">
        <nav aria-label="breadcrumb" class="modern-breadcrumb">
            <a href="{{ route('home') }}" class="breadcrumb-link">{{ __('messages.public.home') }}</a>
            <span class="breadcrumb-divider">/</span>
            <span class="breadcrumb-current">{{ $category->localized_name }}</span>
        </nav>
    </div>
</div>

{{-- Category Overview Hero Header Slot --}}
<section class="category-hero-header">
    <div class="container">
        <div class="category-hero-inner-grid">
            <div class="category-meta-details">
                <h1 class="category-title-main">
                    {{ $category->localized_name }}
                </h1>
                @if($category->description)
                    <p class="category-desc-para">{{ $category->description }}</p>
                @endif
                <div class="category-badge-pill">
                    <x-render-icon icon="heroicon-o-users" class="badge-icon-node" />
                    <span>{{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}</span>
                </div>
            </div>

            <div class="category-graphic-container">
                <div class="graphic-circle-backdrop">
                    <x-render-icon :icon="$category->icon ?: 'heroicon-o-briefcase'" class="graphic-svg" />
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Mobile Quick Action Bar Component --}}
<div class="mobile-action-bar-hub">
    <div class="container mobile-action-flex-container">
        <span class="mobile-results-counter">
            {{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}
        </span>
        <button type="button" id="openMobileFilters" class="btn-mobile-filter-trigger">
            <x-render-icon icon="heroicon-o-funnel" class="mobile-trigger-icon" />
            <span>خيارات التصفية</span>
            @if(request()->anyFilled(['city_id', 'sort']))
                <span class="active-filter-indicator-dot"></span>
            @endif
        </button>
    </div>
</div>

{{-- Search Engine Main Matrix Workspace --}}
<section class="archive-split-workspace">
    <div class="container">
        <div class="workspace-layout-grid">

            {{-- Filters Sidebar Block Module Wrapper Node --}}
            <div id="filterSidebarWrapper" class="workspace-sidebar-sticky hidden-mobile-wrapper">

                <div id="filterSidebarCard" class="filter-card-shell drawer-card-transform">
                    <div class="filter-card-header">
                        <div class="filter-header-main-title">
                            <x-render-icon icon="heroicon-o-funnel" class="filter-header-icon" />
                            <h3 class="filter-header-title">خيارات التصفية</h3>
                        </div>
                        <button type="button" id="closeMobileFilters" class="btn-mobile-drawer-close">✕</button>
                    </div>

                    <form method="GET" action="{{ url()->current() }}" class="filter-form-action-flow">
                        @if(isset($cities))
                            <div class="filter-input-group">
                                <label for="city_id" class="filter-field-label">{{ __('messages.public.city') }}</label>
                                <div class="filter-select-wrapper">
                                    <x-render-icon icon="heroicon-o-map-pin" class="select-embedded-icon" />
                                    <select id="city_id" name="city_id" onchange="this.form.submit()" class="filter-select-input">
                                        <option value="">{{ __('messages.public.all_cities') }}</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" @selected(request('city_id') == $city->id)>
                                                {{ $city->localized_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="filter-input-group">
                            <label for="sort" class="filter-field-label">{{ __('messages.public.sort_by') }}</label>
                            <div class="filter-select-wrapper">
                                <x-render-icon icon="heroicon-o-bars-3-bottom-left" class="select-embedded-icon" />
                                <select id="sort" name="sort" onchange="this.form.submit()" class="filter-select-input">
                                    <option value="" @selected(!request('sort'))>{{ __('messages.public.relevance') }}</option>
                                    <option value="rating" @selected(request('sort') === 'rating')>{{ __('messages.public.highest_rated') }}</option>
                                    <option value="reviews" @selected(request('sort') === 'reviews')>{{ __('messages.public.most_reviewed') }}</option>
                                    <option value="newest" @selected(request('sort') === 'newest')>{{ __('messages.public.newest') }}</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-filter-apply desktop-only-submit-btn">
                            <span>{{ __('messages.public.filter') }}</span>
                        </button>
                    </form>

                    @if(request()->anyFilled(['city_id', 'sort']))
                        <div class="clear-action-wrapper-node">
                            <a href="{{ route('public.category', $category->slug) }}" class="btn-filter-clear-trigger">
                                <x-render-icon icon="heroicon-o-arrow-path" class="clear-icon-svg" />
                                <span>{{ __('messages.public.clear_filters') }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Results Section Grid Dynamic Display Output --}}
            <main class="workspace-results-area">
                @if($profiles && $profiles->count() > 0)
                    <div class="provider-grid-wrapper-node">
                        <x-provider-grid :providers="$profiles" :columns="1" />
                    </div>

                    @if($profiles->hasPages())
                        <nav aria-label="Page navigation" class="pagination-footer-nav-container">
                            {{ $profiles->appends(request()->query())->links('pagination::tailwind') }}
                        </nav>
                    @endif
                @else
                    <div class="premium-empty-state-card">
                        <div class="empty-state-icon-backdrop">
                            <x-render-icon icon="heroicon-o-magnifying-glass" />
                        </div>
                        <h4 class="empty-state-heading">{{ __('messages.public.no_providers_found') }}</h4>
                        <p class="empty-state-description">
                            {{ __('messages.public.no_providers_in_category') }}
                        </p>
                        <a href="{{ route('public.search') }}" class="btn-empty-state-redirect">
                            <span>{{ __('messages.public.browse_all') }}</span>
                        </a>
                    </div>
                @endif
            </main>

        </div>
    </div>
</section>

<style>
    /* Design tokens mapping */
    :root {
        --brand-primary: #F1620F;
        --brand-primary-hover: #D7530A;
        --brand-dark: #0B1A34;
        --brand-dark-gradient: #14284D;
        --bg-surface: #FFFFFF;
        --bg-subtle: #F8FAFC;
        --text-primary: #0B1A34;
        --text-secondary: #475569;
        --text-light-muted: #94A3B8;
        --border-color: #E2E8F0;
        --transition-smooth: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Minimalist Breadcrumb Navigation */
    .breadcrumb-nav-wrapper {
        background-color: var(--bg-surface);
        border-bottom: 1px solid var(--border-color);
        padding: 0.85rem 0;
    }

    .modern-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .breadcrumb-link {
        color: var(--text-secondary);
        text-decoration: none;
        transition: var(--transition-smooth);
    }

    .breadcrumb-link:hover {
        color: var(--brand-primary);
    }

    .breadcrumb-divider {
        color: var(--text-light-muted);
    }

    .breadcrumb-current {
        color: var(--brand-dark);
        font-weight: 600;
    }

    /* Balanced Category Hero Header */
    .category-hero-header {
        background: linear-gradient(135deg, var(--brand-dark), var(--brand-dark-gradient));
        padding: 4rem 0;
        color: #FFFFFF;
    }

    .category-hero-inner-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        align-items: center;
        gap: 2rem;
    }

    .category-meta-details {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .category-title-main {
        font-size: clamp(1.75rem, 4vw, 2.75rem);
        font-weight: 800;
        margin: 0 0 1rem;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .category-desc-para {
        font-size: 1.05rem;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.8);
        max-width: 680px;
        margin: 0 0 1.5rem;
    }

    .category-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 0.4rem 1rem;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
    }

    .badge-icon-node {
        width: 16px;
        height: 16px;
        color: var(--brand-primary);
    }

    .category-graphic-container {
        display: flex;
        justify-content: flex-end;
    }

    .graphic-circle-backdrop {
        width: 110px;
        height: 110px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.85);
    }

    .graphic-svg {
        width: 52px;
        height: 52px;
    }

    /* Sticky Action Hub Above Mobile Content */
    .mobile-action-bar-hub {
        display: none;
        position: sticky;
        top: 0;
        z-index: 40;
        background-color: rgba(248, 250, 252, 0.9);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border-color);
        padding: 0.85rem 0;
    }

    .mobile-action-flex-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1rem;
    }

    .mobile-results-counter {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--brand-dark);
    }

    .btn-mobile-filter-trigger {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-secondary);
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        cursor: pointer;
    }

    .mobile-trigger-icon {
        width: 15px;
        height: 15px;
        color: var(--brand-primary);
    }

    .active-filter-indicator-dot {
        width: 7px;
        height: 7px;
        background-color: var(--brand-primary);
        border-radius: 50%;
        display: inline-block;
    }

    .btn-mobile-drawer-close {
        display: none;
        background: var(--bg-subtle);
        border: none;
        padding: 0.4rem 0.6rem;
        border-radius: 8px;
        color: var(--text-secondary);
        font-weight: 700;
        cursor: pointer;
    }

    /* Workspace Architecture Grid Splitter */
    .archive-split-workspace {
        padding: 4.5rem 0;
        background-color: var(--bg-subtle);
    }

    .workspace-layout-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
        align-items: start;
    }

    /* Refined Sticky Search Filters Card */
    .workspace-sidebar-sticky {
        position: sticky;
        top: 110px;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        transition: background-color 0.2s ease, opacity 0.2s ease;
    }

    .filter-card-shell {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(11, 26, 52, 0.02);
        width: 100%;
        box-sizing: border-box;
    }

    .filter-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .filter-header-main-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-card-shell-inner-row {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
    }
    .filter-card-header {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1rem;
        margin-bottom: 1.25rem;
    }

    .filter-header-icon {
        width: 18px;
        height: 18px;
        color: var(--brand-primary);
    }

    .filter-header-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--brand-dark);
        margin: 0;
    }

    .filter-form-action-flow {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .filter-input-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-field-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-secondary);
    }

    .filter-select-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .select-embedded-icon {
        position: absolute;
        right: 1rem;
        width: 18px;
        height: 18px;
        color: var(--text-light-muted);
        pointer-events: none;
    }

    .filter-select-input {
        width: 100%;
        height: 46px;
        background-color: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0 2.75rem 0 1.25rem; /* Balanced explicitly for RTL spacing frameworks */
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--brand-dark);
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        transition: var(--transition-smooth);
        cursor: pointer;
    }

    .filter-select-input:focus {
        border-color: var(--brand-primary);
        background-color: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(241, 98, 15, 0.1);
    }

    .btn-filter-apply {
        background-color: var(--brand-dark);
        color: #FFFFFF;
        border: none;
        height: 46px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-smooth);
    }

    .btn-filter-apply:hover {
        background-color: var(--brand-primary);
        box-shadow: 0 4px 12px rgba(241, 98, 15, 0.2);
    }

    /* Filter Reset Controls Node Elements */
    .clear-action-wrapper-node {
        width: 100%;
        margin-top: 1rem;
    }

    .btn-filter-clear-trigger {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        height: 44px;
        background: transparent;
        border: 1px dashed var(--border-color);
        color: var(--text-secondary);
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: var(--transition-smooth);
    }

    .btn-filter-clear-trigger:hover {
        border-style: solid;
        border-color: var(--brand-primary);
        color: var(--brand-primary);
        background-color: rgba(241, 98, 15, 0.02);
    }

    .clear-icon-svg {
        width: 15px;
        height: 15px;
    }

    /* Results Workspace Core Component Node overrides */
    .workspace-results-area {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .provider-grid-wrapper-node {
        width: 100%;
    }

    .pagination-footer-nav-container {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    /* Premium Clean Empty State Matrix Minimalist Look */
    .premium-empty-state-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 4rem 2rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 4px 12px rgba(11, 26, 52, 0.01);
    }

    .empty-state-icon-backdrop {
        width: 72px;
        height: 72px;
        background-color: var(--bg-subtle);
        color: var(--text-light-muted);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .empty-state-icon-backdrop svg {
        width: 32px;
        height: 32px;
    }

    .empty-state-heading {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--brand-dark);
        margin: 0 0 0.5rem;
    }

    .empty-state-description {
        font-size: 0.95rem;
        color: var(--text-secondary);
        max-width: 400px;
        margin: 0 0 1.75rem;
        line-height: 1.5;
    }

    .btn-empty-state-redirect {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 46px;
        padding: 0 2rem;
        background-color: var(--brand-primary);
        color: #FFFFFF;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 700;
        border-radius: 10px;
        transition: var(--transition-smooth);
        box-shadow: 0 4px 12px rgba(241, 98, 15, 0.2);
    }

    .btn-empty-state-redirect:hover {
        background-color: var(--brand-primary-hover);
        transform: translateY(-1px);
    }

    /* Core Media Boundary Adaptations Queries */
    @media (max-width: 1024px) {
        .archive-split-workspace {
            padding: 2rem 0;
        }
        .mobile-action-bar-hub {
            display: block;
        }
        .workspace-layout-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        /* Transition Wrapper to Flyout Sheet Overlay */
        .workspace-sidebar-sticky {
            position: fixed;
            inset: 0;
            z-index: 50;
            background-color: rgba(11, 26, 52, 0.4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            margin: 0;
            padding: 0;
        }

        .workspace-sidebar-sticky.active-mobile-drawer {
            opacity: 1;
            pointer-events: auto;
        }

        .filter-card-shell {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: 24px 24px 0 0;
            border: none;
            border-top: 1px solid var(--border-color);
            max-height: 85vh;
            overflow-y: auto;
            transform: translateY(100%);
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .workspace-sidebar-sticky.active-mobile-drawer .filter-card-shell {
            transform: translateY(0);
        }

        .btn-mobile-drawer-close {
            display: block;
        }
        .desktop-only-submit-btn {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .category-hero-header {
            padding: 2.5rem 0;
        }
        .category-hero-inner-grid {
            grid-template-columns: 1fr;
        }
        .category-graphic-container {
            display: none; /* Strip layout containers from heavy responsive render trees */
        }
        .category-meta-details {
            align-items: center;
            text-align: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const openBtn = document.getElementById('openMobileFilters');
        const closeBtn = document.getElementById('closeMobileFilters');
        const sidebarWrapper = document.getElementById('filterSidebarWrapper');

        if (!openBtn || !sidebarWrapper) return;

        const openFilters = () => {
            sidebarWrapper.classList.remove('hidden-mobile-wrapper');
            // Allow display swap to hit browser layout engine prior to firing active transform animations
            setTimeout(() => {
                sidebarWrapper.classList.add('active-mobile-drawer');
            }, 10);
            document.body.style.overflow = 'hidden';
        };

        const closeFilters = () => {
            sidebarWrapper.classList.remove('active-mobile-drawer');
            setTimeout(() => {
                sidebarWrapper.classList.add('hidden-mobile-wrapper');
            }, 250);
            document.body.style.overflow = '';
        };

        openBtn.addEventListener('click', openFilters);
        closeBtn?.addEventListener('click', closeFilters);

        // Close modal sheet easily if clicking onto backdrop area mask
        sidebarWrapper.addEventListener('click', (e) => {
            if (e.target === sidebarWrapper) closeFilters();
        });
    });
</script>

@endsection
