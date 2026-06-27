<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>دلني</title>
    <style>
        :root {
            --bg: #F6F8FF;
            --surface: #FFFFFF;
            --surface-alt: #F1F5F9;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --border: #E8EEF8;
            --border-strong: #CBD5E1;
            --primary: #1E40AF;
            --primary-soft: rgba(30, 64, 175, 0.10);
            --gold: #E1AD01;
            --gold-soft: #FFFBEB;
            --gold-border: #FDE68A;
            --gold-text: #92400E;
            --success: #10B981;
            --shadow: rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(30, 64, 175, 0.12), transparent 28%),
                radial-gradient(circle at left 20%, rgba(225, 173, 1, 0.10), transparent 22%),
                var(--bg);
            color: var(--text-primary);
        }
        a { text-decoration: none; }
        .shell {
            max-width: 1120px;
            margin: 0 auto;
            padding: 28px 20px 48px;
        }
        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 32px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 900;
            font-size: 1.25rem;
            color: var(--text-primary);
        }
        .brand img {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            object-fit: cover;
            border: 1px solid var(--border);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.10);
        }
        .nav-link {
            color: var(--primary);
            font-weight: 700;
        }
        .hero {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 24px;
            align-items: stretch;
        }
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: 0 22px 60px var(--shadow);
        }
        .hero-copy {
            padding: 34px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: var(--gold-soft);
            border: 1px solid var(--gold-border);
            color: var(--gold-text);
            font-size: 0.92rem;
            font-weight: 800;
        }
        h1 {
            margin: 18px 0 14px;
            font-size: clamp(2.1rem, 5vw, 4rem);
            line-height: 1.05;
            letter-spacing: -0.03em;
        }
        .lead {
            margin: 0 0 24px;
            color: var(--text-secondary);
            font-size: 1.05rem;
            line-height: 1.85;
            max-width: 58ch;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            padding: 0 20px;
            border-radius: 16px;
            font-weight: 800;
            transition: transform .15s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            background: var(--primary);
            color: #fff;
        }
        .btn-secondary {
            background: var(--surface-alt);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }
        .micro {
            color: var(--text-muted);
            font-size: 0.92rem;
        }
        .hero-side {
            padding: 22px;
            display: grid;
            gap: 14px;
            background:
                linear-gradient(180deg, rgba(30, 64, 175, 0.04), transparent 38%),
                var(--surface);
        }
        .mini-card {
            padding: 20px;
            border-radius: 22px;
            border: 1px solid var(--border);
            background: var(--surface);
        }
        .mini-card.primary {
            background: var(--primary-soft);
            border-color: rgba(30, 64, 175, 0.18);
        }
        .eyebrow {
            color: var(--text-muted);
            font-size: 0.86rem;
            margin-bottom: 10px;
        }
        .mini-card h2, .mini-card h3 {
            margin: 0 0 8px;
            font-size: 1.08rem;
        }
        .mini-card p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.7;
            font-size: 0.95rem;
        }
        .store-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 24px;
        }
        .store-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid var(--border);
            background: var(--surface);
        }
        .store-card strong {
            display: block;
            font-size: 1rem;
            margin-bottom: 8px;
        }
        .store-card span {
            display: block;
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 14px;
            font-size: 0.94rem;
        }
        .store-card .status {
            display: inline-flex;
            padding: 7px 12px;
            border-radius: 999px;
            background: var(--surface-alt);
            color: var(--text-secondary);
            border: 1px solid var(--border);
            font-size: 0.84rem;
            font-weight: 700;
        }
        .store-card .status.live {
            background: rgba(16, 185, 129, 0.12);
            color: var(--success);
            border-color: rgba(16, 185, 129, 0.24);
        }
        .foot-note {
            margin-top: 26px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        @media (max-width: 900px) {
            .hero,
            .store-grid {
                grid-template-columns: 1fr;
            }
            .hero-copy,
            .hero-side {
                padding: 24px;
            }
            .nav {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
@php
    $badge = $contactInfo->welcome_badge ?: 'تطبيق دلني قريباً';
    $title = $contactInfo->welcome_title ?: 'دلني يقرّبك من الخدمة المناسبة بسرعة ووضوح.';
    $subtitle = $contactInfo->welcome_subtitle ?: 'دلني هو تطبيق يساعدك في اكتشاف مقدمي الخدمات المحليين، مقارنة ملفاتهم، والوصول إليهم بسهولة من مكان واحد.';
    $iosUrl = $contactInfo->ios_app_url;
    $androidUrl = $contactInfo->android_app_url;
@endphp

<div class="shell">
    <div class="nav">
        <div class="brand">
            <img src="{{ asset('images/photo_2026-06-22_23-21-55.jpg') }}" alt="دلني">
            <span>دلني</span>
        </div>

        <a class="nav-link" href="{{ auth()->check() && auth()->user()->hasRole('provider') ? url('/provider/dashboard') : url('/provider/login') }}">
            {{ auth()->check() && auth()->user()->hasRole('provider') ? 'دخول اللوحة' : 'دخول مقدّم الخدمة' }}
        </a>
    </div>

    <section class="hero">
        <div class="panel hero-copy">
            <div class="badge">{{ $badge }}</div>
            <h1>{{ $title }}</h1>
            <p class="lead">{{ $subtitle }}</p>

            <div class="actions">
                <a class="btn btn-primary" href="{{ $androidUrl ?: '#' }}">
                    {{ $androidUrl ? 'Google Play' : 'رابط Google Play لاحقاً' }}
                </a>
                <a class="btn btn-secondary" href="{{ $iosUrl ?: '#' }}">
                    {{ $iosUrl ? 'App Store' : 'رابط App Store لاحقاً' }}
                </a>
            </div>

            <div class="micro">صفحة تعريفية بسيطة حالياً إلى أن تكون روابط المتاجر جاهزة.</div>
        </div>

        <div class="panel hero-side">
            <div class="mini-card primary">
                <div class="eyebrow">للعملاء</div>
                <h2>ابحث، قارن، وتواصل</h2>
                <p>التطبيق مصمم ليسهّل الوصول إلى مقدمي الخدمات عبر ملفات واضحة وتفاصيل مباشرة.</p>
            </div>

            <div class="mini-card">
                <div class="eyebrow">لمقدمي الخدمة</div>
                <h3>لوحة تحكم جاهزة</h3>
                <p>يمكنك بالفعل إدارة ملفك، خدماتك، التقييمات، وروابط التواصل من لوحة دلني.</p>
            </div>
        </div>
    </section>

    <section class="store-grid">
        <div class="store-card">
            <strong>نسخة iPhone</strong>
            <span>أضف رابط المتجر لاحقاً من لوحة التحكم عندما تكون النسخة جاهزة للنشر.</span>
            @if ($iosUrl)
                <a class="status live" href="{{ $iosUrl }}">متاح الآن</a>
            @else
                <span class="status">قريباً</span>
            @endif
        </div>

        <div class="store-card">
            <strong>نسخة Android</strong>
            <span>أضف رابط Google Play لاحقاً من لوحة التحكم بدون الحاجة لتعديل الصفحة يدوياً.</span>
            @if ($androidUrl)
                <a class="status live" href="{{ $androidUrl }}">متاح الآن</a>
            @else
                <span class="status">قريباً</span>
            @endif
        </div>
    </section>

    <div class="foot-note">دلني يجهّز لك نقطة دخول بسيطة وواضحة إلى أن يكتمل إطلاق التطبيق.</div>
</div>
</body>
</html>
