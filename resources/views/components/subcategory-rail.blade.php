@props([
    'items' => collect(),
    'active' => null,
    'allUrl' => null,
    'allLabel' => 'كل الخدمات',
    'allCount' => null,
])

@php
    $allItems = collect($items);
    $visibleItems = $allItems
        ->filter(fn ($item) => (int) ($item->discoverable_profiles_count ?? 0) > 0)
        ->values();
    $railLimit = 12;
    // Always pin active item first, even if it has 0 providers (so user sees which page they're on)
    $activeItem = $active ? $allItems->first(fn ($item) => $item->is($active)) : null;
    $nonActiveVisible = $visibleItems->reject(fn ($item) => $activeItem && $item->is($activeItem));
    $railItems = $nonActiveVisible
        ->when($activeItem, fn ($items) => $items->prepend($activeItem))
        ->take($railLimit);
    $hasMoreItems = $nonActiveVisible->count() > ($railLimit - ($activeItem ? 1 : 0));
    $sheetId = 'subcategorySheet-'.uniqid();
@endphp

@if($allUrl || $visibleItems->isNotEmpty())
    <div class="min-w-0" data-subcategory-nav>
        <nav class="flex gap-2 overflow-x-auto scrollbar-none py-1.5 px-0.5 scroll-smooth snap-x snap-mandatory" aria-label="{{ __('messages.public.subcategories') }}">
            @if($allUrl)
                <a href="{{ $allUrl }}" class="flex-none inline-flex items-center gap-1.5 min-h-[38px] max-w-[min(74vw,_260px)] px-4 py-2 border rounded-full bg-white dark:bg-slate-900 transition-all text-xs font-black snap-start cursor-pointer {{ $active ? 'border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 hover:text-primary hover:border-orange-500/20' : 'border-orange-500/30 bg-orange-50/50 text-primary dark:bg-orange-950/20 dark:text-orange-400' }}">
                    <span class="truncate">{{ $allLabel }}</span>
                    @if($allCount !== null)
                        <small class="inline-flex items-center justify-center min-w-5 h-5 rounded-full text-[10px] font-black {{ $active ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' : 'bg-orange-100/50 dark:bg-orange-950/40 text-primary dark:text-orange-400' }}">{{ number_format((int) $allCount) }}</small>
                    @endif
                </a>
            @endif

            @foreach($railItems as $item)
                @php $isActive = $active && $item->is($active); @endphp
                <a href="{{ route('public.subcategory', $item->slug) }}" class="flex-none inline-flex items-center gap-1.5 min-h-[38px] max-w-[min(74vw,_260px)] px-4 py-2 border rounded-full bg-white dark:bg-slate-900 transition-all text-xs font-black snap-start cursor-pointer {{ $isActive ? 'border-orange-500/30 bg-orange-50/50 text-primary dark:bg-orange-950/20 dark:text-orange-400' : 'border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 hover:text-primary hover:border-orange-500/20' }}">
                    <span class="truncate">{{ $item->localized_name ?? $item->name }}</span>
                    <small class="inline-flex items-center justify-center min-w-5 h-5 rounded-full text-[10px] font-black {{ $isActive ? 'bg-orange-100/50 dark:bg-orange-950/40 text-primary dark:text-orange-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">{{ number_format((int) ($item->discoverable_profiles_count ?? 0)) }}</small>
                </a>
            @endforeach

            @if($hasMoreItems)
                <button type="button" class="flex-none inline-flex items-center gap-1.5 min-h-[38px] max-w-[min(74vw,_260px)] px-4 py-2 border border-dashed border-primary/40 bg-orange-50/10 hover:bg-orange-50/20 text-primary rounded-full transition-all text-xs font-black snap-start cursor-pointer" data-service-sheet-open="{{ $sheetId }}">
                    <span>{{ __('messages.public.more') }}</span>
                    <small class="inline-flex items-center justify-center min-w-5 h-5 rounded-full bg-orange-100/50 dark:bg-orange-950/40 text-primary text-[10px] font-black">{{ number_format($visibleItems->count() - $railItems->count()) }}</small>
                </button>
            @endif
        </nav>

        @if($hasMoreItems)
            <div class="hidden fixed inset-0 z-[80] bg-slate-950/40 backdrop-blur-xs transition-opacity duration-300 [&.is-open]:block" data-service-sheet-close="{{ $sheetId }}" aria-hidden="true"></div>
            <section class="fixed inset-x-0 bottom-0 z-[90] grid gap-3 max-h-[min(78vh,_620px)] p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] border border-slate-200 dark:border-slate-800 border-b-0 rounded-t-3xl bg-white dark:bg-slate-900 shadow-2xl translate-y-[105%] transition-transform duration-300 ease-out md:left-1/2 md:right-auto md:bottom-8 md:w-[min(520px,_calc(100vw-2rem))] md:border md:rounded-3xl md:translate-x-[-50%] md:translate-y-[calc(100%+3rem)] [&.is-open]:translate-y-0 md:[&.is-open]:translate-x-[-50%] md:[&.is-open]:translate-y-0" id="{{ $sheetId }}" role="dialog" aria-modal="true" aria-label="{{ __('messages.public.all_services') }}" aria-hidden="true">
                <header class="flex items-center justify-between gap-4">
                    <div>
                        <strong class="block text-slate-900 dark:text-slate-100 text-sm md:text-base font-black">{{ __('messages.public.all_services') }}</strong>
                        <span class="block text-slate-500 dark:text-slate-400 text-xs font-semibold mt-0.5">{{ number_format($visibleItems->count()) }} {{ __('messages.public.available_services') }}</span>
                    </div>
                    <button type="button" class="flex items-center justify-center w-9.5 h-9.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 hover:text-primary transition-colors cursor-pointer" data-service-sheet-close="{{ $sheetId }}" aria-label="{{ __('messages.public.close') }}">
                        <x-render-icon icon="heroicon-o-x-mark" class="w-4.5 h-4.5" />
                    </button>
                </header>

                <label class="flex items-center gap-2 min-h-[44px] px-3.5 border border-slate-200 dark:border-slate-800 rounded-2xl bg-slate-50 dark:bg-slate-950 text-slate-400 focus-within:border-primary/45 focus-within:ring-4 focus-within:ring-primary/10 transition-all">
                    <x-render-icon icon="heroicon-o-magnifying-glass" class="w-4.5 h-4.5" />
                    <input type="search" class="w-100 min-w-0 border-0 outline-none bg-transparent text-slate-950 dark:text-slate-50 font-semibold text-xs md:text-sm placeholder-slate-400" placeholder="{{ __('messages.public.search_service') }}" autocomplete="off" data-service-sheet-search>
                </label>

                <div class="grid gap-1.5 overflow-y-auto pr-0.5" style="max-height: calc(78vh - 180px);">
                    @foreach($visibleItems as $item)
                        @php $isActive = $active && $item->is($active); @endphp
                        <a href="{{ route('public.subcategory', $item->slug) }}"
                           class="flex items-center justify-between gap-3 min-h-[44px] px-4 py-2.5 border border-slate-100 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 hover:text-primary hover:border-orange-500/20 transition-all text-xs md:text-sm font-semibold [&.is-active]:border-orange-500/30 [&.is-active]:bg-orange-50/50 [&.is-active]:text-primary dark:[&.is-active]:bg-orange-950/20 dark:[&.is-active]:text-orange-400 [&.is-filtered]:hidden {{ $isActive ? 'is-active' : '' }}"
                           data-service-sheet-item>
                            <span class="truncate">{{ $item->localized_name ?? $item->name }}</span>
                            <small class="inline-flex items-center justify-center min-w-6 h-5.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[10px] font-black {{ $isActive ? 'bg-orange-100/50 dark:bg-orange-950/40 text-primary dark:text-orange-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">{{ number_format((int) ($item->discoverable_profiles_count ?? 0)) }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endif

@once
    @push('scripts')
        <script>
            (() => {
                if (window.delniServiceSheetsReady) {
                    return;
                }

                window.delniServiceSheetsReady = true;

                const toggleSheet = (id, open) => {
                    const sheet = document.getElementById(id);
                    const overlay = document.querySelector(`[data-service-sheet-close="${id}"].service-sheet-overlay, [data-service-sheet-close="${id}"]`);
                    if (!sheet || !overlay) { return; }

                    sheet.classList.toggle('is-open', open);
                    sheet.setAttribute('aria-hidden', open ? 'false' : 'true');
                    overlay.classList.toggle('is-open', open);
                    document.body.style.overflow = open ? 'hidden' : '';

                    if (open) {
                        window.setTimeout(() => sheet.querySelector('[data-service-sheet-search]')?.focus(), 80);
                    }
                };

                document.addEventListener('click', (event) => {
                    const openButton = event.target.closest('[data-service-sheet-open]');
                    const closeButton = event.target.closest('[data-service-sheet-close]');

                    if (openButton) {
                        toggleSheet(openButton.dataset.serviceSheetOpen, true);
                        return;
                    }

                    if (closeButton) {
                        toggleSheet(closeButton.dataset.serviceSheetClose, false);
                    }
                });

                document.addEventListener('input', (event) => {
                    const input = event.target.closest('[data-service-sheet-search]');
                    if (!input) { return; }

                    const value = input.value.trim().toLowerCase();
                    input.closest('section')?.querySelectorAll('[data-service-sheet-item]').forEach((item) => {
                        item.classList.toggle('is-filtered', value !== '' && !item.textContent.toLowerCase().includes(value));
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') { return; }
                    document.querySelectorAll('section.is-open').forEach((sheet) => toggleSheet(sheet.id, false));
                });
            })();
        </script>
    @endpush
@endonce
