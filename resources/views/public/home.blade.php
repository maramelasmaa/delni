@extends('public.layout')

@section('title', config('app.name') . ' - خدمات قريبة منك')

@php
    $isSearchView = isset($profiles);
    $resultCount = $isSearchView ? ($profiles?->total() ?? $profiles?->count() ?? 0) : 0;
    $serviceChips = isset($subcategories) ? $subcategories->filter(fn($s) => ($s->discoverable_profiles_count ?? 0) > 0)->take(12) : collect();
    $activeCategorySlug = request('category') ?: (($categories ?? collect())->firstWhere('id', (int) request('category_id'))?->slug);
    $activeServiceSlug = request('service') ?: (($subcategories ?? collect())->firstWhere('id', (int) request('subcategory_id'))?->slug);
    $activeCitySlug = request('city') ?: (($cities ?? collect())->firstWhere('id', (int) request('city_id'))?->slug);
@endphp

@section('content')
<div class="lp-wrapper">

    {{-- Search hero --}}
    <section class="hp-hero">
        <div class="hp-hero__top">
            <div>
                <p class="hp-eyebrow">ماذا تحتاج اليوم؟</p>
                <h1 class="hp-title">اعثر على الخدمة المناسبة لك</h1>
            </div>
            <a href="{{ route('public.top-rated') }}" class="hp-star-btn" aria-label="الأعلى تقييماً">
                <x-render-icon icon="heroicon-o-star" />
            </a>
        </div>

        <form method="GET" action="{{ route('public.search') }}" class="hp-search" id="homeSearchForm" data-live-search-form>
            <label class="hp-search__field">
                <x-render-icon icon="heroicon-o-magnifying-glass" />
                <input
                    type="search"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    maxlength="100"
                    placeholder="مثال: تكييف، محامي، تصوير..."
                    autocomplete="off"
                    data-live-search-input
                >
                <button type="submit" class="hp-search__icon-btn" aria-label="بحث">
                    <x-render-icon icon="heroicon-o-magnifying-glass" />
                </button>
            </label>

            <div class="hp-search__selects">
                <select name="category" class="lp-filter-select" data-live-search-control>
                    <option value="">كل الفئات</option>
                    @foreach(($categories ?? collect()) as $category)
                        <option value="{{ $category->slug }}" @selected($activeCategorySlug === $category->slug)>
                            {{ $category->localized_name ?? $category->name }}
                        </option>
                    @endforeach
                </select>

                <select name="service" class="lp-filter-select" data-live-search-control>
                    <option value="">كل الخدمات</option>
                    @foreach(($subcategories ?? collect())->groupBy('category_id') as $group)
                        @php $parentCategory = $group->first()?->category; @endphp
                        <optgroup label="{{ $parentCategory?->localized_name ?? $parentCategory?->name ?? 'خدمات' }}">
                            @foreach($group as $subcategory)
                                <option value="{{ $subcategory->slug }}" data-category-slug="{{ $parentCategory?->slug }}" @selected($activeServiceSlug === $subcategory->slug)>
                                    {{ $subcategory->localized_name ?? $subcategory->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>

                <select name="city" class="lp-filter-select" data-live-search-control>
                    <option value="">كل المدن</option>
                    @foreach(($cities ?? collect()) as $city)
                        <option value="{{ $city->slug }}" @selected($activeCitySlug === $city->slug)>
                            {{ $city->localized_name ?? $city->name }}
                        </option>
                    @endforeach
                </select>

                @if(isset($providerTypes))
                    <select name="provider_type" class="lp-filter-select" data-live-search-control>
                        <option value="">كل الأنواع</option>
                        @foreach($providerTypes as $code => $name)
                            <option value="{{ $code }}" @selected((string)request('provider_type') === (string)$code)>
                                {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <noscript>
                <button type="submit" class="hp-search__btn">
                    <x-render-icon icon="heroicon-o-magnifying-glass" />
                    <span>بحث</span>
                </button>
            </noscript>
        </form>
    </section>

    @if($isSearchView)
        {{-- Search results --}}
        <div class="lp-results lp-results--search">
            <div class="lp-results-head">
                <div>
                    <span>نتائج البحث</span>
                    <h2>{{ number_format($resultCount) }} نتيجة</h2>
                </div>
                <a href="{{ route('home') }}" class="hp-clear-link">مسح</a>
            </div>

            @if($profiles && $profiles->count() > 0)
                <x-provider-grid :providers="$profiles" :columns="2" />

                @if(method_exists($profiles, 'hasPages') && $profiles->hasPages())
                    <nav class="lp-pagination" aria-label="Pagination">
                        @if($profiles->onFirstPage())
                            <span class="is-disabled">السابق</span>
                        @else
                            <a href="{{ $profiles->previousPageUrl() }}">السابق</a>
                        @endif
                        <strong>{{ $profiles->currentPage() }} / {{ $profiles->lastPage() }}</strong>
                        @if($profiles->hasMorePages())
                            <a href="{{ $profiles->nextPageUrl() }}">التالي</a>
                        @else
                            <span class="is-disabled">التالي</span>
                        @endif
                    </nav>
                @endif
            @else
                <x-empty-state
                    icon="heroicon-o-magnifying-glass"
                    title="لا توجد نتائج"
                    message="جرّب كلمة أخرى، أو اختر مدينة مختلفة."
                    actionLabel="مسح البحث"
                    actionUrl="{{ route('home') }}"
                />
            @endif
        </div>

    @else
        {{-- Browse: categories --}}
        <section class="hp-section">
            <div class="hp-section__head">
                <div>
                    <span class="hp-section__label">تصفح</span>
                    <h2 class="hp-section__title">الفئات</h2>
                </div>
                <a href="{{ route('public.categories') }}" class="hp-section__more">عرض الكل</a>
            </div>

            <div class="hp-cat-row">
                @foreach(($categories ?? collect())->filter(fn($c) => ($c->discoverable_profiles_count ?? 0) > 0)->take(10) as $category)
                    <a href="{{ route('public.category', $category->slug ?? $category->id) }}" class="hp-cat">
                        <span class="hp-cat__icon">
                            @if($category->icon)
                                <x-svg-icon :icon="$category->icon" size="20" />
                            @else
                                <x-render-icon icon="heroicon-o-briefcase" />
                            @endif
                        </span>
                        <strong>{{ $category->localized_name ?? $category->name }}</strong>
                        <small>{{ number_format((int)($category->discoverable_profiles_count ?? 0)) }}</small>
                    </a>
                @endforeach
            </div>

            @if($serviceChips->isNotEmpty())
                <div class="lp-chips lp-chips--service" aria-label="خدمات">
                    @foreach($serviceChips as $subcategory)
                        <a href="{{ route('public.subcategory', $subcategory->slug) }}" class="lp-chip">
                            {{ $subcategory->localized_name ?? $subcategory->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Featured providers --}}
        <section class="hp-section">
            <div class="hp-section__head">
                <div>
                    <span class="hp-section__label">من الدليل</span>
                    <h2 class="hp-section__title">مزودين مميزين</h2>
                </div>
                <a href="{{ route('public.categories') }}" class="hp-section__more">تصفح الكل</a>
            </div>

            @php
                $displayProviders = isset($suggestedProviders) && $suggestedProviders->count() > 0
                    ? $suggestedProviders
                    : (isset($featuredProviders) ? $featuredProviders->take(6) : collect());
            @endphp

            @if($displayProviders->count() > 0)
                <x-provider-grid :providers="$displayProviders" :columns="2" />
            @else
                <x-empty-state
                    icon="heroicon-o-briefcase"
                    title="لا يوجد مزودون حالياً"
                    message="عد لاحقاً، أو ابحث باسم خدمة محددة."
                />
            @endif
        </section>

        {{-- Provider CTA --}}
        <div class="lp-cta">
            <div>
                <span>تقدم خدمة؟</span>
                <h2>اجعل ملفك مرئياً للعملاء</h2>
                <p>أنشئ حساب مزود وابدأ في الظهور في نتائج البحث والفئات.</p>
            </div>
            <a href="{{ $ctaWhatsappUrl ?? route('contact') }}"
               @if($ctaWhatsappUrl ?? false) target="_blank" rel="noopener" @endif>سجّل كمزود</a>
        </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('homeSearchForm');
        const catSelect = form?.querySelector('select[name="category"]');
        const subSelect = form?.querySelector('select[name="service"]');
        const keywordInput = form?.querySelector('[data-live-search-input]');
        let keywordTimer;
        let isSubmitting = false;

        const syncSubcategories = () => {
            if (!catSelect || !subSelect) return;
            const categorySlug = catSelect.value;
            Array.from(subSelect.options).forEach(opt => {
                if (!opt.value) { opt.hidden = opt.disabled = false; return; }
                const match = !categorySlug || opt.dataset.categorySlug === categorySlug;
                opt.hidden = !match;
                opt.disabled = !match;
            });
            const sel = subSelect.selectedOptions[0];
            if (sel?.disabled) subSelect.value = '';
        };

        const submitSearch = () => {
            if (!form || isSubmitting) return;
            isSubmitting = true;
            form.classList.add('is-applying');
            form.setAttribute('aria-busy', 'true');
            form.querySelectorAll('[name="page"]').forEach(field => field.remove());
            form.querySelectorAll('input, select').forEach(field => {
                if (field.name && field.value === '') {
                    field.disabled = true;
                }
            });
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }
            form.submit();
        };

        form?.querySelectorAll('[data-live-search-control]').forEach(control => {
            control.addEventListener('change', () => {
                if (control === catSelect) {
                    syncSubcategories();
                }
                submitSearch();
            });
        });

        keywordInput?.addEventListener('input', () => {
            window.clearTimeout(keywordTimer);
            keywordTimer = window.setTimeout(() => {
                const value = keywordInput.value.trim();
                if (value.length === 0 || value.length >= 2) {
                    submitSearch();
                }
            }, 450);
        });

        keywordInput?.addEventListener('keydown', event => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            submitSearch();
        });

        syncSubcategories();
    });
</script>
@endpush

@push('styles')
<style>
    /* Home search hero */
    .hp-hero {
        display: grid;
        gap: .9rem;
        padding: 1rem;
        border: 1px solid var(--delni-border);
        border-radius: 22px;
        background: linear-gradient(180deg, #fff 0%, #F8FAFC 100%);
        box-shadow: var(--delni-shadow-sm);
    }

    .hp-hero__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .hp-eyebrow {
        margin: 0 0 .15rem;
        color: var(--delni-primary);
        font-size: .72rem;
        font-weight: 900;
    }

    .hp-title {
        margin: 0;
        color: var(--delni-navy);
        font-size: 1.4rem;
        font-weight: 950;
        line-height: 1.2;
    }

    .hp-star-btn {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        background: #FFF7ED;
        border: 1px solid #FED7AA;
        color: var(--delni-primary);
    }

    .hp-star-btn svg { width: 20px; height: 20px; }

    /* Search form */
    .hp-search {
        display: grid;
        gap: .65rem;
        transition: opacity .16s ease;
    }
    .hp-search.is-applying {
        opacity: .68;
        pointer-events: none;
    }

    .hp-search__field {
        display: flex;
        align-items: center;
        gap: .5rem;
        min-height: 50px;
        padding: 0 .85rem;
        border-radius: 14px;
        border: 1px solid var(--delni-border);
        background: #fff;
    }

    .hp-search__field svg {
        width: 18px;
        height: 18px;
        color: #94A3B8;
        flex-shrink: 0;
    }

    .hp-search__field input {
        flex: 1;
        min-width: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--delni-navy);
        font: inherit;
        font-size: .92rem;
        font-weight: 750;
    }

    .hp-search__icon-btn {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 12px;
        background: var(--delni-primary);
        color: #fff;
        cursor: pointer;
    }
    .hp-search__icon-btn svg {
        width: 17px;
        height: 17px;
        color: currentColor;
    }

    .hp-search__selects {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
    }

    .hp-search__selects .lp-filter-select {
        border-radius: 14px;
        min-height: 48px;
        padding: 0 .75rem;
        width: 100%;
    }

    .hp-search__btn {
        min-height: 50px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        border-radius: 14px;
        border: 0;
        background: var(--delni-primary);
        color: #fff;
        font: inherit;
        font-size: .92rem;
        font-weight: 950;
        cursor: pointer;
    }

    .hp-search__btn svg { width: 18px; height: 18px; }

    /* Section header */
    .hp-section { margin-top: 1.15rem; }

    .hp-section__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .75rem;
        padding-inline: .2rem;
    }

    .hp-section__label {
        display: block;
        color: var(--delni-primary);
        font-size: .7rem;
        font-weight: 900;
        margin-bottom: .1rem;
    }

    .hp-section__title {
        margin: 0;
        color: var(--delni-navy);
        font-size: 1.05rem;
        font-weight: 950;
    }

    .hp-section__more {
        flex-shrink: 0;
        color: var(--delni-primary);
        font-size: .78rem;
        font-weight: 900;
    }

    /* Category strip */
    .hp-cat-row {
        display: flex;
        gap: .65rem;
        overflow-x: auto;
        scrollbar-width: none;
        padding: .1rem .1rem .35rem;
        scroll-snap-type: x mandatory;
    }
    .hp-cat-row::-webkit-scrollbar { display: none; }

    .hp-cat {
        width: 106px;
        min-width: 106px;
        min-height: 106px;
        flex-shrink: 0;
        display: grid;
        align-content: space-between;
        gap: .5rem;
        scroll-snap-align: start;
        padding: .8rem;
        border: 1px solid var(--delni-border);
        border-radius: 18px;
        background: #fff;
        text-decoration: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .hp-cat:active {
        border-color: rgba(241,98,15,.3);
        box-shadow: 0 4px 16px rgba(241,98,15,.1);
    }

    .hp-cat__icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: rgba(241,98,15,.07);
        color: var(--delni-primary);
    }
    .hp-cat__icon svg { width: 20px; height: 20px; }

    .hp-cat strong {
        display: block;
        color: var(--delni-navy);
        font-size: .8rem;
        line-height: 1.4;
    }

    .hp-cat small {
        color: #64748B;
        font-size: .7rem;
        font-weight: 850;
    }

    /* Search results head */
    .hp-clear-link {
        flex-shrink: 0;
        color: var(--delni-primary);
        font-size: .78rem;
        font-weight: 900;
    }

    @media (min-width: 640px) {
        .hp-title { font-size: 1.85rem; }
        .hp-search { grid-template-columns: 1fr; }
        .hp-search__field { grid-column: 1 / -1; }
        .hp-search__selects { grid-template-columns: repeat(4, minmax(130px, 1fr)); }
        .hp-search__selects .lp-filter-select { border-radius: 999px; min-height: 38px; }
    }

    @media (max-width: 400px) {
        .hp-search__selects { grid-template-columns: 1fr; }
    }

    [data-theme="dark"] .hp-hero {
        background: #1E293B;
        border-color: #334155;
    }
    [data-theme="dark"] .hp-hero .hp-title,
    [data-theme="dark"] .hp-section__title { color: #F1F5F9; }
    [data-theme="dark"] .hp-search__field { background: #0F172A; border-color: #334155; color: #F1F5F9; }
    [data-theme="dark"] .hp-search__field input { background: transparent; color: #F1F5F9; }
    [data-theme="dark"] .hp-search__field input::placeholder { color: #475569; }
    [data-theme="dark"] .hp-search__icon-btn { background: var(--delni-primary); color: #fff; }
    [data-theme="dark"] .hp-search__btn { background: var(--delni-primary); color: #fff; }
    [data-theme="dark"] .hp-hero { background: #1E293B; border-color: #334155; }
    [data-theme="dark"] .hp-star-btn { background: #0F172A; border-color: #334155; color: #F59E0B; }
    [data-theme="dark"] .hp-cat {
        background: #1E293B;
        border-color: #334155;
    }
    [data-theme="dark"] .hp-cat strong { color: #F1F5F9; }
    [data-theme="dark"] .hp-section__label { color: var(--delni-primary); }
    [data-theme="dark"] .hp-section__more { color: #94A3B8; }
    [data-theme="dark"] .hp-clear-link { color: #94A3B8; }
</style>
@endpush
@endsection
