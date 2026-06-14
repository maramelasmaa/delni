@props([
    'action',
    'cities' => collect(),
    'categories' => collect(),
    'resetUrl',
    'showKeyword' => false,
    'showCategory' => false,
    'showCity' => true,
    'sort' => true,
    'cityUrls' => [],
    'cityResetUrl' => null,
    'keywordPlaceholder' => 'ابحث...',
])

@php
    $activeCategorySlug = request('category') ?: $categories->firstWhere('id', (int) request('category_id'))?->slug;
    $activeCitySlug = request('city') ?: $cities->firstWhere('id', (int) request('city_id'))?->slug;
    $hasFilters = request()->anyFilled(['keyword', 'category', 'category_id', 'city', 'city_id', 'sort']);
@endphp

<button type="button" class="browse-filter-trigger" data-filter-sheet-trigger>
    <x-render-icon icon="heroicon-o-adjustments-horizontal" />
    <span>تصفية</span>
    @if($hasFilters)
        <small></small>
    @endif
</button>
<div class="browse-filter-overlay" data-filter-sheet-close></div>

<form
    method="GET"
    action="{{ $action }}"
    class="browse-filters"
    data-auto-filter
    data-city-urls='@json($cityUrls)'
    data-city-reset-url="{{ $cityResetUrl }}"
    data-city-in-path="{{ request()->attributes->has('active_city') ? 'true' : 'false' }}"
>
    <div class="browse-filters__sheet-head">
        <strong>تصفية النتائج</strong>
        <button type="button" data-filter-sheet-close aria-label="إغلاق">
            <x-render-icon icon="heroicon-o-x-mark" />
        </button>
    </div>

    @if($showKeyword)
        <label class="browse-filters__field browse-filters__field--search">
            <span>البحث</span>
            <span class="browse-filters__input">
                <x-render-icon icon="heroicon-o-magnifying-glass" />
                <input
                    type="search"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    maxlength="100"
                    placeholder="{{ $keywordPlaceholder }}"
                    autocomplete="off"
                    data-auto-filter-input
                >
            </span>
        </label>
    @endif

    @if($showCategory && $categories->isNotEmpty())
        <label class="browse-filters__field">
            <span>الفئة</span>
            <select name="category" data-auto-filter-control>
                <option value="">كل الفئات</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" @selected($activeCategorySlug === $category->slug)>
                        {{ $category->localized_name ?? $category->name }}
                    </option>
                @endforeach
            </select>
        </label>
    @endif

    @if($showCity && $cities->isNotEmpty())
        <label class="browse-filters__field">
            <span>المدينة</span>
            <select name="city" data-auto-filter-control>
                <option value="">كل المدن</option>
                @foreach($cities as $city)
                    <option value="{{ $city->slug }}" @selected($activeCitySlug === $city->slug)>
                        {{ $city->localized_name ?? $city->name }}
                    </option>
                @endforeach
            </select>
        </label>
    @endif

    @if($sort)
        <label class="browse-filters__field">
            <span>الترتيب</span>
            <select name="sort" data-auto-filter-control>
                <option value="" @selected(! request('sort'))>الأفضل لك</option>
                <option value="rating" @selected(request('sort') === 'rating')>الأعلى تقييما</option>
                <option value="reviews" @selected(request('sort') === 'reviews')>الأكثر تقييما</option>
                <option value="newest" @selected(request('sort') === 'newest')>الأحدث</option>
            </select>
        </label>
    @endif

    @if($hasFilters)
        <a href="{{ $resetUrl }}" class="browse-filters__reset">
            <x-render-icon icon="heroicon-o-x-mark" />
            <span>مسح</span>
        </a>
    @endif

    <noscript>
        <button type="submit" class="browse-filters__reset">تطبيق</button>
    </noscript>
</form>

