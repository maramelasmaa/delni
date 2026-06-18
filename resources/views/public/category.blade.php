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

<div class="mx-auto grid w-full max-w-2xl gap-4 px-1 py-2.5 pb-8">
    <section class="grid gap-5 rounded-[2rem] border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 grid gap-4">
                <x-browse-trail :items="[
                    ['label' => 'التخصصات', 'url' => route('public.categories')],
                    ['label' => $category->localized_name ?? $category->name, 'active' => true],
                ]" />

                <div class="flex items-start gap-3">
                    @if($category->icon)
                        <div class="flex h-16 w-16 flex-none items-center justify-center rounded-[1.4rem] bg-slate-50 text-primary shadow-xs ring-1 ring-orange-100/50 dark:bg-slate-950 dark:ring-slate-800 [&_svg]:h-8 [&_svg]:w-8">
                            <x-svg-icon :icon="$category->icon" size="30" />
                        </div>
                    @else
                        <div class="flex h-16 w-16 flex-none items-center justify-center rounded-[1.4rem] bg-slate-50 text-primary shadow-xs ring-1 ring-orange-100/50 dark:bg-slate-950 dark:ring-slate-800">
                            <x-render-icon icon="heroicon-o-briefcase" class="h-8 w-8" />
                        </div>
                    @endif

                    <div class="min-w-0 text-right">
                        <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-1 text-[11px] font-black text-primary dark:bg-orange-950/30 dark:text-orange-300">تخصص رئيسي</span>
                        <h1 class="mt-3 text-xl font-black leading-tight text-slate-950 dark:text-white md:text-2xl">{{ $category->localized_name ?? $category->name }}</h1>
                    </div>
                </div>
            </div>

            <a href="{{ route('public.categories') }}" class="inline-flex h-11 w-11 flex-none items-center justify-center rounded-2xl bg-slate-50 text-slate-700 shadow-xs ring-1 ring-slate-200 transition hover:bg-slate-100 hover:text-primary dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800 dark:hover:text-orange-300" aria-label="الرجوع إلى التخصصات">
                <x-render-icon icon="heroicon-o-arrow-left" class="h-5 w-5" />
            </a>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800/60 my-1"></div>

        <form
            method="GET"
            action="{{ url()->current() }}"
            class="grid gap-4 transition-opacity duration-200 [&.is-applying]:pointer-events-none [&.is-applying]:opacity-70"
            id="categoryFilterForm"
            data-auto-filter
            data-city-urls='@json($cityFilterUrls)'
            data-city-reset-url="{{ route('public.category', $category->slug) }}?clear_city=1"
            data-city-in-path="{{ request()->routeIs('public.category.city') ? 'true' : 'false' }}"
        >
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_160px]">
                <label class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200 transition-all focus-within:bg-white focus-within:ring-primary/30 dark:bg-slate-950 dark:ring-slate-800 dark:focus-within:ring-orange-500/30">
                    <x-render-icon icon="heroicon-o-magnifying-glass" class="h-4.5 w-4.5 flex-none text-slate-400" />
                    <input
                        type="search"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        maxlength="100"
                        placeholder="ابحث في {{ $category->localized_name ?? $category->name }}..."
                        autocomplete="off"
                        class="min-w-0 flex-1 border-0 bg-transparent text-base md:text-sm font-bold text-slate-900 outline-none placeholder:text-slate-400 dark:text-slate-50"
                        data-auto-filter-input
                    >
                </label>

                <div class="grid grid-cols-2 gap-3 md:contents">
                    <label class="relative flex items-center rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200 transition-all focus-within:bg-white focus-within:ring-primary/30 dark:bg-slate-950 dark:ring-slate-800 dark:focus-within:ring-orange-500/30">
                        <x-render-icon icon="heroicon-o-building-office-2" class="pointer-events-none absolute right-4 h-4 w-4 text-slate-400" />
                        <select name="provider_type" class="w-full appearance-none border-0 bg-transparent pr-7 text-base md:text-sm font-black text-slate-800 outline-none dark:text-slate-200" data-auto-filter-control>
                            <option value="">نوع النشاط</option>
                            @foreach(($providerTypes ?? []) as $typeCode => $typeLabel)
                                <option value="{{ $typeCode }}" @selected(request('provider_type') === $typeCode)>{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                        <x-render-icon icon="heroicon-o-chevron-down" class="pointer-events-none absolute left-4 h-3.5 w-3.5 text-slate-400" />
                    </label>

                    <label class="relative flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200 transition-all hover:bg-white hover:ring-primary/30 dark:bg-slate-950 dark:ring-slate-800 dark:hover:ring-orange-500/30 cursor-pointer select-none">
                        <span class="flex items-center gap-1.5">
                            <x-render-icon icon="heroicon-o-globe-alt" class="h-4.5 w-4.5 text-primary flex-none" />
                            <span class="text-xs font-black text-slate-800 dark:text-slate-200">عمل عن بُعد</span>
                        </span>
                        <input type="checkbox" name="remote" value="1" @checked(request('remote') == 1) class="sr-only peer" data-auto-filter-control>
                        <span class="relative w-8 h-4.5 bg-slate-200 dark:bg-slate-850 rounded-full transition-colors peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-0.5 after:right-0.5 after:bg-white after:rounded-full after:h-3.5 after:w-3.5 after:transition-transform peer-checked:-translate-x-3.5"></span>
                    </label>
                </div>
            </div>

            @if($visibleSubcategories->isNotEmpty())
                @php
                    $selectedSlugs = is_array(request('services')) ? request('services') : (request('services') ? explode(',', (string) request('services')) : []);
                    if (empty($selectedSlugs) && request('service')) {
                        $selectedSlugs = is_array(request('service')) ? request('service') : [request('service')];
                    }
                    $selectedSlugs = array_filter($selectedSlugs);
                    $selectedCount = count($selectedSlugs);
                    $isAllChecked = ($selectedCount === 0);
                @endphp

                <div class="grid gap-3">
                    <div class="flex overflow-x-auto md:overflow-x-visible md:flex-wrap whitespace-nowrap md:whitespace-normal scrollbar-none gap-2 pb-1.5 pt-0.5 snap-x -mx-4 px-4 md:-mx-0 md:px-0">
                        <div class="snap-start flex-none">
                            @if($isAllChecked)
                                <div class="inline-flex min-h-11 items-center gap-2 rounded-full bg-orange-500 px-4 text-xs font-black text-white shadow-xs dark:bg-orange-500 dark:text-white">
                                    <x-render-icon icon="heroicon-o-check" class="h-3.5 w-3.5 flex-none" />
                                    <span>الكل</span>
                                </div>
                            @else
                                <a href="{{ route(request()->route()->getName(), request()->route()->parameters() + request()->except(['services', 'service', 'page'])) }}" class="inline-flex min-h-11 items-center gap-2 rounded-full bg-slate-100 px-4 text-xs font-black text-slate-700 transition hover:bg-slate-200 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-800">
                                    <x-render-icon icon="app-categories" class="h-4 w-4 flex-none text-slate-400 dark:text-slate-500" />
                                    <span>الكل</span>
                                </a>
                            @endif
                        </div>

                        @foreach($visibleSubcategories as $index => $sub)
                            @php
                                $isChecked = in_array($sub->slug, $selectedSlugs);
                            @endphp
                            <div class="snap-start flex-none">
                                <label class="inline-flex min-h-11 cursor-pointer select-none items-center gap-2 rounded-full px-4 text-xs font-black transition-all {{ $isChecked ? 'bg-orange-500 text-white shadow-xs dark:bg-orange-500' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                                    <input type="checkbox" name="services[]" value="{{ $sub->slug }}" @checked($isChecked) class="hidden" data-auto-filter-control>

                                    @if($isChecked)
                                        <x-render-icon icon="heroicon-o-check" class="h-3.5 w-3.5 flex-none" />
                                    @endif

                                    <span class="truncate">{{ $sub->localized_name ?? $sub->name }}</span>

                                    <span class="flex-none {{ $isChecked ? 'text-white/80' : 'text-slate-400 dark:text-slate-500' }}">
                                        @if($sub->icon)
                                            <x-svg-icon :icon="$sub->icon" size="16" class="w-4 h-4 text-current" />
                                        @elseif($sub->getRawOriginal('icon'))
                                            <x-render-icon :icon="$sub->getRawOriginal('icon')" class="w-4 h-4 text-current" />
                                        @else
                                            <x-render-icon icon="heroicon-o-briefcase" class="w-4 h-4 text-current" />
                                        @endif
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($selectedCount > 0)
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-dashed border-slate-200 pt-3 dark:border-slate-800">
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($visibleSubcategories as $sub)
                                @if(in_array($sub->slug, $selectedSlugs))
                                    @php
                                        $remainingSlugs = array_diff($selectedSlugs, [$sub->slug]);
                                        $deselectUrl = route(request()->route()->getName(), request()->route()->parameters() + request()->except(['page']) + ['services' => $remainingSlugs]);
                                    @endphp
                                    <a href="{{ $deselectUrl }}" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-800">
                                        <span>{{ $sub->localized_name ?? $sub->name }}</span>
                                        <x-render-icon icon="heroicon-o-x-mark" class="h-3.5 w-3.5 text-slate-400" />
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        <a href="{{ route(request()->route()->getName(), request()->route()->parameters() + request()->except(['services', 'service', 'page'])) }}" class="text-xs font-black text-primary transition hover:text-orange-700 dark:text-orange-300 dark:hover:text-orange-200">
                            إعادة تعيين
                        </a>
                    </div>
                @endif
            @endif
        </form>
    </section>

    <section class="mt-1 grid min-w-0 gap-3.5">
        <div class="flex items-end justify-between gap-4 px-1">
            <div class="text-right">
                <span class="block text-[11px] font-black uppercase tracking-wider text-primary">نتائج البحث</span>
                <h2 class="mt-1 text-base font-black leading-tight text-slate-900 dark:text-slate-100">مقدمو الخدمات</h2>
            </div>

            <div class="rounded-full bg-white px-3 py-1.5 text-xs font-black text-slate-500 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800">
                {{ number_format($totalCount) }} نتيجة
            </div>
        </div>

        @if($profiles && $profiles->count() > 0)
            <x-provider-grid :providers="$profiles" :columns="2" />
            <x-marketplace-pagination :paginator="$profiles" />
        @else
            <x-empty-state
                icon="heroicon-o-magnifying-glass"
                title="لا توجد نتائج"
                message="جرّب تعديل البحث أو اختيار خدمة فرعية مختلفة."
                actionLabel="مسح الفلاتر"
                actionUrl="{{ route('public.category', $category->slug) }}"
            />
        @endif
    </section>
</div>
@endsection
