@php
    // Dynamically render Heroicon using blade-icons
    // Usage: @include('icons.heroicon', ['icon' => 'heroicon-o-star', 'class' => 'w-6 h-6'])
@endphp

@switch($icon ?? null)
    @case('heroicon-o-building-office-2')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V9.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636m0 0h9m-9 0L2.25 9m0 0V6.504c0-1.341 1.084-2.436 2.424-2.436h15.152c1.34 0 2.424 1.095 2.424 2.436V9m-21 0V3.75A2.25 2.25 0 005.25 1.5h13.5A2.25 2.25 0 0021 3.75V9" /></svg>
        @break
    @case('heroicon-s-building-office-2')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M6.819 1.5a2.25 2.25 0 00-2.119 1.375.75.75 0 00.798 1.048c.34-.066.68.149.854.56L7.5 9v9.375A2.25 2.25 0 009.75 21h10.5A2.25 2.25 0 0022.5 18.75V9l1.148-4.087a.75.75 0 00.798-1.048A2.25 2.25 0 0022.181 1.5H6.819z" /><path fill-rule="evenodd" d="M9.75 9a.75.75 0 01.75.75v7.5a.75.75 0 01-1.5 0v-7.5A.75.75 0 019.75 9zm2.25 0a.75.75 0 01.75.75v7.5a.75.75 0 01-1.5 0v-7.5A.75.75 0 0112 9zm2.25 0a.75.75 0 01.75.75v7.5a.75.75 0 01-1.5 0v-7.5a.75.75 0 01.75-.75z" /></svg>
        @break
    @case('heroicon-o-wrench')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.632a2.25 2.25 0 01-2.25 2.25H5.25a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5A2.25 2.25 0 002.25 6.75m19.5 0v-1.5A2.25 2.25 0 0019.5 3H4.5A2.25 2.25 0 002.25 5.25v1.5m19.5 0h-19.5m0 0A2.25 2.25 0 012.25 8.25h19.5A2.25 2.25 0 0121.75 6.75z" /></svg>
        @break
    @case('heroicon-o-star')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25l.840 2.615a.75.75 0 00.712.515h2.743l-2.22 1.612a.75.75 0 00-.27.824l.84 2.616-2.22-1.612a.75.75 0 00-.882 0l-2.22 1.612.84-2.616a.75.75 0 00-.27-.824l-2.22-1.612h2.743a.75.75 0 00.712-.515L12 8.25z" /></svg>
        @break
    @case('heroicon-s-star')
        <svg {{ $class ? "class=\"$class\"" : '' }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.007 1.564 5.694c.27 1.065-.910 1.900-1.838 1.335L12 18.338l-4.856 2.676c-.927.566-2.108-.27-1.838-1.335l1.563-5.694-4.117-3.007c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.006z" /></svg>
        @break
    @default
        📦
@endswitch
