@extends('public.layout')

@section('title', 'الأعلى تقييماً - ' . config('app.name'))

@php
    $providerCount = $providerCount ?? ($profiles?->total() ?? $profiles?->count() ?? 0);
    $activeCategory = request('category')
        ? $categories->firstWhere('slug', request('category'))
        : (request('category_id') ? $categories->find(request('category_id')) : null);
    $activeCity = request('city')
        ? $cities->firstWhere('slug', request('city'))
        : (request('city_id') ? $cities->find(request('city_id')) : null);
    $hasFilters = request()->filled('category') || request()->filled('category_id') || request()->filled('city') || request()->filled('city_id') || request()->filled('keyword');
    $cityFilterUrls = ($cities ?? collect())
        ->mapWithKeys(fn ($city) => [$city->slug => route('public.top-rated.city', $city->slug)])
        ->all();
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
        <div class="lp-header-icon">
            <x-render-icon icon="heroicon-o-star" />
        </div>
    </header>

    {{-- Filter row --}}
    <x-browse-filters
        :action="url()->current()"
        :categories="$categories"
        :cities="$cities"
        :reset-url="route('public.top-rated')"
        :show-keyword="true"
        :show-category="true"
        :sort="false"
        :city-urls="$cityFilterUrls"
        :city-reset-url="route('public.top-rated')"
        keyword-placeholder="ابحث..."
    />

    {{-- Active filter chips --}}
    @if($hasFilters)
        <div class="lp-chips lp-chips--compact">
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
                title="لا توجد نتائج"
                message="لا يوجد مزودون مطابقون للمرشحات المحددة."
                actionLabel="مسح المرشحات"
                actionUrl="{{ route('public.top-rated') }}"
            />
        @endif
    </div>

</div>
@endsection
