@props([
    'src' => '',
    'alt' => '',
    'width' => null,
    'height' => null,
    'lazy' => true,
    'class' => '',
    'sizes' => null,
])

<img
    src="{{ asset($src) }}"
    alt="{{ $alt }}"
    {{ $attributes->merge([
        'class' => $class,
        'loading' => $lazy ? 'lazy' : 'eager',
        'width' => $width,
        'height' => $height,
        'sizes' => $sizes,
        'decoding' => 'async',
    ]) }}
/>
