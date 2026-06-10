# Legal Blades Export

**Generated:** 2026-06-10 13:13:22

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
    <title>@yield('title', config('app.name') . ' - ' . __('messages.public.legal'))</title>
    <meta name="description" content="@yield('meta_description', config('app.name'))">

    {{-- PWA / App Icons --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <meta name="theme-color" content="#F1620F">

    {{-- Cairo Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')

    <style>
        /* ── Design Tokens ── */
        :root {
            --p:       #F1620F;
            --p-hover: #D7530A;
            --p-glow:  rgba(241,98,15,.18);
            --navy:    #0B1A34;
            --navy2:   #13264A;
            --bg:      #FCFBFB;
            --surface: #FFFFFF;
            --muted:   #5D5959;
            --border:  #E7E7E7;
            --radius:  24px;
            --shadow:  0 16px 36px rgba(11,26,52,.08);
            --ease:    .2s cubic-bezier(.4,0,.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            background: var(--bg);
            color: var(--navy);
            font-family: 'Cairo', system-ui, sans-serif;
            text-align: start;
            -webkit-font-smoothing: antialiased;
            line-height: 1.75;
        }

        a { color: inherit; }
        img, svg { max-width: 100%; }

        .container {
            width: min(100% - 2rem, 1240px);
            margin-inline: auto;
        }

        /* ── Sticky Header ── */
        .delni-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(252,251,251,.9);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--border);
        }

        .delni-header__inner {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .delni-logo {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            text-decoration: none;
            font-size: 1.45rem;
            font-weight: 950;
            letter-spacing: -.04em;
            color: var(--navy);
        }

        .delni-logo__mark {
            width: 46px;
            height: 46px;
            border-radius: 15px;
            overflow: hidden;
            background: var(--navy);
            box-shadow: 0 8px 20px rgba(11,26,52,.15);
            flex-shrink: 0;
        }

        .delni-logo__mark img { width: 100%; height: 100%; object-fit: cover; }

        .delni-nav {
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .delni-nav a {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            padding: .55rem .9rem;
            border-radius: 999px;
            color: var(--muted);
            text-decoration: none;
            font-size: .92rem;
            font-weight: 850;
            transition: var(--ease);
        }

        .delni-nav a:hover, .delni-nav a.is-active {
            color: var(--p);
            background: var(--p-glow);
        }

        .delni-actions { display: flex; align-items: center; gap: .6rem; }

        .delni-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .7rem 1.1rem;
            border-radius: 14px;
            border: 1px solid transparent;
            font-family: inherit;
            font-size: .9rem;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition: var(--ease);
        }

        .delni-btn--primary {
            background: var(--p);
            color: #fff;
            box-shadow: 0 12px 24px rgba(241,98,15,.22);
        }
        .delni-btn--primary:hover { transform: translateY(-1px); box-shadow: 0 16px 32px rgba(241,98,15,.28); }

        .delni-btn--ghost {
            background: var(--surface);
            color: var(--navy);
            border-color: var(--border);
        }
        .delni-btn--ghost:hover { border-color: rgba(241,98,15,.28); color: var(--p); }

        /* ── Legal Hero ── */
        .legal-hero {
            background:
                radial-gradient(circle at 10% 50%, rgba(241,98,15,.15), transparent 38%),
                linear-gradient(135deg, var(--navy), var(--navy2));
            padding: clamp(3rem, 7vw, 5rem) 0 clamp(2.5rem, 6vw, 4rem);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        /* subtle grid decoration */
        .legal-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .legal-hero__inner {
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 2rem;
        }

        .legal-hero__kicker {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            margin-bottom: 1rem;
            padding: .4rem .85rem;
            border-radius: 999px;
            background: rgba(241,98,15,.16);
            border: 1px solid rgba(241,98,15,.28);
            color: #ffb079;
            font-size: .82rem;
            font-weight: 950;
        }

        .legal-hero h1 {
            font-size: clamp(2.25rem, 5vw, 3.75rem);
            font-weight: 950;
            line-height: 1.08;
            letter-spacing: -.055em;
            margin-bottom: .75rem;
        }

        .legal-hero h1 span { color: var(--p); }

        .legal-hero__sub {
            color: rgba(255,255,255,.72);
            font-size: 1rem;
            font-weight: 650;
            max-width: 520px;
            line-height: 1.85;
        }

        .legal-hero__badge {
            min-width: 160px;
            padding: 1.25rem 1.5rem;
            border-radius: 20px;
            background: rgba(255,255,255,.09);
            border: 1px solid rgba(255,255,255,.15);
            backdrop-filter: blur(8px);
            text-align: center;
            flex-shrink: 0;
        }

        .legal-hero__badge-icon {
            font-size: 2.25rem;
            line-height: 1;
            display: block;
            margin-bottom: .5rem;
        }

        .legal-hero__badge-label {
            display: block;
            color: rgba(255,255,255,.72);
            font-size: .8rem;
            font-weight: 850;
        }

        /* ── Side Nav + Content Layout ── */
        .legal-layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 1.75rem;
            padding: 2.5rem 0 5rem;
            align-items: start;
        }

        /* ── Sticky Sidebar ── */
        .legal-sidebar {
            position: sticky;
            top: 96px;
        }

        .legal-sidebar__card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .legal-sidebar__head {
            padding: 1.1rem 1.25rem;
            background: linear-gradient(135deg, var(--navy), var(--navy2));
            color: rgba(255,255,255,.8);
            font-size: .8rem;
            font-weight: 950;
            letter-spacing: .04em;
        }

        .legal-sidebar__links {
            padding: .5rem 0;
        }

        .legal-sidebar__links a {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .75rem 1.25rem;
            font-size: .92rem;
            font-weight: 750;
            color: var(--muted);
            text-decoration: none;
            transition: var(--ease);
            border-inline-start: 3px solid transparent;
        }

        .legal-sidebar__links a:hover,
        .legal-sidebar__links a.is-active {
            color: var(--p);
            background: var(--p-glow);
            border-inline-start-color: var(--p);
        }

        .legal-sidebar__links a .lnav-icon {
            font-size: 1.1rem;
        }

        /* Date pill at bottom of sidebar */
        .legal-sidebar__date {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: .8rem;
            font-weight: 700;
        }

        /* ── Main Content Area ── */
        .legal-content {}

        /* Page card */
        .legal-page {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* Accent bar at top */
        .legal-page__header {
            padding: 2rem 2.5rem 1.75rem;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg,
                rgba(11,26,52,.025),
                rgba(11,26,52,.01));
        }

        .legal-page__icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--p), #ff8c47);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 10px 22px rgba(241,98,15,.3);
        }

        .legal-page__header h1 {
            font-size: clamp(1.75rem, 3vw, 2.25rem);
            font-weight: 950;
            letter-spacing: -.045em;
            color: var(--navy);
            margin-bottom: .35rem;
        }

        .legal-page__header .last-updated {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .35rem .8rem;
            border-radius: 999px;
            background: rgba(11,26,52,.05);
            color: var(--muted);
            font-size: .82rem;
            font-weight: 750;
        }

        /* Sections body */
        .legal-page__body {
            padding: 2rem 2.5rem 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .legal-section {
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border);
        }
        .legal-section:last-child { border-bottom: none; padding-bottom: 0; }
        .legal-section:first-child { padding-top: 0; }

        .legal-section__num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--p-glow);
            color: var(--p);
            font-size: .78rem;
            font-weight: 950;
            margin-bottom: .85rem;
            flex-shrink: 0;
        }

        .legal-section h2 {
            font-size: 1.1rem;
            font-weight: 950;
            color: var(--navy);
            letter-spacing: -.025em;
            margin-bottom: .75rem;
        }

        .legal-section p {
            font-size: .975rem;
            color: var(--muted);
            line-height: 1.9;
            margin-bottom: .75rem;
            font-weight: 650;
        }
        .legal-section p:last-child { margin-bottom: 0; }

        .legal-section ul, .legal-section ol {
            padding-inline-start: 1.5rem;
            margin: .5rem 0 0;
        }

        .legal-section li {
            font-size: .95rem;
            color: var(--muted);
            margin-bottom: .55rem;
            font-weight: 650;
            line-height: 1.8;
        }

        .legal-section strong {
            color: var(--navy);
            font-weight: 900;
        }

        .legal-section a {
            color: var(--p);
            font-weight: 750;
            text-decoration: none;
        }
        .legal-section a:hover { color: var(--p-hover); text-decoration: underline; }

        /* ── Footer ── */
        .delni-footer {
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 2rem 0;
            color: var(--muted);
            font-size: .9rem;
            font-weight: 600;
        }

        .delni-footer__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .delni-footer a { color: var(--muted); text-decoration: none; font-weight: 800; }
        .delni-footer a:hover { color: var(--p); }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .legal-layout {
                grid-template-columns: 1fr;
            }
            .legal-sidebar {
                position: static;
                order: -1;
            }
            .legal-sidebar__card {
                display: flex;
                flex-wrap: wrap;
            }
            .legal-sidebar__head {
                width: 100%;
            }
            .legal-sidebar__links {
                display: flex;
                flex-wrap: wrap;
                padding: .5rem;
                gap: .25rem;
                flex: 1;
            }
            .legal-sidebar__links a {
                border-inline-start: none;
                border-radius: 999px;
                padding: .5rem .9rem;
                font-size: .85rem;
                border: 1px solid transparent;
            }
            .legal-sidebar__links a:hover,
            .legal-sidebar__links a.is-active {
                border-color: rgba(241,98,15,.2);
            }
            .legal-sidebar__date { display: none; }
            .legal-hero__badge { display: none; }
            .legal-hero__inner { flex-direction: column; align-items: flex-start; gap: 1rem; }
        }

        @media (max-width: 760px) {
            .container { width: min(100% - 1.25rem, 1240px); }
            .delni-header__inner { min-height: 68px; gap: .4rem; }
            .delni-logo { font-size: 1.2rem; }
            .delni-logo__mark { width: 40px; height: 40px; border-radius: 13px; }
            .delni-nav { gap: .2rem; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .delni-nav a { min-height: 40px; padding: .45rem .65rem; font-size: .82rem; white-space: nowrap; flex-shrink: 0; }
            .delni-btn { min-height: 40px; padding: .55rem .75rem; font-size: .82rem; }
            .legal-page__header, .legal-page__body { padding-inline: 1.25rem; }
            .legal-page__header { padding-top: 1.5rem; padding-bottom: 1.25rem; }
            .legal-page__body { padding-bottom: 1.75rem; }
            .delni-footer__inner { flex-direction: column; text-align: center; gap: .75rem; }
        }
    </style>
</head>
<body>

{{-- ── Sticky Header ── --}}
<header class="delni-header">
    <div class="container">
        <div class="delni-header__inner">
            <a href="{{ route('home') }}" class="delni-logo">
                <span class="delni-logo__mark">
                    <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('app.name') }}">
                </span>
                <span>دلني</span>
            </a>

            <nav class="delni-nav" aria-label="Main navigation">
                <a href="{{ route('home') }}"
                   class="{{ request()->routeIs('home') ? 'is-active' : '' }}">الرئيسية</a>
                <a href="{{ route('public.top-rated') }}"
                   class="{{ request()->routeIs('public.top-rated') ? 'is-active' : '' }}">الأعلى تقييماً</a>
                <a href="{{ route('public.search') }}"
                   class="{{ request()->routeIs('public.search') ? 'is-active' : '' }}">بحث</a>
            </nav>

            <div class="delni-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="delni-btn delni-btn--ghost">لوحتي</a>
                @else
                    <a href="{{ route('login') }}" class="delni-btn delni-btn--primary">تسجيل</a>
                @endauth
            </div>
        </div>
    </div>
