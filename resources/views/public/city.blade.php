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
    <x-browse-filters
        :action="url()->current()"
        :categories="$categories ?? collect()"
        :reset-url="route('public.city', $city->slug)"
        :show-category="true"
        :show-city="false"
    />

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
                title="لا يوجد مزودون"
                message="لا يوجد مزودون في هذه المدينة حالياً. جرّب مدينة أخرى."
                actionLabel="تصفح الكل"
                actionUrl="{{ route('public.search') }}"
            />
        @endif
    </div>

</div>

@endsection
