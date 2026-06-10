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

<section class="subcategory-hero-header">
    <div class="container">
        <div class="subcategory-hero-inner-grid">
            <div class="subcategory-meta-details">
                <h1 class="subcategory-title-main">
                    {{ $subcategory->localized_name }}
                </h1>
                @if($subcategory->description)
                    <p class="subcategory-desc-para">{{ $subcategory->description }}</p>
                @endif
                <div class="subcategory-badge-pill">
                    <x-render-icon icon="heroicon-o-users" class="badge-icon-node" />
                    <span>{{ $profiles->total() ?? 0 }} {{ __('messages.public.professionals') }}</span>
                </div>
            </div>

            <div class="subcategory-graphic-container">
                <div class="graphic-circle-backdrop">
                    <x-render-icon :icon="$subcategory->icon ?: 'heroicon-o-document-text'" class="graphic-svg" />
                </div>
            </div>
        </div>
    </div>
</section>

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

    .subcategory-hero-header {
        padding: 3.5rem 0;
        background: linear-gradient(135deg, rgba(11, 26, 52, 0.93), rgba(20, 40, 77, 0.97)),
                    url('{{ asset('images/herobackground2.png') }}') center/cover no-repeat;
        color: #fff;
    }

    .subcategory-hero-inner-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 2rem;
        align-items: center;
    }

    .subcategory-meta-details {
        max-width: 700px;
    }

    .subcategory-title-main {
        margin: 0 0 1rem;
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 950;
        line-height: 1.15;
        letter-spacing: -0.04em;
    }

    .subcategory-desc-para {
        margin: 0 0 1.25rem;
        color: rgba(255, 255, 255, 0.8);
        font-size: 1rem;
        line-height: 1.8;
        font-weight: 600;
    }

    .subcategory-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.75rem 1.25rem;
        border-radius: 999px;
        background: rgba(241, 98, 15, 0.2);
        border: 1px solid rgba(241, 98, 15, 0.3);
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.9rem;
        font-weight: 700;
    }

    .badge-icon-node {
        width: 18px;
        height: 18px;
        color: #FF9D66;
    }

    .subcategory-graphic-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .graphic-circle-backdrop {
        width: 160px;
        height: 160px;
        border-radius: 24px;
        background: rgba(241, 98, 15, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FF9D66;
    }

    .graphic-svg {
        width: 80px;
        height: 80px;
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

        .subcategory-hero-inner-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .graphic-circle-backdrop {
            width: 140px;
            height: 140px;
        }

        .graphic-svg {
            width: 70px;
            height: 70px;
        }
    }

    @media (max-width: 768px) {
        .subcategory-hero-header {
            padding: 2rem 0;
        }

        .subcategory-title-main {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
        }

        .subcategory-desc-para {
            font-size: 0.9rem;
        }

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

        .subcategory-hero-header {
            padding: 1.5rem 0;
        }

        .subcategory-title-main {
            font-size: clamp(1.25rem, 3vw, 1.75rem);
            margin-bottom: 0.75rem;
        }

        .subcategory-desc-para {
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
        }

        .graphic-circle-backdrop {
            width: 120px;
            height: 120px;
        }

        .graphic-svg {
            width: 60px;
            height: 60px;
        }
    }
</style>
@endsection
