@extends('public.layout')

@section('title', 'سياسة الخصوصية - ' . config('app.name'))

@section('content')
<div class="lp-wrapper lp-wrapper-compact">

    <header class="lp-header">
        <a href="{{ route('settings') }}" class="lp-back" aria-label="رجوع">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">شروط وسياسات</span>
            <h1 class="lp-title">سياسة الخصوصية</h1>
        </div>
    </header>

    <div class="ab-sections">
        <div class="ab-section">
            <h3>من نحن</h3>
            <p>
                دلني منصة دليل إلكتروني تهدف إلى مساعدة المستخدمين في العثور على مقدمي الخدمات داخل ليبيا والتواصل معهم. ولا تقوم المنصة بتقديم الخدمات بنفسها، وإنما توفر وسيلة لعرض معلومات مقدمي الخدمات وتسهيل الوصول إليهم.
            </p>
        </div>

        <div class="ab-section">
            <h3>المعلومات التي نقوم بجمعها</h3>
            <p>قد نقوم بجمع ومعالجة بعض المعلومات، بما في ذلك:</p>
            <p style="margin-top: 0.5rem;">
                • الاسم ورقم الهاتف والبريد الإلكتروني عند إنشاء حساب أو التواصل معنا.<br>
                • بيانات مقدمي الخدمات، مثل اسم النشاط، المدينة، الفئة، الوصف، الصور ووسائل التواصل.<br>
                • التقييمات والمراجعات والمحتوى الذي يقدمه المستخدمون.<br>
                • بيانات الاستخدام الأساسية، مثل الصفحات التي تمت زيارتها وعمليات البحث، بهدف تحسين أداء المنصة وتجربة المستخدم.
            </p>
        </div>

        <div class="ab-section">
            <h3>كيفية استخدام المعلومات</h3>
            <p>تُستخدم المعلومات التي يتم جمعها للأغراض التالية:</p>
            <p style="margin-top: 0.5rem;">
                • عرض بيانات مقدمي الخدمات داخل المنصة.<br>
                • تحسين تجربة البحث والتصفح وتطوير خدمات المنصة.<br>
                • إدارة الحسابات والاشتراكات والمحتوى المنشور.<br>
                • التواصل مع المستخدمين أو مقدمي الخدمات عند الحاجة.<br>
                • مراجعة البلاغات والحد من إساءة استخدام المنصة.
            </p>
        </div>

        <div class="ab-section">
            <h3>مشاركة المعلومات</h3>
            <p>
                لا تقوم دلني ببيع البيانات الشخصية للمستخدمين. وقد يتم عرض بعض المعلومات الخاصة بمقدمي الخدمات بشكل علني، باعتبار أن ذلك يمثل الغرض الأساسي من المنصة.
            </p>
        </div>

        <div class="ab-section">
            <h3>التواصل باحترام</h3>
            <p>
                يلتزم المستخدمون بالتعامل مع مقدمي الخدمات بأسلوب محترم، ويُحظر توجيه أي تهديدات أو مضايقات أو رسائل مسيئة. وتحتفظ إدارة دلني بحق اتخاذ الإجراءات المناسبة بحق الحسابات المخالفة.
            </p>
        </div>

        <div class="ab-section">
            <h3>التواصل خارج المنصة</h3>
            <p>
                أي تواصل يتم عبر الهاتف أو تطبيق واتساب أو وسائل التواصل الأخرى أو الروابط الخارجية يتم خارج نطاق منصة دلني، ولا تتحمل المنصة مسؤولية تلك المراسلات أو التعاملات.
            </p>
        </div>

        <div class="ab-section">
            <h3>حماية البيانات</h3>
            <p>
                تلتزم دلني باتخاذ التدابير المعقولة والمناسبة لحماية البيانات والمحافظة على أمنها، مع الإقرار بأنه لا يمكن ضمان الحماية المطلقة لأي نظام إلكتروني.
            </p>
        </div>
    </div>

    <div class="ab-links">
        <a href="{{ route('terms') }}" class="ab-link">شروط الاستخدام</a>
        <span class="ab-dot">·</span>
        <a href="{{ route('disclaimer') }}" class="ab-link">إخلاء المسؤولية</a>
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
