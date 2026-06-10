@extends('public.layout')

@section('title', __('messages.public.all_categories') . ' - ' . config('app.name'))

@section('content')
{{-- Compact Unified Page Header --}}
<section class="all-categories-hero">
    <div class="container">
        <div class="categories-hero-content">
            <h1>جميع الفئات</h1>
            <p>استكشف الفئات المتاحة على منبر دلني الموثوق للخدمات</p>
        </div>
    </div>
</section>

{{-- Main Directory Body Interface --}}
<section class="all-categories-section">
    <div class="container">
        <div class="categories-layout-grid">
            @forelse($categories as $category)
                <div class="category-panel-card">
                    {{-- Fixed Layout Header Area --}}
                    <div class="panel-main-interactive">
                        <div class="panel-identity-block">
                            <div class="category-icon-circle">
                                <x-svg-icon :icon="$category->getRelation('icon')" />
                            </div>
                            <div class="category-header-text">
                                <h2 class="category-title">{{ $category->localized_name ?? $category->name }}</h2>
                                <span class="category-provider-count">
                                    {{ $category->discoverable_profiles_count ?? 0 }} مزود خدمة
                                </span>
                            </div>
                        </div>

                        {{-- Action Controls: Native View Profile Route or Expand Children --}}
                        <div class="panel-action-controls">
                            @if($category->subcategories->isNotEmpty())
                                <button type="button"
                                        class="btn-trigger-drawer"
                                        data-category-id="drawer-{{ $category->id }}"
                                        aria-label="عرض الفئات الفرعية">
                                    <span>الفئات الفرعية</span>
                                    <x-render-icon icon="heroicon-o-chevron-left" class="icon-indicator" />
                                </button>
                            @else
                                <a href="{{ route('public.category', $category->slug) }}" class="btn-panel-link">
                                    <span>تصفح الكل</span>
                                    <x-render-icon icon="heroicon-o-arrow-left" />
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Scalable Subcategories Drawer Element --}}
                    @if($category->subcategories->isNotEmpty())
                        <div id="drawer-{{ $category->id }}" class="subcategories-panel-drawer">
                            <div class="drawer-inner-scroller">
                                <div class="mobile-drawer-header">
                                    <h3>{{ $category->localized_name ?? $category->name }}</h3>
                                    <button type="button" class="btn-close-drawer" data-close="drawer-{{ $category->id }}">✕</button>
                                </div>

                                <div class="subcategories-flex-list">
                                    {{-- Global fallback choice to view entire parent framework content safely --}}
                                    <a href="{{ route('public.category', $category->slug) }}" class="subcategory-link-item highlight-all">
                                        <span class="sub-name">عرض كافة خدمات الفئة الرئيسية ←</span>
                                    </a>

                                    @foreach($category->subcategories as $subcategory)
                                        <a href="{{ route('public.subcategory', $subcategory->slug) }}" class="subcategory-link-item">
                                            <div class="sub-meta-info">
                                                <span class="sub-name">{{ $subcategory->localized_name ?? $subcategory->name }}</span>
                                                <span class="sub-count">{{ $subcategory->discoverable_profiles_count ?? 0 }} مزود</span>
                                            </div>
                                            <x-render-icon icon="heroicon-o-chevron-left" class="sub-arrow" />
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state-card">
                    <x-render-icon icon="heroicon-o-folder-open" class="empty-icon" />
                    <p>لا توجد فئات متاحة حالياً على المنصة.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

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

{{-- Global Overlay Shade Element for Side Drawers --}}
<div class="layout-drawer-backdrop" id="drawerBackdrop"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const triggers = document.querySelectorAll('.btn-trigger-drawer');
        const closeButtons = document.querySelectorAll('.btn-close-drawer');
        const backdrop = document.getElementById('drawerBackdrop');

        // Drawer toggle mechanism
        triggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const drawerId = trigger.getAttribute('data-category-id');
                const targetDrawer = document.getElementById(drawerId);

                if (targetDrawer) {
                    targetDrawer.classList.add('is-active');
                    backdrop.classList.add('is-active');
                    document.body.style.overflow = 'hidden'; // Block background viewport scrolls
                }
            });
        });

        // Close functions handler
        const closeAllDrawers = () => {
            document.querySelectorAll('.subcategories-panel-drawer').forEach(d => d.classList.remove('is-active'));
            backdrop.classList.remove('is-active');
            document.body.style.overflow = '';
        };

        closeButtons.forEach(btn => btn.addEventListener('click', closeAllDrawers));
        backdrop.addEventListener('click', closeAllDrawers);
    });
