@props([
    'code',
    'title',
    'message',
    'primaryLabel' => __('messages.public.back_home'),
    'primaryUrl' => route('home'),
    'secondaryLabel' => null,
    'secondaryUrl' => null,
    'note' => null,
])

<section class="app-state-page">
    <div class="app-state-card" role="status">
        <span class="app-state-code">{{ $code }}</span>
        <h1 class="app-state-title">{{ $title }}</h1>
        <p class="app-state-message">{{ $message }}</p>

        <div class="app-state-actions">
            <a href="{{ $primaryUrl }}" class="app-state-button app-state-button--primary">
                {{ $primaryLabel }}
            </a>

            @if($secondaryLabel && $secondaryUrl)
                <a href="{{ $secondaryUrl }}" class="app-state-button app-state-button--secondary">
                    {{ $secondaryLabel }}
                </a>
            @endif
        </div>

        @if($note)
            <p class="app-state-note">{{ $note }}</p>
        @endif
    </div>
</section>

@once
    @push('styles')
        <style>
            .app-state-page {
                min-height: min(620px, calc(100vh - var(--pwa-header-height) - var(--pwa-nav-height) - 2rem));
                display: grid;
                place-items: center;
                padding: 1rem 0 2rem;
            }

            .app-state-card {
                width: min(100%, 440px);
                padding: clamp(1.25rem, 4vw, 1.75rem);
                border: 1px solid var(--delni-border);
                border-radius: 20px;
                background: #fff;
                box-shadow: var(--delni-shadow-sm);
                text-align: center;
            }

            .app-state-code {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 58px;
                min-height: 34px;
                margin-bottom: .85rem;
                padding: .25rem .75rem;
                border-radius: 999px;
                background: #FFF7ED;
                color: var(--delni-primary);
                font-size: .9rem;
                font-weight: 950;
            }

            .app-state-title {
                margin: 0;
                color: var(--delni-navy);
                font-size: clamp(1.15rem, 4vw, 1.35rem);
                line-height: 1.45;
                font-weight: 950;
            }

            .app-state-message {
                max-width: 32rem;
                margin: .55rem auto 0;
                color: var(--delni-muted);
                font-size: .92rem;
                line-height: 1.85;
                font-weight: 650;
            }

            .app-state-actions {
                display: grid;
                gap: .6rem;
                margin-top: 1.15rem;
            }

            .app-state-button {
                min-height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: .7rem 1rem;
                border-radius: 14px;
                text-decoration: none;
                font-size: .9rem;
                font-weight: 900;
            }

            .app-state-button--primary {
                background: var(--delni-primary);
                color: #fff;
            }

            .app-state-button--secondary {
                background: #F8FAFC;
                border: 1px solid var(--delni-border);
                color: var(--delni-navy);
            }

            .app-state-note {
                margin: 1rem 0 0;
                padding-top: .9rem;
                border-top: 1px solid var(--delni-border);
                color: var(--delni-muted);
                font-size: .82rem;
                line-height: 1.7;
                font-weight: 650;
            }

            [data-theme="dark"] .app-state-card {
                background: #1E293B;
                border-color: #334155;
            }

            [data-theme="dark"] .app-state-button--secondary {
                background: #0F172A;
                border-color: #334155;
                color: #F1F5F9;
            }
        </style>
    @endpush
@endonce
