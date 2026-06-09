@extends('public.layout')

@section('title', __('messages.public.search_results') . ' - ' . config('app.name'))

@section('content')

<div class="search-page">
    <!-- Page Header -->
    <div class="search-header">
        <div class="container">
            <h1>{{ __('messages.public.search_results') }}</h1>
            <p>{{ __('messages.public.find_trusted_professionals') }}</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="search-main">
        <div class="container">
            <div class="search-container">
                <!-- Sidebar -->
                <aside class="search-sidebar">
                    <div class="filter-box">
                        <x-search-filters
                            :categories="$categories"
                            :cities="$cities"
                            :providerTypes="$providerTypes ?? null"
                        />
                    </div>
                </aside>

                <!-- Results -->
                <main class="search-results">
                    <!-- Results Summary -->
                    <div class="results-summary">
                        <h2>{{ $profiles->total() }} {{ __('messages.public.professionals') }}</h2>
                        @if(request('keyword'))
                            <p>{{ __('messages.public.for') }} <strong>"{{ request('keyword') }}"</strong></p>
                        @endif
                    </div>

                    <!-- Active Filters -->
                    @if(request()->anyFilled(['category_id', 'city_id', 'provider_type', 'remote']))
                        <div class="filter-chips">
                            @if(request('category_id') && $categories->find(request('category_id')))
                                <span class="chip">
                                    {{ $categories->find(request('category_id'))->localized_name ?? $categories->find(request('category_id'))->name }}
                                    <a href="{{ request()->fullUrlWithQuery(['category_id' => null]) }}">×</a>
                                </span>
                            @endif

                            @if(request('city_id') && $cities->find(request('city_id')))
                                <span class="chip">
                                    {{ $cities->find(request('city_id'))->localized_name ?? $cities->find(request('city_id'))->name }}
                                    <a href="{{ request()->fullUrlWithQuery(['city_id' => null]) }}">×</a>
                                </span>
                            @endif

                            @if(request('provider_type') && isset($providerTypes))
                                @php $selectedType = request('provider_type'); @endphp
                                @if(isset($providerTypes[$selectedType]))
                                    <span class="chip">
                                        {{ $providerTypes[$selectedType] }}
                                        <a href="{{ request()->fullUrlWithQuery(['provider_type' => null]) }}">×</a>
                                    </span>
                                @endif
                            @endif

                            @if(request('remote') == 1)
                                <span class="chip">
                                    {{ __('messages.public.remote_work') }}
                                    <a href="{{ request()->fullUrlWithQuery(['remote' => null]) }}">×</a>
                                </span>
                            @endif
                        </div>
                    @endif

                    <!-- Results List -->
                    @if($profiles->count() > 0)
                        <div class="providers-list">
                            <x-provider-grid :providers="$profiles" :columns="1" />
                        </div>

                        @if($profiles->hasPages())
                            <div class="pagination-area">
                                {{ $profiles->links('pagination::tailwind') }}
                            </div>
                        @endif
                    @else
                        <!-- Empty State -->
                        <div class="empty-state">
                            <div class="empty-icon"></div>
                            <h3>{{ __('messages.public.no_results') }}</h3>
                            <p>
                                @if(request('keyword'))
                                    {{ __('messages.public.no_results_for_keyword', ['keyword' => request('keyword')]) }}
                                @else
                                    {{ __('messages.public.no_results_found') }}
                                @endif
                            </p>
                            <a href="{{ route('home') }}" class="empty-btn">
                                {{ __('messages.public.back_to_home') }}
                            </a>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </div>
</div>

<style>
    .search-page {
        background: #ffffff;
        min-height: 100vh;
    }

    .search-header {
        padding: 2rem 0 1.2rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .search-header h1 {
        margin: 0 0 0.4rem;
        color: #0f172a;
        font-size: 1.7rem;
        font-weight: 900;
        line-height: 1.1;
        letter-spacing: -0.01em;
    }

    .search-header p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .search-main {
        padding: 1.5rem 0 2.5rem;
    }

    .search-container {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 1.8rem;
        align-items: start;
    }

    [dir="rtl"] .search-container {
        grid-template-columns: 1fr 300px;
    }

    .search-sidebar {
        /* Compact sidebar */
    }

    .filter-box {
        padding: 1rem;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        top: 5.5rem;
    }

    .search-results {
        min-width: 0;
    }

    .results-summary {
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .results-summary h2 {
        margin: 0;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .results-summary p {
        margin: 0.3rem 0 0;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .results-summary strong {
        color: #ff7a1a;
        font-weight: 700;
    }

    .filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.35rem 0.6rem;
        border-radius: 20px;
        background: #fef3e2;
        color: #d97706;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .chip a {
        color: inherit;
        text-decoration: none;
        opacity: 0.7;
        margin-left: 0.15rem;
    }

    .chip a:hover {
        opacity: 1;
    }

    .providers-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .pagination-area {
        margin-top: 1.5rem;
    }

    .empty-state {
        padding: 2rem;
        border-radius: 14px;
        background: #f9fafb;
        text-align: center;
    }

    .empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 1rem;
        border-radius: 14px;
        background: rgba(255, 122, 26, 0.08);
    }

    .empty-state h3 {
        margin: 0 0 0.5rem;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .empty-state p {
        max-width: 340px;
        margin: 0 auto 1rem;
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.5;
        font-weight: 500;
    }

    .empty-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 40px;
        padding: 0 1.2rem;
        border-radius: 10px;
        background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 900;
        text-decoration: none;
        box-shadow: 0 6px 16px rgba(255, 107, 26, 0.16);
        transition: 0.15s ease;
    }

    .empty-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(255, 107, 26, 0.24);
    }

    /* === RESPONSIVE === */
    @media (max-width: 1024px) {
        .search-container {
            grid-template-columns: 260px 1fr;
            gap: 1.5rem;
        }

        [dir="rtl"] .search-container {
            grid-template-columns: 1fr 260px;
        }
    }

    @media (max-width: 768px) {
        .search-page {
            padding-top: 0;
        }

        .search-header {
            padding: 1.2rem 0 0.8rem;
        }

        .search-header h1 {
            font-size: 1.4rem;
        }

        .search-main {
            padding: 1rem 0 2rem;
        }

        .search-container {
            grid-template-columns: 1fr;
            gap: 1.2rem;
        }

        [dir="rtl"] .search-container {
            grid-template-columns: 1fr;
        }

        .filter-box {
            position: static;
            padding: 0.9rem;
        }

        .empty-state {
            padding: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1rem;
        }

        .empty-state p {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .search-page {
            padding-top: 0;
        }

        .search-header {
            padding: 0.8rem 0 0.6rem;
        }

        .search-header h1 {
            font-size: 1.2rem;
        }

        .search-main {
            padding: 0.8rem 0 1.5rem;
        }

        .search-container {
            gap: 1rem;
        }

        .filter-box {
            padding: 0.8rem;
        }

        .results-summary h2 {
            font-size: 1rem;
        }

        .empty-state {
            padding: 1.2rem;
        }
    }
</style>

@endsection
