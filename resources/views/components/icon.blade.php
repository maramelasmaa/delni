@props(['icon' => null, 'size' => 'w-5 h-5', 'color' => 'text-gray-700'])

@if($icon)
    @php
        $isValid = \App\Services\IconSystem::isValidHeroicon($icon);
    @endphp

    @if($isValid)
        <x-dynamic-component :component="$icon" :class="$size . ' ' . $color" />
    @else
        <!-- Fallback for invalid icon -->
        <x-dynamic-component component="heroicon-o-square-3-stack-3d" :class="$size . ' ' . $color" />
    @endif
@else
    <!-- Fallback for null icon -->
    <x-dynamic-component component="heroicon-o-square-3-stack-3d" :class="$size . ' ' . $color" />
@endif