</header>

{{-- ── Legal Hero ── --}}
<section class="legal-hero">
    <div class="container">
        <div class="legal-hero__inner">
            <div>
                <div class="legal-hero__kicker">
                    ⚖️ &nbsp;المعلومات القانونية
                </div>
                <h1>@yield('hero_title', 'الوثائق <span>القانونية</span>')</h1>
                <p class="legal-hero__sub">@yield('hero_subtitle', 'نلتزم بالشفافية التامة. اطلع على حقوقك والتزاماتك عند استخدام منصة دلني.')</p>
            </div>
            <div class="legal-hero__badge">
                <span class="legal-hero__badge-icon">@yield('hero_icon', '📄')</span>
                <span class="legal-hero__badge-label">@yield('hero_badge_label', 'وثيقة قانونية')</span>
            </div>
        </div>
    </div>
</section>

{{-- ── Main Layout ── --}}
<div class="container">
    <div class="legal-layout">

        {{-- Sidebar Navigation --}}
        <aside class="legal-sidebar">
            <div class="legal-sidebar__card">
                <div class="legal-sidebar__head">الوثائق القانونية</div>
                <nav class="legal-sidebar__links">
                    <a href="{{ route('privacy') }}"
                       class="{{ request()->routeIs('privacy') ? 'is-active' : '' }}">
                        <span class="lnav-icon">🔒</span> سياسة الخصوصية
                    </a>
                    <a href="{{ route('terms') }}"
                       class="{{ request()->routeIs('terms') ? 'is-active' : '' }}">
                        <span class="lnav-icon">📋</span> شروط الاستخدام
                    </a>
                    <a href="{{ route('disclaimer') }}"
                       class="{{ request()->routeIs('disclaimer') ? 'is-active' : '' }}">
                        <span class="lnav-icon">⚠️</span> إخلاء المسؤولية
                    </a>
                </nav>
                <div class="legal-sidebar__date">
                    📅 &nbsp;آخر تحديث: {{ now()->format('Y-m-d') }}
                </div>
            </div>
        </aside>

        {{-- Content --}}
        <main class="legal-content">
            @yield('content')
        </main>

    </div>
