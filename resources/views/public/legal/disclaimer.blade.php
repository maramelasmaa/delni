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
            <h3>طبيعة المنصة</h3>
            <p>
                تُعد دلني منصة دليل إلكتروني مستقلة، ولا تعمل بصفتها وكيلاً أو ممثلاً لأي مقدم خدمة مدرج على المنصة.
            </p>
        </div>

        <div class="ab-section">
            <h3>عدم ضمان جودة الخدمات</h3>
            <p>
                لا تقدم دلني أي ضمانات تتعلق بجودة الخدمات أو سلامتها أو نتائجها، وتبقى المسؤولية الكاملة عن الخدمات المقدمة على عاتق مقدمي الخدمات.
            </p>
        </div>

        <div class="ab-section">
            <h3>دقة المعلومات</h3>
            <p>
                تبذل دلني جهوداً معقولة لضمان صحة المعلومات المنشورة وتحديثها، إلا أنها لا تضمن اكتمالها أو دقتها أو استمرار حداثتها.
            </p>
        </div>

        <div class="ab-section">
            <h3>التواصل والمدفوعات خارج المنصة</h3>
            <p>
                أي تواصل أو تفاوض أو دفع أو اتفاق يتم خارج منصة دلني يكون بين المستخدم ومقدم الخدمة مباشرة، ولا تتحمل المنصة أي مسؤولية عن تلك التعاملات.
            </p>
        </div>

        <div class="ab-section">
            <h3>التقييمات والآراء</h3>
            <p>
                تعبر التقييمات والتعليقات المنشورة عن آراء أصحابها، ولا تمثل بالضرورة رأي أو موقف منصة دلني.
            </p>
        </div>

        <div class="ab-section">
            <h3>الروابط الخارجية</h3>
            <p>
                قد تتضمن المنصة روابط لمواقع إلكترونية أو تطبيقات تابعة لأطراف أخرى، ولا تتحمل دلني أي مسؤولية عن محتوى تلك الجهات أو خدماتها أو سياسات الخصوصية الخاصة بها.
            </p>
        </div>

        <div class="ab-section">
            <h3>توفر المنصة</h3>
            <p>
                تسعى دلني إلى توفير خدماتها بصورة مستمرة، إلا أنها لا تضمن خلو المنصة من الأعطال أو الانقطاعات أو المشكلات التقنية.
            </p>
        </div>

        <div class="ab-section">
            <h3>حدود المسؤولية</h3>
            <p>
                في الحدود التي يسمح بها القانون، لا تتحمل دلني أي مسؤولية عن الأضرار أو الخسائر المباشرة أو غير المباشرة أو التبعية الناتجة عن استخدام المنصة أو عن التعامل مع مقدمي الخدمات المدرجين فيها.
            </p>
        </div>

        <div class="ab-section">
            <h3>عدم تقديم المشورة المهنية</h3>
            <p>
                جميع المعلومات المتاحة على منصة دلني مقدمة لأغراض التعريف والبحث فقط، ولا تشكل استشارة مهنية أو قانونية أو مالية أو طبية أو تقنية من أي نوع.
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
