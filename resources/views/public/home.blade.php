@extends('public.layout')

@section('title', __('messages.public.home') . ' - ' . config('app.name'))

@section('content')
@php
    $categories = $categories ?? collect();
    $cities = $cities ?? collect();

    $featuredProviders = $featuredProviders ?? collect();

    $providersCount = $categories->sum(fn ($category) => (int) ($category->discoverable_profiles_count ?? 0));
    $categoriesCount = $categories->count();
    $citiesCount = $cities->count();
@endphp

{{-- Main Landing Hero Section --}}
<section class="home-hero-viewport">
    <div class="container">
        <div class="hero-content-wrapper">
            <h1 class="hero-main-title">
                دور على الخدمة<br>
                اللي تحتاجها <span class="highlight">بسهولة</span>
            </h1>

            {{-- Floating Combined Search Engine Form --}}
            <form action="{{ route('public.search') }}" method="GET" class="hero-search-card" id="searchForm">

                {{-- Global Search Bar Input Field --}}
                <div class="hero-input-field field-keyword">
                    <x-render-icon icon="heroicon-o-magnifying-glass" class="field-icon" />
                    <input
                        type="text"
                        name="keyword"
                        placeholder="ابحث عن خدمة، مقدم خدمة أو كلمة مفتاحية..."
                        maxlength="100"
                        autocomplete="off"
                    >
                </div>

                {{-- Desktop Filters Block Layout Nodes --}}
                <div class="desktop-filters-group">
                    <div class="hero-input-field">
                        <x-render-icon icon="heroicon-o-briefcase" class="field-icon" />
                        <select name="category_id" id="desktopCategory">
                            <option value="">كل الفئات</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->localized_name ?? $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="hero-input-field">
                        <x-render-icon icon="heroicon-o-map-pin" class="field-icon" />
                        <select name="city_id" id="desktopCity">
                            <option value="">كل المدن</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">
                                    {{ $city->localized_name ?? $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Mobile Drawer Trigger Action Button Control --}}
                <button type="button" class="mobile-filter-pill-trigger" id="openMobileFilters">
                    <x-render-icon icon="heroicon-o-funnel" class="mobile-pill-icon" />
                    <span id="filterPillText">تحديد الفئة والمدينة</span>
                </button>

                {{-- Core Action Search Submit Button --}}
                <button type="submit" class="btn-hero-submit">
                    <x-render-icon icon="heroicon-o-magnifying-glass" />
                    <span>بحث</span>
                </button>

                {{-- PWA Mobile Sliding Bottom Sheet Filter Drawer Module --}}
                <div class="mobile-filter-drawer-overlay" id="drawerOverlay">
                    <div class="mobile-filter-drawer-card">
                        <div class="drawer-drag-handle"></div>
                        <div class="drawer-header">
                            <h3 class="drawer-title">تخصيص البحث</h3>
                            <button type="button" class="drawer-close-btn" id="closeMobileFilters">✕</button>
                        </div>

                        <div class="drawer-body-inputs">
                            <div class="drawer-input-group">
                                <label class="drawer-field-label">مجال الخدمة المطلوب</label>
                                <div class="drawer-select-wrapper">
                                    <x-render-icon icon="heroicon-o-briefcase" class="drawer-select-icon" />
                                    <select id="mobileCategory" class="drawer-custom-select">
                                        <option value="">كل الفئات والمجالات</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->localized_name ?? $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="drawer-input-group">
                                <label class="drawer-field-label">المدينة / المنطقة</label>
                                <div class="drawer-select-wrapper">
                                    <x-render-icon icon="heroicon-o-map-pin" class="drawer-select-icon" />
                                    <select id="mobileCity" class="drawer-custom-select">
                                        <option value="">كل المدن والمناطق</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}">
                                                {{ $city->localized_name ?? $city->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="drawer-footer-actions">
                            <button type="button" class="btn-drawer-apply-trigger" id="applyMobileFilters">
                                تأكيد الاختيارات
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Modern Platform Live Metric Statistics Counter Nodes --}}
            <div class="hero-stats-row">
                <div class="stat-metric-card">
                    <div class="stat-icon-box">
                        <x-render-icon icon="heroicon-o-briefcase" />
                    </div>
                    <div class="stat-info-text">
                        <strong class="stat-number">{{ number_format($categoriesCount) }}+</strong>
                        <span class="stat-label">فئة متنوعة</span>
                    </div>
                </div>

                <div class="stat-metric-card">
                    <div class="stat-icon-box">
                        <x-render-icon icon="heroicon-o-map-pin" />
                    </div>
                    <div class="stat-info-text">
                        <strong class="stat-number">{{ number_format($citiesCount) }}</strong>
                        <span class="stat-label">مدينة في ليبيا</span>
                    </div>
                </div>

                <div class="stat-metric-card">
                    <div class="stat-icon-box">
                        <x-render-icon icon="heroicon-o-check-circle" />
                    </div>
                    <div class="stat-info-text">
                        <strong class="stat-number">{{ number_format($providersCount) }}+</strong>
                        <span class="stat-label">مقدم خدمة موثوق</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Ultra-Compact Horizontal Categories Carousel Selector Section --}}
