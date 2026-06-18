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
            <h3>1. من نحن</h3>
            <p>
                دلني منصة دليل إلكتروني تساعد المستخدمين في العثور على مقدمي خدمات داخل ليبيا.
                نحن لا نقدم الخدمات بأنفسنا، بل نعرض معلومات مقدمي الخدمات لتسهيل الوصول إليهم.
            </p>
        </div>

        <div class="ab-section">
            <h3>2. البيانات التي قد نجمعها</h3>
            <p>
                - الاسم ورقم الهاتف والبريد الإلكتروني عند إنشاء حساب أو التواصل معنا.<br>
                - بيانات مقدمي الخدمات مثل اسم النشاط، المدينة، التخصص، الوصف، الصور، وروابط التواصل.<br>
                - التقييمات أو التعليقات التي يرسلها المستخدمون.<br>
                - بيانات استخدام بسيطة مثل الصفحات التي تمت زيارتها أو عمليات البحث لتحسين المنصة.
            </p>
        </div>

        <div class="ab-section">
            <h3>3. كيف نستخدم البيانات</h3>
            <p>
                - عرض مقدمي الخدمات داخل المنصة.<br>
                - تحسين تجربة البحث والتصفح.<br>
                - إدارة الحسابات والاشتراكات والمحتوى.<br>
                - التواصل مع المستخدم أو مقدم الخدمة عند الحاجة.<br>
                - مراجعة البلاغات أو منع الاستخدام المسيء للمنصة.
            </p>
        </div>

        <div class="ab-section">
            <h3>4. مشاركة البيانات</h3>
            <p>
                لا نبيع بياناتك الشخصية. قد تظهر بعض بيانات مقدم الخدمة للعامة مثل الاسم التجاري،
                رقم الهاتف، الواتساب، المدينة، الصور، والوصف، لأن هذا هو الغرض الأساسي من المنصة.
            </p>
            <p style="margin-top: 0.5rem;">
                قد نشارك البيانات فقط عند الحاجة لتشغيل المنصة، أو عند وجود طلب قانوني، أو لحماية حقوق المنصة والمستخدمين.
            </p>
        </div>

        <div class="ab-section">
            <h3>5. حماية مقدمي الخدمات من الإزعاج</h3>
            <p>
                نحن ملتزمون بحماية مقدمي الخدمات من التحرش والإزعاج والسلوك المسيء.
                لا يجوز للمستخدمين تهديد أو مضايقة أو إرسال رسائل مسيئة أو مزعجة إلى مقدمي الخدمات.
            </p>
            <p style="margin-top: 0.5rem;">
                إذا تعرض مقدم خدمة للتحرش أو تلقى تهديدات أو رسائل مسيئة، يمكنه الإبلاغ عن ذلك لفريق دلني.
                قد نتخذ إجراءات ضد المستخدمين الذين ينتهكون هذه السياسة، بما في ذلك حظر الحساب.
            </p>
        </div>

        <div class="ab-section">
            <h3>6. التواصل خارج دلني</h3>
            <p>
                عند الضغط على رقم الهاتف أو واتساب أو أي رابط خارجي، قد تنتقل إلى تطبيق أو موقع خارج دلني.
                نحن لا نتحكم في سياسات الخصوصية أو طريقة استخدام البيانات خارج منصتنا.
            </p>
        </div>

        <div class="ab-section">
            <h3>7. حماية البيانات</h3>
            <p>
                نستخدم إجراءات مناسبة لحماية البيانات من الوصول غير المصرح به قدر الإمكان.
                ومع ذلك، لا توجد منصة إلكترونية يمكنها ضمان حماية كاملة بنسبة 100%.
            </p>
        </div>

        <div class="ab-section">
            <h3>8. التغييرات على السياسة</h3>
            <p>
                قد نقوم بتحديث هذه السياسة من وقت لآخر. استمرارك في استخدام المنصة بعد التحديث يعني موافقتك على النسخة الجديدة.
            </p>
        </div>

        <div class="ab-section">
            <h3>9. التواصل معنا</h3>
            <p>
                لأي سؤال بخصوص الخصوصية أو بياناتك، يمكنك التواصل مع فريق دلني عبر وسائل التواصل المتاحة في المنصة.
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
