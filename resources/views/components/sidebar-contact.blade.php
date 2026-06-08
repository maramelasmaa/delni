@php
    $contact = \App\Models\ContactInfo::instance();
@endphp

@if($contact->whatsapp || $contact->phone || $contact->email)
    <div class="card border-0 bg-light mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">{{ __('messages.public.contact_information') }}</h5>
            </div>

            <div class="d-flex flex-column gap-2">
                @if($contact->whatsapp)
                    <div class="small">
                        <div class="text-muted mb-1">{{ __('messages.public.whatsapp') }}</div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp) }}" target="_blank" class="text-decoration-none text-primary fw-500">
                            {{ $contact->whatsapp }}
                        </a>
                    </div>
                @endif

                @if($contact->phone)
                    <div class="small">
                        <div class="text-muted mb-1">{{ __('messages.public.phone') }}</div>
                        <a href="tel:{{ preg_replace('/\s+/', '', $contact->phone) }}" class="text-decoration-none text-primary fw-500">
                            {{ $contact->phone }}
                        </a>
                    </div>
                @endif

                @if($contact->email)
                    <div class="small">
                        <div class="text-muted mb-1">{{ __('messages.public.email') }}</div>
                        <a href="mailto:{{ $contact->email }}" class="text-decoration-none text-primary fw-500">
                            {{ $contact->email }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

