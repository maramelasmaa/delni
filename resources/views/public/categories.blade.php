@extends('public.layout')

@section('title', 'جميع الفئات - ' . config('app.name'))

@section('content')
<div class="lp-wrapper market-browse">
    <x-marketplace-header
        eyebrow="دليل الخدمات"
        title="تصفح الفئات"
        :count="$categories->count() . ' فئة'"
        :back-url="route('home')"
        back-label="الرئيسية"
        description="اختر المجال المناسب ثم انتقل للخدمات الفرعية ومزودي الخدمة المتاحين."
    />

    <div class="market-browse__toolbar">
        <label class="market-search">
            <x-render-icon icon="heroicon-o-magnifying-glass" />
            <input type="search" id="categorySearch" placeholder="ابحث عن فئة أو خدمة" autocomplete="off">
        </label>
    </div>

    <section class="market-browse__grid" id="categoryGrid">
        @forelse($categories as $category)
            <x-category-discovery-card :category="$category" />
        @empty
            <x-empty-state
                icon="heroicon-o-folder-open"
                title="لا توجد فئات"
                message="لا توجد فئات متاحة حالياً."
            />
        @endforelse
    </section>

    <div class="lp-cta">
        <div>
            <span>تقدم خدمة؟</span>
            <h2>اجعل ملفك مرئيا للعملاء</h2>
        </div>
        <a href="{{ $ctaWhatsappUrl ?? route('contact') }}"
           @if($ctaWhatsappUrl ?? false) target="_blank" rel="noopener" @endif>سجل كمزود</a>
    </div>
</div>

@push('styles')
<style>
    .market-browse {
        display: grid;
        gap: .85rem;
        max-width: 1120px;
        margin-inline: auto;
    }

    .market-browse__toolbar {
        display: grid;
        gap: .65rem;
        position: sticky;
        top: calc(var(--pwa-header-height) + env(safe-area-inset-top) + .35rem);
        z-index: 4;
    }

    .market-search {
        min-height: 46px;
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: 0 .85rem;
        border: 1px solid var(--delni-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: var(--delni-shadow-sm);
    }

    .market-search svg {
        width: 19px;
        height: 19px;
        color: #94A3B8;
        flex: 0 0 auto;
    }

    .market-search input {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--delni-navy);
        font: inherit;
        font-size: .9rem;
        font-weight: 750;
    }

    .market-search input::placeholder {
        color: #94A3B8;
    }

    .market-browse__grid {
        display: grid;
        gap: .65rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: stretch;
    }

    .cat-card.is-filtered {
        display: none;
    }

    [data-theme="dark"] .market-search {
        background: #1E293B;
        border-color: #334155;
    }
    [data-theme="dark"] .market-search input { color: #F1F5F9; }
    @media (min-width: 760px) {
        .market-browse__grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: .85rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const input = document.getElementById('categorySearch');
        const cards = Array.from(document.querySelectorAll('#categoryGrid .cat-card'));
        if (!input || cards.length === 0) { return; }

        input.addEventListener('input', () => {
            const value = input.value.trim().toLowerCase();
            cards.forEach((card) => {
                card.classList.toggle('is-filtered', value !== '' && !card.textContent.toLowerCase().includes(value));
            });
        });
    })();
</script>
@endpush
@endsection
