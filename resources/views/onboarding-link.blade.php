<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رابط الإعداد والتفعيل</title>
    {{-- High-quality typography addition --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <style>
        /* Design Tokens & Variables */
        :root {
            --brand-primary: #F1620F;
            --brand-primary-hover: #D7530A;
            --brand-dark: #0B1A34;
            --brand-dark-light: #14284D;
            --bg-surface: #FFFFFF;
            --bg-subtle: #F8FAFC;
            --text-primary: #0B1A34;
            --text-secondary: #475569;
            --border-color: #E2E8F0;
            --transition-smooth: all 0.2s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand-dark-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .container {
            background: var(--bg-surface);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            max-width: 580px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        h1 {
            color: var(--brand-dark);
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
            font-weight: 800;
        }

        .subtitle {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Clean Link Display Box */
        .link-box {
            background: var(--bg-subtle);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: var(--text-primary);
            line-height: 1.6;
            direction: ltr; /* Keeps URL slashes and tokens properly ordered */
            text-align: left;
        }

        /* Action Controls Layout Matrix */
        .actions-wrapper {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin: 1.5rem 0 2rem;
        }

        .btn {
            width: 100%;
            height: 50px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            border: none;
            transition: var(--transition-smooth);
        }

        .btn-primary {
            background-color: var(--brand-primary);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(241, 98, 15, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--brand-primary-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: var(--bg-subtle);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background-color: #EDF2F7;
            border-color: #CBD5E1;
        }

        /* Interactive Success Status Layer */
        .success-banner {
            display: none;
            background: #DEF7EC;
            color: #03543F;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-top: -0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            font-weight: 700;
            border: 1px solid #BCF0DA;
            animation: fadeIn 0.2s ease-out;
        }

        /* RTL-Correct Information Alert Box */
        .info-alert {
            background: rgba(241, 98, 15, 0.05);
            border-right: 4px solid var(--brand-primary);
            border-left: none; /* Corrects standard default left-border frameworks */
            padding: 1rem 1.25rem;
            text-align: right;
            border-radius: 4px 12px 12px 4px;
            color: #A73F05;
            font-size: 0.85rem;
            font-weight: 500;
            line-height: 1.6;
        }

        .footer-note {
            margin-top: 2rem;
            color: var(--text-light-muted);
            font-size: 0.8rem;
            font-weight: 500;
            line-height: 1.5;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .container {
                padding: 2rem 1.25rem;
            }
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>🔐 رابط الإعداد والتفعيل</h1>
        <p class="subtitle">قم بالضغط مباشرة على زر التفعيل المرفق بالأسفل، أو يمكنك نسخ الرابط المباشر واستخدامه في المتصفح الخاص بك.</p>

        {{-- Interactive URL text terminal node --}}
        <div class="link-box" id="linkBox">{{ $onboardingUrl }}</div>

        <div class="success-banner" id="successBanner">✓ تم نسخ رابط التفعيل بنجاح!</div>

        <div class="actions-wrapper">
            <a href="{{ $onboardingUrl }}" class="btn btn-primary">
                <span>إكمال عملية الإعداد والبدء</span>
                <span>←</span>
            </a>

            <button class="btn btn-secondary" onclick="copyToClipboard()">
                <span>📋 نسخ الرابط المباشر</span>
            </button>
        </div>

        <div class="info-alert">
            ⏰ <strong>تنبيه هام:</strong> هذا الرابط مخصص للاستخدام مرة واحدة وصالح لفترة زمنية محدودة فقط. يرجى إتمام عملية الإعداد قبل انتهاء صلاحية الجلسة.
        </div>

        <p class="footer-note">
            إذا لم يعمل زر الانتقال المباشر، يرجى نسخ عنوان الرابط ولصقه يدوياً في شريط العنوان أعلى متصفح الويب الخاص بك.
        </p>
    </div>

    <script>
        function copyToClipboard() {
            const text = document.getElementById('linkBox').innerText.trim();
            navigator.clipboard.writeText(text).then(() => {
                const banner = document.getElementById('successBanner');
                banner.style.display = 'block';
                setTimeout(() => {
                    banner.style.display = 'none';
                }, 3500);
            }).catch(() => {
                alert('عذراً، فشل النسخ التلقائي. يرجى تظليل الرابط ونسخه يدوياً.');
            });
        }
    </script>
</body>
</html>
