@props(['provider'])

<style>
    .sticky-contact {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        position: sticky;
        top: 2rem;
    }

    .sticky-contact .card-body {
        padding: 1.5rem;
    }

    .contact-row {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
    }

    .contact-row:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .contact-row svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        display: block;
    }

    .contact-row-label {
        font-size: 0.875rem;
        color: #1e293b;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0b1a34;
    }

    .rating-section {
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
        margin-top: 1rem;
    }

    .rating-stars {
        color: #f59e0b;
        letter-spacing: 1px;
        font-size: 0.9rem;
    }
</style>

<div class="card sticky-contact">
    <div class="card-body">
        <h5 class="card-title mb-3">{{ __('messages.public.contact') }}</h5>

        @if($provider->phone)
            <a href="tel:{{ preg_replace('/\s+/', '', $provider->phone) }}" class="contact-row">
                <x-render-icon icon="heroicon-o-phone" />
                <span class="contact-row-label">{{ $provider->phone }}</span>
            </a>
        @endif

        @if($provider->whatsapp)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $provider->whatsapp) }}" target="_blank" class="contact-row" style="background: #dcfce7; border-color: #86efac;">
                <x-render-icon icon="heroicon-o-chat-bubble-left" />
                <span class="contact-row-label">{{ __('messages.public.whatsapp') }}</span>
            </a>
        @endif

        @if($provider->map_url)
            <a href="{{ $provider->map_url }}" target="_blank" class="contact-row">
                <x-render-icon icon="heroicon-o-map-pin" />
                <span class="contact-row-label">{{ __('messages.public.location') }}</span>
            </a>
        @endif

        @if($provider->stats)
            <div class="rating-section">
                <div class="rating-stars mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <span style="{{ $i <= floor($provider->stats->rating_avg) ? '' : 'opacity: 0.28;' }}">★</span>
                    @endfor
                </div>
                <small class="text-muted d-block">
                    <strong class="text-dark">{{ number_format($provider->stats->rating_avg, 1) }}</strong>
                    ({{ $provider->stats->reviews_count }})
                </small>
            </div>
        @endif
    </div>
</div>
