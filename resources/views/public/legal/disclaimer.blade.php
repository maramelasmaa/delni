@extends('public.layout')

@section('title', 'إخلاء المسؤولية - ' . config('app.name'))

@section('content')
<div class="lp-wrapper lp-wrapper-compact">

    <header class="lp-header">
        <a href="{{ route('settings') }}" class="lp-back" aria-label="رجوع">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">شروط وسياسات</span>
            <h1 class="lp-title">إخلاء المسؤولية</h1>
        </div>
    </header>

    <div class="ab-sections">

        <div class="ab-section">
            <h3>1. دلني منصة وسيطة</h3>
            <p>
                دلني منصة دليل إلكتروني تعرض معلومات مقدمي الخدمات لتسهيل الوصول إليهم.
                نحن لا نقدم الخدمات بأنفسنا، ولا نعمل كوكيل أو ممثل لأي مقدم خدمة.
            </p>
        </div>

        <div class="ab-section">
            <h3>2. عدم ضمان جودة الخدمات</h3>
            <p>
                لا تضمن دلني جودة أو نتيجة أو سلامة أي خدمة يقدمها أي مقدم خدمة.
                اختيار مقدم الخدمة والتعامل معه يكون على مسؤولية المستخدم.
            </p>
        </div>

        <div class="ab-section">
            <h3>3. دقة المعلومات</h3>
            <p>
                نحاول مراجعة المعلومات المعروضة قدر الإمكان، لكن قد تحتوي بعض الملفات على بيانات غير مكتملة أو قديمة أو غير دقيقة.
                مقدم الخدمة مسؤول عن تحديث بياناته وصحة ما يعرضه.
            </p>
        </div>

        <div class="ab-section">
            <h3>4. التواصل والدفع خارج المنصة</h3>
            <p>
                أي تواصل أو اتفاق أو دفع يتم خارج دلني، مثل الهاتف أو واتساب، يكون بين المستخدم ومقدم الخدمة مباشرة.
                دلني لا تتحمل مسؤولية النزاعات أو الخسائر الناتجة عن هذه التعاملات.
            </p>
        </div>

        <div class="ab-section">
            <h3>5. التقييمات والآراء</h3>
            <p>
                التقييمات والتعليقات تعبّر عن آراء أصحابها فقط، ولا تعبر بالضرورة عن رأي دلني.
                قد نقوم بحذف أي تقييم مخالف أو مسيء أو مشكوك في صحته.
            </p>
        </div>

        <div class="ab-section">
            <h3>6. الروابط الخارجية</h3>
            <p>
                قد تحتوي المنصة على روابط خارجية مثل واتساب أو مواقع التواصل أو مواقع مقدمي الخدمات.
                نحن لا نتحكم في هذه المواقع ولا نتحمل مسؤولية محتواها أو سياساتها.
            </p>
        </div>

        <div class="ab-section">
            <h3>7. توفر المنصة</h3>
            <p>
                نسعى لتوفير المنصة بشكل مستمر، لكن لا نضمن أن تعمل دون انقطاع أو أخطاء أو مشاكل تقنية.
                قد نقوم بإيقاف أو تعديل أي جزء من المنصة عند الحاجة.
            </p>
        </div>

        <div class="ab-section">
            <h3>8. حدود المسؤولية</h3>
            <p>
                لا تتحمل دلني مسؤولية أي خسائر أو أضرار مباشرة أو غير مباشرة تنتج عن استخدام المنصة،
                أو الاعتماد على معلومات منشورة فيها، أو التعامل مع مقدمي الخدمات.
            </p>
        </div>

        <div class="ab-section">
            <h3>9. لا نقدم نصائح مهنية</h3>
            <p>
                المعلومات الموجودة في دلني لغرض التعريف والبحث فقط.
                لا تعتبر المنصة مصدرًا لنصائح قانونية أو مالية أو طبية أو هندسية أو أي نصائح مهنية متخصصة.
            </p>
        </div>

        <div class="ab-section">
            <h3>10. قبول الإخلاء</h3>
            <p>
                باستخدامك لمنصة دلني، فإنك تقر بأنك فهمت حدود دور المنصة وتوافق على هذا الإخلاء.
            </p>
        </div>

    </div>

    <div class="ab-links">
        <a href="{{ route('terms') }}" class="ab-link">شروط الاستخدام</a>
        <span class="ab-dot">·</span>
        <a href="{{ route('privacy') }}" class="ab-link">سياسة الخصوصية</a>
    </div>

    <p class="ab-version">آخر تحديث: 18/06/2026</p>

</div>

@push('styles')
<style>
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
        font-weight: 750;
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
