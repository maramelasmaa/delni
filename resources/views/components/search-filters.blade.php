@props([
    'action' => route('public.search'),
    'clearUrl' => route('public.search'),
    'categories' => null,
    'subcategories' => null,
    'cities' => null,
    'providerTypes' => null,
    'showKeyword' => true,
    'showRemote' => true,
])

@php
    $hasFilters = ($showKeyword && request()->filled('keyword'))
        || request()->filled('category_id')
        || request()->filled('subcategory_id')
        || request()->filled('city_id')
        || request()->filled('provider_type')
        || ($showRemote && request()->filled('remote'))
        || request()->filled('sort');
@endphp

<div class="delni-filters">
    <form method="GET" action="{{ $action }}" class="delni-filters__form">

        <header class="delni-filters__header">
            <div class="delni-filters__title-group">
                <h3>مرشحات البحث</h3>
                <p>ضيّق نتائج البحث حسب احتياجك الفعلي.</p>
            </div>

            @if($hasFilters)
                <a href="{{ $clearUrl }}" class="delni-filters__clear-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="icon-clear">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    مسح التصفية
                </a>
            @endif
        </header>

        <div class="delni-filters__body">

            @if($showKeyword)
            {{-- حقل كلمة البحث --}}
            <div class="delni-filter-field">
                <label for="keyword">كلمة البحث</label>
                <div class="input-wrapper">
                    <input
                        type="text"
                        id="keyword"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        maxlength="100"
                        placeholder="مثال: تصوير، سباكة، تصميم..."
                    >
                </div>
            </div>

            @endif

            {{-- فئة البحث --}}
            @if($categories)
                <div class="delni-filter-field">
                    <label for="category_id">الفئة</label>
                    <div class="select-wrapper">
                        <select id="category_id" name="category_id">
                            <option value="">جميع الفئات</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                    {{ $category->localized_name ?? $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            {{-- المدينة --}}
            @if($subcategories && $subcategories->isNotEmpty())
                <div class="delni-filter-field">
                    <label for="subcategory_id">الخدمة بالتحديد</label>
                    <div class="select-wrapper">
                        <select id="subcategory_id" name="subcategory_id">
                            <option value="">كل الخدمات</option>
                            @foreach($subcategories->groupBy('category_id') as $group)
                                @php($parentCategory = $group->first()?->category)
                                <optgroup label="{{ $parentCategory?->localized_name ?? $parentCategory?->name ?? 'خدمات' }}">
                                    @foreach($group as $subcategory)
                                        <option value="{{ $subcategory->id }}" data-category-id="{{ $subcategory->category_id }}" @selected((string) request('subcategory_id') === (string) $subcategory->id)>
                                            {{ $subcategory->localized_name ?? $subcategory->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            @if($cities)
                <div class="delni-filter-field">
                    <label for="city_id">المدينة</label>
                    <div class="select-wrapper">
                        <select id="city_id" name="city_id">
                            <option value="">جميع المدن</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>
                                    {{ $city->localized_name ?? $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            {{-- نوع المزود --}}
            @if($providerTypes)
                <div class="delni-filter-field">
                    <label for="provider_type">نوع المزود</label>
                    <div class="select-wrapper">
                        <select id="provider_type" name="provider_type">
                            <option value="">جميع الأنواع</option>
                            @foreach($providerTypes as $code => $name)
                                <option value="{{ $code }}" @selected((string) request('provider_type') === (string) $code)>
                                    {{ is_object($name) ? ($name->localized_name ?? $name->name) : $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            {{-- ترتيب النتائج --}}
            <div class="delni-filter-field">
                <label for="sort">ترتيب النتائج</label>
                <div class="select-wrapper">
                    <select id="sort" name="sort">
                        <option value="" @selected(!request('sort'))>الأكثر صلة</option>
                        <option value="rating" @selected(request('sort') === 'rating')>الأعلى تقييماً</option>
                        <option value="reviews" @selected(request('sort') === 'reviews')>الأكثر مراجعات</option>
                        <option value="newest" @selected(request('sort') === 'newest')>الأحدث</option>
                    </select>
                </div>
            </div>

            @if($showRemote)
            {{-- العمل عن بعد --}}
            <label class="delni-filter-check" for="remote">
                <input
                    type="checkbox"
                    id="remote"
                    name="remote"
                    value="1"
                    @checked(request('remote') == 1)
                >
                <span class="custom-checkbox"></span>
                <span class="check-text">
                    <strong>يدعم العمل عن بعد</strong>
                    <small>مناسب للخدمات الرقمية والاستشارات</small>
                </span>
            </label>
            @endif

        </div>

        <button type="submit" class="delni-filters__submit">
            <span>تطبيق فلاتر البحث</span>
        </button>
    </form>
</div>

@once
    @push('styles')
        <style>
            :root {
                --delni-orange: #F1620F;
                --delni-orange-hover: #d55309;
                --delni-dark: #0B1A34;
                --delni-text-muted: #5D5959;
                --delni-gray-bg: #FCFBFB;
                --delni-border: #E7E7E7;
                --delni-radius-lg: 20px;
                --delni-radius-md: 12px;
                --delni-font: system-ui, -apple-system, sans-serif;
            }

            .delni-filters {
                border-radius: var(--delni-radius-lg);
                background: #ffffff;
                border: 1px solid var(--delni-border);
                box-shadow: 0 10px 30px rgba(11, 26, 52, 0.04);
                overflow: hidden;
                font-family: var(--delni-font);
            }

            .delni-filters__form {
                display: flex;
                flex-direction: column;
                padding: 1.25rem;
            }

            .delni-filters__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid var(--delni-border);
                margin-bottom: 1.25rem;
            }

            .delni-filters__header h3 {
                margin: 0;
                color: var(--delni-dark);
                font-size: 1.1rem;
                font-weight: 800;
                line-height: 1.2;
            }

            .delni-filters__header p {
                margin: 0.25rem 0 0;
                color: var(--delni-text-muted);
                font-size: 0.8rem;
                font-weight: 500;
            }

            .delni-filters__clear-btn {
                color: var(--delni-orange);
                text-decoration: none;
                font-size: 0.85rem;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                transition: color 0.2s ease;
            }

            .delni-filters__clear-btn:hover {
                color: var(--delni-orange-hover);
            }

            .delni-filters__clear-btn .icon-clear {
                width: 15px;
                height: 15px;
            }

            .delni-filters__body {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .delni-filter-field {
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
            }

            .delni-filter-field label {
                color: var(--delni-dark);
                font-size: 0.85rem;
                font-weight: 700;
            }

            .input-wrapper, .select-wrapper {
                position: relative;
                width: 100%;
            }

            .delni-filter-field input,
            .delni-filter-field select {
                width: 100%;
                height: 46px;
                padding: 0 1rem;
                border-radius: var(--delni-radius-md);
                border: 1px solid var(--delni-border);
                background: var(--delni-gray-bg);
                color: var(--delni-dark);
                font-size: 0.88rem;
                font-weight: 600;
                outline: none;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                -webkit-appearance: none;
                appearance: none;
            }

            /* سهم مخصص للقوائم المنسدلة */
            .select-wrapper::after {
                content: "";
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                width: 10px;
                height: 6px;
                background-color: var(--delni-dark);
                clip-path: polygon(100% 0%, 0 0%, 50% 100%);
                pointer-events: none;
                opacity: 0.7;
            }

            .delni-filter-field input::placeholder {
                color: #A09C9C;
                font-weight: 500;
            }

            .delni-filter-field input:focus,
            .delni-filter-field select:focus {
                border-color: var(--delni-orange);
                background: #ffffff;
                box-shadow: 0 0 0 4px rgba(241, 98, 15, 0.08);
            }

            /* صندوق الاختيار المحسّن (Checkbox) */
            .delni-filter-check {
                display: flex;
                align-items: flex-start;
                gap: 0.85rem;
                padding: 0.9rem;
                border-radius: var(--delni-radius-md);
                background: var(--delni-gray-bg);
                border: 1px solid var(--delni-border);
                cursor: pointer;
                position: relative;
                transition: all 0.2s ease;
                user-select: none;
            }

            .delni-filter-check input {
                position: absolute;
                opacity: 0;
                cursor: pointer;
                height: 0;
                width: 0;
            }

            .custom-checkbox {
                width: 20px;
                height: 20px;
                border: 2px solid var(--delni-border);
                border-radius: 6px;
                background: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                margin-top: 2px;
                transition: all 0.15s ease;
            }

            .delni-filter-check input:checked ~ .custom-checkbox {
                background: var(--delni-orange);
                border-color: var(--delni-orange);
            }

            .custom-checkbox::after {
                content: "";
                display: none;
                width: 5px;
                height: 9px;
                border: solid white;
                border-width: 0 2px 2px 0;
                transform: rotate(45deg);
                margin-bottom: 2px;
            }

            .delni-filter-check input:checked ~ .custom-checkbox::after {
                display: block;
            }

            .check-text {
                display: flex;
                flex-direction: column;
                gap: 0.15rem;
            }

            .check-text strong {
                color: var(--delni-dark);
                font-size: 0.88rem;
                font-weight: 700;
            }

            .check-text small {
                color: var(--delni-text-muted);
                font-size: 0.78rem;
                line-height: 1.4;
                font-weight: 500;
            }

            /* زر الإرسال الأساسي */
            .delni-filters__submit {
                margin-top: 1.25rem;
                min-height: 48px;
                border: 0;
                border-radius: var(--delni-radius-md);
                background: var(--delni-orange);
                color: #ffffff;
                font-size: 0.95rem;
                font-weight: 700;
                cursor: pointer;
                box-shadow: 0 8px 20px rgba(241, 98, 15, 0.15);
                transition: all 0.2s ease;
            }

            .delni-filters__submit:hover {
                background: var(--delni-orange-hover);
                transform: translateY(-1px);
                box-shadow: 0 10px 24px rgba(241, 98, 15, 0.22);
            }

            .delni-filters__submit:active {
                transform: translateY(0);
            }

            /* دعم الشاشات الكبيرة (التابلت والكمبيوتر) لعدم أخذ مساحة طولية مفرطة */
            @media (min-width: 768px) {
                .delni-filters__body {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 1.25rem;
                }
                .delni-filter-check {
                    grid-column: span 2;
                }
            }
        </style>
    @endpush
@endonce
