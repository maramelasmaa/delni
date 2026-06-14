@extends('public.layout')

@section('title', 'المفضلة - ' . config('app.name'))

@section('content')
<div class="lp-wrapper">

    <header class="lp-header">
        <div class="lp-header-body">
            <span class="lp-label">دلني</span>
            <h1 class="lp-title">المفضلة</h1>
            @auth
                <span class="lp-count">{{ $favorites instanceof \Illuminate\Pagination\LengthAwarePaginator ? $favorites->total() : $favorites->count() }} مزود</span>
            @endauth
        </div>
        <div class="fv-heart-icon">
            <x-render-icon icon="app-heart-filled" />
        </div>
    </header>

    @guest
        <div class="fv-guest">
            <div class="fv-guest__icon">
                <x-render-icon icon="app-heart" />
            </div>
            <h2 class="fv-guest__title">احفظ مزوديك المفضلين</h2>
            <p class="fv-guest__desc">سجّل دخولك لتتمكن من حفظ المزودين ومراجعتهم لاحقاً بسهولة.</p>
            <a href="{{ route('login') }}" class="fv-guest__btn">تسجيل الدخول</a>
        </div>
    @endguest

    @auth
        @if($favorites->count() > 0)
            <div class="lp-results">
                <x-provider-grid :providers="$favorites" :columns="2" />

                @if($favorites->hasPages())
                    <nav class="lp-pagination" aria-label="Pagination">
                        @if($favorites->onFirstPage())
                            <span class="is-disabled">السابق</span>
                        @else
                            <a href="{{ $favorites->previousPageUrl() }}">السابق</a>
                        @endif

                        <strong>{{ $favorites->currentPage() }} / {{ $favorites->lastPage() }}</strong>

                        @if($favorites->hasMorePages())
                            <a href="{{ $favorites->nextPageUrl() }}">التالي</a>
                        @else
                            <span class="is-disabled">التالي</span>
                        @endif
                    </nav>
                @endif
            </div>
        @else
            <div class="fv-empty">
                <div class="fv-empty__icon">
                    <x-render-icon icon="app-heart" />
                </div>
                <h2 class="fv-empty__title">لا توجد مفضلة بعد</h2>
                <p class="fv-empty__desc">اضغط على أيقونة القلب في صفحة أي مزود لإضافته إلى مفضلتك.</p>
                <a href="{{ route('home') }}" class="fv-empty__btn">استكشف المزودين</a>
            </div>
        @endif
    @endauth

</div>
@endsection

@push('styles')
<style>
    .fv-heart-icon {
        width: 2.5rem;
        height: 2.5rem;
        background: rgba(241, 98, 15, .1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--delni-primary);
        flex-shrink: 0;
    }

    .fv-heart-icon svg { width: 1.25rem; height: 1.25rem; }

    /* Guest prompt */
    .fv-guest {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 3rem 1.5rem;
        gap: 1rem;
    }

    .fv-guest__icon {
        width: 4rem;
        height: 4rem;
        background: rgba(241, 98, 15, .08);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--delni-primary);
    }

    .fv-guest__icon svg { width: 2rem; height: 2rem; }

    .fv-guest__title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--delni-navy);
        margin: 0;
    }

    .fv-guest__desc {
        font-size: .875rem;
        color: var(--delni-muted);
        margin: 0;
        max-width: 18rem;
        line-height: 1.6;
    }

    .fv-guest__btn {
        display: inline-block;
        background: var(--delni-primary);
        color: #fff;
        font-size: .9rem;
        font-weight: 600;
        padding: .75rem 2rem;
        border-radius: 2rem;
        text-decoration: none;
        margin-top: .5rem;
    }

    /* Empty state */
    .fv-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 3rem 1.5rem;
        gap: 1rem;
    }

    .fv-empty__icon {
        width: 4rem;
        height: 4rem;
        background: #F1F5F9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--delni-muted);
    }

    .fv-empty__icon svg { width: 2rem; height: 2rem; }

    .fv-empty__title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--delni-navy);
        margin: 0;
    }

    .fv-empty__desc {
        font-size: .875rem;
        color: var(--delni-muted);
        margin: 0;
        max-width: 18rem;
        line-height: 1.6;
    }

    .fv-empty__btn {
        display: inline-block;
        background: var(--delni-navy);
        color: #fff;
        font-size: .875rem;
        font-weight: 600;
        padding: .65rem 1.75rem;
        border-radius: 2rem;
        text-decoration: none;
        margin-top: .5rem;
    }

    /* Dark mode */
    [data-theme="dark"] .fv-guest__title,
    [data-theme="dark"] .fv-empty__title { color: #F1F5F9; }

    [data-theme="dark"] .fv-empty__icon { background: #1E293B; }

    [data-theme="dark"] .fv-empty__btn { background: #334155; }
</style>
@endpush
