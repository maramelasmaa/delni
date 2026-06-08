@php
    $contact = \App\Models\ContactInfo::instance();
@endphp

<style>
    .footer-contact-card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .footer-contact-card .card-body {
        padding: 1.5rem;
    }

    .footer-contact-row {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-radius: 0.75rem;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
        font-size: 0.875rem;
        color: #1e293b;
        font-weight: 500;
    }

    .footer-contact-row:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0b1a34;
    }

    .footer-contact-row svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        display: block;
    }

    .footer-contact-row.whatsapp-row {
        background: #dcfce7;
        border-color: #86efac;
    }

    .footer-contact-row.whatsapp-row:hover {
        background: #c6f6d5;
    }

    .footer-contact-address {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        color: #64748b;
    }

    .footer-contact-address svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        display: block;
        margin-top: 0.1rem;
    }
</style>

@if($contact->whatsapp || $contact->email)
    <div class="card footer-contact-card">
        <div class="card-body">
            <h5 class="card-title mb-3">{{ __('messages.public.contact_information') }}</h5>

            <div class="d-flex flex-column gap-2">
                @if($contact->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp) }}" target="_blank" class="footer-contact-row whatsapp-row">
                        <x-render-icon icon="heroicon-o-chat-bubble-left" />
                        <span>{{ __('messages.public.contact_us') }}</span>
                    </a>
                @endif

                @if($contact->email)
                    <a href="mailto:{{ $contact->email }}" class="footer-contact-row">
                        <x-render-icon icon="heroicon-o-envelope" />
                        <span>{{ $contact->email }}</span>
                    </a>
                @endif

                @if($contact->address)
                    <div class="footer-contact-address">
                        <x-render-icon icon="heroicon-o-map-pin" />
                        <span>{{ $contact->address }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

