@extends('public.layout')

@section('title', 'الأعلى تقييماً - ' . config('app.name'))

@php
    $providerCount = $providerCount ?? ($profiles?->total() ?? $profiles?->count() ?? 0);
    $activeCategory = request('category_id') ? ($categories->find(request('category_id'))) : null;
    $activeCity = request('city_id') ? ($cities->find(request('city_id'))) : null;
    $hasFilters = request()->filled('category_id') || request()->filled('city_id') || request()->filled('keyword');
@endphp

@section('content')
<div class="lp-wrapper">

    {{-- App page header --}}
    <header class="lp-header">
        <a href="{{ route('home') }}" class="lp-back" aria-label="الرئيسية">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">دلني</span>
            <h1 class="lp-title">الأعلى تقييماً</h1>
            <span class="lp-count">{{ number_format($providerCount) }} مزود مؤهل</span>
        </div>
        <div class="lp-star-icon">★</div>
    </header>

    {{-- Filter row --}}
    <form action="{{ route('public.top-rated') }}" method="GET" class="lp-filter-row" id="trFilterForm">
        <div class="lp-search-wrap">
            <x-render-icon icon="heroicon-o-magnifying-glass" />
            <input
                type="search"
                name="keyword"
                value="{{ request('keyword') }}"
                maxlength="100"
                placeholder="ابحث..."
                class="lp-search-input"
                onchange="this.form.submit()"
            >
        </div>

        @if($categories->isNotEmpty())
            <select name="category_id" class="lp-filter-select" onchange="this.form.submit()">
                <option value="">كل الفئات</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>
                        {{ $category->localized_name ?? $category->name }}
                    </option>
                @endforeach
            </select>
        @endif

        @if($cities->isNotEmpty())
            <select name="city_id" class="lp-filter-select" onchange="this.form.submit()">
                <option value="">كل المدن</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" @selected((string)request('city_id') === (string)$city->id)>
                        {{ $city->localized_name ?? $city->name }}
                    </option>
                @endforeach
            </select>
        @endif

        @if($hasFilters)
            <a href="{{ route('public.top-rated') }}"
               class="lp-chip"
               style="border-color:rgba(241,98,15,.25);background:#FFF7ED;color:#F1620F;">
                <x-render-icon icon="heroicon-o-x-mark" />
                مسح
            </a>
        @endif
    </form>

    {{-- Active filter chips --}}
    @if($hasFilters)
        <div class="lp-chips" style="padding-top:.3rem;">
            @if(request('keyword'))
                <span class="lp-chip is-active">{{ request('keyword') }}</span>
            @endif
            @if($activeCategory)
                <span class="lp-chip is-active">{{ $activeCategory->localized_name ?? $activeCategory->name }}</span>
            @endif
            @if($activeCity)
                <span class="lp-chip is-active">{{ $activeCity->localized_name ?? $activeCity->name }}</span>
            @endif
        </div>
    @endif

    {{-- Results --}}
    <div class="lp-results">
        @if($profiles && $profiles->count() > 0)
            <x-provider-grid :providers="$profiles" :columns="2" />

            @if($profiles->hasPages())
                <nav class="lp-pagination" aria-label="Pagination">
                    @if($profiles->onFirstPage())
                        <span class="is-disabled">السابق</span>
                    @else
                        <a href="{{ $profiles->appends(request()->query())->previousPageUrl() }}">السابق</a>
                    @endif

                    <strong>{{ $profiles->currentPage() }} / {{ $profiles->lastPage() }}</strong>

                    @if($profiles->hasMorePages())
                        <a href="{{ $profiles->appends(request()->query())->nextPageUrl() }}">التالي</a>
                    @else
                        <span class="is-disabled">التالي</span>
                    @endif
                </nav>
            @endif
        @else
            <x-empty-state
                icon="heroicon-o-star"
                title="ما لقيناش نتائج"
                message="ما فيش مزودين مطابقين للمرشحات الحالية."
                actionLabel="مسح المرشحات"
                actionUrl="{{ route('public.top-rated') }}"
            />
        @endif
    </div>

</div>

@push('styles')
<style>
    .lp-star-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #FEF3C7;
        color: #D97706;
        font-size: 1.25rem;
    }

    .lp-search-wrap {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: .45rem;
        min-height: 38px;
        padding: 0 .75rem;
        border-radius: 999px;
        border: 1px solid var(--delni-border);
        background: #fff;
        min-width: 140px;
    }

    .lp-search-wrap svg {
        width: 16px;
        height: 16px;
        color: #94A3B8;
        flex-shrink: 0;
    }

    .lp-search-input {
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--delni-navy);
        font: inherit;
        font-size: .78rem;
        font-weight: 750;
        width: 100%;
        min-width: 0;
    }
</style>
@endpush
@endsection
