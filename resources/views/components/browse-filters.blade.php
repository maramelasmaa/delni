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

<button type="button" class="inline-flex md:hidden items-center gap-1.5 min-h-[42px] px-4 py-2 border border-slate-200 dark:border-slate-800 rounded-full bg-white dark:bg-slate-900 text-slate-850 dark:text-slate-200 text-xs font-black shadow-xs cursor-pointer hover:border-primary/20 transition-all [&>svg]:w-4.5 [&>svg]:h-4.5 [&>svg]:text-primary" data-filter-sheet-trigger>
    <x-render-icon icon="heroicon-o-adjustments-horizontal" />
    <span>تصفية</span>
    @if($hasFilters)
        <small class="w-1.5 h-1.5 rounded-full bg-primary"></small>
    @endif
</button>
<div class="hidden fixed inset-0 z-[80] bg-slate-950/40 backdrop-blur-xs transition-opacity duration-300 [&.is-open]:block" data-filter-sheet-close></div>

<form
    method="GET"
    action="{{ $action }}"
    class="flex flex-col items-stretch gap-3.5 transition-all duration-300 [&.is-applying]:opacity-70 [&.is-applying]:pointer-events-none fixed inset-x-0 bottom-0 z-[90] max-h-[min(78vh,_620px)] overflow-y-auto p-4 pb-[calc(1.25rem+env(safe-area-inset-bottom))] border border-slate-200 dark:border-slate-800 border-b-0 rounded-t-3xl bg-white dark:bg-slate-900 shadow-2xl translate-y-[105%] [&.is-open]:translate-y-0 md:static md:translate-y-0 md:flex md:flex-row md:items-end md:gap-2 md:p-0 md:bg-transparent md:border-0 md:shadow-none md:overflow-visible md:h-auto md:max-h-none"
    data-auto-filter
    data-city-urls='@json($cityUrls)'
    data-city-reset-url="{{ $cityResetUrl }}"
    data-city-in-path="{{ request()->attributes->has('active_city') ? 'true' : 'false' }}"
