@extends('public.layout')

@section('title', 'جميع التخصصات - ' . config('app.name'))

@section('content')
<div class="grid gap-4 max-w-2xl mx-auto py-2.5 px-1 w-full">
    <!-- Custom Hero Banner matching the design mockup -->
    <div class="relative overflow-hidden rounded-3xl bg-[#FFF6F0] dark:bg-slate-900/60 p-5 md:p-6.5 flex items-center justify-between border border-orange-100/40 dark:border-slate-800/60 shadow-2xs">
        <!-- Right Side Text (RTL) -->
        <div class="flex-1 min-w-0 z-1 pr-1 pl-20 sm:pl-32 text-right">
            <h1 class="text-[#0B1A34] dark:text-slate-100 text-lg md:text-xl font-black mb-1 leading-tight">تصفح التخصصات</h1>
            <p class="text-[#5D5959] dark:text-slate-400 text-[11px] md:text-xs font-bold leading-relaxed max-w-[240px] sm:max-w-md">
                اختر المجال المناسب ثم انتقل للتخصصات الفرعية ومقدمي الخدمات المتاحين.
            </p>
        </div>
        <!-- Left Side Image: Floating illustration -->
        <div class="absolute left-2.5 bottom-0 top-0 w-24 sm:w-32 flex items-center justify-center pointer-events-none">
            <img src="{{ asset('images/categories_hero.png') }}" alt="" class="object-contain max-h-[92%] drop-shadow-md">
        </div>
    </div>

    <!-- Search Input Bar -->
    <div class="z-4 my-1">
        <label class="flex items-center gap-3 min-h-[46px] px-4 border border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-950 shadow-3xs focus-within:border-primary/50 focus-within:ring-2 focus-within:ring-primary/10 transition-all">
            <x-render-icon icon="heroicon-o-magnifying-glass" class="w-4.5 h-4.5 text-slate-400 flex-none" />
            <input type="search" id="categorySearch" placeholder="ابحث عن تخصص أو خدمة..." autocomplete="off" class="w-full min-w-0 border-0 outline-none bg-transparent text-slate-900 dark:text-slate-50 font-bold text-xs md:text-sm placeholder-slate-400">
        </label>
    </div>

    <!-- Categories list cards stack -->
    <section class="grid gap-3 grid-cols-1 w-full" id="categoryGrid">
        @forelse($categories as $category)
            <x-category-discovery-card :category="$category" />
        @empty
            <x-empty-state
                icon="heroicon-o-folder-open"
                title="لا توجد تخصصات"
                message="لا توجد تخصصات متاحة حالياً."
            />
        @endforelse
    </section>

    <!-- Join as Provider CTA -->
    <div class="lp-cta">
        <div class="text-right">
            <span>تقدم خدمة؟</span>
            <h2>اجعل ملفك مرئياً للعملاء</h2>
        </div>
        <a href="{{ $ctaWhatsappUrl ?? route('contact') }}"
           @if($ctaWhatsappUrl ?? false) target="_blank" rel="noopener" @endif>سجل كمقدم خدمة</a>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const input = document.getElementById('categorySearch');
        const cards = Array.from(document.querySelectorAll('#categoryGrid > article'));
        if (!input || cards.length === 0) { return; }

        input.addEventListener('input', () => {
            const value = input.value.trim().toLowerCase();
            cards.forEach((card) => {
                card.classList.toggle('hidden', value !== '' && !card.textContent.toLowerCase().includes(value));
            });
        });
    })();
</script>
@endpush
@endsection

