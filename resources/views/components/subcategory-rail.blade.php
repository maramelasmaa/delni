@props([
    'items' => collect(),
    'active' => null,
    'allUrl' => null,
    'allLabel' => 'كل الخدمات',
    'allCount' => null,
])

@php
    $visibleItems = collect($items)
        ->filter(fn ($item) => (int) ($item->discoverable_profiles_count ?? 0) > 0)
        ->values();
    $railLimit = 12;
    $activeItem = $active ? $visibleItems->first(fn ($item) => $item->is($active)) : null;
    $railItems = $visibleItems
        ->when($activeItem, fn ($items) => $items->reject(fn ($item) => $item->is($activeItem))->prepend($activeItem))
        ->take($railLimit);
    $hasMoreItems = $visibleItems->count() > $railItems->count();
    $sheetId = 'subcategorySheet-'.uniqid();
@endphp

@if($allUrl || $visibleItems->isNotEmpty())
    <div class="service-nav" data-subcategory-nav>
        <nav class="service-rail" aria-label="الخدمات الفرعية">
            @if($allUrl)
                <a href="{{ $allUrl }}" class="{{ $active ? '' : 'is-active' }}">
                    <span>{{ $allLabel }}</span>
                    @if($allCount !== null)
                        <small>{{ number_format((int) $allCount) }}</small>
                    @endif
                </a>
            @endif

            @foreach($railItems as $item)
                <a href="{{ route('public.subcategory', $item->slug) }}" class="{{ $active && $item->is($active) ? 'is-active' : '' }}">
                    <span>{{ $item->localized_name ?? $item->name }}</span>
                    <small>{{ number_format((int) ($item->discoverable_profiles_count ?? 0)) }}</small>
                </a>
            @endforeach

            @if($hasMoreItems)
                <button type="button" class="service-rail__more" data-service-sheet-open="{{ $sheetId }}">
                    <span>المزيد</span>
                    <small>{{ number_format($visibleItems->count() - $railItems->count()) }}</small>
                </button>
            @endif
        </nav>

        @if($hasMoreItems)
            <div class="service-sheet-overlay" data-service-sheet-close="{{ $sheetId }}" aria-hidden="true"></div>
            <section class="service-sheet" id="{{ $sheetId }}" role="dialog" aria-modal="true" aria-label="كل الخدمات" aria-hidden="true">
                <header class="service-sheet__head">
                    <div>
                        <strong>كل الخدمات</strong>
                        <span>{{ number_format($visibleItems->count()) }} خدمة متاحة</span>
                    </div>
                    <button type="button" data-service-sheet-close="{{ $sheetId }}" aria-label="إغلاق">
                        <x-render-icon icon="heroicon-o-x-mark" />
                    </button>
                </header>

                <label class="service-sheet__search">
                    <x-render-icon icon="heroicon-o-magnifying-glass" />
                    <input type="search" placeholder="ابحث عن خدمة" autocomplete="off" data-service-sheet-search>
                </label>

                <div class="service-sheet__list">
                    @foreach($visibleItems as $item)
                        <a href="{{ route('public.subcategory', $item->slug) }}"
                           class="{{ $active && $item->is($active) ? 'is-active' : '' }}"
                           data-service-sheet-item>
                            <span>{{ $item->localized_name ?? $item->name }}</span>
                            <small>{{ number_format((int) ($item->discoverable_profiles_count ?? 0)) }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endif

@once
    @push('styles')
        <style>
            .service-nav { min-width: 0; }
            .service-rail {
                display: flex;
                gap: .5rem;
                overflow-x: auto;
                padding: .15rem .05rem .35rem;
                scroll-snap-type: inline mandatory;
                scrollbar-width: none;
                -webkit-overflow-scrolling: touch;
            }
            .service-rail::-webkit-scrollbar { display: none; }

            .service-rail a,
            .service-rail__more {
                flex: 0 0 auto;
                min-height: 38px;
                display: inline-flex;
                align-items: center;
                gap: .38rem;
                max-width: min(74vw, 260px);
                padding: .45rem .75rem;
                border: 1px solid var(--delni-border);
                border-radius: 999px;
                background: #fff;
                color: var(--delni-navy);
                text-decoration: none;
                font-size: .78rem;
                font-weight: 900;
                scroll-snap-align: start;
                font-family: inherit;
                cursor: pointer;
            }

            .service-rail span,
            .service-rail__more span {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .service-rail small,
            .service-rail__more small {
                min-width: 20px;
                min-height: 20px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                background: #F1F5F9;
                color: #64748B;
                font-size: .66rem;
                font-weight: 900;
            }

            .service-rail a.is-active {
                border-color: rgba(241,98,15,.28);
                background: #FFF7ED;
                color: var(--delni-primary);
            }
            .service-rail__more {
                border-style: dashed;
                color: var(--delni-primary);
            }

            .service-sheet-overlay {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 80;
                background: rgba(2,6,23,.38);
            }

            .service-sheet {
                position: fixed;
                inset-inline: 0;
                bottom: 0;
                z-index: 90;
                display: grid;
                gap: .75rem;
                max-height: min(78vh, 620px);
                padding: .95rem 1rem calc(1rem + env(safe-area-inset-bottom));
                border: 1px solid var(--delni-border);
                border-bottom: 0;
                border-radius: 22px 22px 0 0;
                background: #fff;
                box-shadow: 0 -18px 44px rgba(2,6,23,.18);
                transform: translateY(105%);
                transition: transform .22s ease;
            }

            .service-sheet.is-open {
                transform: translateY(0);
            }
            .service-sheet-overlay.is-open {
                display: block;
            }

            .service-sheet__head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }
            .service-sheet__head strong,
            .service-sheet__head span {
                display: block;
            }
            .service-sheet__head strong {
                color: var(--delni-navy);
                font-size: .98rem;
                font-weight: 950;
            }
            .service-sheet__head span {
                margin-top: .12rem;
                color: #64748B;
                font-size: .74rem;
                font-weight: 850;
            }
            .service-sheet__head button {
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--delni-border);
                border-radius: 12px;
                background: #F8FAFC;
                color: var(--delni-navy);
                cursor: pointer;
            }
            .service-sheet__head svg,
            .service-sheet__search svg {
                width: 18px;
                height: 18px;
            }

            .service-sheet__search {
                min-height: 44px;
                display: flex;
                align-items: center;
                gap: .48rem;
                padding: 0 .78rem;
                border: 1px solid var(--delni-border);
                border-radius: 14px;
                background: #F8FAFC;
                color: #94A3B8;
            }
            .service-sheet__search input {
                width: 100%;
                min-width: 0;
                border: 0;
                outline: 0;
                background: transparent;
                color: var(--delni-navy);
                font: inherit;
                font-size: .86rem;
                font-weight: 800;
            }

            .service-sheet__list {
                display: grid;
                gap: .45rem;
                overflow-y: auto;
                padding-inline-end: .15rem;
            }
            .service-sheet__list a {
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
                padding: .58rem .72rem;
                border: 1px solid var(--delni-border);
                border-radius: 14px;
                background: #fff;
                color: var(--delni-navy);
                text-decoration: none;
                font-size: .84rem;
                font-weight: 900;
            }
            .service-sheet__list a.is-active {
                border-color: rgba(241,98,15,.28);
                background: #FFF7ED;
                color: var(--delni-primary);
            }
            .service-sheet__list span {
                min-width: 0;
                overflow-wrap: anywhere;
            }
            .service-sheet__list small {
                min-width: 24px;
                min-height: 22px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                background: #F1F5F9;
                color: #64748B;
                font-size: .68rem;
                font-weight: 900;
            }
            .service-sheet__list a.is-filtered { display: none; }

            @media (min-width: 760px) {
                .service-sheet {
                    inset-inline: 50%;
                    bottom: 2rem;
                    width: min(520px, calc(100vw - 2rem));
                    border: 1px solid var(--delni-border);
                    border-radius: 22px;
                    transform: translateX(50%) translateY(calc(100% + 3rem));
                }
                .service-sheet.is-open {
                    transform: translateX(50%) translateY(0);
                }
            }

            [data-theme="dark"] .service-rail a,
            [data-theme="dark"] .service-rail__more {
                background: #1E293B;
                border-color: #334155;
                color: #F1F5F9;
            }
            [data-theme="dark"] .service-rail a.is-active {
                background: rgba(241,98,15,.12);
                border-color: rgba(241,98,15,.28);
                color: #FB923C;
            }
            [data-theme="dark"] .service-rail small {
                background: #0F172A;
                color: #94A3B8;
            }
            [data-theme="dark"] .service-sheet {
                background: #1E293B;
                border-color: #334155;
            }
            [data-theme="dark"] .service-sheet__head strong,
            [data-theme="dark"] .service-sheet__list a,
            [data-theme="dark"] .service-sheet__search input { color: #F1F5F9; }
            [data-theme="dark"] .service-sheet__head span { color: #94A3B8; }
            [data-theme="dark"] .service-sheet__head button,
            [data-theme="dark"] .service-sheet__search,
            [data-theme="dark"] .service-sheet__list a {
                background: #0F172A;
                border-color: #334155;
            }
            [data-theme="dark"] .service-sheet__list a.is-active {
                background: rgba(241,98,15,.12);
                border-color: rgba(241,98,15,.28);
                color: #FB923C;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                if (window.delniServiceSheetsReady) {
                    return;
                }

                window.delniServiceSheetsReady = true;

                const toggleSheet = (id, open) => {
                    const sheet = document.getElementById(id);
                    const overlay = document.querySelector(`[data-service-sheet-close="${id}"].service-sheet-overlay`);
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
                    input.closest('.service-sheet')?.querySelectorAll('[data-service-sheet-item]').forEach((item) => {
                        item.classList.toggle('is-filtered', value !== '' && !item.textContent.toLowerCase().includes(value));
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') { return; }
                    document.querySelectorAll('.service-sheet.is-open').forEach((sheet) => toggleSheet(sheet.id, false));
                });
            })();
        </script>
    @endpush
@endonce
