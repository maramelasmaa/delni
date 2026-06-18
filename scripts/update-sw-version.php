<?php

$swPath = __DIR__.'/../public/sw.js';

if (file_exists($swPath)) {
    $content = file_get_contents($swPath);
    // Convert current time timestamp to base36 to get a clean short alphanumeric hash
    $version = 'delni-public-'.base_convert((string) time(), 10, 36);
    $content = preg_replace("/const CACHE_VERSION = '[^']*';/", "const CACHE_VERSION = '{$version}';", $content);
    file_put_contents($swPath, $content);
    echo "[PWA] Updated sw.js CACHE_VERSION to: {$version}\n";
} else {
    echo "[PWA] sw.js not found at {$swPath}\n";
}
