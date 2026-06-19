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
            <h3>قبول الشروط</h3>
            <p>
                يُعد استخدام منصة دلني موافقةً من المستخدم على الالتزام بهذه الشروط والأحكام.
            </p>
        </div>

        <div class="ab-section">
            <h3>طبيعة المنصة</h3>
            <p>
                دلني منصة دليل إلكتروني، ولا تُعد طرفاً في أي اتفاق أو تعاقد أو تعامل يتم بين المستخدم ومقدم الخدمة.
            </p>
        </div>

        <div class="ab-section">
            <h3>مسؤوليات المستخدم</h3>
            <p>يلتزم المستخدم بما يلي:</p>
            <p style="margin-top: 0.5rem;">
                • استخدام المنصة بطريقة قانونية ومسؤولة.<br>
                • الامتناع عن تقديم بلاغات أو تقييمات مضللة أو غير صحيحة.<br>
                • عدم القيام بأي تصرف من شأنه الإضرار بالمنصة أو تعطيل عملها.
            </p>
        </div>

        <div class="ab-section">
            <h3>مسؤوليات مقدم الخدمة</h3>
            <p>يتحمل مقدم الخدمة وحده المسؤولية عن:</p>
            <p style="margin-top: 0.5rem;">
                • صحة ودقة المعلومات التي يقدمها.<br>
                • جودة الخدمات التي يقدمها.<br>
                • جميع التعاملات والاتفاقات التي تتم مع العملاء.
            </p>
        </div>

        <div class="ab-section">
            <h3>المحتوى المحظور</h3>
            <p>يُمنع نشر أو مشاركة أي محتوى:</p>
            <p style="margin-top: 0.5rem;">
                • مخالف للقوانين أو الأنظمة المعمول بها.<br>
                • مضلل أو يتضمن معلومات غير صحيحة.<br>
                • ينتهك حقوق الآخرين أو يمس بسمعتهم.<br>
                • يتضمن إساءة أو ألفاظاً غير لائقة أو محتوى غير مناسب.
            </p>
        </div>

        <div class="ab-section">
            <h3>التقييمات والمراجعات</h3>
            <p>
                تشجع دلني المستخدمين على تقديم تقييمات ومراجعات موضوعية وصادقة. كما تحتفظ المنصة بحق حذف أو إخفاء أي تقييم أو مراجعة تتضمن معلومات مضللة أو إساءة أو مخالفة لهذه الشروط.
            </p>
        </div>

        <div class="ab-section">
            <h3>إدارة الحسابات والمحتوى</h3>
            <p>
                تحتفظ دلني بحق تعديل أو تعليق أو حذف الحسابات أو المحتوى المخالف لهذه الشروط أو للقوانين النافذة، دون الإخلال بحقوقها القانونية الأخرى.
            </p>
        </div>

        <div class="ab-section">
            <h3>الخدمات والميزات المدفوعة</h3>
            <p>
                قد تتطلب بعض خدمات أو مزايا الظهور والترويج لمقدمي الخدمات رسوماً أو اشتراكات محددة، وسيتم توضيح تفاصيلها بشكل منفصل.
            </p>
        </div>

        <div class="ab-section">
            <h3>التعاملات خارج المنصة</h3>
            <p>
                تتم جميع الاتفاقات والمدفوعات والتعاملات التي تتم خارج منصة دلني على مسؤولية الأطراف المعنية وحدهم، ولا تتحمل المنصة أي مسؤولية عنها.
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
