@props([
    'icon' => 'heroicon-o-magnifying-glass',
    'title' => __('messages.public.no_results'),
    'message' => __('messages.public.try_again_later'),
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="empty-state">
    <div class="empty-state-icon">
        <x-render-icon :icon="$icon" />
    </div>

    <h3 class="empty-state-title">
        {{ $title }}
    </h3>

    @if($message)
        <p class="empty-state-message">
            {{ $message }}
        </p>
    @endif

    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary mt-4">
            {{ $actionLabel }}
        </a>
    @endif
</div>

@once
    @push('styles')
        <style>
            .empty-state {
                text-align: center;
                padding: 3.5rem 2rem;
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 1rem;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            }

            .empty-state-icon {
                width: 56px;
                height: 56px;
                margin: 0 auto 1.5rem;
                border-radius: 0.75rem;
                background: #f1f5f9;
                color: #94a3b8;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #e2e8f0;
            }

            .empty-state-icon svg {
                width: 26px;
                height: 26px;
                display: block;
            }

            .empty-state-title {
                font-size: 1.1rem;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 0.6rem;
            }

            .empty-state-message {
                color: #64748b;
                max-width: 380px;
                margin: 0 auto;
                line-height: 1.6;
                font-size: 0.95rem;
            }
        </style>
    @endpush
@endonce