</div>

{{-- ── Footer ── --}}
<footer class="delni-footer">
    <div class="container">
        <div class="delni-footer__inner">
            <span>© {{ date('Y') }} دلني. جميع الحقوق محفوظة.</span>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;">
                <a href="{{ route('privacy') }}">الخصوصية</a>
                <span style="opacity:.3">·</span>
                <a href="{{ route('terms') }}">الشروط</a>
                <span style="opacity:.3">·</span>
                <a href="{{ route('disclaimer') }}">إخلاء المسؤولية</a>
            </div>
        </div>
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
@section('meta_description', 'سياسة الخصوصية لمنصة دلني - كيفية جمع واستخدام وحماية بياناتك الشخصية.')

@section('hero_title')سياسة <span>الخصوصية</span>@endsection
@section('hero_subtitle', 'نحن نحترم خصوصيتك ونلتزم بحماية بياناتك الشخصية. تعرّف على كيفية جمعنا لمعلوماتك واستخدامها.')
@section('hero_icon', '🔒')
@section('hero_badge_label', 'سياسة الخصوصية')

@section('content')
<div class="legal-page">

    <div class="legal-page__header">
        <div class="legal-page__icon">🔒</div>
        <h1>سياسة الخصوصية</h1>
        <span class="last-updated">📅 &nbsp;آخر تحديث: {{ now()->format('Y-m-d') }}</span>
    </div>

    <div class="legal-page__body">

        <div class="legal-section">
            <span class="legal-section__num">1</span>
            <h2>المقدمة</h2>
            <p>
                يلتزم تطبيق دلني ("نحن"، "الخدمة") بحماية خصوصيتك وسرية بياناتك الشخصية.
                تشرح هذه السياسة كيفية جمعنا واستخدامنا لمعلوماتك بكل شفافية.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">2</span>
            <h2>البيانات التي نجمعها</h2>
            <ul>
                <li><strong>بيانات التسجيل:</strong> الاسم، البريد الإلكتروني، رقم الهاتف، كلمة المرور</li>
                <li><strong>بيانات الملف الشخصي:</strong> صورة الملف الشخصي، السيرة الذاتية، الفئة، المدينة</li>
                <li><strong>بيانات المراجعات:</strong> التقييمات والتعليقات المكتوبة من قبل المستخدمين</li>
                <li><strong>بيانات الاستخدام:</strong> سجل النشاط والبحث على المنصة</li>
            </ul>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">3</span>
            <h2>كيفية استخدام بياناتك</h2>
            <p>نستخدم البيانات الشخصية الخاصة بك من أجل:</p>
            <ul>
                <li>تقديم الخدمات والمنتجات المطلوبة</li>
                <li>تحسين تجربتك على المنصة</li>
                <li>الاتصال بك بشأن الحسابات والخدمات</li>
                <li>مكافحة الاحتيال والنشاط غير القانوني</li>
                <li>الامتثال للقوانين واللوائح المعمول بها</li>
            </ul>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">4</span>
            <h2>حماية البيانات</h2>
            <p>
                نطبق إجراءات أمان صارمة لحماية بياناتك من الوصول غير المصرح به والتعديل والحذف والكشف.
                البيانات مشفرة أثناء النقل وفي حالة السكون.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">5</span>
            <h2>مشاركة البيانات</h2>
            <p>
                لن نشارك بياناتك الشخصية مع أطراف ثالثة دون موافقتك، باستثناء:
            </p>
            <ul>
                <li>عند الامتثال للقوانين القانونية والنظامية</li>
                <li>مع مقدمي الخدمات الموثوقين الذين يساعدوننا في تشغيل المنصة</li>
                <li>عند نقل الأعمال التجارية (الاندماج أو الاستحواذ)</li>
            </ul>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">6</span>
            <h2>حقوقك</h2>
            <p>لديك الحق في:</p>
            <ul>
                <li>الوصول إلى بياناتك الشخصية</li>
                <li>تصحيح المعلومات غير الدقيقة</li>
                <li>حذف بياناتك (الحق في أن تنسى)</li>
                <li>الاعتراض على معالجة بياناتك</li>
                <li>نقل بياناتك إلى خدمة أخرى</li>
            </ul>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">7</span>
            <h2>ملفات تعريف الارتباط</h2>
            <p>
                نستخدم ملفات تعريف الارتباط لتحسين تجربتك. يمكنك تعطيل ملفات تعريف الارتباط من خلال إعدادات متصفحك،
                لكن قد يؤثر ذلك على وظائف المنصة.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">8</span>
            <h2>التغييرات على هذه السياسة</h2>
            <p>
                قد نحدث هذه السياسة من وقت لآخر. سيتم إخطارك بأي تغييرات جوهرية عبر البريد الإلكتروني أو على المنصة.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">9</span>
            <h2>اتصل بنا</h2>
            <p>
                إذا كان لديك أسئلة حول سياسة الخصوصية هذه، يرجى الاتصال بنا عبر البريد الإلكتروني أو نموذج الاتصال.
            </p>
        </div>

    </div>
