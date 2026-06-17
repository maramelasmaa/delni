@props([
    'categories' => collect(),
])

<section class="mt-6">
    <div class="flex items-center justify-between gap-4 mb-3 px-1">
        <div>
            <span class="block text-primary text-[10px] md:text-xs font-black uppercase tracking-wider mb-0.5">تصفح</span>
            <h2 class="m-0 text-slate-900 dark:text-slate-100 text-sm md:text-base font-black">الفئات</h2>
        </div>
        <a href="{{ route('public.categories') }}" class="text-primary dark:text-orange-400 text-xs font-black text-decoration-none hover:underline">عرض الكل</a>
    </div>

    {{-- Horizontal scroll on mobile, flex-wrap on desktop --}}
    <div class="flex gap-3 overflow-x-auto scrollbar-none py-2 px-1 scroll-smooth snap-x snap-mandatory lg:grid lg:grid-cols-4 lg:gap-4 lg:overflow-x-visible">
        @foreach($categories->take(8) as $category)
            <a href="{{ route('public.category', $category->slug ?? $category->id) }}" class="w-[106px] min-w-[106px] min-h-[106px] lg:w-auto lg:min-w-0 lg:min-h-0 flex-none flex flex-col justify-between p-3.5 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 text-decoration-none transition-all hover:border-primary/25 hover:shadow-sm snap-start active:scale-97 cursor-pointer group">
                <span class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-950/20 text-primary flex items-center justify-center [&_svg]:w-4.5 [&_svg]:h-4.5 group-hover:scale-105 transition-transform">
                    @if($category->icon)
                        <x-svg-icon :icon="$category->icon" size="20" />
                    @else
                        <x-render-icon icon="heroicon-o-briefcase" />
                    @endif
                </span>

                <div class="min-w-0 mt-3">
                    <strong class="block text-slate-900 dark:text-slate-100 text-xs font-black leading-tight truncate">{{ $category->localized_name ?? $category->name }}</strong>
                </div>
            </a>
        @endforeach
    </div>
</section>
