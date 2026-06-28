<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>دلني</title>
    <style>
        :root {
            --bg: #0F172A;
            --border: rgba(255, 255, 255, 0.08);
            --text-primary: #FFFFFF;
            --text-secondary: #94A3B8;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .welcome-container {
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .brand-img {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            object-fit: cover;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2);
        }

        h1 {
            margin: 0 0 12px;
            font-size: 2.5rem;
            font-weight: 800;
        }

        p {
            margin: 0 0 32px;
            color: var(--text-secondary);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .btn-store {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #1E293B;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            text-align: right;
            min-width: 165px;
            transition: transform 0.2s, background 0.2s;
        }

        .btn-store:hover {
            background: #2D3748;
            transform: translateY(-2px);
        }

        .store-icon {
            flex-shrink: 0;
            color: #fff;
        }

        .btn-text {
            display: flex;
            flex-direction: column;
        }

        .top-t {
            font-size: 0.7rem;
            color: var(--text-secondary);
        }

        .main-t {
            font-size: 0.95rem;
            font-weight: 700;
        }
    </style>
</head>
<body>

@php
    $iosUrl = $contactInfo->ios_app_url ?: '#';
    $androidUrl = $contactInfo->android_app_url ?: '#';
@endphp

<div class="welcome-container">
    <img class="brand-img" src="{{ asset('images/photo_2026-06-22_23-21-55.jpg') }}" alt="دلني">

    <h1>مرحباً بك في دلني</h1>
    <p>تطبيقك للوصول إلى مقدمي الخدمات المحليين بسهولة.</p>

    <div class="actions">
        <a class="btn-store" href="{{ $androidUrl }}">
            <svg class="store-icon" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                <path d="M17.523 15.3l-2.046-2.046 2.046-2.046a1 1 0 0 0 0-1.414l-4.586-4.586a1 1 0 0 0-1.414 0L9.477 7.254l2.046 2.046-2.046 2.046a1 1 0 0 0 0 1.414l4.586 4.586a1 1 0 0 0 1.414 0l2.046-2.046zM3.609 2.455l11.459 11.459-3.23 3.23L1.135 6.438a1.5 1.5 0 0 1 .118-2.227l2.356-1.756zM1.135 17.562l10.703-10.703 3.23 3.23L3.609 21.545l-2.356-1.756a1.5 1.5 0 0 1-.118-2.227zM22.071 10.515l-3.078-1.756-1.922 1.922 1.922 1.922 3.078-1.756a1.67 1.67 0 0 0 0-2.332z"/>
            </svg>
            <div class="btn-text">
                <span class="top-t">تحميل للأندرويد</span>
                <span class="main-t">Google Play</span>
            </div>
        </a>

        <a class="btn-store" href="{{ $iosUrl }}">
            <svg class="store-icon" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-1 .04-2.21.67-2.93 1.49-.62.69-1.16 1.84-1.01 2.96 1.12.09 2.27-.58 2.95-1.39z"/>
            </svg>
            <div class="btn-text">
                <span class="top-t">تحميل للآيفون</span>
                <span class="main-t">App Store</span>
            </div>
        </a>
    </div>
</div>

</body>
</html>
