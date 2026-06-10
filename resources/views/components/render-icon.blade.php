@props([
    'icon' => null,
    'class' => '',
])

@php
    $name = $icon ?: 'heroicon-o-square-3-stack-3d';

    $svgs = [
        'heroicon-o-phone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.37a1.5 1.5 0 00-1.024-1.423l-4.106-1.369a1.5 1.5 0 00-1.594.37l-1.03 1.03a11.25 11.25 0 01-6.734-6.734l1.03-1.03a1.5 1.5 0 00.37-1.594L7.293 3.274A1.5 1.5 0 005.87 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z" />',
        'heroicon-o-chat-bubble-left' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 15a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'heroicon-o-map-pin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />',
        'heroicon-o-briefcase' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.1A2.25 2.25 0 0118 20.5H6a2.25 2.25 0 01-2.25-2.25v-4.1m16.5 0A2.25 2.25 0 0018 11.9H6a2.25 2.25 0 00-2.25 2.25m16.5 0v-3.4A2.25 2.25 0 0018 8.5h-1.5m-13.5 5.65v-3.4A2.25 2.25 0 016 8.5h1.5m9 0V6.75A2.25 2.25 0 0014.25 4.5h-4.5A2.25 2.25 0 007.5 6.75V8.5m9 0h-9" />',
        'heroicon-o-globe-alt' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" /><path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M12 3c2.25 2.25 3.375 5.25 3.375 9S14.25 18.75 12 21M12 3C9.75 5.25 8.625 8.25 8.625 12S9.75 18.75 12 21" />',
        'heroicon-o-magnifying-glass' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.197 5.197a7.5 7.5 0 0010.606 10.606z" />',
        'heroicon-o-envelope' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0l-7.5-4.615A2.25 2.25 0 012.25 6.993V6.75" />',
        'heroicon-o-building-office-2' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V9.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636m0 0h9m-9 0L2.25 9m0 0V6.504c0-1.341 1.084-2.436 2.424-2.436h15.152c1.34 0 2.424 1.095 2.424 2.436V9m-21 0V3.75A2.25 2.25 0 015.25 1.5h13.5A2.25 2.25 0 0121 3.75V9" />',
        'heroicon-o-photo' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />',
        'heroicon-o-funnel' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.132 0 4.116.756 5.604 2.01m-7.08 8.994L12 15m0 0l2.475 2.006M12 15l-2.475 2.006M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />',
        'heroicon-o-arrow-path' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992M2.763 9.348c.547-4.055 4.029-7.036 8.237-7.036 4.735 0 8.659 3.373 9.021 7.646m15.997 3.464c-.547 4.055-4.029 7.036-8.236 7.036-4.735 0-8.659-3.373-9.021-7.646" />',
        'heroicon-o-square-3-stack-3d' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12a1.5 1.5 0 011.5 1.5V9m-18-6a1.5 1.5 0 00-1.5 1.5v6m0 0a1.5 1.5 0 001.5 1.5h12a1.5 1.5 0 001.5-1.5m-18 0V5.25m0 10.5a1.5 1.5 0 001.5 1.5h12a1.5 1.5 0 001.5-1.5M6 21h12a1.5 1.5 0 001.5-1.5v-6a1.5 1.5 0 00-1.5-1.5H6a1.5 1.5 0 00-1.5 1.5v6a1.5 1.5 0 001.5 1.5z" />',
    ];

    $path = $svgs[$name] ?? $svgs['heroicon-o-square-3-stack-3d'];
@endphp

<svg
    class="{{ $class }}"
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
    stroke-width="1.5"
    aria-hidden="true"
>
    {!! $path !!}
</svg>
