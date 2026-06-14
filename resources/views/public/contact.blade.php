@extends('public.layout')

@section('title', __('messages.public.contact_us') . ' - ' . config('app.name'))

@section('content')
<div class="lp-wrapper">

    {{-- App page header --}}
    <header class="lp-header">
        <a href="{{ route('home') }}" class="lp-back" aria-label="الرئيسية">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">دلني</span>
            <h1 class="lp-title">{{ __('messages.public.contact_us') }}</h1>
        </div>
        <div class="lp-header-icon">
            <x-render-icon icon="heroicon-o-chat-bubble-left-ellipsis" />
        </div>
    </header>

    @if($contactInfo)
        <p class="ct-intro">{{ __('messages.public.need_help') }}</p>

        <div class="ct-cards">

            @if($contactInfo->whatsapp)
                @php $waNumber = preg_replace('/[^0-9]/', '', $contactInfo->whatsapp); @endphp
                <a href="https://wa.me/{{ $waNumber }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="ct-card ct-card--wa">
                    <div class="ct-card__icon ct-card__icon--wa">
                        <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.458 5.704 1.459h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <div class="ct-card__body">
                        <span class="ct-card__label">واتساب</span>
                        <span class="ct-card__action">ابدأ محادثة</span>
                    </div>
                    <div class="ct-card__arrow">
                        <x-render-icon icon="heroicon-o-arrow-left" />
                    </div>
                </a>
            @endif

            @if($contactInfo->phone)
                <a href="tel:{{ $contactInfo->phone }}" class="ct-card ct-card--phone">
                    <div class="ct-card__icon ct-card__icon--phone">
                        <x-render-icon icon="heroicon-o-phone" />
                    </div>
                    <div class="ct-card__body">
                        <span class="ct-card__label">{{ __('filament.fields.phone') }}</span>
                        <span class="ct-card__action">اتصل بنا</span>
                    </div>
                    <div class="ct-card__arrow">
                        <x-render-icon icon="heroicon-o-arrow-left" />
                    </div>
                </a>
            @endif

            @if($contactInfo->email)
                <a href="mailto:{{ $contactInfo->email }}" class="ct-card ct-card--email">
                    <div class="ct-card__icon ct-card__icon--email">
                        <x-render-icon icon="heroicon-o-envelope" />
                    </div>
                    <div class="ct-card__body">
                        <span class="ct-card__label">{{ __('filament.fields.email') }}</span>
                        <span class="ct-card__action">راسلنا</span>
                    </div>
                    <div class="ct-card__arrow">
                        <x-render-icon icon="heroicon-o-arrow-left" />
                    </div>
                </a>
            @endif

            @if($contactInfo->address)
                <div class="ct-card ct-card--address">
                    <div class="ct-card__icon ct-card__icon--address">
                        <x-render-icon icon="heroicon-o-map-pin" />
                    </div>
                    <div class="ct-card__body">
                        <span class="ct-card__label">{{ __('filament.fields.address') }}</span>
                        <span class="ct-card__value">{{ $contactInfo->address }}</span>
                    </div>
                </div>
            @endif

        </div>

    @else
        <div class="ct-empty">
            <x-empty-state
                icon="heroicon-o-chat-bubble-left-ellipsis"
                title="معلومات التواصل غير متوفرة"
                message="سيتم إضافة معلومات التواصل قريباً."
                actionLabel="{{ __('messages.public.back_home') }}"
                actionUrl="{{ route('home') }}"
            />
        </div>
    @endif


</div>

@push('styles')
<style>
    .ct-intro {
        margin: .85rem 0 1.2rem;
        color: #64748B;
        font-size: .88rem;
        font-weight: 700;
        line-height: 1.7;
        text-align: center;
    }

    .ct-cards {
        display: flex;
        flex-direction: column;
        gap: .7rem;
    }

    .ct-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.1rem;
        border-radius: 18px;
        border: 1px solid var(--delni-border);
        background: #fff;
        text-decoration: none;
        transition: box-shadow .15s, border-color .15s;
    }
    .ct-card:active { box-shadow: 0 4px 18px rgba(0,0,0,.07); }

    .ct-card--address { cursor: default; }

    .ct-card__icon {
        width: 48px; height: 48px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 14px;
        font-size: 1.2rem;
    }
    .ct-card__icon svg { width: 22px; height: 22px; }

    .ct-card__icon--wa   { background: #E8FBF0; color: #25D366; }
    .ct-card__icon--phone { background: rgba(241,98,15,.08); color: var(--delni-primary); }
    .ct-card__icon--email { background: #EEF2FF; color: #6366F1; }
    .ct-card__icon--address { background: #F0F9FF; color: #0EA5E9; }

    .ct-card__body {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: .2rem;
    }
    .ct-card__label {
        color: var(--delni-navy);
        font-size: .9rem;
        font-weight: 900;
    }
    .ct-card__action {
        color: #94A3B8;
        font-size: .78rem;
        font-weight: 700;
    }
    .ct-card__value {
        color: #64748B;
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.5;
    }

    .ct-card__arrow {
        flex-shrink: 0;
        color: #CBD5E1;
    }
    .ct-card__arrow svg { width: 18px; height: 18px; }

    .ct-card--wa   { border-color: rgba(37,211,102,.2); }
    .ct-card--wa:active { border-color: rgba(37,211,102,.45); box-shadow: 0 4px 18px rgba(37,211,102,.12); }

    .ct-card--phone { border-color: rgba(241,98,15,.15); }
    .ct-card--phone:active { border-color: rgba(241,98,15,.35); box-shadow: 0 4px 18px rgba(241,98,15,.1); }

    .ct-card--email { border-color: rgba(99,102,241,.15); }
    .ct-card--email:active { border-color: rgba(99,102,241,.3); }

    .ct-empty { margin-top: 2rem; }

    [data-theme="dark"] .ct-card { background: #1E293B; border-color: #334155; }
    [data-theme="dark"] .ct-card--wa   { border-color: rgba(37,211,102,.15); }
    [data-theme="dark"] .ct-card--phone { border-color: rgba(241,98,15,.12); }
    [data-theme="dark"] .ct-card--email { border-color: rgba(99,102,241,.12); }
    [data-theme="dark"] .ct-card__label { color: #F1F5F9; }
    [data-theme="dark"] .ct-intro { color: #94A3B8; }
    [data-theme="dark"] .ct-back-btn { background: #1E293B; border-color: #334155; color: #F1F5F9; }
</style>
@endpush
@endsection