</script>

<style>
    :root {
        --brand-orange: #F1620F;
        --brand-orange-hover: #D7530A;
        --dark-blue: #0B1A34;
        --border-gray: #EAEAEA;
        --bg-light: #FAFAFA;
        --transition-standard: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Minimal Uniform Header Design */
    .all-categories-hero {
        background: linear-gradient(135deg, rgba(11, 26, 52, 0.95), rgba(20, 40, 77, 0.98)),
                    url('{{ asset('images/herobackground2.png') }}') center/cover no-repeat;
        padding: 3.5rem 0;
        color: #FFFFFF;
        text-align: center;
    }

    .categories-hero-content h1 {
        font-size: clamp(2rem, 4vw, 2.75rem);
        font-weight: 800;
        margin: 0 0 0.5rem;
    }

    .categories-hero-content p {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.75);
        margin: 0;
    }

    /* Professional Grid Structure */
    .all-categories-section {
        padding: 3rem 0;
        background: var(--bg-light);
    }

    .categories-layout-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.25rem;
    }

    /* Perfectly Symmetrical Visual Anchors */
    .category-panel-card {
        background: #FFFFFF;
        border: 1px solid var(--border-gray);
        border-radius: 18px;
        padding: 1.25rem;
        box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
        transition: var(--transition-standard);
    }

    .category-panel-card:hover {
        border-color: rgba(241, 98, 15, 0.3);
        box-shadow: 0 10px 25px rgba(11, 26, 52, 0.06);
    }

    .panel-main-interactive {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        gap: 1.25rem;
    }

    .panel-identity-block {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .category-icon-circle {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: rgba(241, 98, 15, 0.06);
        color: var(--brand-orange);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .category-icon-circle svg {
        width: 24px;
        height: 24px;
    }

    .category-header-text {
        overflow: hidden;
    }

    .category-title {
        margin: 0;
        color: var(--dark-blue);
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .category-provider-count {
        display: block;
        margin-top: 0.15rem;
        color: #64748B;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* Interactive Clean Action Triggers */
    .panel-action-controls {
        border-top: 1px solid var(--border-gray);
        padding-top: 0.85rem;
    }

    .btn-trigger-drawer, .btn-panel-link {
        width: 100%;
        height: 40px;
        border-radius: 10px;
        border: 1px solid var(--border-gray);
        background: #FFFFFF;
        color: var(--dark-blue);
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1rem;
        text-decoration: none;
        transition: var(--transition-standard);
    }

    .btn-trigger-drawer:hover, .btn-panel-link:hover {
        border-color: var(--brand-orange);
        color: var(--brand-orange);
        background: rgba(241, 98, 15, 0.02);
    }

    .btn-trigger-drawer svg, .btn-panel-link svg {
        width: 16px;
        height: 16px;
        transition: transform 0.2s ease;
    }

    .btn-trigger-drawer:hover .icon-indicator {
        transform: translateX(-4px); /* Moves arrow inline with Arabic layout flow */
    }

    /* Scalable Slide-Out Navigation Drawer Engine (Desktop) */
    .subcategories-panel-drawer {
        position: fixed;
        top: 0;
        left: -420px; /* Hidden off-canvas by default */
        width: 400px;
        height: 100vh;
        background: #FFFFFF;
        box-shadow: 25px 0 50px -12px rgba(11, 26, 52, 0.25);
        z-index: 1100;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Flip properties natively for Arabic RTL alignment */
    [dir="rtl"] .subcategories-panel-drawer {
        left: auto;
        right: -420px;
        box-shadow: -25px 0 50px -12px rgba(11, 26, 52, 0.25);
    }

    [dir="rtl"] .subcategories-panel-drawer.is-active {
        right: 0;
        left: auto;
    }

    .subcategories-panel-drawer.is-active {
        left: 0;
    }

    .drawer-inner-scroller {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 2rem 1.5rem;
    }

    .mobile-drawer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-gray);
        padding-bottom: 1rem;
    }

    .mobile-drawer-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--dark-blue);
    }

    .btn-close-drawer {
        background: var(--bg-light);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .subcategories-flex-list {
        flex: 1;
        overflow-y: auto;
        padding-right: 4px;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    /* Drawer Sub-Item Hyperlinks */
    .subcategory-link-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 1rem;
        background: var(--bg-light);
        border-radius: 10px;
        text-decoration: none;
        border: 1px solid transparent;
        transition: var(--transition-standard);
    }

    .subcategory-link-item:hover {
        background: #FFFFFF;
        border-color: var(--brand-orange);
    }

    .subcategory-link-item.highlight-all {
        background: rgba(241, 98, 15, 0.06);
        color: var(--brand-orange);
        font-weight: 700;
    }

    .sub-meta-info {
        display: flex;
        flex-direction: column;
    }

    .sub-name {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--dark-blue);
    }

    .subcategory-link-item:hover .sub-name {
        color: var(--brand-orange);
    }

    .sub-count {
        font-size: 0.75rem;
        color: #64748B;
        margin-top: 0.15rem;
    }

    .sub-arrow {
        width: 14px;
        height: 14px;
        color: #94A3B8;
    }

    /* Dim Backdrop Layer Overlay */
    .layout-drawer-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(11, 26, 52, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1050;
        display: none;
    }

    .layout-drawer-backdrop.is-active {
        display: block;
    }

    .empty-state-card {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem;
        background: #FFFFFF;
        border-radius: 18px;
        border: 1px dashed var(--border-gray);
    }

    /* Screen Adaptations: Responsive Transformation to Mobile Bottom Sheets */
    @media (max-width: 640px) {
        .subcategories-panel-drawer {
            width: 100% !important;
            height: 75vh !important;
            top: auto !important;
            bottom: -80vh !important;
            left: 0 !important;
            right: 0 !important;
            border-radius: 24px 24px 0 0;
            box-shadow: 0 -15px 30px rgba(0,0,0,0.15) !important;
            transition: bottom 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .subcategories-panel-drawer.is-active {
            bottom: 0 !important;
        }

        [dir="rtl"] .subcategories-panel-drawer,
        [dir="rtl"] .subcategories-panel-drawer.is-active {
            left: 0 !important;
            right: 0 !important;
        }
    }

    /* Provider CTA Section */
    .provider-cta-section {
        padding: 3rem 0;
        background: linear-gradient(135deg, rgba(241, 98, 15, 0.08), rgba(241, 98, 15, 0.04));
    }

    .provider-cta-card {
        background: #FFFFFF;
        border: 2px solid #F1620F;
        border-radius: 20px;
        padding: clamp(2rem, 5vw, 3rem);
        text-align: center;
        box-shadow: 0 10px 30px rgba(241, 98, 15, 0.1);
    }

    .cta-title {
        font-size: clamp(1.5rem, 4vw, 2.2rem);
        font-weight: 900;
        color: #0B1A34;
        margin-bottom: 1rem;
        letter-spacing: -0.03em;
    }

    .cta-description {
        font-size: clamp(0.9rem, 2vw, 1.05rem);
        color: #64748B;
        margin-bottom: 1.5rem;
        line-height: 1.7;
        max-width: 500px;
        margin-inline: auto;
    }

    .cta-button {
        display: inline-block;
        background: #F1620F;
        color: white;
        padding: 0.85rem 2rem;
        border-radius: 12px;
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 700;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #F1620F;
    }

    .cta-button:hover {
        background: transparent;
        color: #F1620F;
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
