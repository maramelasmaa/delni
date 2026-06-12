@extends('public.layout')

@section('title', $city->localized_name . ' - ' . config('app.name'))

@section('content')
@php
    $totalCount = $profiles->total() ?? $profiles->count() ?? 0;
@endphp

<div class="lp-wrapper">

    {{-- App page header --}}
    <header class="lp-header">
        <a href="{{ route('home') }}" class="lp-back" aria-label="الرئيسية">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">مزودون في</span>
            <h1 class="lp-title">{{ $city->localized_name }}</h1>
            <span class="lp-count">{{ number_format($totalCount) }} مزود</span>
        </div>
        <div class="lp-header-icon">
            <x-render-icon icon="heroicon-o-map-pin" />
        </div>
    </header>

    {{-- Inline filter row --}}
    <form method="GET" action="{{ url()->current() }}" class="lp-filter-row">
        @if(isset($categories) && $categories->isNotEmpty())
            <select name="category_id" class="lp-filter-select" onchange="this.form.submit()">
                <option value="">كل الفئات</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                        {{ $category->localized_name ?? $category->name }}
                    </option>
                @endforeach
            </select>
        @endif

        <select name="sort" class="lp-filter-select" onchange="this.form.submit()">
            <option value="" @selected(!request('sort'))>الأحدث</option>
            <option value="rating" @selected(request('sort') === 'rating')>الأعلى تقييماً</option>
            <option value="reviews" @selected(request('sort') === 'reviews')>الأكثر تقييماً</option>
        </select>

        @if(request()->anyFilled(['category_id', 'sort']))
            <a href="{{ route('public.city', $city->slug) }}"
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
                icon="heroicon-o-map-pin"
                title="ما لقيناش مزودين"
                message="ما فيش مزودين في هذه المدينة حالياً. جرّب مدينة أخرى."
                actionLabel="تصفح الكل"
                actionUrl="{{ route('public.search') }}"
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
    .lp-header-icon svg { width: 22px; height: 22px; }
</style>
@endpush
@endsection
