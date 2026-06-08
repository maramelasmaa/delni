<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #0B1A34 0%, #112240 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: rgba(17, 34, 64, 0.8);
            border: 1px solid rgba(241, 98, 15, 0.3);
            border-radius: 8px;
            padding: 40px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .error-code {
            font-size: 48px;
            font-weight: bold;
            color: #F1620F;
            margin-bottom: 16px;
        }

        .error-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .error-message {
            color: #ccc;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .back-button {
            display: inline-block;
            background: #F1620F;
            color: white;
            padding: 10px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .back-button:hover {
            background: #D9550C;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">⚠️</div>
        <div class="error-title">An Error Occurred</div>
        <div class="error-message">
            We encountered an issue processing your request.<br>
            Please try again or contact support if the problem persists.
        </div>
        @if(config('app.debug') && isset($exception))
            <div style="text-align: left; background: rgba(0,0,0,0.3); padding: 20px; border-radius: 4px; margin-top: 30px; font-family: monospace; font-size: 12px;">
                <strong>{{ get_class($exception) }}:</strong> {{ $exception->getMessage() }}
                @if($exception->getFile())
                    <br><br><strong>File:</strong> {{ $exception->getFile() }}:{{ $exception->getLine() }}
                @endif
            </div>
        @endif
        <button class="back-button" onclick="history.back()">Go Back</button>
    </div>
</body>
</html>