>
    <div class="flex md:hidden items-center justify-between gap-4 pb-2 border-b border-slate-105 dark:border-slate-800">
        <strong class="text-slate-900 dark:text-slate-100 text-sm md:text-base font-black">تصفية النتائج</strong>
        <button type="button" class="flex items-center justify-center w-9.5 h-9.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 hover:text-primary transition-colors cursor-pointer [&>svg]:w-4.5 [&>svg]:h-4.5" data-filter-sheet-close aria-label="إغلاق">
            <x-render-icon icon="heroicon-o-x-mark" />
        </button>
    </div>

    @if($showKeyword)
        <label class="flex-none w-full md:w-[220px] flex flex-col gap-1">
            <span class="text-slate-500 dark:text-slate-400 text-[10px] font-black uppercase tracking-wider px-1">البحث</span>
            <span class="flex items-center gap-2 min-h-[42px] px-3.5 border border-slate-200 dark:border-slate-800 rounded-2xl bg-slate-50 dark:bg-slate-950 text-slate-400 focus-within:border-primary/45 focus-within:ring-4 focus-within:ring-primary/10 transition-all">
                <x-render-icon icon="heroicon-o-magnifying-glass" class="w-4.5 h-4.5 text-slate-400 flex-none" />
                <input
                    type="search"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    maxlength="100"
                    placeholder="{{ $keywordPlaceholder }}"
                    autocomplete="off"
                    class="w-full min-w-0 border-0 outline-none bg-transparent text-slate-950 dark:text-slate-50 font-semibold text-base md:text-sm placeholder-slate-400"
                    data-auto-filter-input
                >
            </span>
        </label>
    @endif

    @if($showCategory && $categories->isNotEmpty())
        <label class="flex-none w-full md:w-[150px] flex flex-col gap-1">
            <span class="text-slate-500 dark:text-slate-400 text-[10px] font-black uppercase tracking-wider px-1">التخصص</span>
            <select name="category" class="min-h-[42px] px-3.5 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 font-semibold text-base md:text-sm outline-none cursor-pointer focus:border-primary/45 focus:ring-4 focus:ring-primary/10 transition-all w-full" data-auto-filter-control>
                <option value="">كل التخصصات</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" @selected($activeCategorySlug === $category->slug)>
                        {{ $category->localized_name ?? $category->name }}
                    </option>
                @endforeach
            </select>
        </label>
    @endif

    @if($showCity && $cities->isNotEmpty())
        <label class="flex-none w-full md:w-[150px] flex flex-col gap-1">
            <span class="text-slate-500 dark:text-slate-400 text-[10px] font-black uppercase tracking-wider px-1">المدينة</span>
            <select name="city" class="min-h-[42px] px-3.5 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 font-semibold text-base md:text-sm outline-none cursor-pointer focus:border-primary/45 focus:ring-4 focus:ring-primary/10 transition-all w-full" data-auto-filter-control>
                <option value="">كل المدن</option>
                @foreach($cities as $city)
                    <option value="{{ $city->slug }}" @selected($activeCitySlug === $city->slug)>
                        {{ $city->localized_name ?? $city->name }}
                    </option>
                @endforeach
            </select>
        </label>
    @endif

    <label class="flex-none w-full md:w-[140px] flex flex-col gap-1 cursor-pointer select-none">
        <span class="text-slate-500 dark:text-slate-400 text-[10px] font-black uppercase tracking-wider px-1">مكان العمل</span>
        <div class="flex items-center justify-between min-h-[42px] px-3.5 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 font-semibold text-xs md:text-sm hover:border-primary/20 dark:hover:border-slate-800 transition-all relative">
            <span class="flex items-center gap-1.5">
                <x-render-icon icon="heroicon-o-globe-alt" class="w-4 h-4 text-primary" />
                <span class="text-xs md:text-sm font-semibold">عن بُعد</span>
            </span>
            <input type="checkbox" name="remote" value="1" @checked(request('remote') == 1) class="sr-only peer" data-auto-filter-control>
            <span class="relative w-8 h-4.5 bg-slate-200 dark:bg-slate-800 rounded-full transition-colors peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-0.5 after:right-0.5 after:bg-white after:rounded-full after:h-3.5 after:w-3.5 after:transition-transform peer-checked:-translate-x-3.5"></span>
        </div>
    </label>

    @if($sort)
        <label class="flex-none w-full md:w-[150px] flex flex-col gap-1">
            <span class="text-slate-500 dark:text-slate-400 text-[10px] font-black uppercase tracking-wider px-1">الترتيب</span>
            <select name="sort" class="min-h-[42px] px-3.5 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-200 font-semibold text-base md:text-sm outline-none cursor-pointer focus:border-primary/45 focus:ring-4 focus:ring-primary/10 transition-all w-full" data-auto-filter-control>

                <option value="rating" @selected(request('sort') === 'rating')>الأعلى تقييما</option>
                <option value="reviews" @selected(request('sort') === 'reviews')>الأكثر تقييما</option>
                <option value="newest" @selected(request('sort') === 'newest')>الأحدث</option>
            </select>
        </label>
    @endif

    @if($hasFilters)
        <a href="{{ $resetUrl }}" class="flex-none min-h-[42px] inline-flex items-center justify-center gap-1.5 px-4 rounded-2xl border border-orange-500/20 dark:border-orange-500/35 bg-orange-50/50 dark:bg-orange-950/20 text-primary dark:text-orange-400 text-xs font-black text-decoration-none transition-all hover:bg-orange-50 dark:hover:bg-orange-950/30 [&>svg]:w-4 [&>svg]:h-4">
            <x-render-icon icon="heroicon-o-x-mark" />
            <span>مسح</span>
        </a>
    @endif

    <noscript>
        <button type="submit" class="flex-none min-h-[42px] inline-flex items-center justify-center gap-1.5 px-4 rounded-2xl border border-orange-500/20 dark:border-orange-500/35 bg-orange-50/50 dark:bg-orange-950/20 text-primary dark:text-orange-400 text-xs font-black text-decoration-none transition-all hover:bg-orange-50">تطبيق</button>
    </noscript>
</form>

@once
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