</div>
@endsection

```

## terms.blade.php

```blade
@extends('public.legal_layout')

@section('title', 'شروط الاستخدام - ' . config('app.name'))
@section('meta_description', 'شروط الاستخدام لمنصة دلني - اقرأ الشروط والأحكام الخاصة باستخدام المنصة.')

@section('hero_title')شروط <span>الاستخدام</span>@endsection
@section('hero_subtitle', 'باستخدامك لمنصة دلني فأنت توافق على هذه الشروط. اقرأها بعناية لتفهم حقوقك والتزاماتك.')
@section('hero_icon', '📋')
@section('hero_badge_label', 'شروط الاستخدام')

@section('content')
<div class="legal-page">

    <div class="legal-page__header">
        <div class="legal-page__icon">📋</div>
        <h1>شروط الاستخدام</h1>
        <span class="last-updated">📅 &nbsp;آخر تحديث: {{ now()->format('Y-m-d') }}</span>
    </div>

    <div class="legal-page__body">

        <div class="legal-section">
            <span class="legal-section__num">1</span>
            <h2>قبول الشروط</h2>
            <p>
                بالوصول واستخدام منصة دلني، فإنك توافق على الالتزام بهذه الشروط والأحكام.
                إذا كنت لا توافق على أي جزء من هذه الشروط، فرجاء عدم استخدام المنصة.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">2</span>
            <h2>حساب المستخدم</h2>
            <p>
                عند إنشاء حساب، فأنت تتعهد بتقديم معلومات دقيقة وتحديثها بانتظام.
                أنت مسؤول عن الحفاظ على سرية كلمة المرور الخاصة بك.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">3</span>
            <h2>السلوك المرفوض</h2>
            <p>لا يجوز لك استخدام المنصة من أجل:</p>
            <ul>
                <li>نشر محتوى مسيء أو إباحي أو غير قانوني</li>
                <li>الاحتيال أو الخداع أو الابتزاز</li>
                <li>انتهاك حقوق الملكية الفكرية</li>
                <li>التحرش أو المضايقة أو التمييز</li>
                <li>محاولة الوصول غير المصرح به إلى الأنظمة</li>
                <li>نشر البرامج الضارة أو الفيروسات</li>
            </ul>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">4</span>
            <h2>محتوى المستخدم</h2>
            <p>
                أنت تحتفظ بجميع حقوق الملكية على محتواك. بنشر محتوى على المنصة، تمنحنا ترخيصًا لاستخدامه وتعديله وتوزيعه.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">5</span>
            <h2>التزامات مقدمي الخدمات</h2>
            <ul>
                <li>تقديم خدمات عالية الجودة</li>
                <li>الامتثال للقوانين والأنظمة السارية</li>
                <li>احترام سرية العملاء</li>
                <li>عدم استخدام بيانات العملاء بشكل غير شرعي</li>
            </ul>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">6</span>
            <h2>التزامات المستخدمين</h2>
            <ul>
                <li>الدفع في الوقت المناسب</li>
                <li>احترام حقوق مقدمي الخدمات</li>
                <li>عدم استخدام الخدمات بطريقة غير قانونية</li>
                <li>الإبلاغ عن أي مشاكل أو انتهاكات</li>
            </ul>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">7</span>
            <h2>الرسوم والدفع</h2>
            <p>
                تحتفظ المنصة بحق تغيير الرسوم أو إضافة رسوم جديدة بإشعار مسبق.
                قد يتم إيقاف الخدمات عند عدم الدفع في المواعيد المحددة.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">8</span>
            <h2>المسؤوليات الضارة</h2>
            <p>
                لن تكون المنصة مسؤولة عن أي أضرار مباشرة أو غير مباشرة ناشئة عن استخدام الخدمة أو عدم القدرة على استخدامها.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">9</span>
            <h2>إنهاء الحساب</h2>
            <p>
                يمكننا إنهاء أو تعليق حسابك دون سابق إنذار إذا انتهكت هذه الشروط أو تصرفت بطريقة غير قانونية.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">10</span>
            <h2>التغييرات على الشروط</h2>
            <p>
                نحتفظ بحق تعديل هذه الشروط في أي وقت. سيتم إخطارك بأي تغييرات جوهرية.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">11</span>
            <h2>القانون الحاكم</h2>
            <p>
                تخضع هذه الشروط لقوانين دولة ليبيا.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">12</span>
            <h2>اتصل بنا</h2>
            <p>
                لأية استفسارات حول هذه الشروط، يرجى الاتصال بفريق الدعم.
            </p>
        </div>

    </div>
