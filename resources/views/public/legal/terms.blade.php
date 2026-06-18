@extends('public.layout')

@section('title', 'شروط الاستخدام - ' . config('app.name'))

@section('content')
<div class="lp-wrapper lp-wrapper-compact">

    <header class="lp-header">
        <a href="{{ route('settings') }}" class="lp-back" aria-label="رجوع">
            <x-render-icon icon="heroicon-o-arrow-right" />
        </a>
        <div class="lp-header-body">
            <span class="lp-label">شروط وسياسات</span>
            <h1 class="lp-title">شروط الاستخدام</h1>
        </div>
    </header>

    <div class="ab-sections">

        <div class="ab-section">
            <h3>1. قبول الشروط</h3>
            <p>
                باستخدامك لمنصة دلني، فإنك توافق على الالتزام بهذه الشروط.
                إذا كنت لا توافق عليها، يرجى عدم استخدام المنصة.
            </p>
        </div>

        <div class="ab-section">
            <h3>2. طبيعة المنصة</h3>
            <p>
                دلني منصة دليل إلكتروني تعرض مقدمي خدمات ومعلوماتهم بهدف تسهيل الوصول إليهم.
                دلني ليست طرفًا في الاتفاق أو التعامل الذي يتم بين المستخدم ومقدم الخدمة.
            </p>
        </div>

        <div class="ab-section">
            <h3>3. مسؤولية المستخدم</h3>
            <p>
                - استخدام المنصة بطريقة قانونية ومحترمة.<br>
                - التحقق من مقدم الخدمة قبل الاتفاق معه.<br>
                - عدم إرسال بلاغات أو تقييمات كاذبة أو مسيئة.<br>
                - عدم محاولة اختراق المنصة أو تعطيلها أو إساءة استخدامها.
            </p>
        </div>

        <div class="ab-section">
            <h3>4. مسؤولية مقدم الخدمة</h3>
            <p>
                - تقديم معلومات صحيحة وحديثة عن النشاط والخدمات.<br>
                - عدم نشر صور أو بيانات مضللة.<br>
                - الالتزام بالاتفاقات التي تتم مع العملاء خارج المنصة.<br>
                - تحمل مسؤولية جودة الخدمة والأسعار والتعامل مع العملاء.
            </p>
        </div>

        <div class="ab-section">
            <h3>5. المحتوى الممنوع</h3>
            <p>يمنع نشر أو إرسال أي محتوى:</p>
            <p style="margin-top: 0.25rem;">
                - مسيء أو تهديدي أو يحرض على الكراهية.<br>
                - مخالف للقانون أو الآداب العامة.<br>
                - ينتهك حقوق الآخرين أو يستخدم صورهم دون إذن.<br>
                - مضلل أو احتيالي أو يحتوي على معلومات غير صحيحة.
            </p>
        </div>

        <div class="ab-section">
            <h3>6. التقييمات والمراجعات</h3>
            <p>
                يجوز للمستخدمين إرسال تقييمات عن تجربتهم. تحتفظ دلني بحق إخفاء أو حذف أي تقييم نراه مسيئًا،
                غير حقيقي، مكررًا، أو مخالفًا لشروط المنصة.
            </p>
        </div>

        <div class="ab-section">
            <h3>7. إدارة الحسابات والمحتوى</h3>
            <p>
                يحق لإدارة دلني تعديل أو إخفاء أو حذف أي حساب أو محتوى أو ملف مقدم خدمة إذا كان مخالفًا للشروط،
                أو يحتوي على معلومات غير دقيقة، أو يسبب ضررًا لتجربة المستخدمين.
            </p>
        </div>

        <div class="ab-section">
            <h3>8. الاشتراكات والظهور في المنصة</h3>
            <p>
                قد تكون بعض خدمات الظهور داخل دلني مدفوعة لمقدمي الخدمات.
                عدم دفع الرسوم أو انتهاء الاشتراك قد يؤدي إلى إخفاء الملف أو تقليل ظهوره داخل المنصة.
            </p>
        </div>

        <div class="ab-section">
            <h3>9. التعامل خارج المنصة</h3>
            <p>
                أي تواصل أو اتفاق أو دفع يتم بين المستخدم ومقدم الخدمة عبر الهاتف أو واتساب أو خارج دلني
                يكون مسؤولية الطرفين فقط.
            </p>
        </div>

        <div class="ab-section">
            <h3>10. تعديل الشروط</h3>
            <p>
                قد نقوم بتعديل هذه الشروط عند الحاجة. استمرار استخدامك للمنصة بعد التعديل يعني موافقتك على الشروط الجديدة.
            </p>
        </div>

        <div class="ab-section">
            <h3>11. القانون الحاكم</h3>
            <p>
                تخضع هذه الشروط للقوانين المعمول بها في دولة ليبيا، ما لم ينص القانون على غير ذلك.
            </p>
        </div>

    </div>

    <div class="ab-links">
        <a href="{{ route('privacy') }}" class="ab-link">سياسة الخصوصية</a>
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
