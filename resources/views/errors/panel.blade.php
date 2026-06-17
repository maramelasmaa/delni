<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Error - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
        .panel-error {
            width: min(100%, 440px);
            padding: clamp(1.25rem, 4vw, 1.75rem);
            border: 1px solid #E7E7E7;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 12px 30px rgba(11, 26, 52, .05);
            text-align: center;
        }
        .panel-error__code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            min-height: 34px;
            margin-bottom: .85rem;
            padding: .25rem .75rem;
            border-radius: 999px;
            background: #FFF7ED;
            color: #F1620F;
            font-size: .9rem;
            font-weight: 950;
        }
        .panel-error h1 {
            margin: 0;
            font-size: clamp(1.15rem, 4vw, 1.35rem);
            line-height: 1.45;
            font-weight: 950;
        }
        .panel-error p {
            margin: .55rem auto 0;
            color: #5D5959;
            font-size: .92rem;
            line-height: 1.85;
            font-weight: 650;
        }
        .panel-error button {
            min-height: 46px;
            margin-top: 1.15rem;
            padding: .7rem 1rem;
            border: 0;
            border-radius: 14px;
            background: #F1620F;
            color: #fff;
            font: inherit;
            font-size: .9rem;
            font-weight: 900;
            cursor: pointer;
        }
        .panel-error__debug {
            margin-top: 1rem;
            padding-top: .9rem;
            border-top: 1px solid #E7E7E7;
            color: #5D5959;
            direction: ltr;
            font-family: Consolas, "Courier New", monospace;
            font-size: .75rem;
            line-height: 1.6;
            overflow-wrap: anywhere;
            text-align: left;
        }
    </style>
</head>
<body>
    <main class="panel-error">
        <span class="panel-error__code">!</span>
        <h1>تعذر إكمال هذا الطلب</h1>
        <p>يرجى المحاولة مرة أخرى. إذا استمرت المشكلة، فتواصل مع الدعم.</p>

        @if(config('app.debug') && isset($exception))
            <div class="panel-error__debug">
                <strong>{{ get_class($exception) }}:</strong> {{ $exception->getMessage() }}
                @if($exception->getFile())
                    <br><br><strong>File:</strong> {{ $exception->getFile() }}:{{ $exception->getLine() }}
                @endif
            </div>
        @endif

        <button type="button" onclick="history.back()">رجوع</button>
    </main>
</body>
</html>
