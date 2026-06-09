<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رابط الإعداد</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        h1 {
            color: #003366;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .link-box {
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 40px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .copy-btn {
            background: #f1620f;
            padding: 10px 20px;
            font-size: 14px;
            margin-top: 15px;
        }
        .copy-btn:hover {
            background: #e0540a;
        }
        .success {
            display: none;
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
            border: 1px solid #c3e6cb;
        }
        .info {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            text-align: right;
            border-radius: 4px;
            color: #0c5394;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 رابط الإعداد</h1>
        <p class="subtitle">انسخ الرابط أدناه أو اضغط على الزر لتعيين كلمة مرورك</p>

        <div class="link-box" id="linkBox">
            {{ $onboardingUrl }}
        </div>

        <button class="button copy-btn" onclick="copyToClipboard()">
            📋 انسخ الرابط
        </button>
        <div class="success" id="success">✓ تم النسخ بنجاح!</div>

        <div class="info">
            ⏰ انتبه: هذا الرابط صالح لمدة محدودة فقط. تأكد من استخدامه قبل انتهاء الصلاحية.
        </div>

        <a href="{{ $onboardingUrl }}" class="button" style="margin-top: 20px;">
            إكمال الإعداد →
        </a>

        <p style="margin-top: 30px; color: #999; font-size: 13px;">
            إذا واجهت مشاكل، يرجى نسخ الرابط أعلاه والصقه في عنوان المتصفح.
        </p>
    </div>

    <script>
        function copyToClipboard() {
            const text = document.getElementById('linkBox').innerText;
            navigator.clipboard.writeText(text).then(() => {
                const successMsg = document.getElementById('success');
                successMsg.style.display = 'block';
                setTimeout(() => {
                    successMsg.style.display = 'none';
                }, 3000);
            }).catch(() => {
                alert('فشل النسخ. يرجى محاولة يدويًا.');
            });
        }
    </script>
</body>
</html>