@if($categories->count() > 0)
    <section class="categories-explorer-section">
        <div class="container">
            <div class="section-header-inline">
                <div class="header-text-side">
                    <span class="section-tagline">تصفح حسب الفئة</span>
                    <h2 class="section-main-title">الخدمات المتاحة على المنصة</h2>
                </div>
                <a href="{{ route('public.categories') }}" class="btn-link-all">
                    <span>عرض الكل</span>
                    <x-render-icon icon="heroicon-o-arrow-left" class="icon-flip-rtl" />
                </a>
            </div>

            <div class="modern-categories-slider">
                @foreach($categories->take(8) as $category)
                    <a href="{{ route('public.category', $category->slug) }}" class="category-interactive-card">
                        <div class="category-icon-wrapper">
                            <x-svg-icon :icon="$category->getRelation('icon')" size="24" />
                        </div>
                        <div class="category-meta-text">
                            <strong class="category-card-name">{{ $category->localized_name ?? $category->name }}</strong>
                            <span class="category-card-count">
                                {{ $category->discoverable_profiles_count ?? 0 }} مزود
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Featured Providers Custom Layout Slot Node --}}
@if($featuredProviders->count() > 0)
    <section class="featured-providers-section text-center">
        <div class="container">
            <x-provider-grid
                :providers="$featuredProviders"
                :columns="3"
                title="الخدمات الموثوقة"
                subtitle="مقدمو خدمات برتبة عالية وتقييمات إيجابية من العملاء."
                compact="true"
            />
        </div>
    </section>
@endif

{{-- Provider CTA Section --}}
<section class="provider-cta-section">
    <div class="container">
        <div class="provider-cta-card">
            <h2 class="cta-title">{{ __('messages.public.are_you_professional') }}</h2>
            <p class="cta-description">{{ __('messages.public.join_marketplace_description') }}</p>
            <a href="{{ route('contact') }}" class="cta-button">{{ __('messages.public.contact_us') }}</a>
        </div>
    </div>
</section>

