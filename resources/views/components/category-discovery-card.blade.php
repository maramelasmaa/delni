@props(['category'])

@php
    $categoryName = $category->localized_name ?? $category->name;
    $subcategories = $category->subcategories ?? collect();
    $previewSubcategories = $subcategories->filter(fn ($subcategory) => (int) ($subcategory->discoverable_profiles_count ?? 0) > 0)->take(4);
@endphp

<article class="cat-card">
    <a href="{{ route('public.category', $category->slug) }}" class="cat-card__main">
        <span class="cat-card__icon">
            @if($category->icon)
                <x-svg-icon :icon="$category->icon" size="22" />
            @else
                <x-render-icon icon="heroicon-o-briefcase" />
            @endif
        </span>

        <span class="cat-card__body">
            <strong>{{ $categoryName }}</strong>
            <small>{{ number_format((int) ($category->discoverable_profiles_count ?? 0)) }} مزود</small>
        </span>

        <x-render-icon icon="heroicon-o-chevron-left" class="cat-card__chevron" />
    </a>

    @if($previewSubcategories->isNotEmpty())
        <div class="cat-card__subs">
            @foreach($previewSubcategories as $subcategory)
                <a href="{{ route('public.subcategory', $subcategory->slug) }}">
                    <span>{{ $subcategory->localized_name ?? $subcategory->name }}</span>
                    <small>{{ $subcategory->discoverable_profiles_count ?? 0 }}</small>
                </a>
            @endforeach
        </div>
    @endif
</article>

@once
    @push('styles')
        <style>
            .cat-card {
                min-height: 100%;
                overflow: hidden;
                border: 1px solid var(--delni-border);
                border-radius: 16px;
                background: #fff;
                box-shadow: var(--delni-shadow-sm);
            }

            .cat-card__main {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr);
                align-items: start;
                gap: .65rem;
                min-height: 92px;
                padding: .8rem;
                text-decoration: none;
            }

            .cat-card__icon {
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
                background: rgba(241,98,15,.08);
                color: var(--delni-primary);
            }
            .cat-card__icon svg { width: 20px; height: 20px; }

            .cat-card__body {
                min-width: 0;
            }
            .cat-card__body strong,
            .cat-card__body small {
                display: block;
            }
            .cat-card__body strong {
                color: var(--delni-navy);
                font-size: .9rem;
                line-height: 1.45;
                font-weight: 950;
                overflow-wrap: anywhere;
            }
            .cat-card__body small {
                margin-top: .08rem;
                color: #64748B;
                font-size: .7rem;
                font-weight: 800;
            }

            .cat-card__chevron {
                display: none;
            }

            .cat-card__subs {
                display: flex;
                gap: .35rem;
                overflow-x: auto;
                padding: 0 .65rem .7rem;
                border-top: 1px solid var(--delni-border);
                background: #fff;
                scrollbar-width: none;
            }
            .cat-card__subs::-webkit-scrollbar { display: none; }

            .cat-card__subs a {
                flex: 0 0 auto;
                min-height: 28px;
                display: inline-flex;
                align-items: center;
                gap: .38rem;
                max-width: min(62vw, 170px);
                padding: .25rem .5rem;
                border: 1px solid var(--delni-border);
                border-radius: 999px;
                background: #F8FAFC;
                color: #475569;
                font-size: .7rem;
                font-weight: 850;
                text-decoration: none;
            }
            .cat-card__subs span {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .cat-card__subs small {
                min-width: 18px;
                min-height: 18px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                background: #fff;
                color: #64748B;
                font-size: .64rem;
                font-weight: 900;
            }

            [data-theme="dark"] .cat-card {
                background: #1E293B;
                border-color: #334155;
            }
            [data-theme="dark"] .cat-card__body strong,
            [data-theme="dark"] .cat-card__subs a { color: #F1F5F9; }
            [data-theme="dark"] .cat-card__body small { color: #94A3B8; }
            [data-theme="dark"] .cat-card__subs {
                background: #1E293B;
                border-color: #334155;
            }
            [data-theme="dark"] .cat-card__subs a {
                background: #0F172A;
                border-color: #334155;
                color: #CBD5E1;
            }
            [data-theme="dark"] .cat-card__subs small {
                background: #334155;
                color: #94A3B8;
            }

            @media (min-width: 760px) {
                .cat-card__main {
                    min-height: 76px;
                    grid-template-columns: auto minmax(0, 1fr) auto;
                    align-items: center;
                    padding: .85rem .95rem;
                }

                .cat-card__icon {
                    width: 44px;
                    height: 44px;
                    border-radius: 14px;
                }

                .cat-card__chevron {
                    display: block;
                    width: 18px;
                    height: 18px;
                    color: #94A3B8;
                }
            }
        </style>
    @endpush
@endonce