@once
    @push('styles')
        <style>
            .browse-filters {
                display: flex;
                align-items: end;
                gap: .55rem;
                overflow-x: auto;
                padding: .8rem .05rem .25rem;
                scrollbar-width: none;
                transition: opacity .16s ease;
            }
            .browse-filters::-webkit-scrollbar { display: none; }
            .browse-filters.is-applying {
                opacity: .68;
                pointer-events: none;
            }

            .browse-filter-trigger,
            .browse-filter-overlay,
            .browse-filters__sheet-head {
                display: none;
            }

            .browse-filters__field {
                flex: 0 0 auto;
                min-width: min(44vw, 180px);
                display: grid;
                gap: .28rem;
            }
            .browse-filters__field--search {
                min-width: min(70vw, 260px);
            }

            .browse-filters__field > span:first-child {
                color: #64748B;
                font-size: .68rem;
                font-weight: 900;
                padding-inline: .25rem;
            }

            .browse-filters select,
            .browse-filters__input {
                min-height: 42px;
                border: 1px solid var(--delni-border);
                border-radius: 14px;
                background: #fff;
                color: var(--delni-navy);
                font: inherit;
                font-size: .8rem;
                font-weight: 850;
                outline: none;
            }

            .browse-filters select {
                padding: 0 .78rem;
                cursor: pointer;
            }

            .browse-filters__input {
                display: flex;
                align-items: center;
                gap: .45rem;
                padding: 0 .72rem;
            }
            .browse-filters__input svg {
                width: 16px;
                height: 16px;
                color: #94A3B8;
                flex-shrink: 0;
            }
            .browse-filters__input input {
                width: 100%;
                min-width: 0;
                border: 0;
                outline: 0;
                background: transparent;
                color: inherit;
                font: inherit;
            }

            .browse-filters__reset {
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .35rem;
                padding: .55rem .85rem;
                border-radius: 14px;
                border: 1px solid rgba(241,98,15,.22);
                background: #FFF7ED;
                color: var(--delni-primary);
                font-size: .8rem;
                font-weight: 900;
                text-decoration: none;
                white-space: nowrap;
            }
            .browse-filters__reset svg { width: 16px; height: 16px; }

            [data-theme="dark"] .browse-filters__field > span:first-child { color: #94A3B8; }
            [data-theme="dark"] .browse-filters select,
            [data-theme="dark"] .browse-filters__input {
                background: #1E293B;
                border-color: #334155;
                color: #F1F5F9;
                color-scheme: dark;
            }
            [data-theme="dark"] .browse-filters__reset {
                background: rgba(241,98,15,.12);
                border-color: rgba(241,98,15,.25);
                color: #FB923C;
            }

            @media (max-width: 700px) {
                .browse-filter-trigger {
                    min-height: 42px;
                    width: fit-content;
                    display: inline-flex;
                    align-items: center;
                    gap: .42rem;
                    padding: .52rem .8rem;
                    border: 1px solid var(--delni-border);
                    border-radius: 999px;
                    background: #fff;
                    color: var(--delni-navy);
                    font: inherit;
                    font-size: .82rem;
                    font-weight: 950;
                    box-shadow: var(--delni-shadow-sm);
                    cursor: pointer;
                }
                .browse-filter-trigger svg {
                    width: 17px;
                    height: 17px;
                    color: var(--delni-primary);
                }
                .browse-filter-trigger small {
                    width: 7px;
                    height: 7px;
                    border-radius: 999px;
                    background: var(--delni-primary);
                }

                .browse-filter-overlay {
                    position: fixed;
                    inset: 0;
                    z-index: 80;
                    background: rgba(2,6,23,.38);
                }

                .browse-filters {
                    position: fixed;
                    inset-inline: 0;
                    bottom: 0;
                    z-index: 90;
                    display: grid;
                    gap: .75rem;
                    max-height: min(78vh, 620px);
                    overflow-y: auto;
                    padding: .9rem 1rem calc(1rem + env(safe-area-inset-bottom));
                    border: 1px solid var(--delni-border);
                    border-bottom: 0;
                    border-radius: 22px 22px 0 0;
                    background: #fff;
                    box-shadow: 0 -18px 44px rgba(2,6,23,.18);
                    transform: translateY(105%);
                    transition: transform .22s ease;
                }

                .browse-filters__sheet-head {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                }
                .browse-filters__sheet-head strong {
                    color: var(--delni-navy);
                    font-size: .95rem;
                    font-weight: 950;
                }
                .browse-filters__sheet-head button {
                    width: 38px;
                    height: 38px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border: 1px solid var(--delni-border);
                    border-radius: 12px;
                    background: #F8FAFC;
                    color: var(--delni-navy);
                }
                .browse-filters__sheet-head svg {
                    width: 18px;
                    height: 18px;
                }

                .browse-filters__field,
                .browse-filters__field--search {
                    min-width: 0;
                    width: 100%;
                }

                .browse-filter-overlay.is-open {
                    display: block;
                }
                .browse-filters.is-open {
                    transform: translateY(0);
                }

                [data-theme="dark"] .browse-filter-trigger,
                [data-theme="dark"] .browse-filters {
                    background: #1E293B;
                    border-color: #334155;
                    color: #F1F5F9;
                }
                [data-theme="dark"] .browse-filters__sheet-head strong { color: #F1F5F9; }
                [data-theme="dark"] .browse-filters__sheet-head button {
                    background: #0F172A;
                    border-color: #334155;
                    color: #F1F5F9;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                if (window.delniAutoFiltersReady) {
                    return;
                }

                window.delniAutoFiltersReady = true;
                const pendingTimers = new WeakMap();

                const redirectToCleanFilterUrl = (form, control) => {
                    if (!form || !control || control.name !== 'city') {
                        return false;
                    }

                    let urls = {};

                    try {
                        urls = JSON.parse(form.dataset.cityUrls || '{}');
                    } catch (error) {
                        urls = {};
                    }

                    const targetUrl = control.value
                        ? urls[control.value]
                        : form.dataset.cityResetUrl;

                    if (!targetUrl) {
                        return false;
                    }

                    const url = new URL(targetUrl, window.location.origin);
                    const params = new FormData(form);

                    params.delete('city');
                    params.delete('city_id');
                    params.delete('page');

                    for (const [name, value] of params.entries()) {
                        if (value !== '') {
                            url.searchParams.set(name, value);
                        }
                    }

                    window.location.assign(url.toString());

                    return true;
                };

                const submitFilterForm = (form, control = null) => {
                    if (!form || form.dataset.submitting === 'true') {
                        return;
                    }

                    if (redirectToCleanFilterUrl(form, control)) {
                        return;
                    }

                    form.dataset.submitting = 'true';
                    form.classList.add('is-applying');
                    form.setAttribute('aria-busy', 'true');
                    form.querySelectorAll('[name="page"]').forEach((field) => field.remove());

                    form.querySelectorAll('input, select').forEach((field) => {
                        if (field.name && field.value === '') {
                            field.disabled = true;
                        }
                    });

                    if (form.dataset.cityInPath === 'true' && control?.name !== 'city') {
                        form.querySelectorAll('[name="city"]').forEach((field) => {
                            field.disabled = true;
                        });
                    }

                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                        return;
                    }

                    form.submit();
                };

                document.addEventListener('change', (event) => {
                    const control = event.target.closest('[data-auto-filter-control]');

                    if (!control) {
                        return;
                    }

                    submitFilterForm(control.form, control);
                });

                document.addEventListener('input', (event) => {
                    const input = event.target.closest('[data-auto-filter-input]');

                    if (!input) {
                        return;
                    }

                    window.clearTimeout(pendingTimers.get(input));
                    pendingTimers.set(input, window.setTimeout(() => {
                        const value = input.value.trim();

                        if (value.length === 0 || value.length >= 2) {
                            submitFilterForm(input.form);
                        }
                    }, 450));
                });

                document.addEventListener('keydown', (event) => {
                    const input = event.target.closest('[data-auto-filter-input]');

                    if (!input || event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();
                    submitFilterForm(input.form);
                });

                document.addEventListener('click', (event) => {
                    const trigger = event.target.closest('[data-filter-sheet-trigger]');
                    const close = event.target.closest('[data-filter-sheet-close]');

                    if (!trigger && !close) {
                        return;
                    }

                    const form = document.querySelector('.browse-filters');
                    const overlay = document.querySelector('.browse-filter-overlay');

                    if (!form || !overlay) {
                        return;
                    }

                    const open = !!trigger;
                    form.classList.toggle('is-open', open);
                    overlay.classList.toggle('is-open', open);
                    document.body.style.overflow = open ? 'hidden' : '';
                });
            })();
        </script>
    @endpush
@endonce
