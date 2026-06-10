@props([
    'categories' => null,
    'cities' => null,
    'providerTypes' => null,
])

@php
    $hasFilters = request()->filled('keyword')
        || request()->filled('category_id')
        || request()->filled('city_id')
        || request()->filled('provider_type')
        || request()->filled('remote')
        || request()->filled('sort');
@endphp

<div class="delni-filters">
    <form method="GET" action="{{ route('public.search') }}" class="delni-filters__form">
        <header class="delni-filters__header">
            <div>
                <h3>مرشحات البحث</h3>
                <p>ضيّق النتائج حسب احتياجك.</p>
            </div>

            @if($hasFilters)
                <a href="{{ route('public.search') }}">مسح</a>
            @endif
        </header>

        <div class="delni-filter-field">
            <label for="keyword">كلمة البحث</label>
            <input
                type="text"
                id="keyword"
                name="keyword"
                value="{{ request('keyword') }}"
                maxlength="100"
                placeholder="مثال: تصوير، سباكة، تصميم..."
            >
        </div>

        @if($categories)
            <div class="delni-filter-field">
                <label for="category_id">الفئة</label>
                <select id="category_id" name="category_id">
                    <option value="">جميع الفئات</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                            {{ $category->localized_name ?? $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($cities)
            <div class="delni-filter-field">
                <label for="city_id">المدينة</label>
                <select id="city_id" name="city_id">
                    <option value="">جميع المدن</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                            {{ $city->localized_name ?? $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($providerTypes)
            <div class="delni-filter-field">
                <label for="provider_type">نوع المزود</label>
                <select id="provider_type" name="provider_type">
                    <option value="">جميع الأنواع</option>
                    @foreach($providerTypes as $code => $name)
                        <option value="{{ $code }}" @selected((string) request('provider_type') === (string) $code)>
                            {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <label class="delni-filter-check" for="remote">
            <input
                type="checkbox"
                id="remote"
                name="remote"
                value="1"
                @checked(request('remote') == 1)
            >
            <span>
                <strong>يدعم العمل عن بعد</strong>
                <small>مناسب للخدمات الرقمية والاستشارات</small>
            </span>
        </label>

        <div class="delni-filter-field">
            <label for="sort">ترتيب النتائج</label>
            <select id="sort" name="sort">
                <option value="" @selected(!request('sort'))>الأكثر صلة</option>
                <option value="rating" @selected(request('sort') === 'rating')>الأعلى تقييماً</option>
                <option value="reviews" @selected(request('sort') === 'reviews')>الأكثر مراجعات</option>
                <option value="newest" @selected(request('sort') === 'newest')>الأحدث</option>
            </select>
        </div>

        <button type="submit" class="delni-filters__submit">
            تطبيق البحث
        </button>
    </form>
</div>

@once
    @push('styles')
        <style>
            .delni-filters {
                border-radius: 24px;
                background: #fff;
                border: 1px solid #E7E7E7;
                box-shadow: 0 14px 34px rgba(11, 26, 52, .06);
                overflow: hidden;
            }

            .delni-filters__form {
                display: flex;
                flex-direction: column;
                gap: .9rem;
                padding: 1rem;
            }

            .delni-filters__header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                padding-bottom: .9rem;
                border-bottom: 1px solid #E7E7E7;
            }

            .delni-filters__header h3 {
                margin: 0;
                color: #0B1A34;
                font-size: 1.05rem;
                line-height: 1.3;
                font-weight: 950;
                letter-spacing: -.025em;
            }

            .delni-filters__header p {
                margin: .35rem 0 0;
                color: #5D5959;
                font-size: .82rem;
                line-height: 1.7;
                font-weight: 600;
            }

            .delni-filters__header a {
                color: #F1620F;
                text-decoration: none;
                font-size: .82rem;
                font-weight: 950;
            }

            .delni-filter-field {
                display: flex;
                flex-direction: column;
                gap: .4rem;
            }

            .delni-filter-field label {
                color: #0B1A34;
                font-size: .84rem;
                font-weight: 950;
            }

            .delni-filter-field input,
            .delni-filter-field select {
                width: 100%;
                height: 44px;
                padding-inline: .85rem;
                border-radius: 14px;
                border: 1px solid #E7E7E7;
                background: #FCFBFB;
                color: #0B1A34;
                font: inherit;
                font-size: .88rem;
                font-weight: 800;
                outline: none;
                transition: .18s ease;
            }

            .delni-filter-field input::placeholder {
                color: #9b9696;
                font-weight: 700;
            }

            .delni-filter-field input:focus,
            .delni-filter-field select:focus {
                border-color: rgba(241, 98, 15, .65);
                background: #fff;
                box-shadow: 0 0 0 4px rgba(241, 98, 15, .08);
            }

            .delni-filter-check {
                min-height: 74px;
                display: flex;
                align-items: center;
                gap: .75rem;
                padding: .8rem;
                border-radius: 18px;
                background: #FCFBFB;
                border: 1px solid #E7E7E7;
                cursor: pointer;
            }

            .delni-filter-check input {
                width: 20px;
                height: 20px;
                flex-shrink: 0;
                accent-color: #F1620F;
                cursor: pointer;
            }

            .delni-filter-check span {
                display: flex;
                flex-direction: column;
                gap: .15rem;
            }

            .delni-filter-check strong {
                color: #0B1A34;
                font-size: .9rem;
                font-weight: 950;
            }

            .delni-filter-check small {
                color: #5D5959;
                font-size: .78rem;
                line-height: 1.6;
                font-weight: 600;
            }

            .delni-filters__submit {
                min-height: 46px;
                border: 0;
                border-radius: 15px;
                background: #F1620F;
                color: #fff;
                font: inherit;
                font-size: .9rem;
                font-weight: 950;
                cursor: pointer;
                box-shadow: 0 12px 24px rgba(241, 98, 15, .2);
                transition: .18s ease;
            }

            .delni-filters__submit:hover {
                transform: translateY(-1px);
                box-shadow: 0 16px 30px rgba(241, 98, 15, .26);
            }

            @media (max-width: 900px) {
                .delni-filters__form {
                    padding: .9rem;
                }
            }
        </style>
    @endpush
@endonce
