# Legal Blades Export

**Generated:** 2026-06-10 13:32:54

## Table of Contents

- [legal_layout.blade.php](#public-legal_layout)
- [privacy.blade.php](#public-legal-privacy)
- [terms.blade.php](#public-legal-terms)
- [disclaimer.blade.php](#public-legal-disclaimer)

---

## legal_layout.blade.php

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', '')">

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #F1620F;
            --navy: #0B1A34;
            --bg: #FCFBFB;
            --surface: #FFFFFF;
            --border: #E7E7E7;
            --muted: #5D5959;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Cairo', system-ui, sans-serif;
            background: var(--bg);
            color: var(--navy);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; }
        img, svg { max-width: 100%; }

        .container {
            width: min(100% - 2rem, 1240px);
            margin-inline: auto;
        }

        /* Header */
        .legal-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .legal-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 70px;
            gap: 1.5rem;
        }

        .legal-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 900;
            color: var(--navy);
            letter-spacing: -0.02em;
            flex-shrink: 0;
        }

        .legal-logo img {
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }

        .legal-tabs {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legal-tab {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .legal-tab:hover,
        .legal-tab.active {
            color: var(--primary);
            background: rgba(241, 98, 15, 0.08);
        }

        /* Main Content */
        .legal-main {
            padding: 2.5rem 0;
        }

        .legal-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
        }

        .legal-card h1 {
            font-size: clamp(1.75rem, 4vw, 2.2rem);
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .legal-card-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 600;
        }

        /* Content Sections */
        .legal-section {
            margin-bottom: 2rem;
        }

        .legal-section:last-child {
            margin-bottom: 0;
        }

        .legal-section h2 {
            font-size: 1.15rem;
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .legal-section p {
            font-size: 0.95rem;
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .legal-section p:last-child {
            margin-bottom: 0;
        }

        .legal-section ul,
        .legal-section ol {
            padding-inline-start: 1.5rem;
            margin: 1rem 0;
        }

        .legal-section li {
            font-size: 0.95rem;
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 0.6rem;
            font-weight: 500;
        }

        .legal-section strong {
            color: var(--navy);
            font-weight: 700;
        }

        .legal-section a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .legal-section a:hover {
            text-decoration: underline;
        }

        /* Footer */
        .legal-footer {
            padding: 2rem 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--border);
            margin-top: 3rem;
        }

        .legal-footer a {
            color: var(--muted);
            text-decoration: none;
            font-weight: 700;
        }

        .legal-footer a:hover {
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .legal-header__inner {
                min-height: 64px;
            }

            .legal-logo {
                font-size: 1rem;
            }

            .legal-logo img {
                width: 32px;
                height: 32px;
            }

            .legal-tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                flex: 1;
                gap: 0.3rem;
            }

            .legal-tab {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .legal-card {
                padding: 1.25rem;
            }

            .legal-card h1 {
                font-size: 1.5rem;
            }

            .legal-card-meta {
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                gap: 0.75rem;
                flex-wrap: wrap;
            }

            .legal-section {
                margin-bottom: 1.5rem;
            }

            .legal-section h2 {
                font-size: 1.05rem;
                margin-bottom: 0.75rem;
            }

            .legal-section p,
            .legal-section li {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                width: min(100% - 1rem, 1240px);
            }

            .legal-header__inner {
                min-height: 60px;
                gap: 1rem;
            }

            .legal-logo {
                font-size: 0.95rem;
            }

            .legal-logo img {
                width: 30px;
                height: 30px;
            }

            .legal-tab {
                padding: 0.35rem 0.7rem;
                font-size: 0.75rem;
            }

            .legal-card {
                padding: 1rem;
            }

            .legal-card h1 {
                font-size: 1.35rem;
                margin-bottom: 0.4rem;
            }

            .legal-card-meta {
                font-size: 0.8rem;
                margin-bottom: 1.25rem;
                padding-bottom: 0.75rem;
            }

            .legal-section {
                margin-bottom: 1.25rem;
            }

            .legal-section h2 {
                font-size: 1rem;
                margin-bottom: 0.6rem;
            }

            .legal-section p,
            .legal-section li {
                font-size: 0.88rem;
                line-height: 1.7;
            }
        }
    </style>
</head>
<body>

<header class="legal-header">
    <div class="container">
        <div class="legal-header__inner">
            <a href="{{ route('home') }}" class="legal-logo">
                <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('app.name') }}">
                <span>دلني</span>
            </a>

            <nav class="legal-tabs">
                <a href="{{ route('privacy') }}"
                   class="legal-tab {{ request()->routeIs('privacy') ? 'active' : '' }}">
                    الخصوصية
                </a>
                <a href="{{ route('terms') }}"
                   class="legal-tab {{ request()->routeIs('terms') ? 'active' : '' }}">
                    الشروط
                </a>
                <a href="{{ route('disclaimer') }}"
                   class="legal-tab {{ request()->routeIs('disclaimer') ? 'active' : '' }}">
                    إخلاء
                </a>
            </nav>
        </div>
    </div>
</header>

<main class="legal-main">
    <div class="container">
        <article class="legal-card">
            @yield('content')
        </article>
    </div>
</main>

<footer class="legal-footer">
    <div class="container">
        © {{ date('Y') }} دلني
        <span style="opacity: 0.3; margin: 0 0.5rem;">•</span>
        <a href="{{ route('privacy') }}">الخصوصية</a>
        <span style="opacity: 0.3; margin: 0 0.5rem;">•</span>
        <a href="{{ route('terms') }}">الشروط</a>
        <span style="opacity: 0.3; margin: 0 0.5rem;">•</span>
        <a href="{{ route('disclaimer') }}">إخلاء</a>
    </div>
</footer>

@stack('scripts')
</body>
</html>

```

## privacy.blade.php

```blade
@extends('public.legal_layout')

@section('title', 'سياسة الخصوصية - ' . config('app.name'))
@section('meta_description', 'سياسة الخصوصية لمنصة دلني.')

@section('content')
<h1>سياسة الخصوصية</h1>

<div class="legal-card-meta">
    آخر تحديث: {{ now()->format('d/m/Y') }}
</div>

<div class="legal-section">
    <h2>1. من نحن</h2>
    <p>
        دلني منصة دليل إلكتروني تساعد المستخدمين في العثور على مقدمي خدمات داخل ليبيا.
        نحن لا نقدم الخدمات بأنفسنا، بل نعرض معلومات مقدمي الخدمات لتسهيل الوصول إليهم.
    </p>
</div>

<div class="legal-section">
    <h2>2. البيانات التي قد نجمعها</h2>
    <ul>
        <li>الاسم ورقم الهاتف والبريد الإلكتروني عند إنشاء حساب أو التواصل معنا.</li>
        <li>بيانات مقدمي الخدمات مثل اسم النشاط، المدينة، الفئة، الوصف، الصور، وروابط التواصل.</li>
        <li>التقييمات أو التعليقات التي يرسلها المستخدمون.</li>
        <li>بيانات استخدام بسيطة مثل الصفحات التي تمت زيارتها أو عمليات البحث لتحسين المنصة.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>3. كيف نستخدم البيانات</h2>
    <ul>
        <li>عرض مقدمي الخدمات داخل المنصة.</li>
        <li>تحسين تجربة البحث والتصفح.</li>
        <li>إدارة الحسابات والاشتراكات والمحتوى.</li>
        <li>التواصل مع المستخدم أو مقدم الخدمة عند الحاجة.</li>
        <li>مراجعة البلاغات أو منع الاستخدام المسيء للمنصة.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>4. مشاركة البيانات</h2>
    <p>
        لا نبيع بياناتك الشخصية. قد تظهر بعض بيانات مقدم الخدمة للعامة مثل الاسم التجاري،
        رقم الهاتف، الواتساب، المدينة، الصور، والوصف، لأن هذا هو الغرض الأساسي من المنصة.
    </p>
    <p>
        قد نشارك البيانات فقط عند الحاجة لتشغيل المنصة، أو عند وجود طلب قانوني، أو لحماية حقوق المنصة والمستخدمين.
    </p>
</div>

<div class="legal-section">
    <h2>5. حماية مقدمي الخدمات من الإزعاج</h2>
    <p>
        نحن ملتزمون بحماية مقدمي الخدمات من التحرش والإزعاج والسلوك المسيء.
        لا يجوز للمستخدمين تهديد أو مضايقة أو إرسال رسائل مسيئة أو مزعجة إلى مقدمي الخدمات.
    </p>
    <p>
        إذا تعرض مقدم خدمة للتحرش أو تلقى تهديدات أو رسائل مسيئة، يمكنه الإبلاغ عن ذلك لفريق دلني.
        قد نتخذ إجراءات ضد المستخدمين الذين ينتهكون هذه السياسة، بما في ذلك حظر الحساب.
    </p>
</div>

<div class="legal-section">
    <h2>6. التواصل خارج دلني</h2>
    <p>
        عند الضغط على رقم الهاتف أو واتساب أو أي رابط خارجي، قد تنتقل إلى تطبيق أو موقع خارج دلني.
        نحن لا نتحكم في سياسات الخصوصية أو طريقة استخدام البيانات خارج منصتنا.
    </p>
</div>

<div class="legal-section">
    <h2>7. حماية البيانات</h2>
    <p>
        نستخدم إجراءات مناسبة لحماية البيانات من الوصول غير المصرح به قدر الإمكان.
        ومع ذلك، لا توجد منصة إلكترونية يمكنها ضمان حماية كاملة بنسبة 100%.
    </p>
</div>

<div class="legal-section">
    <h2>8. التغييرات على السياسة</h2>
    <p>
        قد نقوم بتحديث هذه السياسة من وقت لآخر. استمرارك في استخدام المنصة بعد التحديث يعني موافقتك على النسخة الجديدة.
    </p>
</div>

<div class="legal-section">
    <h2>9. التواصل معنا</h2>
    <p>
        لأي سؤال بخصوص الخصوصية أو بياناتك، يمكنك التواصل مع فريق دلني عبر وسائل التواصل المتاحة في المنصة.
    </p>
</div>
@endsection

```

## terms.blade.php

```blade
@extends('public.legal_layout')

@section('title', 'شروط الاستخدام - ' . config('app.name'))
@section('meta_description', 'شروط استخدام منصة دلني.')

@section('content')
<h1>شروط الاستخدام</h1>

<div class="legal-card-meta">
    آخر تحديث: {{ now()->format('d/m/Y') }}
</div>

<div class="legal-section">
    <h2>1. قبول الشروط</h2>
    <p>
        باستخدامك لمنصة دلني، فإنك توافق على الالتزام بهذه الشروط.
        إذا كنت لا توافق عليها، يرجى عدم استخدام المنصة.
    </p>
</div>

<div class="legal-section">
    <h2>2. طبيعة المنصة</h2>
    <p>
        دلني منصة دليل إلكتروني تعرض مقدمي خدمات ومعلوماتهم بهدف تسهيل الوصول إليهم.
        دلني ليست طرفًا في الاتفاق أو التعامل الذي يتم بين المستخدم ومقدم الخدمة.
    </p>
</div>

<div class="legal-section">
    <h2>3. مسؤولية المستخدم</h2>
    <ul>
        <li>استخدام المنصة بطريقة قانونية ومحترمة.</li>
        <li>التحقق من مقدم الخدمة قبل الاتفاق معه.</li>
        <li>عدم إرسال بلاغات أو تقييمات كاذبة أو مسيئة.</li>
        <li>عدم محاولة اختراق المنصة أو تعطيلها أو إساءة استخدامها.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>4. مسؤولية مقدم الخدمة</h2>
    <ul>
        <li>تقديم معلومات صحيحة وحديثة عن النشاط والخدمات.</li>
        <li>عدم نشر صور أو بيانات مضللة.</li>
        <li>الالتزام بالاتفاقات التي تتم مع العملاء خارج المنصة.</li>
        <li>تحمل مسؤولية جودة الخدمة والأسعار والتعامل مع العملاء.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>5. المحتوى الممنوع</h2>
    <p>يمنع نشر أو إرسال أي محتوى:</p>
    <ul>
        <li>مسيء أو تهديدي أو يحرض على الكراهية.</li>
        <li>مخالف للقانون أو الآداب العامة.</li>
        <li>ينتهك حقوق الآخرين أو يستخدم صورهم دون إذن.</li>
        <li>مضلل أو احتيالي أو يحتوي على معلومات غير صحيحة.</li>
    </ul>
</div>

<div class="legal-section">
    <h2>6. التقييمات والمراجعات</h2>
    <p>
        يجوز للمستخدمين إرسال تقييمات عن تجربتهم. تحتفظ دلني بحق إخفاء أو حذف أي تقييم نراه مسيئًا،
        غير حقيقي، مكررًا، أو مخالفًا لشروط المنصة.
    </p>
</div>

<div class="legal-section">
    <h2>7. إدارة الحسابات والمحتوى</h2>
    <p>
        يحق لإدارة دلني تعديل أو إخفاء أو حذف أي حساب أو محتوى أو ملف مقدم خدمة إذا كان مخالفًا للشروط،
        أو يحتوي على معلومات غير دقيقة، أو يسبب ضررًا لتجربة المستخدمين.
    </p>
</div>

<div class="legal-section">
    <h2>8. الاشتراكات والظهور في المنصة</h2>
    <p>
        قد تكون بعض خدمات الظهور داخل دلني مدفوعة لمقدمي الخدمات.
        عدم دفع الرسوم أو انتهاء الاشتراك قد يؤدي إلى إخفاء الملف أو تقليل ظهوره داخل المنصة.
    </p>
</div>

<div class="legal-section">
    <h2>9. التعامل خارج المنصة</h2>
    <p>
        أي تواصل أو اتفاق أو دفع يتم بين المستخدم ومقدم الخدمة عبر الهاتف أو واتساب أو خارج دلني
        يكون مسؤولية الطرفين فقط.
    </p>
</div>

<div class="legal-section">
    <h2>10. تعديل الشروط</h2>
    <p>
        قد نقوم بتعديل هذه الشروط عند الحاجة. استمرار استخدامك للمنصة بعد التعديل يعني موافقتك على الشروط الجديدة.
    </p>
</div>

<div class="legal-section">
    <h2>11. القانون الحاكم</h2>
    <p>
        تخضع هذه الشروط للقوانين المعمول بها في دولة ليبيا، ما لم ينص القانون على غير ذلك.
    </p>
</div>
@endsection

```

## disclaimer.blade.php

```blade
@extends('public.legal_layout')

@section('title', 'إخلاء المسؤولية - ' . config('app.name'))
@section('meta_description', 'إخلاء مسؤولية منصة دلني.')

@section('content')
<h1>إخلاء المسؤولية</h1>

<div class="legal-card-meta">
    آخر تحديث: {{ now()->format('d/m/Y') }}
</div>

<div class="legal-section">
    <h2>1. دلني منصة وسيطة</h2>
    <p>
        دلني منصة دليل إلكتروني تعرض معلومات مقدمي الخدمات لتسهيل الوصول إليهم.
        نحن لا نقدم الخدمات بأنفسنا، ولا نعمل كوكيل أو ممثل لأي مقدم خدمة.
    </p>
</div>

<div class="legal-section">
    <h2>2. عدم ضمان جودة الخدمات</h2>
    <p>
        لا تضمن دلني جودة أو نتيجة أو سلامة أي خدمة يقدمها أي مقدم خدمة.
        اختيار مقدم الخدمة والتعامل معه يكون على مسؤولية المستخدم.
    </p>
</div>

<div class="legal-section">
    <h2>3. دقة المعلومات</h2>
    <p>
        نحاول مراجعة المعلومات المعروضة قدر الإمكان، لكن قد تحتوي بعض الملفات على بيانات غير مكتملة أو قديمة أو غير دقيقة.
        مقدم الخدمة مسؤول عن تحديث بياناته وصحة ما يعرضه.
    </p>
</div>

<div class="legal-section">
    <h2>4. التواصل والدفع خارج المنصة</h2>
    <p>
        أي تواصل أو اتفاق أو دفع يتم خارج دلني، مثل الهاتف أو واتساب، يكون بين المستخدم ومقدم الخدمة مباشرة.
        دلني لا تتحمل مسؤولية النزاعات أو الخسائر الناتجة عن هذه التعاملات.
    </p>
</div>

<div class="legal-section">
    <h2>5. التقييمات والآراء</h2>
    <p>
        التقييمات والتعليقات تعبّر عن آراء أصحابها فقط، ولا تعبر بالضرورة عن رأي دلني.
        قد نقوم بحذف أي تقييم مخالف أو مسيء أو مشكوك في صحته.
    </p>
</div>

<div class="legal-section">
    <h2>6. الروابط الخارجية</h2>
    <p>
        قد تحتوي المنصة على روابط خارجية مثل واتساب أو مواقع التواصل أو مواقع مقدمي الخدمات.
        نحن لا نتحكم في هذه المواقع ولا نتحمل مسؤولية محتواها أو سياساتها.
    </p>
</div>

<div class="legal-section">
    <h2>7. توفر المنصة</h2>
    <p>
        نسعى لتوفير المنصة بشكل مستمر، لكن لا نضمن أن تعمل دون انقطاع أو أخطاء أو مشاكل تقنية.
        قد نقوم بإيقاف أو تعديل أي جزء من المنصة عند الحاجة.
    </p>
</div>

<div class="legal-section">
    <h2>8. حدود المسؤولية</h2>
    <p>
        لا تتحمل دلني مسؤولية أي خسائر أو أضرار مباشرة أو غير مباشرة تنتج عن استخدام المنصة،
        أو الاعتماد على معلومات منشورة فيها، أو التعامل مع مقدمي الخدمات.
    </p>
</div>

<div class="legal-section">
    <h2>9. لا نقدم نصائح مهنية</h2>
    <p>
        المعلومات الموجودة في دلني لغرض التعريف والبحث فقط.
        لا تعتبر المنصة مصدرًا لنصائح قانونية أو مالية أو طبية أو هندسية أو أي نصائح مهنية متخصصة.
    </p>
</div>

<div class="legal-section">
    <h2>10. قبول الإخلاء</h2>
    <p>
        باستخدامك لمنصة دلني، فإنك تقر بأنك فهمت حدود دور المنصة وتوافق على هذا الإخلاء.
    </p>
</div>
@endsection

```

