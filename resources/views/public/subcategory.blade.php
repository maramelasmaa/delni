@extends('public.layout')

@section('title', $subcategory->localized_name . ' - ' . config('app.name'))

@section('content')
<div class="breadcrumb-nav-wrapper">
    <div class="container">
        <nav aria-label="breadcrumb" class="modern-breadcrumb">
            <a href="{{ route('home') }}" class="breadcrumb-link">{{ __('messages.public.home') }}</a>
            <span class="breadcrumb-divider">/</span>
            @if($category = $subcategory->category)
                <a href="{{ route('public.category', $category->slug) }}" class="breadcrumb-link">{{ $category->localized_name }}</a>
                <span class="breadcrumb-divider">/</span>
            @endif
            <span class="breadcrumb-current">{{ $subcategory->localized_name }}</span>
        </nav>
    </div>
</div>

<section class="archive-split-workspace">
    <div class="container">
        <div class="workspace-layout-grid">
            <aside class="workspace-sidebar-sticky">
                <x-search-filters :cities="$cities ?? null" />
            </aside>

            <main class="workspace-main-content">
                @if($profiles && $profiles->count() > 0)
                    <x-provider-grid :providers="$profiles" :columns="1" />

                    @if($profiles->hasPages())
                        <nav aria-label="pagination" class="pagination-wrapper">
                            {{ $profiles->appends(request()->query())->links('pagination::tailwind') }}
                        </nav>
                    @endif
                @else
                    <x-empty-state
                        title="{{ __('messages.public.no_providers_found') }}"
                        message="{{ __('messages.public.try_different_search') }}"
                        actionLabel="{{ __('messages.public.search') }}"
                        actionUrl="{{ route('public.search') }}"
                    />
                @endif
            </main>
        </div>
    </div>
</section>

<style>
    .breadcrumb-nav-wrapper {
        padding: 1rem 0;
        background: #FCFBFB;
        border-bottom: 1px solid #E7E7E7;
    }

    .modern-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .breadcrumb-link {
        color: #5D5959;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.18s ease;
    }

    .breadcrumb-link:hover {
        color: #F1620F;
    }

    .breadcrumb-divider {
        color: #E7E7E7;
        margin: 0 0.25rem;
    }

    .breadcrumb-current {
        color: #0B1A34;
        font-weight: 950;
    }

    .archive-split-workspace {
        padding: 2rem 0 4rem;
        background: #FCFBFB;
    }

    .workspace-layout-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    .workspace-sidebar-sticky {
        position: sticky;
        top: 100px;
    }

    .workspace-main-content {
        min-width: 0;
    }

    .pagination-wrapper {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 1024px) {
        .workspace-layout-grid {
            grid-template-columns: 1fr;
        }

        .workspace-sidebar-sticky {
            position: static;
            top: auto;
        }
    }

    @media (max-width: 768px) {
        .archive-split-workspace {
            padding: 1.5rem 0 3rem;
        }
    }

    @media (max-width: 640px) {
        .breadcrumb-nav-wrapper {
            padding: 0.75rem 0;
        }

        .modern-breadcrumb {
            font-size: 0.8rem;
            gap: 0.35rem;
        }

        .breadcrumb-divider {
            margin: 0 0.2rem;
        }
    }
</style>
@endsection