</div>
@endsection

```

## disclaimer.blade.php

```blade
@extends('public.legal_layout')

@section('title', 'إخلاء المسؤولية - ' . config('app.name'))
@section('meta_description', 'إخلاء مسؤولية منصة دلني - تعرّف على حدود مسؤولية المنصة وحقوقك كمستخدم.')

@section('hero_title')إخلاء <span>المسؤولية</span>@endsection
@section('hero_subtitle', 'منصة دلني وسيطة بين العملاء ومقدمي الخدمات. تعرّف على حدود مسؤوليتنا وضماناتنا.')
@section('hero_icon', '⚠️')
@section('hero_badge_label', 'إخلاء المسؤولية')

@section('content')
<div class="legal-page">

    <div class="legal-page__header">
        <div class="legal-page__icon">⚠️</div>
        <h1>إخلاء المسؤولية</h1>
        <span class="last-updated">📅 &nbsp;آخر تحديث: {{ now()->format('Y-m-d') }}</span>
    </div>

    <div class="legal-page__body">

        <div class="legal-section">
            <span class="legal-section__num">1</span>
            <h2>عدم المسؤولية</h2>
            <p>
                منصة دلني هي منصة وسيطة تربط بين العملاء ومقدمي الخدمات.
                نحن لا نقدم الخدمات بشكل مباشر، بل نوفر فقط منصة للاتصال والتعاقد.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">2</span>
            <h2>المسؤولية عن الخدمات</h2>
            <p>
                مقدمو الخدمات مسؤولون بالكامل عن جودة وسلامة الخدمات المقدمة.
                المنصة لا تضمن جودة الخدمات أو تحقق من كفاءة مقدمي الخدمات.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">3</span>
            <h2>المعلومات والمحتوى</h2>
            <p>
                البيانات والمعلومات المنشورة على المنصة مقدمة "كما هي" دون ضمانات من أي نوع.
                لا نضمن دقة أو اكتمال أو صحة أي معلومات على المنصة.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">4</span>
            <h2>تقييمات المستخدمين</h2>
            <p>
                التقييمات والآراء المنشورة من قبل المستخدمين لا تعكس بالضرورة آراء المنصة.
                لا نتحمل المسؤولية عن دقة أو صحة التقييمات المكتوبة.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">5</span>
            <h2>عدم الضمان</h2>
            <p>
                المنصة توفر خدماتها "كما هي" و"كما هي متاحة" دون أي ضمانات صريحة أو ضمنية.
                لا نضمن:
            </p>
            <ul>
                <li>عدم انقطاع الخدمة</li>
                <li>خالية من الأخطاء</li>
                <li>خالية من الفيروسات</li>
                <li>نتائج معينة من استخدام الخدمة</li>
            </ul>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">6</span>
            <h2>تحديد المسؤولية</h2>
            <p>في أي حال من الأحوال، لن تكون المنصة مسؤولة عن:</p>
            <ul>
                <li>الأضرار غير المباشرة أو التبعية</li>
                <li>خسارة البيانات أو الأرباح</li>
                <li>انقطاع العمل</li>
                <li>أضرار السمعة</li>
            </ul>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">7</span>
            <h2>روابط الطرف الثالث</h2>
            <p>
                قد تحتوي المنصة على روابط لمواقع طرف ثالث. نحن لا نتحمل مسؤولية محتوى هذه المواقع أو سياساتها.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">8</span>
            <h2>التعديلات على الخدمة</h2>
            <p>
                نحتفظ بحق تعديل أو إيقاف أي جزء من الخدمة في أي وقت دون إشعار مسبق.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">9</span>
            <h2>المسؤولية القانونية</h2>
            <p>
                أنت وحدك المسؤول عن امتثالك للقوانين والأنظمة المعمول بها.
                نحن لا نقدم نصائح قانونية أو مالية.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">10</span>
            <h2>عدم التنازل عن الحقوق</h2>
            <p>
                عدم ممارسة المنصة لأي حق بموجب هذا الإخلاء لا يشكل تنازلاً عن هذا الحق.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">11</span>
            <h2>الفصل</h2>
            <p>
                إذا تم اعتبار أي جزء من هذا الإخلاء غير صالح أو غير قابل للتنفيذ،
                فسيستمر الجزء المتبقي في الصلاحية والنفاذ.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">12</span>
            <h2>تاريخ السريان</h2>
            <p>
                هذا الإخلاء ساري من تاريخ آخر تحديث أعلاه ويستمر حتى إشعار آخر.
            </p>
        </div>

        <div class="legal-section">
            <span class="legal-section__num">13</span>
            <h2>الاتصال</h2>
            <p>
                للاستفسارات عن هذا الإخلاء، يرجى الاتصال بفريق الدعم لدينا.
            </p>
        </div>

    </div>
</div>
@endsection

```

