<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>رابط الإعداد والتفعيل</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 1rem;
            background: #FCFBFB;
            color: #0B1A34;
            font-family: Cairo, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.8;
        }
        .setup-card {
            width: min(100%, 520px);
            padding: clamp(1.25rem, 4vw, 1.75rem);
            border: 1px solid #E7E7E7;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 16px 36px rgba(11, 26, 52, .08);
        }
        .setup-card h1 {
            margin: 0;
            font-size: 1.25rem;
            line-height: 1.4;
            font-weight: 950;
        }
        .setup-card p {
            margin: .35rem 0 1rem;
            color: #5D5959;
            font-size: .92rem;
            line-height: 1.8;
            font-weight: 650;
        }
        .setup-link {
            direction: ltr;
            text-align: left;
            overflow-wrap: anywhere;
            padding: .85rem;
            border: 1px solid #E7E7E7;
            border-radius: 14px;
            background: #F8FAFC;
            color: #334155;
            font-family: Consolas, "Courier New", monospace;
            font-size: .82rem;
            line-height: 1.7;
        }
        .setup-actions {
            display: grid;
            gap: .65rem;
            margin-top: 1rem;
        }
        .setup-button {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            border: 1px solid #E7E7E7;
            background: #F8FAFC;
            color: #0B1A34;
            font: inherit;
            font-size: .9rem;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }
        .setup-button--primary {
            border-color: #F1620F;
            background: #F1620F;
            color: #fff;
        }
        .setup-status {
            display: none;
            margin-top: .8rem;
            padding: .75rem .85rem;
            border: 1px solid #BBF7D0;
            border-radius: 14px;
            background: #F0FDF4;
            color: #166534;
            font-size: .84rem;
            font-weight: 800;
        }
        .setup-note {
            margin-top: 1rem;
            padding-top: .9rem;
            border-top: 1px solid #E7E7E7;
            color: #5D5959;
            font-size: .82rem;
            line-height: 1.7;
            font-weight: 650;
        }
    </style>
</head>
<body>
    <main class="setup-card">
        <h1>رابط الإعداد والتفعيل</h1>
        <p>افتح الرابط مباشرة أو انسخه لاستخدامه في المتصفح.</p>

        <div class="setup-link" id="linkBox">{{ $onboardingUrl }}</div>
        <div class="setup-status" id="successBanner">تم نسخ رابط التفعيل بنجاح.</div>

        <div class="setup-actions">
            <a href="{{ $onboardingUrl }}" class="setup-button setup-button--primary">إكمال الإعداد</a>
            <button class="setup-button" type="button" onclick="copyToClipboard()">نسخ الرابط</button>
        </div>

        <div class="setup-note">
            هذا الرابط مخصص للاستخدام مرة واحدة ولفترة محدودة.
        </div>
    </main>

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
                window.prompt('انسخ الرابط يدويا:', text);
            });
        }
    </script>
</body>
</html>
