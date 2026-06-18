@extends('public.layout')

@section('title', __('messages.public.contact_us') . ' - ' . config('app.name'))

@section('content')
<div class="lp-wrapper lp-wrapper-compact">

    <header class="lp-header">
        <a href="{{ route('settings') }}" class="lp-back" aria-label="رجوع">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">دلني</span>
            <h1 class="lp-title">{{ __('messages.public.contact_us') }}</h1>
        </div>
    </header>



    @if($contactInfo)
        <div class="ct-section-title">
            <span>وسائل التواصل المباشر</span>
        </div>

        <div class="ct-list">
            @if($contactInfo->whatsapp)
                @php $waNumber = preg_replace('/[^0-9]/', '', $contactInfo->whatsapp); @endphp
                <a href="https://wa.me/{{ $waNumber }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="ct-item ct-item--wa">
                    <span class="ct-item-badge ct-item-badge--wa">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.458 5.704 1.459h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </span>
                    <div class="ct-item-content">
                        <span class="ct-item-title">واتساب</span>
                        <span class="ct-item-subtitle">تواصل فوري ومحادثة مباشرة</span>
                    </div>
                    <span class="ct-item-arrow">
                        <x-render-icon icon="heroicon-o-chevron-left" />
                    </span>
                </a>
            @endif

            @if($contactInfo->phone)
                <a href="tel:{{ $contactInfo->phone }}" class="ct-item ct-item--phone">
                    <span class="ct-item-badge ct-item-badge--phone">
                        <x-render-icon icon="heroicon-o-phone" />
                    </span>
                    <div class="ct-item-content">
                        <span class="ct-item-title">{{ __('filament.fields.phone') }}</span>
                        <span class="ct-item-subtitle">اتصال هاتفي مباشر</span>
                    </div>
                    <span class="ct-item-arrow">
                        <x-render-icon icon="heroicon-o-chevron-left" />
                    </span>
                </a>
            @endif

            @if($contactInfo->email)
                <a href="mailto:{{ $contactInfo->email }}" class="ct-item ct-item--email">
                    <span class="ct-item-badge ct-item-badge--email">
                        <x-render-icon icon="heroicon-o-envelope" />
                    </span>
                    <div class="ct-item-content">
                        <span class="ct-item-title">{{ __('filament.fields.email') }}</span>
                        <span class="ct-item-subtitle">راسل فريق الدعم الفني</span>
                    </div>
                    <span class="ct-item-arrow">
                        <x-render-icon icon="heroicon-o-chevron-left" />
                    </span>
                </a>
            @endif

            @if($contactInfo->facebook)
                <a href="{{ $contactInfo->facebook }}"
                   target="_blank"
                   rel="noopener noreferrer nofollow"
                   class="ct-item ct-item--facebook">
                    <span class="ct-item-badge ct-item-badge--facebook">
                        <x-render-icon icon="brand-facebook" />
                    </span>
                    <div class="ct-item-content">
                        <span class="ct-item-title">{{ __('filament.fields.facebook') }}</span>
                        <span class="ct-item-subtitle">تابع صفحتنا وتواصل معنا عبر فيسبوك</span>
                    </div>
                    <span class="ct-item-arrow">
                        <x-render-icon icon="heroicon-o-chevron-left" />
                    </span>
                </a>
            @endif
        </div>

        {{-- FAQ Section --}}
        <div class="ct-section-title mt-6">
            <span>الأسئلة الشائعة</span>
        </div>
        <div class="ct-faq-list">
            <div class="ct-faq-item">
                <details class="group">
                    <summary class="flex justify-between items-center font-bold cursor-pointer list-none py-3 text-slate-800 dark:text-slate-200 text-xs">
                        <span>كيف يمكنني استخدام التطبيق؟</span>
                        <span class="transition group-open:rotate-180 text-slate-400 flex-none">
                            <x-render-icon icon="heroicon-o-chevron-down" class="w-4 h-4" />
                        </span>
                    </summary>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 pb-3 leading-relaxed">
                        يمكنك استخدام تطبيق دلني للبحث وتصفح التخصصات المختلفة للعثور على مقدمي الخدمات المحليين الموثوقين في مدينتك والتواصل معهم مباشرة عبر الواتساب أو الهاتف دون أي وسطاء أو رسوم إضافية.
                    </p>
                </details>
            </div>
            <div class="ct-faq-item">
                <details class="group">
                    <summary class="flex justify-between items-center font-bold cursor-pointer list-none py-3 text-slate-800 dark:text-slate-200 text-xs">
                        <span>كيف يمكنني التسجيل كمقدم خدمة في دلني؟</span>
                        <span class="transition group-open:rotate-180 text-slate-400 flex-none">
                            <x-render-icon icon="heroicon-o-chevron-down" class="w-4 h-4" />
                        </span>
                    </summary>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 pb-3 leading-relaxed">
                        التسجيل في غاية السهولة! كل ما عليك فعله هو الضغط على زر الواتساب أعلاه، وسيقوم فريق الدعم الفني بمساعدتك في إعداد ملفك الشخصي وتنشيط اشتراكك للظهور في نتائج البحث.
                    </p>
                </details>
            </div>
            <div class="ct-faq-item">
                <details class="group">
                    <summary class="flex justify-between items-center font-bold cursor-pointer list-none py-3 text-slate-800 dark:text-slate-200 text-xs">
                        <span>هل يقدم دلني الدعم الفني مجاناً للمستخدمين؟</span>
                        <span class="transition group-open:rotate-180 text-slate-400 flex-none">
                            <x-render-icon icon="heroicon-o-chevron-down" class="w-4 h-4" />
                        </span>
                    </summary>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 pb-3 leading-relaxed">
                        نعم بالتأكيد! نحن نقدم الدعم الفني الكامل والمساعدة للمستخدمين والعملاء لتسهيل الوصول لمقدمي الخدمات المناسبين مجاناً ودون أي رسوم.
                    </p>
                </details>
            </div>
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
    /* Contact page styles */



    /* Lists and Sections */
    .ct-section-title {
        font-size: .72rem;
        font-weight: 900;
        color: var(--delni-primary);
        text-transform: uppercase;
        letter-spacing: .02em;
        margin-bottom: .75rem;
        padding-right: .25rem;
    }
    .ct-list {
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    /* High fidelity interactive rows */
    .ct-item {
        display: flex;
        align-items: center;
        gap: .95rem;
        padding: .95rem 1.1rem;
        border-radius: 20px;
        border: 1px solid var(--delni-border);
        background: #fff;
        text-decoration: none;
        transition: transform .2s cubic-bezier(0.4, 0, 0.2, 1), border-color .2s, box-shadow .2s;
    }
    .ct-item:active {
        transform: scale(0.98);
    }

    /* Badges */
    .ct-item-badge {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ct-item-badge svg {
        width: 20px;
        height: 20px;
    }
    .ct-item-badge--wa { background: #E8FBF0; color: #25D366; }
    .ct-item-badge--phone { background: rgba(241,98,15,.08); color: var(--delni-primary); }
    .ct-item-badge--email { background: #EEF2FF; color: #6366F1; }
    .ct-item-badge--facebook { background: #EFF6FF; color: #1877F2; }

    /* Contents */
    .ct-item-content {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: .15rem;
        text-align: right;
    }
    .ct-item-title {
        color: var(--delni-navy);
        font-size: .85rem;
        font-weight: 900;
    }
    .ct-item-subtitle {
        color: #94A3B8;
        font-size: .7rem;
        font-weight: 700;
    }
    .ct-item-value {
        color: #64748B;
        font-size: .75rem;
        font-weight: 700;
        line-height: 1.5;
    }

    /* Arrows */
    .ct-item-arrow {
        flex-shrink: 0;
        color: #CBD5E1;
        display: inline-flex;
        align-items: center;
    }
    .ct-item-arrow svg {
        width: 18px;
        height: 18px;
    }
    [dir="ltr"] .ct-item-arrow svg {
        transform: scaleX(-1);
    }

    /* Row borders on active */
    .ct-item--wa { border-color: rgba(37,211,102,.15); }
    .ct-item--wa:active { border-color: rgba(37,211,102,.4); box-shadow: 0 8px 20px rgba(37,211,102,.08); }
    .ct-item--phone { border-color: rgba(241,98,15,.12); }
    .ct-item--phone:active { border-color: rgba(241,98,15,.3); box-shadow: 0 8px 20px rgba(241,98,15,.08); }
    .ct-item--email { border-color: rgba(99,102,241,.12); }
    .ct-item--email:active { border-color: rgba(99,102,241,.3); box-shadow: 0 8px 20px rgba(99,102,241,.08); }
    .ct-item--facebook { border-color: rgba(24,119,242,.12); }
    .ct-item--facebook:active { border-color: rgba(24,119,242,.3); box-shadow: 0 8px 20px rgba(24,119,242,.08); }

    /* FAQ accordion */
    .ct-faq-list {
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }
    .ct-faq-item {
        background: #fff;
        border: 1px solid var(--delni-border);
        border-radius: 18px;
        padding: 0 .95rem;
    }
    .ct-faq-item details summary::-webkit-details-marker {
        display: none;
    }

    .ct-empty {
        margin-top: 2rem;
    }

    /* Dark Mode Settings */
    [data-theme="dark"] .ct-item {
        background: var(--delni-card);
        border-color: var(--delni-border);
    }
    [data-theme="dark"] .ct-item-title {
        color: var(--delni-navy);
    }
    [data-theme="dark"] .ct-item-subtitle {
        color: var(--delni-muted);
    }
    [data-theme="dark"] .ct-item-value {
        color: var(--delni-muted);
    }
    [data-theme="dark"] .ct-item--wa { border-color: rgba(37,211,102,.12); }
    [data-theme="dark"] .ct-item--phone { border-color: rgba(241,98,15,.1); }
    [data-theme="dark"] .ct-item--email { border-color: rgba(99,102,241,.1); }
    [data-theme="dark"] .ct-item--facebook { border-color: rgba(24,119,242,.14); }
    [data-theme="dark"] .ct-faq-item {
        background: var(--delni-card);
        border-color: var(--delni-border);
    }
</style>
@endpush
@endsection
