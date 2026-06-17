@extends('public.layout')

@section('title', 'الأعلى تقييماً - ' . config('app.name'))

@php
    $providerCount = $providerCount ?? ($profiles?->total() ?? $profiles?->count() ?? 0);
@endphp

@section('content')
<div class="lp-wrapper market-browse">

    <x-marketplace-header
        eyebrow="دلني"
        title="الأعلى تقييماً"
        description="استعرض أفضل مقدمي الخدمات بناءً على تقييمات المستخدمين"
        :back-url="route('home')"
        back-label="الرئيسية"
    >
        <x-slot:icon>
            <x-render-icon icon="heroicon-o-star" />
        </x-slot:icon>
    </x-marketplace-header>

    <section class="market-results">
        @if($profiles && $profiles->count() > 0)
            <x-provider-grid :providers="$profiles" :columns="2" />
            <x-marketplace-pagination :paginator="$profiles" />
        @else
            <x-empty-state
                icon="heroicon-o-star"
                title="لا توجد نتائج"
                message="لا يوجد مزودون مطابقون للمرشحات المحددة."
                actionLabel="مسح المرشحات"
                actionUrl="{{ route('public.top-rated') }}"
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
