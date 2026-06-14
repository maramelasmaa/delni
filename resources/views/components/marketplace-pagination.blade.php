@props(['paginator'])

@if($paginator->hasPages())
    <nav class="mp-pagination" aria-label="Pagination">
        @if($paginator->onFirstPage())
            <span class="is-disabled">السابق</span>
        @else
            <a href="{{ $paginator->appends(request()->query())->previousPageUrl() }}">السابق</a>
        @endif

        <strong>{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</strong>

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->appends(request()->query())->nextPageUrl() }}">التالي</a>
        @else
            <span class="is-disabled">التالي</span>
        @endif
    </nav>
@endif

@once
    @push('styles')
        <style>
            .mp-pagination {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .6rem;
                margin-top: 1rem;
            }

            .mp-pagination a,
            .mp-pagination span {
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: .55rem .9rem;
                border-radius: 14px;
                border: 1px solid var(--delni-border);
                background: #fff;
                color: var(--delni-navy);
                font-size: .82rem;
                font-weight: 900;
                text-decoration: none;
            }

            .mp-pagination strong {
                color: #64748B;
                font-size: .78rem;
                font-weight: 900;
            }

            .mp-pagination .is-disabled {
                background: #F1F5F9;
                color: #94A3B8;
            }

            [data-theme="dark"] .mp-pagination a,
            [data-theme="dark"] .mp-pagination span {
                background: #1E293B;
                border-color: #334155;
                color: #F1F5F9;
            }
            [data-theme="dark"] .mp-pagination strong { color: #94A3B8; }
            [data-theme="dark"] .mp-pagination .is-disabled {
                background: #0F172A;
                color: #475569;
            }
        </style>
    @endpush
@endonce
