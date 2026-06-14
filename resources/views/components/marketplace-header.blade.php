@props([
    'eyebrow' => null,
    'title',
    'count' => null,
    'backUrl' => null,
    'backLabel' => 'رجوع',
    'icon' => null,
    'description' => null,
])

<header class="mp-header">
    @if($backUrl)
        <a href="{{ $backUrl }}" class="mp-header__back" aria-label="{{ $backLabel }}">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
    @endif

    <div class="mp-header__body">
        @if($eyebrow)
            <span class="mp-header__eyebrow">{{ $eyebrow }}</span>
        @endif

        <h1>{{ $title }}</h1>

        @if($description)
            <p>{{ $description }}</p>
        @endif

        @if($count !== null)
            <span class="mp-header__count">{{ $count }}</span>
        @endif
    </div>

    @if($icon)
        <div class="mp-header__icon">
            {{ $icon }}
        </div>
    @endif
</header>

@once
    @push('styles')
        <style>
            .mp-header {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr) auto;
                align-items: center;
                gap: .75rem;
                padding: .95rem;
                border: 1px solid var(--delni-border);
                border-radius: 18px;
                background: #fff;
                box-shadow: var(--delni-shadow-sm);
            }

            .mp-header__back,
            .mp-header__icon {
                width: 42px;
                height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
                border: 1px solid var(--delni-border);
                background: #F8FAFC;
                color: var(--delni-navy);
            }

            .mp-header__back svg,
            .mp-header__icon svg {
                width: 20px;
                height: 20px;
            }

            .mp-header__body {
                min-width: 0;
            }

            .mp-header__eyebrow {
                display: block;
                color: var(--delni-primary);
                font-size: .72rem;
                font-weight: 900;
                margin-bottom: .15rem;
            }

            .mp-header h1 {
                margin: 0;
                color: var(--delni-navy);
                font-size: clamp(1.15rem, 2vw, 1.45rem);
                line-height: 1.25;
                font-weight: 950;
            }

            .mp-header p {
                max-width: 56rem;
                margin: .35rem 0 0;
                color: var(--delni-muted);
                font-size: .86rem;
                line-height: 1.7;
                font-weight: 650;
            }

            .mp-header__count {
                display: inline-flex;
                margin-top: .35rem;
                color: #64748B;
                font-size: .76rem;
                font-weight: 850;
            }

            [data-theme="dark"] .mp-header {
                background: #1E293B;
                border-color: #334155;
            }
            [data-theme="dark"] .mp-header__back,
            [data-theme="dark"] .mp-header__icon {
                background: #0F172A;
                border-color: #334155;
                color: #F1F5F9;
            }
            [data-theme="dark"] .mp-header h1 { color: #F1F5F9; }
            [data-theme="dark"] .mp-header p,
            [data-theme="dark"] .mp-header__count { color: #94A3B8; }

            @media (max-width: 700px) {
                .mp-header {
                    position: sticky;
                    top: calc(var(--pwa-header-height) + env(safe-area-inset-top) + .35rem);
                    z-index: 5;
                    gap: .6rem;
                    padding: .75rem;
                    border-radius: 16px;
                }

                .mp-header__back,
                .mp-header__icon {
                    width: 38px;
                    height: 38px;
                    border-radius: 12px;
                }

                .mp-header h1 {
                    font-size: 1.05rem;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .mp-header p {
                    display: none;
                }

                .mp-header__count {
                    margin-top: .12rem;
                    font-size: .72rem;
                }
            }
        </style>
    @endpush
@endonce
