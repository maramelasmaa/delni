@extends('public.layout')

@section('title', $city->localized_name . ' - ' . config('app.name'))

@section('content')
@php
    $totalCount = $profiles->total() ?? $profiles->count() ?? 0;
@endphp

<div class="lp-wrapper market-browse">

    <x-marketplace-header
        eyebrow="مزودون في"
        :title="$city->localized_name"
        :back-url="route('home')"
        back-label="الرئيسية"
    >
        <x-slot:icon>
            <x-render-icon icon="heroicon-o-map-pin" />
        </x-slot:icon>
    </x-marketplace-header>

    <x-browse-trail :items="[
        ['label' => 'الرئيسية', 'url' => route('home')],
        ['label' => $city->localized_name, 'active' => true],
    ]" />

    <x-browse-filters
        :action="url()->current()"
        :categories="$categories ?? collect()"
        :reset-url="route('public.city', $city->slug)"
        :show-category="true"
        :show-city="false"
    />

    <section class="market-results">
        @if($profiles && $profiles->count() > 0)
            <x-provider-grid :providers="$profiles" :columns="2" />
            <x-marketplace-pagination :paginator="$profiles" />
        @else
            <x-empty-state
                icon="heroicon-o-map-pin"
                title="لا يوجد مزودون"
                message="لا يوجد مزودون في هذه المدينة حالياً. جرّب مدينة أخرى."
                actionLabel="تصفح الفئات"
                actionUrl="{{ route('public.categories') }}"
            />
        @endif
    </section>

</div>

@push('styles')
<style>
    .market-browse {
        display: grid;
        gap: .85rem;
    }

    .market-results { min-width: 0; }
</style>
@endpush
@endsection
