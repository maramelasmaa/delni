@extends('public.layout')

@section('title', $category->localized_name . ' - ' . config('app.name'))

@section('content')
@php
    $totalCount = $profiles->total() ?? 0;
    $visibleSubcategories = $category->subcategories->filter(fn ($subcategory) => (int) ($subcategory->discoverable_profiles_count ?? 0) > 0);
    $cityFilterUrls = ($cities ?? collect())
        ->mapWithKeys(fn ($city) => [$city->slug => route('public.category.city', [$category->slug, $city->slug])])
        ->all();
@endphp

<div class="lp-wrapper market-browse">
    <x-marketplace-header
        eyebrow="فئة"
        :title="$category->localized_name ?? $category->name"
        :count="number_format($totalCount) . ' مزود'"
        :back-url="route('public.categories')"
        back-label="الفئات"
        description="اختر خدمة فرعية أو صف النتائج حسب المدينة والتقييم."
    >
        @if($category->icon)
            <x-slot:icon>
                <x-svg-icon :icon="$category->icon" size="22" />
            </x-slot:icon>
        @endif
    </x-marketplace-header>

    <x-browse-trail :items="[
        ['label' => 'الفئات', 'url' => route('public.categories')],
        ['label' => $category->localized_name ?? $category->name, 'active' => true],
    ]" />

    <x-subcategory-rail
        :items="$visibleSubcategories"
        :all-url="route('public.category', $category->slug)"
        all-label="كل الخدمات"
        :all-count="$totalCount"
    />

    <x-browse-filters
        :action="url()->current()"
        :cities="$cities ?? collect()"
        :reset-url="route('public.category', $category->slug)"
        :city-urls="$cityFilterUrls"
        :city-reset-url="route('public.category', $category->slug)"
    />

    <section class="market-results">
        @if($profiles && $profiles->count() > 0)
            <x-provider-grid :providers="$profiles" :columns="2" />
            <x-marketplace-pagination :paginator="$profiles" />
        @else
            <x-empty-state
                icon="heroicon-o-magnifying-glass"
                title="لا توجد نتائج"
                message="جرب مدينة أخرى أو اختر خدمة فرعية مختلفة."
                actionLabel="مسح الفلاتر"
                actionUrl="{{ route('public.category', $category->slug) }}"
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

    .market-results {
        min-width: 0;
    }
</style>
@endpush
@endsection
