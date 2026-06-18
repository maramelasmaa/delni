@extends('public.layout')

@section('title', 'من نحن - ' . config('app.name'))

@section('content')
<div class="lp-wrapper lp-wrapper-compact">

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
        <p class="ab-tagline">دليلك للعثور على أفضل مقدمي الخدمات</p>
    </div>

    <div class="ab-sections">

        <div class="ab-section">
            <h3>ما هو دلني؟</h3>
            <p>دلني منصة تربط العملاء بمقدمي الخدمات المحليين من مختلف التخصصات. سواء كنت تبحث عن سبّاك أو كاتب أو معلم خصوصي، دلني يساعدك في إيجاد الشخص المناسب بكل سهولة.</p>
        </div>

        <div class="ab-section">
            <h3>رؤيتنا</h3>
            <p>نسعى إلى بناء سوق محلي موثوق يُمكّن مقدمي الخدمات من الوصول إلى عملاء جدد، ويمنح العملاء خياراتٍ واسعةً وتقييماتٍ حقيقية.</p>
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
        gap: .6rem;
        padding: 2.2rem 1.5rem;
        text-align: center;
        background: linear-gradient(135deg, #0b1a34 0%, #112240 100%);
        border-radius: 24px;
        color: #ffffff;
        box-shadow: 0 10px 30px rgba(11, 26, 52, 0.18);
        margin-bottom: 1.2rem;
    }
    .ab-logo {
        width: 76px; height: 76px;
        border-radius: 20px;
        border: 2px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }
    .ab-name {
        margin: .3rem 0 0;
        color: #ffffff;
        font-size: 1.45rem;
        font-weight: 950;
        letter-spacing: -.02em;
    }
    .ab-tagline {
        margin: 0;
        color: rgba(255, 255, 255, 0.78);
        font-size: .86rem;
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

    [data-theme="dark"] .ab-section { background: var(--delni-card); border-color: var(--delni-border); }
</style>
@endpush
@endsection
