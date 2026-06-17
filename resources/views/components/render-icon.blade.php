@props([
    'icon' => null,
    'class' => '',
])

@php
    $name = $icon ?: 'app-stack';

    $aliases = [
        'heroicon-o-arrow-right' => 'app-back',
        'heroicon-o-arrow-left' => 'app-forward',
        'heroicon-o-arrow-path' => 'app-refresh',
        'heroicon-o-arrow-right-on-rectangle' => 'app-login',
        'heroicon-o-bars-3-bottom-left' => 'app-sort',
        'heroicon-o-briefcase' => 'app-category',
        'heroicon-o-building-office-2' => 'app-category',
        'heroicon-o-chat-bubble-left' => 'app-contact',
        'heroicon-o-chat-bubble-left-ellipsis' => 'app-contact',
        'heroicon-o-chevron-left' => 'app-chevron-left',
        'heroicon-o-document-text' => 'app-document',
        'heroicon-o-envelope' => 'app-mail',
        'heroicon-o-exclamation-triangle' => 'app-warning',
        'heroicon-o-folder-open' => 'app-folder',
        'heroicon-o-funnel' => 'app-filter',
        'heroicon-o-globe-alt' => 'app-stack',
        'heroicon-o-home' => 'app-home',
        'heroicon-o-identification' => 'app-account',
        'heroicon-o-information-circle' => 'app-info',
        'heroicon-o-magnifying-glass' => 'app-search',
        'heroicon-o-map-pin' => 'app-location',
        'heroicon-o-moon' => 'app-moon',
        'heroicon-o-phone' => 'app-phone',
        'heroicon-o-photo' => 'app-image',
        'heroicon-o-pencil-square' => 'app-edit',
        'heroicon-o-shield-check' => 'app-shield',
        'heroicon-o-square-3-stack-3d' => 'app-stack',
        'heroicon-o-star' => 'app-star',
        'heroicon-o-trash' => 'app-trash',
        'heroicon-o-user-circle' => 'app-account',
        'heroicon-o-users' => 'app-users',
    ];

    $name = $aliases[$name] ?? $name;

    $svgs = [
        'app-account' => '<circle cx="12" cy="8.25" r="3.25" /><path d="M5.25 19.25c1.25-3.2 3.6-4.8 6.75-4.8s5.5 1.6 6.75 4.8" />',
        'app-back' => '<path d="M12 6.5 6.5 12l5.5 5.5" /><path d="M7 12H17.5" />',
        'app-category' => '<path d="M4 8C4 5.17157 4 3.75736 4.87868 2.87868C5.75736 2 7.17157 2 10 2H14C16.8284 2 18.2426 2 19.1213 2.87868C20 3.75736 20 5.17157 20 8V16C20 18.8284 20 20.2426 19.1213 21.1213C18.2426 22 16.8284 22 14 22H10C7.17157 22 5.75736 22 4.87868 21.1213C4 20.2426 4 18.8284 4 16V8Z" stroke="currentColor" stroke-width="1.5"/><path d="M19.8978 16H7.89778C6.96781 16 6.50282 16 6.12132 16.1022C5.08604 16.3796 4.2774 17.1883 4 18.2235" stroke="currentColor" stroke-width="1.5"/><path opacity="0.5" d="M8 7H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path opacity="0.5" d="M8 10.5H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path opacity="0.5" d="M19.5 19H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
        'app-categories' => '<rect x="4" y="4" width="6" height="6" rx="1" /><rect x="14" y="4" width="6" height="6" rx="1" /><rect x="4" y="14" width="6" height="6" rx="1" /><rect x="14" y="14" width="6" height="6" rx="1" />',
        'app-chevron-left' => '<path d="m14.25 6.75-5.5 5.25 5.5 5.25" />',
        'app-contact' => '<path d="M5.25 6.75h13.5v8.5H9l-3.75 3.25v-3.25h0V6.75Z" /><path d="M8.5 10h7" /><path d="M8.5 12.75h4.5" />',
        'app-document' => '<path d="M7 4.75h7.25L18 8.5v10.75H7V4.75Z" /><path d="M14.25 4.75V8.5H18" /><path d="M9.5 12h5" /><path d="M9.5 15h5" />',
        'app-edit' => '<path d="M5.25 18.75h13.5" /><path d="M7 15.75 16.4 6.35a2.05 2.05 0 0 1 2.9 2.9l-9.4 9.4H7v-2.9Z" />',
        'app-filter' => '<path d="M4.5 7h8.25" /><path d="M16.25 7h3.25" /><circle cx="14.5" cy="7" r="1.75" /><path d="M4.5 12h3.25" /><path d="M11.25 12h8.25" /><circle cx="9.5" cy="12" r="1.75" /><path d="M4.5 17h8.25" /><path d="M16.25 17h3.25" /><circle cx="14.5" cy="17" r="1.75" />',
        'app-folder' => '<path d="M4.25 7.25h5l1.8 2h8.7v7.25a2 2 0 0 1-2 2H6.25a2 2 0 0 1-2-2V7.25Z" /><path d="M4.25 9.25h15.5" />',
        'app-forward' => '<path d="M12 6.5 17.5 12l-5.5 5.5" /><path d="M6.5 12H17" />',
        'app-heart' => '<path d="M12 20.25C12 20.25 4 14.5 4 8.75a4.25 4.25 0 0 1 8-2 4.25 4.25 0 0 1 8 2c0 5.75-8 11.5-8 11.5Z" />',
        'app-heart-filled' => '<path d="M12 20.25C12 20.25 4 14.5 4 8.75a4.25 4.25 0 0 1 8-2 4.25 4.25 0 0 1 8 2c0 5.75-8 11.5-8 11.5Z" fill="currentColor" />',
        'app-home' => '<path d="M4 10.75 12 4l8 6.75" /><path d="M6.25 9.25v9.5h4.2v-5.1h3.1v5.1h4.2v-9.5" />',
        'app-image' => '<rect x="4" y="5" width="16" height="14" rx="2" /><circle cx="8.75" cy="9" r="1.35" /><path d="m5.5 16 4.25-4.2 3.15 3.05 1.9-1.85 3.7 3.8" />',
        'app-info' => '<circle cx="12" cy="12" r="8" /><path d="M12 10.75v5" /><path d="M12 7.75h.01" />',
        'app-location' => '<path d="M12 20s6-5.1 6-10a6 6 0 1 0-12 0c0 4.9 6 10 6 10Z" /><circle cx="12" cy="10" r="2.2" />',
        'app-login' => '<path d="M9.25 5.25H6.5a2 2 0 0 0-2 2v9.5a2 2 0 0 0 2 2h2.75" /><path d="M13.25 8.25 17 12l-3.75 3.75" /><path d="M8.5 12H17" />',
        'app-mail' => '<rect x="4" y="6.25" width="16" height="11.5" rx="2" /><path d="m5.5 8.25 6.5 4.4 6.5-4.4" />',
        'app-moon' => '<path d="M18.75 14.35A7.25 7.25 0 0 1 9.65 5.25 7.5 7.5 0 1 0 18.75 14.35Z" />',
        'app-phone' => '<path d="M7.25 4.75 9.3 4l2.05 4.55-1.55 1.1a9.2 9.2 0 0 0 4.55 4.55l1.1-1.55L20 14.7l-.75 2.05c-.35.95-1.25 1.55-2.25 1.5C10.6 17.95 6.05 13.4 5.75 7c-.05-1 .55-1.9 1.5-2.25Z" />',
        'app-refresh' => '<path d="M6.25 8.25A7.2 7.2 0 0 1 18.1 7.2L20 9.15" /><path d="M20.25 5.25v4.25h-4.2" /><path d="M17.75 15.75A7.2 7.2 0 0 1 5.9 16.8L4 14.85" /><path d="M3.75 18.75V14.5h4.2" />',
        'app-search' => '<circle cx="10.75" cy="10.75" r="5.75" /><path d="m15.1 15.1 4.15 4.15" />',
        'app-shield' => '<path d="M12 4.25 18.75 7v4.75c0 4-2.35 6.35-6.75 8-4.4-1.65-6.75-4-6.75-8V7L12 4.25Z" /><path d="m9 12.25 2 2 4-4.25" />',
        'app-sort' => '<path d="M4.5 7h10.75" /><path d="M4.5 12h8" /><path d="M4.5 17h4.75" /><path d="m16.75 15.25 2.25 2.25 2.25-2.25" /><path d="M19 6.5v10.75" />',
        'app-stack' => '<path d="m12 4 7.25 3.7L12 11.4 4.75 7.7 12 4Z" /><path d="m5 12 7 3.6 7-3.6" /><path d="m5 16 7 3.6 7-3.6" />',
        'app-star' => '<path d="m12 4.2 2.15 4.35 4.8.7-3.48 3.38.82 4.77L12 15.15 7.71 17.4l.82-4.77-3.48-3.38 4.8-.7L12 4.2Z" />',
        'app-trash' => '<path d="M5.25 7.25h13.5" /><path d="M9.25 7.25V5.5h5.5v1.75" /><path d="m7.25 9.25.75 9.5h8l.75-9.5" /><path d="M10.25 11.5v4.75" /><path d="M13.75 11.5v4.75" />',
        'app-users' => '<circle cx="9.25" cy="8.5" r="2.75" /><path d="M4.25 18.25c.9-3.15 2.6-4.7 5-4.7s4.1 1.55 5 4.7" /><path d="M14.25 11.25a2.45 2.45 0 1 0 .05-4.9" /><path d="M15.25 14.1c2.05.35 3.5 1.75 4.5 4.15" />',
        'app-warning' => '<path d="M12 4.5 20 18.5H4L12 4.5Z" /><path d="M12 9.5v4" /><path d="M12 16.25h.01" />',
        'brand-instagram' => '<rect x="4" y="4" width="16" height="16" rx="4.5" /><circle cx="12" cy="12" r="3.5" /><circle cx="16.5" cy="7.5" r=".75" fill="currentColor" stroke="none" />',
        'brand-facebook'  => '<path d="M17 4h-2.5A4.5 4.5 0 0 0 10 8.5V11H7v3h3v6h3v-6h3l.5-3H13V8.5A1.5 1.5 0 0 1 14.5 7H17V4Z" />',
        'brand-linkedin'  => '<rect x="4" y="4" width="16" height="16" rx="2.5" /><path d="M8.5 10.5v6" /><circle cx="8.5" cy="7.75" r=".9" fill="currentColor" stroke="none" /><path d="M12 10.5v6m0-3.5a3 3 0 0 1 6 0v3.5" />',
        'brand-x'         => '<path d="M4.5 4.5 11 12.5m0 0 6.5 7m-6.5-7L4.5 19.5M11 12.5 19.5 4.5" />',
    ];

    $path = $svgs[$name] ?? $svgs['app-stack'];
@endphp

<svg
    class="{{ $class }}"
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
>
    {!! $path !!}
</svg>
