@extends('public.layout')

@section('title', 'من نحن - ' . config('app.name'))

@section('content')
<div class="lp-wrapper">

    <header class="lp-header">
        <a href="{{ route('settings') }}" class="lp-back" aria-label="رجوع">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">دلني</span>
            <h1 class="lp-title">من نحن</h1>
        </div>
    </header>

    <div class="ab-hero">
        <img src="{{ asset('images/icon-192.png') }}" alt="دلني" class="ab-logo">
        <h2 class="ab-name">{{ config('app.name') }}</h2>
        <p class="ab-tagline">دليلك للعثور على أفضل مزودي الخدمات</p>
    </div>

    <div class="ab-sections">

        <div class="ab-section">
            <h3>ما هو دلني؟</h3>
            <p>دلني منصة تربط العملاء بمزودي الخدمات المحليين من مختلف التخصصات. سواء كنت تبحث عن سبّاك أو كاتب أو معلم خصوصي، دلني يساعدك في إيجاد الشخص المناسب بكل سهولة.</p>
        </div>

        <div class="ab-section">
            <h3>رؤيتنا</h3>
            <p>نسعى إلى بناء سوق محلي موثوق يُمكّن مزودي الخدمات من الوصول إلى عملاء جدد، ويمنح العملاء خياراتٍ واسعةً ومراجعاتٍ حقيقية.</p>
        </div>

        <div class="ab-section">
            <h3>تواصل معنا</h3>
            <p>لأي استفسار أو اقتراح، تواصل معنا عبر صفحة <a href="{{ route('contact') }}">الاتصال</a>.</p>
        </div>

    </div>

    <div class="ab-links">
        <a href="{{ route('terms') }}" class="ab-link">شروط الاستخدام</a>
        <span class="ab-dot">·</span>
        <a href="{{ route('privacy') }}" class="ab-link">سياسة الخصوصية</a>
        <span class="ab-dot">·</span>
        <a href="{{ route('disclaimer') }}" class="ab-link">إخلاء المسؤولية</a>
    </div>

    <p class="ab-version">الإصدار 1.0.0</p>

</div>

@push('styles')
<style>
    .ab-hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .5rem;
        padding: 1.5rem 1rem 1rem;
        text-align: center;
    }
    .ab-logo {
        width: 80px; height: 80px;
        border-radius: 22px;
        box-shadow: var(--delni-shadow-md);
    }
    .ab-name {
        margin: .4rem 0 0;
        color: var(--delni-navy);
        font-size: 1.4rem;
        font-weight: 950;
        letter-spacing: -.02em;
    }
    .ab-tagline {
        margin: 0;
        color: var(--delni-muted);
        font-size: .84rem;
        font-weight: 700;
    }
    .ab-sections {
        display: flex;
        flex-direction: column;
        gap: .6rem;
        margin-top: 1rem;
    }
    .ab-section {
        background: #fff;
        border: 1px solid var(--delni-border);
        border-radius: 18px;
        padding: 1rem 1.1rem;
    }
    .ab-section h3 {
        margin: 0 0 .4rem;
        color: var(--delni-navy);
        font-size: .88rem;
        font-weight: 900;
    }
    .ab-section p {
        margin: 0;
        color: var(--delni-muted);
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.75;
    }
    .ab-section a { color: var(--delni-primary); }
    .ab-links {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        margin-top: 1.2rem;
        flex-wrap: wrap;
    }
    .ab-link {
        color: var(--delni-muted);
        font-size: .78rem;
        font-weight: 750;
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    .ab-dot { color: var(--delni-border); font-size: .7rem; }
    .ab-version {
        text-align: center;
        margin: .65rem 0 0;
        color: var(--delni-gray);
        font-size: .72rem;
        font-weight: 700;
    }

    [data-theme="dark"] .ab-section { background: #1E293B; border-color: #334155; }
    [data-theme="dark"] .ab-name { color: #F1F5F9; }
    [data-theme="dark"] .ab-tagline { color: #94A3B8; }
</style>
@endpush
@endsection