<style>
    /* Premium Application Variables Setup */
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

    /* Modern Minimalist Layout Settings */
    .home-hero-viewport {
        position: relative;
        background: linear-gradient(135deg, rgba(11, 26, 52, 0.93), rgba(20, 40, 77, 0.97)),
                    url('{{ asset('images/herobackground2.png') }}') center/cover no-repeat;
        padding: 5rem 0 4rem;
        color: #FFFFFF;
        overflow: hidden;
    }

    .hero-content-wrapper {
        max-width: 1040px;
        margin: 0 auto;
        text-align: center;
    }

    .hero-main-title {
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -0.03em;
        margin: 0 0 2.25rem;
    }

    .hero-main-title .highlight {
        color: var(--brand-primary);
        position: relative;
    }

    /* Redesign of Search Card Container */
    .hero-search-card {
        background: var(--bg-surface);
        padding: 0.65rem;
        border-radius: 20px;
        box-shadow: 0 25px 60px -15px rgba(11, 26, 52, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.15);
        display: grid;
        grid-template-columns: 1.5fr 2fr auto;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 3rem;
    }

    .desktop-filters-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        width: 100%;
    }

    .hero-input-field {
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        height: 54px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        padding: 0 1.15rem;
        gap: 0.65rem;
        transition: var(--transition-smooth);
    }

    .hero-input-field:focus-within {
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 3px rgba(241, 98, 15, 0.12);
        background: #FFFFFF;
    }

    .hero-input-field .field-icon {
        width: 18px;
        height: 18px;
        color: #64748B;
        flex-shrink: 0;
    }

    .hero-input-field input,
    .hero-input-field select {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        color: var(--brand-dark);
        font-size: 0.95rem;
        font-weight: 600;
    }

    .hero-input-field input::placeholder {
        color: var(--text-light-muted);
    }

    /* Mobile Filter Touch-pill trigger UI */
    .mobile-filter-pill-trigger {
        display: none;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        height: 44px;
        padding: 0 1rem;
        border-radius: 10px;
        color: var(--text-secondary);
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        text-align: right;
    }

    .mobile-pill-icon {
        width: 15px;
        height: 15px;
        color: var(--brand-primary);
    }

    /* Redesigned Clean Action Submit Node Button */
    .btn-hero-submit {
        background: var(--brand-primary);
        color: #FFFFFF;
        border: none;
        height: 54px;
        padding: 0 2.25rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: var(--transition-smooth);
        box-shadow: 0 8px 18px -4px rgba(241, 98, 15, 0.3);
    }

    .btn-hero-submit:hover {
        background: var(--brand-primary-hover);
        transform: translateY(-1px);
    }

    .btn-hero-submit svg {
        width: 18px;
        height: 18px;
    }

    /* Native App Flyout-Drawer System Framework (Mobile PWA) */
    .mobile-filter-drawer-overlay {
        position: fixed;
        inset: 0;
        background-color: rgba(11, 26, 52, 0.5);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        z-index: 200;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
        display: flex;
        align-items: flex-end;
    }

    .mobile-filter-drawer-overlay.drawer-open {
        opacity: 1;
        pointer-events: auto;
    }

    .mobile-filter-drawer-card {
        background: var(--bg-surface);
        width: 100%;
        border-radius: 24px 24px 0 0;
        padding: 1.25rem 1.5rem 2.5rem;
        box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
        transform: translateY(100%);
        transition: transform 0.28s cubic-bezier(0.32, 0.94, 0.6, 1);
        box-sizing: border-box;
    }

    .mobile-filter-drawer-overlay.drawer-open .mobile-filter-drawer-card {
        transform: translateY(0);
    }

    .drawer-drag-handle {
        width: 40px;
        height: 5px;
        background-color: var(--border-color);
        border-radius: 3px;
        margin: 0 auto 1.25rem;
    }

    .drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.75rem;
    }

    .drawer-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--brand-dark);
        margin: 0;
    }

    .drawer-close-btn {
        background: var(--bg-subtle);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-size: 0.9rem;
        color: var(--text-secondary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .drawer-body-inputs {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    .drawer-input-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        text-align: right;
    }

    .drawer-field-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-secondary);
    }

    .drawer-select-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .drawer-select-icon {
        position: absolute;
        right: 1rem;
        width: 18px;
        height: 18px;
        color: var(--text-light-muted);
        pointer-events: none;
    }

    .drawer-custom-select {
        width: 100%;
        height: 50px;
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 0 2.75rem 0 1.25rem;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--brand-dark);
        outline: none;
        appearance: none;
        -webkit-appearance: none;
    }

    .drawer-footer-actions {
        width: 100%;
    }

    .btn-drawer-apply-trigger {
        width: 100%;
        background-color: var(--brand-dark);
        color: #FFFFFF;
        border: none;
        height: 50px;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
    }

    /* Professional Floating Metric Counter Layout Row */
    .hero-stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }

    .stat-metric-card {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        padding: 1rem 1.25rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 1rem;
        text-align: right;
    }

    .stat-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: rgba(241, 98, 15, 0.12);
        color: #FF9D66;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-box svg {
        width: 20px;
        height: 20px;
    }

    .stat-info-text {
        display: flex;
        flex-direction: column;
    }

    .stat-number {
        font-size: 1.35rem;
        font-weight: 800;
        color: #FFFFFF;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.65);
        font-weight: 500;
        margin-top: 0.15rem;
    }

    /* Ultra-Compact Categories Section Styling */
    .categories-explorer-section {
        padding: 2.5rem 0;
        background-color: var(--bg-surface);
    }

    .section-header-inline {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 1.5rem;
    }

    .section-tagline {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--brand-primary);
        text-transform: uppercase;
        margin-bottom: 0.25rem;
    }

    .section-main-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--brand-dark);
        margin: 0;
    }

    .btn-link-all {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--brand-primary);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 700;
        transition: var(--transition-smooth);
    }

    .btn-link-all:hover {
        color: var(--brand-primary-hover);
    }

    .btn-link-all svg {
        width: 16px;
        height: 16px;
        transition: transform 0.25s ease;
    }

    .btn-link-all:hover .icon-flip-rtl {
        transform: translateX(-4px);
    }

    /* Horizontal Carousel Grid Mechanics */
    .modern-categories-slider {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        padding-bottom: 0.75rem;
        scroll-behavior: smooth;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .modern-categories-slider::-webkit-scrollbar {
        display: none;
    }

    .category-interactive-card {
        flex: 0 0 auto;
        width: 240px;
        background: var(--bg-subtle);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 0.85rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        text-decoration: none;
        transition: var(--transition-smooth);
    }

    .category-interactive-card:hover {
        background: #FFFFFF;
        border-color: var(--brand-primary);
        transform: translateY(-2px);
    }

    .category-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: rgba(241, 98, 15, 0.06);
        color: var(--brand-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: var(--transition-smooth);
    }

    .category-interactive-card:hover .category-icon-wrapper {
        background: var(--brand-primary);
        color: #FFFFFF;
    }

    .category-icon-wrapper svg {
        width: 20px;
        height: 20px;
    }

    .category-meta-text {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .category-card-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--brand-dark);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .category-card-count {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 500;
        margin-top: 0.1rem;
    }

    .featured-providers-section {
        padding: 4rem 0;
        background-color: var(--bg-subtle);
        border-top: 1px solid var(--border-color);
    }

    /* Screen Breakpoint Adaptations Viewport Adjustments */
    @media (max-width: 991px) {
        .hero-search-card {
            grid-template-columns: 1fr auto;
        }
        .desktop-filters-group {
            display: none;
        }
        .mobile-filter-pill-trigger {
            display: inline-flex;
        }
    }

    @media (max-width: 768px) {
        .hero-stats-row {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
    }

    @media (max-width: 640px) {
        .home-hero-viewport {
            padding: 3.5rem 0 3rem;
        }
        .hero-search-card {
            grid-template-columns: 1fr;
            padding: 0.75rem;
            border-radius: 18px;
            gap: 0.65rem;
        }
        .btn-hero-submit {
            width: 100%;
            height: 48px;
        }
        .hero-input-field {
            height: 48px;
        }
        .category-interactive-card {
            width: 210px;
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
        color: var(--text-secondary);
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
        transition: var(--transition-smooth);
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const openBtn = document.getElementById('openMobileFilters');
        const closeBtn = document.getElementById('closeMobileFilters');
        const applyBtn = document.getElementById('applyMobileFilters');
        const drawerOverlay = document.getElementById('drawerOverlay');

        const mobileCategory = document.getElementById('mobileCategory');
        const mobileCity = document.getElementById('mobileCity');
        const desktopCategory = document.getElementById('desktopCategory');
        const desktopCity = document.getElementById('desktopCity');
        const filterPillText = document.getElementById('filterPillText');

        if (!openBtn || !drawerOverlay) return;

        // Open bottom drawer overlay card
        openBtn.addEventListener('click', () => {
            drawerOverlay.classList.add('drawer-open');
            document.body.style.overflow = 'hidden';
        });

        // Close drawer overlay card helper
        const closeDrawer = () => {
            drawerOverlay.classList.remove('drawer-open');
            document.body.style.overflow = '';
        };

        closeBtn.addEventListener('click', closeDrawer);
        drawerOverlay.addEventListener('click', (e) => {
            if (e.target === drawerOverlay) closeDrawer();
        });

        // Map values chosen inside mobile view back to underlying inputs
        applyBtn.addEventListener('click', () => {
            desktopCategory.value = mobileCategory.value;
            desktopCity.value = mobileCity.value;

            // Change pill text label state color contextually
            if (mobileCategory.value || mobileCity.value) {
                filterPillText.textContent = "تمت تصفية الاختيارات ✓";
                filterPillText.style.color = "var(--brand-primary)";
            } else {
                filterPillText.textContent = "تحديد الفئة والمدينة";
                filterPillText.style.color = "";
            }
            closeDrawer();
        });
    });
</script>
@endsection
