@extends('public.layout')

@section('title', $subcategory->localized_name . ' - ' . config('app.name'))

@section('content')
@php
    $parentCategory = $subcategory->category;
    $totalCount = $profiles->total() ?? $profiles->count() ?? 0;
@endphp

<div class="lp-wrapper">

    {{-- App page header --}}
    <header class="lp-header">
        <a href="{{ $parentCategory ? route('public.category', $parentCategory->slug) : route('public.categories') }}"
           class="lp-back" aria-label="رجوع">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            @if($parentCategory)
                <span class="lp-label">{{ $parentCategory->localized_name ?? $parentCategory->name }}</span>
            @endif
            <h1 class="lp-title">{{ $subcategory->localized_name ?? $subcategory->name }}</h1>
            <span class="lp-count">{{ number_format($totalCount) }} مزود</span>
        </div>
    </header>

    {{-- Inline filter row --}}
    <form method="GET" action="{{ url()->current() }}" class="lp-filter-row">
        @if(isset($cities) && $cities->isNotEmpty())
            <select name="city_id" class="lp-filter-select" onchange="this.form.submit()">
                <option value="">كل المدن</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" @selected(request('city_id') == $city->id)>
                        {{ $city->localized_name ?? $city->name }}
                    </option>
                @endforeach
            </select>
        @endif

        <select name="sort" class="lp-filter-select" onchange="this.form.submit()">
            <option value="" @selected(!request('sort'))>الأحدث</option>
            <option value="rating" @selected(request('sort') === 'rating')>الأعلى تقييماً</option>
            <option value="reviews" @selected(request('sort') === 'reviews')>الأكثر تقييماً</option>
        </select>

        @if(request()->anyFilled(['city_id', 'sort']))
            <a href="{{ route('public.subcategory', $subcategory->slug) }}"
               class="lp-chip"
               style="border-color:rgba(241,98,15,.25);background:#FFF7ED;color:#F1620F;">
                <x-render-icon icon="heroicon-o-x-mark" />
                مسح
            </a>
        @endif
    </form>

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
                icon="heroicon-o-magnifying-glass"
                title="ما لقيناش نتائج"
                message="جرّب مدينة ثانية أو تصفح فئات أخرى."
                actionLabel="تصفح الفئات"
                actionUrl="{{ route('public.categories') }}"
            />
        @endif
    </div>

</div>
@endsection
