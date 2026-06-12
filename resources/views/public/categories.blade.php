@extends('public.layout')

@section('title', 'جميع الفئات - ' . config('app.name'))

@section('content')
<div class="lp-wrapper">

    {{-- App page header --}}
    <header class="lp-header">
        <a href="{{ route('home') }}" class="lp-back" aria-label="الرئيسية">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">دلني</span>
            <h1 class="lp-title">جميع الفئات</h1>
            <span class="lp-count">{{ $categories->count() }} فئة</span>
        </div>
    </header>

    {{-- Category list --}}
    <div class="cats-grid">
        @forelse($categories as $category)
            <div class="cats-card">
                {{-- Main row --}}
                <div class="cats-row">
                    <div class="cats-icon">
                        @if($category->icon)
                            <x-svg-icon :icon="$category->icon" size="22" />
                        @else
                            <x-render-icon icon="heroicon-o-briefcase" />
                        @endif
                    </div>
                    <div class="cats-info">
                        <strong>{{ $category->localized_name ?? $category->name }}</strong>
                        <span>{{ number_format($category->discoverable_profiles_count ?? 0) }} مزود</span>
                    </div>
                    <div class="cats-actions">
                        @if($category->subcategories->isNotEmpty())
                            <button type="button"
                                    class="cats-expand-btn"
                                    data-drawer="cats-drawer-{{ $category->id }}"
                                    aria-expanded="false">
                                <x-render-icon icon="heroicon-o-chevron-left" />
                            </button>
                        @endif
                        <a href="{{ route('public.category', $category->slug) }}" class="cats-browse-btn">
                            تصفح
                        </a>
                    </div>
                </div>

                {{-- Subcategories inline expand --}}
                @if($category->subcategories->isNotEmpty())
                    <div class="cats-subs" id="cats-drawer-{{ $category->id }}" hidden>
                        <a href="{{ route('public.category', $category->slug) }}" class="cats-sub-link cats-sub-link--all">
                            كل خدمات {{ $category->localized_name ?? $category->name }}
                        </a>
                        @foreach($category->subcategories as $subcategory)
                            <a href="{{ route('public.subcategory', $subcategory->slug) }}" class="cats-sub-link">
                                <span>{{ $subcategory->localized_name ?? $subcategory->name }}</span>
                                <small>{{ $subcategory->discoverable_profiles_count ?? 0 }}</small>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <x-empty-state
                icon="heroicon-o-folder-open"
                title="لا توجد فئات"
                message="لا توجد فئات متاحة حالياً."
            />
        @endforelse
    </div>

    {{-- Provider CTA --}}
    <div class="lp-cta">
        <div>
            <span>تقدم خدمة؟</span>
            <h2>خلّي ملفك يظهر للناس</h2>
        </div>
        <a href="{{ route('register') }}">سجل كمزود</a>
    </div>

</div>

@push('styles')
<style>
    .cats-grid {
        display: flex;
        flex-direction: column;
        gap: .6rem;
        margin-top: .65rem;
    }

    .cats-card {
        background: #fff;
        border: 1px solid var(--delni-border);
        border-radius: 18px;
        overflow: hidden;
    }

    .cats-row {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .9rem 1rem;
    }

    .cats-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(241,98,15,.07);
        color: var(--delni-primary);
    }

    .cats-icon svg { width: 22px; height: 22px; }

    .cats-info {
        flex: 1;
        min-width: 0;
    }

    .cats-info strong {
        display: block;
        color: var(--delni-navy);
        font-size: .92rem;
        font-weight: 900;
        line-height: 1.3;
    }

    .cats-info span {
        display: block;
        margin-top: .1rem;
        color: #64748B;
        font-size: .74rem;
        font-weight: 750;
    }

    .cats-actions {
        display: flex;
        align-items: center;
        gap: .45rem;
        flex-shrink: 0;
    }

    .cats-expand-btn {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid var(--delni-border);
        background: #F8FAFC;
        color: var(--delni-navy);
        cursor: pointer;
        transition: transform .2s ease;
    }

    .cats-expand-btn[aria-expanded="true"] {
        transform: rotate(-90deg);
        border-color: rgba(241,98,15,.3);
        background: #FFF7ED;
        color: var(--delni-primary);
    }

    .cats-expand-btn svg { width: 16px; height: 16px; }

    .cats-browse-btn {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        padding: .45rem .8rem;
        border-radius: 10px;
        background: var(--delni-primary);
        color: #fff;
        font-size: .78rem;
        font-weight: 900;
        text-decoration: none;
    }

    /* Subcategories panel */
    .cats-subs {
        border-top: 1px solid var(--delni-border);
        padding: .65rem .85rem;
        display: flex;
        flex-direction: column;
        gap: .3rem;
        background: #FCFBFB;
    }

    .cats-sub-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        min-height: 42px;
        padding: .5rem .65rem;
        border-radius: 12px;
        background: #fff;
        border: 1px solid transparent;
        color: var(--delni-navy);
        font-size: .84rem;
        font-weight: 800;
        text-decoration: none;
        transition: border-color .15s;
    }

    .cats-sub-link:active,
    .cats-sub-link:hover {
        border-color: rgba(241,98,15,.2);
        color: var(--delni-primary);
    }

    .cats-sub-link--all {
        color: var(--delni-primary);
        background: rgba(241,98,15,.05);
        font-weight: 900;
    }

    .cats-sub-link small {
        flex-shrink: 0;
        min-width: 28px;
        min-height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #F1F5F9;
        color: #64748B;
        font-size: .7rem;
        font-weight: 900;
    }

    /* CTA */
    .lp-cta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1.2rem;
        padding: 1rem 1.1rem;
        border-radius: 20px;
        background: var(--delni-navy);
        color: #fff;
    }

    .lp-cta span {
        display: block;
        color: var(--delni-primary);
        font-size: .72rem;
        font-weight: 900;
        margin-bottom: .2rem;
    }

    .lp-cta h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 950;
    }

    .lp-cta a {
        flex-shrink: 0;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        padding: .55rem 1rem;
        border-radius: 12px;
        background: #fff;
        color: var(--delni-navy);
        font-size: .82rem;
        font-weight: 950;
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.cats-expand-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const drawerId = btn.dataset.drawer;
            const panel = document.getElementById(drawerId);
            const open = btn.getAttribute('aria-expanded') === 'true';

            btn.setAttribute('aria-expanded', String(!open));
            panel.hidden = open;
        });
    });
</script>
@endpush
@endsection
