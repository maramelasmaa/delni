<?php

return [
    'enabled' => env('DEEPSEEK_ENABLED', false),
    'api_key' => env('DEEPSEEK_API_KEY'),
    'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
    'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    'timeout' => (int) env('DEEPSEEK_TIMEOUT', 15),
    'max_tokens' => (int) env('DEEPSEEK_MAX_TOKENS', 500),
    'temperature' => (float) env('DEEPSEEK_TEMPERATURE', 0.2),
];
