@props(['icon' => null, 'class' => ''])

@php
    use App\Services\IconSystem;

    $isValid = !empty($icon) && IconSystem::isValidHeroicon($icon);

    // Since blade-heroicons isn't installed for public views, we render a safe fallback
    // The icon name is stored in DB and admin can select it properly
    // Public just shows a symbol to indicate icon position
    if ($isValid) {
        $display = '◆'; // Diamond for valid icon (system tried to render it)
    } else {
        $display = '■'; // Square for invalid/empty (fallback)
    }
@endphp

<span class="{{ $class }}" title="{{ $icon ?? 'no-icon' }}">{{ $display }}</span>
