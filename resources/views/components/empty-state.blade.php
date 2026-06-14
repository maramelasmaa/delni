@props([
    'icon' => 'heroicon-o-magnifying-glass',
    'title' => __('messages.public.no_results'),
    'message' => __('messages.public.try_again_later'),
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="delni-empty-state">
    <div class="delni-empty-state__icon">
        <x-render-icon :icon="$icon" />
    </div>

    <h3 class="delni-empty-state__title">
        {{ $title }}
    </h3>

    @if($message)
        <p class="delni-empty-state__message">
            {{ $message }}
        </p>
    @endif

    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="delni-empty-state__action">
            {{ $actionLabel }}
        </a>
    @endif
</div>

@once
    @push('styles')
        <style>
            .delni-empty-state {
                text-align: center;
                padding: clamp(2rem, 5vw, 3.5rem) 1.25rem;
                background: #ffffff;
                border: 1px solid #e8edf4;
                border-radius: 20px;
                box-shadow: 0 12px 30px rgba(11, 26, 52, 0.05);
            }

            .delni-empty-state__icon {
                width: 64px;
                height: 64px;
                margin: 0 auto 1.25rem;
                border-radius: 18px;
                background: #fff7ed;
                color: #f1620f;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(241, 98, 15, 0.14);
            }

            .delni-empty-state__icon svg {
                width: 30px;
                height: 30px;
            }

            .delni-empty-state__title {
                margin: 0 0 0.5rem;
                font-size: 1.12rem;
                font-weight: 900;
                color: #0b1a34;
                letter-spacing: -0.02em;
            }

            .delni-empty-state__message {
                max-width: 420px;
                margin: 0 auto;
                color: #64748b;
                line-height: 1.8;
                font-size: 0.94rem;
                font-weight: 500;
            }

            .delni-empty-state__action {
                margin-top: 1.2rem;
                min-height: 44px;
                padding: 0.7rem 1.1rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
                background: #f1620f;
                color: #fff;
                text-decoration: none;
                font-size: 0.9rem;
                font-weight: 900;
                box-shadow: 0 12px 24px rgba(241, 98, 15, 0.16);
                transition: 0.15s ease;
            }

            .delni-empty-state__action:hover {
                transform: translateY(-1px);
                box-shadow: 0 14px 32px rgba(255, 107, 26, 0.24);
            }

            @media (max-width: 640px) {
                .delni-empty-state {
                    padding: 2rem 1rem;
                }

                .delni-empty-state__icon {
                    width: 56px;
                    height: 56px;
                    margin-bottom: 1rem;
                }

                .delni-empty-state__title {
                    font-size: 1rem;
                }

                .delni-empty-state__message {
                    font-size: 0.88rem;
                }

                .delni-empty-state__action {
                    min-height: 40px;
                    font-size: 0.85rem;
                }
            }
        </style>
    @endpush
@endonce
