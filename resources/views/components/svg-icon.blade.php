@props(['icon', 'size' => '24'])

@if($icon)
    <img
        src="{{ route('icon.show', $icon) }}"
        alt="{{ $icon->name ?? 'Icon' }}"
        width="{{ $size }}"
        height="{{ $size }}"
        style="width: {{ $size }}px !important; height: {{ $size }}px !important; object-fit: contain;"
        loading="lazy"
        {{ $attributes }}
    />
@else
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#F1620F"
        width="{{ $size }}"
        height="{{ $size }}"
        style="width: {{ $size }}px !important; height: {{ $size }}px !important;"
        {{ $attributes }}>
        <path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-2h2v16h-2z"/>
    </svg>
@endif
