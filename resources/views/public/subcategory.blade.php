@extends('public.layout')

@section('title', $subcategory->localized_name . ' - ' . config('app.name'))

@section('content')
@php
    $parentCategory = $subcategory->category;
    $totalCount = $profiles->total() ?? $profiles->count() ?? 0;
    $siblings = $parentCategory?->subcategories ?? collect();
    $cityFilterUrls = ($cities ?? collect())
        ->mapWithKeys(fn ($city) => [$city->slug => route('public.subcategory.city', [$subcategory->slug, $city->slug])])
        ->all();
@endphp

<div class="lp-wrapper market-browse">
    <x-marketplace-header
        :eyebrow="$parentCategory ? ($parentCategory->localized_name ?? $parentCategory->name) : 'خدمة'"
        :title="$subcategory->localized_name ?? $subcategory->name"
        :count="number_format($totalCount) . ' مزود'"
        :back-url="$parentCategory ? route('public.category', $parentCategory->slug) : route('public.categories')"
        back-label="رجوع"
        description="قارن المزودين حسب المدينة والثقة ثم افتح الملف للتواصل مباشرة."
    />

    <x-browse-trail :items="[
        ['label' => 'الفئات', 'url' => route('public.categories')],
        $parentCategory ? ['label' => $parentCategory->localized_name ?? $parentCategory->name, 'url' => route('public.category', $parentCategory->slug)] : null,
        ['label' => $subcategory->localized_name ?? $subcategory->name, 'active' => true],
    ]" />

    <x-subcategory-rail
        :items="$siblings"
        :active="$subcategory"
        :all-url="$parentCategory ? route('public.category', $parentCategory->slug) : null"
        all-label="كل الخدمات"
        :all-count="$parentCategory?->discoverable_profiles_count"
    />

    <x-browse-filters
        :action="url()->current()"
        :cities="$cities ?? collect()"
        :reset-url="route('public.subcategory', $subcategory->slug)"
        :city-urls="$cityFilterUrls"
        :city-reset-url="route('public.subcategory', $subcategory->slug)"
    />

    <section class="market-results">
        @if($profiles && $profiles->count() > 0)
            <x-provider-grid :providers="$profiles" :columns="2" />
            <x-marketplace-pagination :paginator="$profiles" />
        @else
            <x-empty-state
                icon="heroicon-o-magnifying-glass"
                title="لا توجد نتائج"
                message="جرب مدينة أخرى أو تصفح فئات مختلفة."
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
