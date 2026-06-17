@extends('public.layout')

@section('title', config('app.name') . ' - خدمات قريبة منك')

@php
    $isSearchView = isset($profiles);
    $resultCount = $isSearchView ? ($profiles?->total() ?? $profiles?->count() ?? 0) : 0;
@endphp

@section('content')
<div class="py-2 pb-10">

    {{-- Main Search Section (Common to both home and search result views for seamless UX) --}}
    <section class="mt-3 mb-5">
        <form action="{{ route('public.search') }}" method="GET" class="relative">
            <div class="relative flex items-center">
                <span class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                    <x-render-icon icon="heroicon-o-magnifying-glass" class="w-5 h-5" />
                </span>
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="ابحث عن خدمة أو مقدم خدمة..."
                    class="w-full pr-10 pl-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-900 dark:text-slate-100 text-xs font-bold shadow-xs focus:outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500"
                    required
                >
            </div>

        </form>
    </section>

    @if($isSearchView)
        {{-- Search results view --}}
        <div class="mt-5">
            <div class="flex items-center justify-between gap-4 mb-3 px-1">
                <div>
                    <span class="block text-primary text-[10px] md:text-xs font-black uppercase tracking-wider mb-0.5">نتائج البحث</span>
                    <h2 class="m-0 text-slate-900 dark:text-slate-100 text-sm md:text-base font-black">{{ number_format($resultCount) }} نتيجة</h2>
                </div>
                <a href="{{ route('home') }}" class="text-primary dark:text-orange-400 text-xs font-bold text-decoration-none hover:underline">مسح</a>
            </div>

            @if($profiles && $profiles->count() > 0)
                <x-provider-grid :providers="$profiles" :columns="2" />

                @if(method_exists($profiles, 'hasPages'))
                    <x-marketplace-pagination :paginator="$profiles" />
                @endif
            @else
                <x-empty-state
                    icon="heroicon-o-magnifying-glass"
                    title="لا توجد نتائج"
                    message="جرّب كلمة أخرى، أو اختر مدينة مختلفة."
                    actionLabel="مسح البحث"
                    actionUrl="{{ route('home') }}"
                />
            @endif
        </div>

    @else
        {{-- Main PWA Marketplace Home Flow --}}

        {{-- Section 3: Category Rail --}}
        <x-public.category-rail :categories="$categories" />

        {{-- Section 4: Paid Featured Providers --}}
        @if(isset($featuredProviders) && $featuredProviders->isNotEmpty())
            <section class="mt-8">
                <div class="flex items-center justify-between gap-4 mb-3 px-1">
                    <div>
                        <span class="inline-flex items-center gap-1 bg-primary text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full mb-0.5">مميز</span>
                        <h2 class="m-0 text-slate-900 dark:text-slate-100 text-sm md:text-base font-black">مميزون في دلني</h2>
                    </div>
                </div>

                {{-- Horizontal scroll rail on mobile, grid on desktop --}}
                <div class="flex gap-3 overflow-x-auto scrollbar-none py-2 px-1 scroll-smooth snap-x snap-mandatory lg:grid lg:grid-cols-3 lg:gap-4 lg:overflow-x-visible">
                    @foreach($featuredProviders as $provider)
                        <x-public.provider-card :provider="$provider" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Section 5: Stats Section --}}
        @if(isset($stats))
            <x-public.stat-card :stats="$stats" />
        @endif

        {{-- Section 7: Final Provider Call To Action --}}
        <div class="lp-cta">
            <div>
                <span>تقدم خدمة؟</span>
                <h2>اجعل ملفك مرئياً للعملاء</h2>
                <p>أنشئ حساب مزود وابدأ في الظهور في نتائج البحث والفئات.</p>
            </div>
            <a href="{{ $ctaWhatsappUrl ?? route('contact') }}"
               @if($ctaWhatsappUrl ?? false) target="_blank" rel="noopener" @endif>سجّل كمزود</a>
        </div>

    @endif

</div>
@endsection
