@extends('public.layout')

@section('title', 'الأعلى تقييماً - ' . config('app.name'))

@section('content')
@php
    $providerCount = $providerCount ?? ($profiles?->total() ?? $profiles?->count() ?? 0);

    $activeCategory = request('category_id') ? $categories->find(request('category_id')) : null;
    $activeCity = request('city_id') ? $cities->find(request('city_id')) : null;

    $hasFilters = request()->filled('category_id') || request()->filled('city_id') || request()->filled('keyword');
@endphp

<div class="top-rated-page">
    <section class="top-rated-hero">
        <div class="container">
            <div class="top-rated-hero__inner">
                <div class="top-rated-hero__text">
                    <span class="top-rated-kicker">
                        ⭐ الأعلى ثقة في دلني
                    </span>

                    <h1>
                        مقدمو خدمات الناس
                        <span>يثقون فيهم</span>
                    </h1>

                    <p>
                        اكتشف مزودين حصلوا على تقييمات عالية من عملاء حقيقيين، وفلتر حسب المدينة أو الفئة بسرعة.
                    </p>
                </div>

                <div class="top-rated-hero__card">
                    <strong>{{ number_format($providerCount) }}</strong>
                    <span>مزود عالي التقييم</span>
                </div>
            </div>

            <form action="{{ route('public.top-rated') }}" method="GET" class="top-rated-search">
                <div class="top-rated-field top-rated-field--wide">
                    <x-render-icon icon="heroicon-o-magnifying-glass" />
                    <input
                        type="text"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        placeholder="ابحث باسم الخدمة أو المزود..."
                        maxlength="100"
                    >
                </div>

                <div class="top-rated-field">
                    <x-render-icon icon="heroicon-o-briefcase" />
                    <select name="category_id">
                        <option value="">كل الفئات</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                {{ $category->localized_name ?? $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="top-rated-field">
                    <x-render-icon icon="heroicon-o-map-pin" />
                    <select name="city_id">
                        <option value="">كل المدن</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                                {{ $city->localized_name ?? $city->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit">
                    بحث
                </button>
            </form>
        </div>
    </section>

    <section class="top-rated-body">
        <div class="container">
            <div class="top-rated-toolbar">
                <div>
                    <span>النتائج</span>
                    <h2>الأعلى تقييماً</h2>
                    <p>{{ number_format($providerCount) }} مزود مطابق</p>
                </div>

                @if($hasFilters)
                    <a href="{{ route('public.top-rated') }}">
                        مسح المرشحات
                    </a>
                @endif
            </div>

            @if($hasFilters)
                <div class="top-rated-chips">
                    @if(request('keyword'))
                        <span>{{ request('keyword') }}</span>
                    @endif

                    @if($activeCategory)
                        <span>{{ $activeCategory->localized_name ?? $activeCategory->name }}</span>
                    @endif

                    @if($activeCity)
                        <span>{{ $activeCity->localized_name ?? $activeCity->name }}</span>
                    @endif
                </div>
            @endif

            @if($profiles && $profiles->count() > 0)
                <x-provider-grid :providers="$profiles" :columns="3" />

                @if($profiles->hasPages())
                    <nav class="delni-pagination" aria-label="Pagination">
                        @if($profiles->onFirstPage())
                            <span class="delni-page-btn is-disabled">السابق</span>
                        @else
                            <a href="{{ $profiles->previousPageUrl() }}" class="delni-page-btn">السابق</a>
                        @endif

                        <span class="delni-page-info">
                            صفحة {{ $profiles->currentPage() }} من {{ $profiles->lastPage() }}
                        </span>

                        @if($profiles->hasMorePages())
                            <a href="{{ $profiles->nextPageUrl() }}" class="delni-page-btn">التالي</a>
                        @else
                            <span class="delni-page-btn is-disabled">التالي</span>
                        @endif
                    </nav>
                @endif
            @else
                <x-empty-state
                    icon="heroicon-o-star"
                    title="لا توجد نتائج"
                    message="لم نجد مزودين الأعلى تقييماً حسب المرشحات الحالية. جرّب مدينة أو فئة أخرى."
                    actionLabel="مسح المرشحات"
                    actionUrl="{{ route('public.top-rated') }}"
                />
            @endif
        </div>
    </section>
</div>

<style>
    .top-rated-page {
        background: #FCFBFB;
        min-height: 100vh;
    }

    .top-rated-hero {
        padding: clamp(2rem, 5vw, 3.5rem) 0 2rem;
        background:
            radial-gradient(circle at 15% 20%, rgba(241,98,15,.18), transparent 32%),
            linear-gradient(135deg, #0B1A34, #13264A);
        color: #fff;
    }

    .top-rated-hero__inner {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1.25rem;
    }

    .top-rated-kicker {
        display: inline-flex;
        margin-bottom: .9rem;
        padding: .42rem .8rem;
        border-radius: 999px;
        background: rgba(241,98,15,.16);
        border: 1px solid rgba(241,98,15,.26);
        color: #ffb079;
        font-size: .82rem;
        font-weight: 950;
    }

    .top-rated-hero h1 {
        max-width: 760px;
        margin: 0;
        font-size: clamp(2.1rem, 5.5vw, 4.2rem);
        line-height: 1.08;
        font-weight: 950;
        letter-spacing: -.06em;
    }

    .top-rated-hero h1 span {
        color: #F1620F;
    }

    .top-rated-hero p {
        max-width: 650px;
        margin: .9rem 0 0;
        color: rgba(255,255,255,.74);
        font-size: 1rem;
        line-height: 1.9;
        font-weight: 650;
    }

    .top-rated-hero__card {
        min-width: 170px;
        padding: 1rem;
        border-radius: 24px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.16);
        box-shadow: 0 18px 42px rgba(0,0,0,.16);
        text-align: center;
    }

    .top-rated-hero__card strong {
        display: block;
        font-size: 2rem;
        line-height: 1;
        font-weight: 950;
        color: #fff;
    }

    .top-rated-hero__card span {
        display: block;
        margin-top: .4rem;
        color: rgba(255,255,255,.72);
        font-size: .82rem;
        font-weight: 850;
    }

    .top-rated-search {
        margin-top: 1.5rem;
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(170px, .6fr) minmax(170px, .6fr) 130px;
        gap: .6rem;
        padding: .65rem;
        border-radius: 24px;
        background: rgba(255,255,255,.96);
        border: 1px solid rgba(255,255,255,.5);
        box-shadow: 0 24px 60px rgba(0,0,0,.2);
    }

    .top-rated-field {
        min-height: 54px;
        display: flex;
        align-items: center;
        gap: .65rem;
        padding-inline: .95rem;
        border-radius: 17px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
    }

    .top-rated-field svg {
        width: 20px;
        height: 20px;
        color: #F1620F;
        flex-shrink: 0;
    }

    .top-rated-field input,
    .top-rated-field select {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: #0B1A34;
        font: inherit;
        font-size: .9rem;
        font-weight: 850;
    }

    .top-rated-field input::placeholder {
        color: #9b9696;
    }

    .top-rated-search button {
        min-height: 54px;
        border: 0;
        border-radius: 17px;
        background: #F1620F;
        color: #fff;
        font: inherit;
        font-size: .92rem;
        font-weight: 950;
        cursor: pointer;
        box-shadow: 0 12px 24px rgba(241,98,15,.24);
    }

    .top-rated-body {
        padding: 1.6rem 0 3.5rem;
    }

    .top-rated-toolbar {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .top-rated-toolbar span {
        display: block;
        margin-bottom: .25rem;
        color: #F1620F;
        font-size: .8rem;
        font-weight: 950;
    }

    .top-rated-toolbar h2 {
        margin: 0;
        color: #0B1A34;
        font-size: clamp(1.35rem, 3vw, 2rem);
        line-height: 1.2;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .top-rated-toolbar p {
        margin: .3rem 0 0;
        color: #5D5959;
        font-size: .9rem;
        font-weight: 700;
    }

    .top-rated-toolbar a {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        padding: .6rem .9rem;
        border-radius: 999px;
        background: rgba(241,98,15,.08);
        border: 1px solid rgba(241,98,15,.14);
        color: #F1620F;
        text-decoration: none;
        font-size: .84rem;
        font-weight: 950;
    }

    .top-rated-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: 1rem;
    }

    .top-rated-chips span {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        padding: .45rem .75rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
        font-size: .82rem;
        font-weight: 900;
    }

    .delni-pagination {
        margin-top: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .delni-page-btn {
        min-height: 42px;
        padding: .65rem 1rem;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
        text-decoration: none;
        font-size: .9rem;
        font-weight: 900;
    }

    .delni-page-btn:hover {
        border-color: #F1620F;
        color: #F1620F;
    }

    .delni-page-btn.is-disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .delni-page-info {
        color: #5D5959;
        font-size: .9rem;
        font-weight: 850;
    }

    @media (max-width: 980px) {
        .top-rated-hero__inner {
            align-items: start;
            flex-direction: column;
        }

        .top-rated-hero__card {
            width: 100%;
            text-align: start;
        }

        .top-rated-search {
            grid-template-columns: 1fr;
        }

        .top-rated-toolbar {
            align-items: start;
            flex-direction: column;
        }
    }

    @media (max-width: 560px) {
        .top-rated-hero {
            padding: 1.75rem 0 1.3rem;
        }

        .top-rated-hero h1 {
            font-size: clamp(2rem, 11vw, 3rem);
        }

        .top-rated-search {
            border-radius: 21px;
        }

        .top-rated-field,
        .top-rated-search button {
            min-height: 50px;
            border-radius: 15px;
        }

        .top-rated-body {
            padding-top: 1rem;
        }
    }
</style>
@endsection
