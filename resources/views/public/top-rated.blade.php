@extends('public.layout')

@section('title', 'الأعلى تقييماً - ' . config('app.name'))

@php
    $providerCount = $providerCount ?? ($profiles?->total() ?? $profiles?->count() ?? 0);
    $activeCategory = request('category_id') ? $categories->find(request('category_id')) : null;
    $activeCity = request('city_id') ? $cities->find(request('city_id')) : null;
    $hasFilters = request()->filled('category_id') || request()->filled('city_id') || request()->filled('keyword');
@endphp

@section('content')
<main class="directory-tab">
    <header class="directory-tab__header">
        <a href="{{ route('home') }}" class="directory-tab__back" aria-label="الرئيسية">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>

        <div>
            <span>الأعلى تقييماً</span>
            <h1>مزودين عليهم تقييمات قوية</h1>
            <p>{{ number_format($providerCount) }} مزود</p>
        </div>
    </header>

    <form action="{{ route('public.top-rated') }}" method="GET" class="directory-tab__filters">
        <label class="directory-tab__keyword">
            <span>ابحث هنا</span>
            <div>
                <x-render-icon icon="heroicon-o-magnifying-glass" />
                <input
                    type="search"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    maxlength="100"
                    placeholder="اسم خدمة أو مزود..."
                >
            </div>
        </label>

        <label>
            <span>الفئة</span>
            <select name="category_id">
                <option value="">كل الفئات</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                        {{ $category->localized_name ?? $category->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span>المدينة</span>
            <select name="city_id">
                <option value="">كل المدن</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                        {{ $city->localized_name ?? $city->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <button type="submit">
            <x-render-icon icon="heroicon-o-funnel" />
            <span>تصفية</span>
        </button>
    </form>

    @if($hasFilters)
        <div class="directory-tab__chips">
            @if(request('keyword'))
                <span>{{ request('keyword') }}</span>
            @endif

            @if($activeCategory)
                <span>{{ $activeCategory->localized_name ?? $activeCategory->name }}</span>
            @endif

            @if($activeCity)
                <span>{{ $activeCity->localized_name ?? $activeCity->name }}</span>
            @endif

            <a href="{{ route('public.top-rated') }}">مسح</a>
        </div>
    @endif

    <section class="directory-tab__results">
        <div class="directory-tab__section-head">
            <div>
                <span>حسب تقييمات المستخدمين</span>
                <h2>كل المزودين المؤهلين</h2>
            </div>
        </div>

        @if($profiles && $profiles->count() > 0)
            <x-provider-grid :providers="$profiles" :columns="2" />

            @if($profiles->hasPages())
                <nav class="directory-tab__pagination" aria-label="Pagination">
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
                icon="heroicon-o-star"
                title="ما لقيناش نتائج"
                message="ما فيش مزودين مطابقين للمرشحات الحالية. جرّب مدينة أو فئة ثانية."
                actionLabel="مسح المرشحات"
                actionUrl="{{ route('public.top-rated') }}"
            />
        @endif
    </section>
</main>
@endsection

@push('styles')
<style>
    .directory-tab {
        width: min(100% - 1.25rem, 1120px);
        margin-inline: auto;
        padding: .85rem 0 2rem;
    }

    .directory-tab__header {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: 1rem;
        border: 1px solid #E8EDF4;
        border-radius: 22px;
        background: #FFFFFF;
        box-shadow: 0 12px 32px rgba(11, 26, 52, .06);
    }

    .directory-tab__back {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 14px;
        color: #0B1A34;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
    }

    .directory-tab__back svg {
        width: 20px;
        height: 20px;
    }

    .directory-tab__header span,
    .directory-tab__section-head span {
        display: block;
        margin-bottom: .18rem;
        color: #F1620F;
        font-size: .75rem;
        font-weight: 900;
    }

    .directory-tab__header h1,
    .directory-tab__section-head h2 {
        margin: 0;
        color: #0B1A34;
        font-weight: 950;
        letter-spacing: 0;
        line-height: 1.25;
    }

    .directory-tab__header h1 {
        font-size: 1.35rem;
    }

    .directory-tab__header p {
        margin: .2rem 0 0;
        color: #64748B;
        font-size: .82rem;
        font-weight: 800;
    }

    .directory-tab__filters {
        display: grid;
        gap: .65rem;
        margin-top: .85rem;
        padding: .85rem;
        border: 1px solid #E8EDF4;
        border-radius: 20px;
        background: #FFFFFF;
    }

    .directory-tab__filters label {
        display: grid;
        gap: .32rem;
        color: #334155;
        font-size: .74rem;
        font-weight: 850;
    }

    .directory-tab__keyword div,
    .directory-tab__filters select,
    .directory-tab__filters button {
        min-height: 48px;
        border-radius: 14px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
    }

    .directory-tab__keyword div {
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: 0 .8rem;
    }

    .directory-tab__keyword svg {
        width: 19px;
        height: 19px;
        color: #94A3B8;
    }

    .directory-tab__filters input,
    .directory-tab__filters select {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        color: #0B1A34;
        background: transparent;
        font-size: .92rem;
        font-weight: 750;
    }

    .directory-tab__filters select {
        padding: 0 .75rem;
    }

    .directory-tab__filters button {
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

    .directory-tab__filters button svg {
        width: 18px;
        height: 18px;
    }

    .directory-tab__chips {
        display: flex;
        align-items: center;
        gap: .5rem;
        overflow-x: auto;
        margin-top: .75rem;
    }

    .directory-tab__chips span,
    .directory-tab__chips a {
        flex: 0 0 auto;
        padding: .42rem .72rem;
        border-radius: 999px;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        color: #475569;
        font-size: .76rem;
        font-weight: 850;
    }

    .directory-tab__chips a {
        color: #F1620F;
        border-color: #FED7AA;
        background: #FFF7ED;
    }

    .directory-tab__results {
        margin-top: 1.15rem;
    }

    .directory-tab__section-head {
        margin-bottom: .8rem;
        padding-inline: .2rem;
    }

    .directory-tab__section-head h2 {
        font-size: 1.08rem;
    }

    .directory-tab__pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .65rem;
        margin-top: 1rem;
    }

    .directory-tab__pagination a,
    .directory-tab__pagination span {
        flex: 0 0 auto;
        padding: .55rem .85rem;
        border-radius: 12px;
        background: #FFFFFF;
        color: #0B1A34;
        border: 1px solid #E2E8F0;
        font-size: .78rem;
        font-weight: 950;
    }

    .directory-tab__pagination strong {
        color: #64748B;
        font-size: .78rem;
    }

    .directory-tab__pagination .is-disabled {
        color: #94A3B8;
        background: #F1F5F9;
    }

    @media (min-width: 760px) {
        .directory-tab {
            padding-top: 1.25rem;
        }

        .directory-tab__header h1 {
            font-size: 1.8rem;
        }

        .directory-tab__filters {
            grid-template-columns: minmax(260px, 1fr) minmax(160px, .45fr) minmax(160px, .45fr) 132px;
            align-items: end;
        }
    }

    @media (max-width: 520px) {
        .directory-tab__pagination {
            gap: .45rem;
        }

        .directory-tab__pagination a,
        .directory-tab__pagination span {
            padding-inline: .65rem;
        }
    }
</style>
@endpush
