@extends('public.layout')

@section('title', config('app.name') . ' - خدمات قريبة منك')

@php
    $isSearchView = isset($profiles);
    $resultCount = $isSearchView ? ($profiles?->total() ?? $profiles?->count() ?? 0) : 0;
    $suggestedCount = isset($suggestedProviders) ? $suggestedProviders->count() : 0;
    $serviceChips = isset($subcategories) ? $subcategories->take(12) : collect();
@endphp

@section('content')
<main class="directory-home">
    <section class="directory-hero">
        <div class="directory-hero__top">
            <div>
                <p class="directory-eyebrow">شن تبي تنجز اليوم؟</p>
                <h1>لقى الخدمة اللي تحتاجها</h1>
            </div>

            <a href="{{ route('public.top-rated') }}" class="directory-icon-link" aria-label="الأعلى تقييماً">
                <x-render-icon icon="heroicon-o-star" />
            </a>
        </div>

        <form method="GET" action="{{ route('public.search') }}" class="directory-search">
            <label class="directory-search__input">
                <span>اكتب الخدمة أو اسم المزود</span>
                <div>
                    <x-render-icon icon="heroicon-o-magnifying-glass" />
                    <input
                        type="search"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        maxlength="100"
                        placeholder="مثال: تكييف، محامي، تصوير..."
                    >
                </div>
            </label>

            <div class="directory-search__filters">
                <label>
                    <span>الفئة</span>
                    <select name="category_id">
                        <option value="">كل الفئات</option>
                        @foreach(($categories ?? collect()) as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                {{ $category->localized_name ?? $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>الخدمة</span>
                    <select name="subcategory_id">
                        <option value="">كل الخدمات</option>
                        @foreach(($subcategories ?? collect())->groupBy('category_id') as $group)
                            @php($parentCategory = $group->first()?->category)
                            <optgroup label="{{ $parentCategory?->localized_name ?? $parentCategory?->name ?? 'خدمات' }}">
                                @foreach($group as $subcategory)
                                    <option value="{{ $subcategory->id }}" data-category-id="{{ $subcategory->category_id }}" @selected((string) request('subcategory_id') === (string) $subcategory->id)>
                                        {{ $subcategory->localized_name ?? $subcategory->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>المدينة</span>
                    <select name="city_id">
                        <option value="">كل المدن</option>
                        @foreach(($cities ?? collect()) as $city)
                            <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                                {{ $city->localized_name ?? $city->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                @if(isset($providerTypes))
                    <label>
                        <span>نوع المزود</span>
                        <select name="provider_type">
                            <option value="">كل الأنواع</option>
                            @foreach($providerTypes as $code => $name)
                                <option value="{{ $code }}" @selected((string) request('provider_type') === (string) $code)>
                                    {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </div>

            <button type="submit" class="directory-search__button">
                <x-render-icon icon="heroicon-o-magnifying-glass" />
                <span>بحث</span>
            </button>
        </form>
    </section>

    @if($isSearchView)
        <section class="directory-section">
            <div class="directory-section__head">
                <div>
                    <span>نتائج البحث</span>
                    <h2>{{ number_format($resultCount) }} نتيجة</h2>
                </div>

                <a href="{{ route('home') }}">مسح</a>
            </div>

            @if($profiles && $profiles->count() > 0)
                <x-provider-grid :providers="$profiles" :columns="2" />

                @if(method_exists($profiles, 'hasPages') && $profiles->hasPages())
                    <nav class="directory-pagination" aria-label="Pagination">
                        @if($profiles->onFirstPage())
                            <span class="is-disabled">السابق</span>
                        @else
                            <a href="{{ $profiles->previousPageUrl() }}">السابق</a>
                        @endif

                        <strong>صفحة {{ $profiles->currentPage() }} من {{ $profiles->lastPage() }}</strong>

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
                    title="ما لقيناش نتائج"
                    message="جرّب كلمة أبسط، أو اختار مدينة ثانية."
                    actionLabel="مسح البحث"
                    actionUrl="{{ route('home') }}"
                />
            @endif
        </section>
    @else
        <section class="directory-section">
            <div class="directory-section__head">
                <div>
                    <span>تصفح</span>
                    <h2>الفئات</h2>
                </div>

                <a href="{{ route('public.categories') }}">عرض الكل</a>
            </div>

            <div class="directory-category-row">
                @foreach(($categories ?? collect())->take(10) as $category)
                    <a href="{{ route('public.category', $category->slug ?? $category->id) }}" class="directory-category">
                        <span>
                            @if($category->icon)
                                <x-svg-icon :icon="$category->icon" size="20" />
                            @else
                                <x-render-icon icon="heroicon-o-briefcase" />
                            @endif
                        </span>
                        <strong>{{ $category->localized_name ?? $category->name }}</strong>
                        <small>{{ number_format((int) ($category->discoverable_profiles_count ?? 0)) }}</small>
                    </a>
                @endforeach
            </div>

            @if($serviceChips->isNotEmpty())
                <div class="directory-service-chips" aria-label="خدمات">
                    @foreach($serviceChips as $subcategory)
                        <a href="{{ route('public.subcategory', $subcategory->slug) }}">
                            {{ $subcategory->localized_name ?? $subcategory->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="directory-section">
            <div class="directory-section__head">
                <div>
                    <span>من الدليل</span>
                    <h2>مزودين قريبين منك</h2>
                </div>
            </div>

            @if($suggestedCount > 0)
                <x-provider-grid :providers="$suggestedProviders" :columns="2" />
            @elseif(isset($featuredProviders) && $featuredProviders->count() > 0)
                <x-provider-grid :providers="$featuredProviders->take(6)" :columns="2" />
            @else
                <x-empty-state
                    icon="heroicon-o-briefcase"
                    title="ما فيش مزودين حالياً"
                    message="ارجع بعد شوية، أو جرّب تبحث باسم خدمة محددة."
                />
            @endif
        </section>
    @endif

    <section class="directory-provider-cta">
        <div>
            <span>تقدم خدمة؟</span>
            <h2>خلّي ملفك يظهر للناس</h2>
            <p>افتح حساب مزود وخلي خدماتك تطلع في البحث والفئات.</p>
        </div>

        <a href="{{ route('register') }}">
            سجل كمزود
        </a>
    </section>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchForm = document.querySelector('.directory-search');
        const categorySelect = searchForm?.querySelector('select[name="category_id"]');
        const subcategorySelect = searchForm?.querySelector('select[name="subcategory_id"]');

        const syncSubcategories = () => {
            if (!categorySelect || !subcategorySelect) return;

            const categoryId = categorySelect.value;

            Array.from(subcategorySelect.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const matches = !categoryId || option.dataset.categoryId === categoryId;
                option.hidden = !matches;
                option.disabled = !matches;
            });

            const selected = subcategorySelect.selectedOptions[0];
            if (selected && selected.disabled) {
                subcategorySelect.value = '';
            }
        };

        categorySelect?.addEventListener('change', syncSubcategories);
        syncSubcategories();
    });
</script>
@endpush

@push('styles')
<style>
    .directory-home {
        width: min(100% - 1.25rem, 1120px);
        margin-inline: auto;
        padding: .85rem 0 2rem;
    }

    .directory-hero {
        display: grid;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid #E8EDF4;
        border-radius: 22px;
        background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
        box-shadow: 0 12px 32px rgba(11, 26, 52, .06);
    }

    .directory-hero__top,
    .directory-section__head,
    .directory-provider-cta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .directory-eyebrow,
    .directory-section__head span,
    .directory-provider-cta span {
        margin: 0 0 .18rem;
        color: #F1620F;
        font-size: .75rem;
        font-weight: 900;
    }

    .directory-hero h1,
    .directory-section__head h2,
    .directory-provider-cta h2 {
        margin: 0;
        color: #0B1A34;
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.25;
    }

    .directory-hero h1 {
        font-size: 1.45rem;
    }

    .directory-icon-link {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 14px;
        color: #F1620F;
        background: #FFF7ED;
        border: 1px solid #FED7AA;
    }

    .directory-icon-link svg {
        width: 21px;
        height: 21px;
    }

    .directory-search {
        display: grid;
        gap: .75rem;
    }

    .directory-search label {
        display: grid;
        gap: .32rem;
        color: #334155;
        font-size: .74rem;
        font-weight: 850;
    }

    .directory-search__input div,
    .directory-search select,
    .directory-search__button {
        min-height: 48px;
        border-radius: 14px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
    }

    .directory-search__input div {
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: 0 .8rem;
    }

    .directory-search__input svg {
        width: 19px;
        height: 19px;
        color: #94A3B8;
        flex: 0 0 auto;
    }

    .directory-search input,
    .directory-search select {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        color: #0B1A34;
        background: transparent;
        font-size: .92rem;
        font-weight: 750;
    }

    .directory-search select {
        padding: 0 .75rem;
    }

    .directory-search__filters {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem;
    }

    .directory-search__button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        color: #FFFFFF;
        background: #F1620F;
        border-color: #F1620F;
        font-weight: 950;
        cursor: pointer;
    }

    .directory-search__button svg {
        width: 18px;
        height: 18px;
    }

    .directory-section {
        margin-top: 1.15rem;
    }

    .directory-section__head {
        margin-bottom: .8rem;
        padding-inline: .2rem;
    }

    .directory-section__head h2,
    .directory-provider-cta h2 {
        font-size: 1.08rem;
    }

    .directory-section__head a {
        flex: 0 0 auto;
        color: #F1620F;
        font-size: .78rem;
        font-weight: 900;
    }

    .directory-category-row {
        display: flex;
        gap: .7rem;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding: .1rem .1rem .35rem;
    }

    .directory-category {
        width: 108px;
        min-width: 108px;
        min-height: 108px;
        display: grid;
        align-content: space-between;
        gap: .5rem;
        scroll-snap-align: start;
        padding: .8rem;
        border: 1px solid #E8EDF4;
        border-radius: 18px;
        background: #FFFFFF;
        box-shadow: 0 4px 16px rgba(11, 26, 52, .04);
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .directory-category:hover {
        border-color: rgba(241,98,15,.25);
        box-shadow: 0 8px 24px rgba(241,98,15,.1);
    }

    .directory-category span {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: #F1620F;
        background: #FFF7ED;
    }

    .directory-category svg {
        width: 20px;
        height: 20px;
    }

    .directory-category strong {
        color: #0B1A34;
        font-size: .82rem;
        line-height: 1.45;
    }

    .directory-category small {
        color: #64748B;
        font-size: .72rem;
        font-weight: 800;
    }

    .directory-service-chips {
        display: flex;
        gap: .5rem;
        overflow-x: auto;
        padding: .45rem .1rem .2rem;
    }

    .directory-service-chips a {
        flex: 0 0 auto;
        padding: .42rem .72rem;
        border-radius: 999px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        color: #475569;
        font-size: .76rem;
        font-weight: 800;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .directory-service-chips a:hover {
        background: #FFF7ED;
        color: #C2410C;
        border-color: rgba(241,98,15,.2);
    }

    .directory-provider-cta {
        margin-top: 1.2rem;
        padding: 1rem;
        border-radius: 20px;
        border: 1px solid #E8EDF4;
        background: #0B1A34;
        color: #FFFFFF;
    }

    .directory-provider-cta h2 {
        color: #FFFFFF;
    }

    .directory-provider-cta p {
        margin: .25rem 0 0;
        color: rgba(255, 255, 255, .72);
        font-size: .82rem;
        font-weight: 650;
        line-height: 1.7;
    }

    .directory-provider-cta a,
    .directory-pagination a,
    .directory-pagination span {
        flex: 0 0 auto;
        padding: .55rem .85rem;
        border-radius: 12px;
        background: #FFFFFF;
        color: #0B1A34;
        font-size: .78rem;
        font-weight: 950;
    }

    .directory-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .65rem;
        margin-top: 1rem;
    }

    .directory-pagination strong {
        color: #64748B;
        font-size: .78rem;
    }

    .directory-pagination .is-disabled {
        color: #94A3B8;
        background: #F1F5F9;
    }

    @media (min-width: 760px) {
        .directory-home {
            padding-top: 1.25rem;
        }

        .directory-hero {
            padding: 1.25rem;
        }

        .directory-hero h1 {
            font-size: 2rem;
        }

        .directory-search {
            grid-template-columns: minmax(260px, 1fr) auto;
            align-items: end;
        }

        .directory-search__input {
            grid-column: 1 / -1;
        }

        .directory-search__filters {
            grid-template-columns: repeat(4, minmax(150px, 1fr));
        }

        .directory-search__button {
            min-width: 132px;
        }
    }

    @media (max-width: 520px) {
        .directory-search__filters {
            grid-template-columns: 1fr;
        }

        .directory-provider-cta {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush
