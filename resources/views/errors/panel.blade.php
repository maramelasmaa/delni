<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #F1620F;
            --navy: #0B1A34;
            --bg: #FCFBFB;
            --surface: #FFFFFF;
            --border: #E7E7E7;
            --muted: #5D5959;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);
            color: var(--navy);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        .error-container {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: clamp(1.5rem, 4vw, 2.5rem);
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(11, 26, 52, 0.03);
            text-align: center;
        }

        .error-code {
            font-size: clamp(2.5rem, 10vw, 3.5rem);
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .error-title {
            font-size: clamp(1.5rem, 3vw, 1.8rem);
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .error-message {
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .back-button {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 700;
            transition: background 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .back-button:hover {
            background: #D9550C;
        }

        .debug-info {
            text-align: left;
            background: #FCFBFB;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            border-top: 1px solid var(--border);
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 0.8rem;
            color: var(--muted);
            overflow-x: auto;
            line-height: 1.5;
        }

        .debug-info strong {
            color: var(--navy);
            font-weight: 700;
        }

        @media (max-width: 640px) {
            .error-container {
                padding: 1.25rem;
            }

            .error-code {
                font-size: 2.2rem;
                margin-bottom: 0.4rem;
            }

            .error-title {
                font-size: 1.4rem;
                margin-bottom: 0.75rem;
            }

            .error-message {
                font-size: 0.9rem;
                margin-bottom: 1.25rem;
            }

            .back-button {
                padding: 0.65rem 1.25rem;
                font-size: 0.9rem;
            }

            .debug-info {
                padding: 0.75rem;
                margin-top: 1.25rem;
                font-size: 0.75rem;
            }
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
            <div class="debug-info">
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
