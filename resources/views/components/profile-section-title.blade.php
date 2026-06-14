@props(['eyebrow' => null, 'title'])

<div class="profile-section-title">
    @if($eyebrow)
        <span>{{ $eyebrow }}</span>
    @endif
    <h2>{{ $title }}</h2>
</div>
