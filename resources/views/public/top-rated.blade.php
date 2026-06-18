@extends('public.layout')

@section('title', 'الأعلى تقييماً - ' . config('app.name'))

@php
    $providerCount = $providerCount ?? ($profiles?->total() ?? $profiles?->count() ?? 0);
@endphp

@section('content')
<div class="mx-auto grid w-full max-w-2xl gap-4 px-1 py-2.5 pb-8">
    <section class="grid gap-5 rounded-[2rem] border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-5">
        {{-- Header design same as category blade --}}
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 grid gap-4">
                <x-browse-trail :items="[
                    ['label' => 'الرئيسية', 'url' => route('home')],
                    ['label' => 'الأعلى تقييماً', 'active' => true],
                ]" />

                <div class="flex items-start gap-3">
                    <div class="flex h-16 w-16 flex-none items-center justify-center rounded-[1.4rem] bg-orange-50/50 text-primary shadow-xs ring-1 ring-orange-100/50 dark:bg-slate-950 dark:ring-slate-800">
                        <x-render-icon icon="heroicon-o-star" class="h-8 w-8" />
                    </div>

                    <div class="min-w-0 text-right">
                        <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-1 text-[11px] font-black text-primary dark:bg-orange-950/30 dark:text-orange-300">التميز</span>
                        <h1 class="mt-3 text-xl font-black leading-tight text-slate-955 dark:text-white md:text-2xl">الأعلى تقييماً</h1>
                    </div>
                </div>
            </div>

            <a href="{{ route('home') }}" class="inline-flex h-11 w-11 flex-none items-center justify-center rounded-2xl bg-slate-50 text-slate-700 shadow-xs ring-1 ring-slate-200 transition hover:bg-slate-100 hover:text-primary dark:bg-slate-950 dark:text-slate-200 dark:ring-slate-800 dark:hover:text-orange-300" aria-label="الرجوع إلى الرئيسية">
                <x-render-icon icon="heroicon-o-arrow-left" class="h-5 w-5" />
            </a>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800/60 my-1"></div>

        {{-- Category Filter Pills --}}
        @if($categories && $categories->count() > 0)
            <div class="flex gap-2 overflow-x-auto scrollbar-none py-1.5 px-1 scroll-smooth snap-x snap-mandatory">
                @php
                    $isNoCat = !request()->filled('category') && !request()->filled('category_id');
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['category_id' => null, 'category' => null, 'page' => null]) }}" 
                   class="inline-flex items-center justify-center shrink-0 px-4 py-2 rounded-full text-xs font-black transition-all border {{ $isNoCat ? 'bg-gradient-to-r from-primary to-orange-500 text-white border-transparent shadow-xs shadow-orange-500/10' : 'bg-slate-50 hover:bg-slate-100 dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700' }} text-decoration-none">
                    جميع التخصصات
                </a>

                @foreach($categories as $cat)
                    @php
                        $isSelected = (string) request('category_id') === (string) $cat->id || (string) request('category') === (string) $cat->slug;
                        $url = request()->fullUrlWithQuery(['category_id' => $cat->id, 'category' => null, 'page' => null]);
                    @endphp
                    <a href="{{ $url }}" 
                       class="inline-flex items-center justify-center shrink-0 px-4 py-2 rounded-full text-xs font-black transition-all border {{ $isSelected ? 'bg-gradient-to-r from-primary to-orange-500 text-white border-transparent shadow-xs shadow-orange-500/10' : 'bg-slate-50 hover:bg-slate-100 dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700' }} text-decoration-none snap-start">
                        {{ $cat->localized_name ?? $cat->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="grid min-w-0 gap-3.5">
            @if($profiles && $profiles->count() > 0)
                
                {{-- Olympics Podium Section (Displays only on Page 1 and when we have 1+ profiles) --}}
                @if($profiles->currentPage() === 1 && $profiles->count() >= 1)
                    @php
                        $podium = $profiles->values();
                        $gold = $podium[0];
                        $silver = $podium->count() >= 2 ? $podium[1] : null;
                        $bronze = $podium->count() >= 3 ? $podium[2] : null;
                    @endphp
                    
                    <div class="mt-2 mb-8 px-1">
                        {{-- Podium Title --}}
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-1.5 h-6 rounded-full bg-amber-500 shadow-sm shadow-amber-500/30"></span>
                            <h2 class="text-slate-900 dark:text-slate-100 text-sm md:text-base font-black m-0">المتصدرون</h2>
                        </div>

                        <div class="grid grid-cols-3 gap-2.5 sm:gap-6 items-end justify-center max-w-xl mx-auto pt-8 pb-3 bg-gradient-to-t from-slate-50/60 to-transparent dark:from-slate-900/30 rounded-3xl border border-slate-100/40 dark:border-slate-800/20 p-4 sm:p-6 shadow-xs">
                            
                            {{-- #2 Silver --}}
                            @if($silver)
                                <a href="{{ route('public.provider', $silver->slug) }}" class="flex flex-col items-center text-center group transition-all duration-300 text-decoration-none">
                                    <div class="relative w-15 h-15 sm:w-20 sm:h-20 rounded-2xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 flex items-center justify-center shadow-[0_0_12px_rgba(148,163,184,0.15)] group-hover:shadow-[0_0_20px_rgba(148,163,184,0.35)] group-hover:scale-103 transition-all duration-300">
                                        @php
                                            $sLogo = $silver->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($silver->logo) : null;
                                        @endphp
                                        @if($sLogo)
                                            <img src="{{ $sLogo }}" class="w-full h-full object-cover rounded-2xl" alt="">
                                        @else
                                            <div class="w-full h-full rounded-2xl bg-slate-50 dark:bg-slate-950 flex items-center justify-center">
                                                <span class="text-xl sm:text-2xl font-black text-slate-500">{{ mb_substr($silver->business_name ?? $silver->user?->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        
                                        {{-- Silver Medal --}}
                                        <span class="absolute -top-2.5 -right-2.5 w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 flex items-center justify-center text-[10px] font-black text-slate-600 dark:text-slate-300 shadow-sm">
                                            <span dir="ltr" class="font-sans">2</span>
                                        </span>
                                    </div>
                                    
                                    <h3 class="mt-2.5 mb-0.5 text-slate-800 dark:text-slate-200 text-[10px] sm:text-xs font-black line-clamp-1 w-full px-1 hover:text-primary transition-colors">
                                        {{ $silver->business_name ?? $silver->user?->name }}
                                    </h3>
                                    
                                    <div class="flex items-center gap-0.5 text-slate-600 dark:text-slate-400 text-[9px] sm:text-[10px] font-bold">
                                        <span class="text-slate-400 leading-none">★</span>
                                        <span dir="ltr" class="font-sans">{{ number_format($silver->approved_reviews_avg_rating ?? $silver->stats?->rating_avg ?? 0.0, 1) }}</span>
                                        <span class="text-slate-400 dark:text-slate-500 font-sans" dir="ltr">({{ $silver->approved_reviews_count ?? $silver->stats?->reviews_count ?? 0 }})</span>
                                    </div>
                                </a>
                            @else
                                <div></div>
                            @endif

                            {{-- #1 Gold (Elevated in center) --}}
                            <a href="{{ route('public.provider', $gold->slug) }}" class="flex flex-col items-center text-center group -translate-y-3.5 sm:-translate-y-5 transition-all duration-300 text-decoration-none">
                                <div class="relative w-18 h-18 sm:w-24 sm:h-24 rounded-2xl border-3 border-amber-400 bg-gradient-to-br from-amber-50/50 to-white dark:from-amber-950/10 dark:to-slate-900 flex items-center justify-center shadow-[0_0_15px_rgba(245,158,11,0.2)] dark:shadow-[0_0_25px_rgba(245,158,11,0.15)] group-hover:shadow-[0_0_25px_rgba(245,158,11,0.45)] group-hover:scale-103 transition-all duration-300">
                                    @php
                                        $gLogo = $gold->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($gold->logo) : null;
                                    @endphp
                                    @if($gLogo)
                                        <img src="{{ $gLogo }}" class="w-full h-full object-cover rounded-2xl" alt="">
                                    @else
                                        <div class="w-full h-full rounded-2xl bg-amber-50/20 dark:bg-amber-950/10 flex items-center justify-center">
                                            <span class="text-2xl sm:text-3xl font-black text-amber-500">{{ mb_substr($gold->business_name ?? $gold->user?->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Gold Crown floating above avatar --}}
                                    <span class="absolute -top-5 left-1/2 -translate-x-1/2 text-2xl z-20 drop-shadow-sm animate-bounce duration-1000">👑</span>

                                    {{-- Gold Medal Badge --}}
                                    <span class="absolute -top-2.5 -right-2.5 w-6 h-6 rounded-full bg-amber-400 border-2 border-amber-500 flex items-center justify-center text-[10px] font-black text-amber-950 shadow-sm">
                                        <span dir="ltr" class="font-sans">1</span>
                                    </span>
                                </div>
                                
                                <h3 class="mt-2.5 mb-0.5 text-slate-905 dark:text-slate-100 text-xs sm:text-sm font-black line-clamp-1 w-full px-1 hover:text-primary transition-colors">
                                    {{ $gold->business_name ?? $gold->user?->name }}
                                </h3>
                                
                                <div class="flex items-center gap-0.5 text-amber-600 dark:text-amber-400 text-[10px] sm:text-xs font-black">
                                    <span class="text-amber-500 leading-none">★</span>
                                    <span dir="ltr" class="font-sans">{{ number_format($gold->approved_reviews_avg_rating ?? $gold->stats?->rating_avg ?? 0.0, 1) }}</span>
                                    <span class="text-amber-500/70 dark:text-amber-500/50 font-sans" dir="ltr">({{ $gold->approved_reviews_count ?? $gold->stats?->reviews_count ?? 0 }})</span>
                                </div>
                            </a>

                            {{-- #3 Bronze --}}
                            @if($bronze)
                                <a href="{{ route('public.provider', $bronze->slug) }}" class="flex flex-col items-center text-center group transition-all duration-300 text-decoration-none">
                                    <div class="relative w-13 h-13 sm:w-18 sm:h-18 rounded-2xl border-2 border-amber-700/40 bg-white dark:bg-slate-900 flex items-center justify-center shadow-[0_0_12px_rgba(180,83,9,0.15)] group-hover:shadow-[0_0_20px_rgba(180,83,9,0.35)] group-hover:scale-103 transition-all duration-300">
                                        @php
                                            $bLogo = $bronze->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($bronze->logo) : null;
                                        @endphp
                                        @if($bLogo)
                                            <img src="{{ $bLogo }}" class="w-full h-full object-cover rounded-2xl" alt="">
                                        @else
                                            <div class="w-full h-full rounded-2xl bg-slate-50 dark:bg-slate-950 flex items-center justify-center">
                                                <span class="text-lg sm:text-xl font-black text-amber-700">{{ mb_substr($bronze->business_name ?? $bronze->user?->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        
                                        {{-- Bronze Medal --}}
                                        <span class="absolute -top-2 -right-2 w-5.5 h-5.5 rounded-full bg-amber-600 border-2 border-amber-700 flex items-center justify-center text-[9px] font-black text-amber-100 shadow-xs">
                                            <span dir="ltr" class="font-sans">3</span>
                                        </span>
                                    </div>
                                    
                                    <h3 class="mt-2 mb-0.5 text-slate-800 dark:text-slate-200 text-[10px] sm:text-xs font-black line-clamp-1 w-full px-1 hover:text-primary transition-colors">
                                        {{ $bronze->business_name ?? $bronze->user?->name }}
                                    </h3>
                                    
                                    <div class="flex items-center gap-0.5 text-slate-600 dark:text-slate-400 text-[9px] sm:text-[10px] font-bold">
                                        <span class="text-slate-400 leading-none">★</span>
                                        <span dir="ltr" class="font-sans">{{ number_format($bronze->approved_reviews_avg_rating ?? $bronze->stats?->rating_avg ?? 0.0, 1) }}</span>
                                        <span class="text-slate-400 dark:text-slate-500 font-sans" dir="ltr">({{ $bronze->approved_reviews_count ?? $bronze->stats?->reviews_count ?? 0 }})</span>
                                    </div>
                                </a>
                            @else
                                <div></div>
                            @endif

                        </div>
                    </div>
                @endif

                {{-- Stacked Row Layout --}}
                <div class="grid gap-3 grid-cols-1 w-full">
                    @foreach($profiles as $index => $provider)
                        @if($profiles->currentPage() === 1 && $index < 3)
                            @continue
                        @endif
                        <x-provider-row-card :provider="$provider" />
                    @endforeach
                </div>
                
                @if(method_exists($profiles, 'hasPages') && $profiles->hasPages())
                    <x-marketplace-pagination :paginator="$profiles" />
                @endif
            @else
                <x-empty-state
                    icon="heroicon-o-star"
                    title="لا توجد نتائج"
                    message="لا يوجد مقدمو خدمات يطابقون خيارات التصفية المحددة."
                    actionLabel="مسح المرشحات"
                    actionUrl="{{ route('public.top-rated') }}"
                />
            @endif
        </div>
    </section>
</div>
@endsection
