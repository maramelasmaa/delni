@props(['items' => []])

@php
    $trailItems = collect($items)->filter(fn ($item) => filled($item['label'] ?? null))->values();
@endphp

@if($trailItems->isNotEmpty())
    <nav class="browse-trail" aria-label="مسار التصفح">
        @foreach($trailItems as $item)
            @if(! empty($item['url']) && empty($item['active']))
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @else
                <span class="is-active">{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif

@once
    @push('styles')
        <style>
            .browse-trail {
                display: flex;
                align-items: center;
                gap: .4rem;
                overflow-x: auto;
                padding: .05rem .05rem .15rem;
                scrollbar-width: none;
                -webkit-overflow-scrolling: touch;
            }
            .browse-trail::-webkit-scrollbar { display: none; }

            .browse-trail a,
            .browse-trail span {
                flex: 0 0 auto;
                min-height: 30px;
                display: inline-flex;
                align-items: center;
                padding: .32rem .62rem;
                border: 1px solid var(--delni-border);
                border-radius: 999px;
                background: #fff;
                color: #64748B;
                font-size: .74rem;
                font-weight: 900;
                text-decoration: none;
                white-space: nowrap;
            }

            .browse-trail .is-active {
                border-color: rgba(241,98,15,.22);
                background: #FFF7ED;
                color: var(--delni-primary);
            }

            [data-theme="dark"] .browse-trail a,
            [data-theme="dark"] .browse-trail span {
                background: #1E293B;
                border-color: #334155;
                color: #CBD5E1;
            }
            [data-theme="dark"] .browse-trail .is-active {
                background: rgba(241,98,15,.12);
                border-color: rgba(241,98,15,.28);
                color: #FB923C;
            }
        </style>
    @endpush
@endonce
