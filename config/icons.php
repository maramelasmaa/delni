<?php

return [
    'default_color' => env('ICON_DEFAULT_COLOR', '#F1620F'),
    'max_file_size' => 500 * 1024,
    'download_timeout' => 10,
    'supported_formats' => ['svg', 'png'],
    'storage_disk' => 'icons',
];
