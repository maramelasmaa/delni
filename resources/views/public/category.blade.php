@extends('public.layout')

@section('title', $category->localized_name . ' - ' . config('app.name'))

@section('content')
@php
    $totalCount = $profiles->total() ?? 0;
@endphp

<div class="lp-wrapper">

    {{-- App page header --}}
    <header class="lp-header">
        <a href="{{ route('public.categories') }}" class="lp-back" aria-label="الفئات">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">فئة</span>
            <h1 class="lp-title">{{ $category->localized_name }}</h1>
            <span class="lp-count">{{ number_format($totalCount) }} مزود</span>
        </div>
        @if($category->icon)
            <div class="lp-header-icon">
                <x-svg-icon :icon="$category->icon" size="22" />
            </div>
        @endif
    </header>

    {{-- Subcategory chips --}}
    @if($category->subcategories->isNotEmpty())
        <div class="lp-chips" role="list" aria-label="الفئات الفرعية">
            <a href="{{ route('public.category', $category->slug) }}"
               class="lp-chip {{ !request('subcategory_id') ? 'is-active' : '' }}"
               role="listitem">
                الكل
                <small>{{ $totalCount }}</small>
            </a>
            @foreach($category->subcategories as $subcategory)
                <a href="{{ route('public.subcategory', $subcategory->slug) }}"
                   class="lp-chip"
                   role="listitem">
                    {{ $subcategory->localized_name ?? $subcategory->name }}
                    <small>{{ $subcategory->discoverable_profiles_count ?? 0 }}</small>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Inline filter row --}}
    <form method="GET" action="{{ url()->current() }}" class="lp-filter-row" id="lpFilterForm">
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
            <a href="{{ route('public.category', $category->slug) }}" class="lp-chip" style="border-color: rgba(241,98,15,.25); background:#FFF7ED; color:#F1620F;">
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
                message="جرّب مدينة ثانية أو اختار فئة فرعية مختلفة."
                actionLabel="مسح الفلاتر"
                actionUrl="{{ route('public.category', $category->slug) }}"
            />
        @endif
    </div>

</div>

@push('styles')
<style>
    .lp-header-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(241,98,15,.08);
        color: var(--delni-primary);
    }
</style>
@endpush
@endsection
