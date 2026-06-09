@props(['categories' => collect(), 'active' => null])

<div class="category-nav-section">
    <div class="category-nav-container">
        <div class="category-nav-scroll">
            @forelse($categories as $category)
                <a
                    href="{{ route('public.category', $category->slug) }}"
                    class="category-nav-item {{ $active === $category->id ? 'is-active' : '' }}"
                    title="{{ $category->localized_name ?? $category->name }}"
                >
                    <span class="category-nav-icon">
                        @if($category->icon)
                            <x-render-icon :icon="$category->icon" class="w-5 h-5" />
                        @else
                            <span class="icon-placeholder">📁</span>
                        @endif
                    </span>
                    <span class="category-nav-text">
                        <span class="category-nav-name">{{ $category->localized_name ?? $category->name }}</span>
                        <span class="category-nav-count">{{ $category->discoverable_profiles_count ?? 0 }}</span>
                    </span>
                </a>
            @empty
                <div class="category-nav-empty">
                    {{ __('messages.public.no_categories') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .category-nav-section {
                background: #f8fafc;
                border-bottom: 1px solid #e5e7eb;
                padding: 2rem 0;
                overflow-x: auto;
            }

            .category-nav-container {
                max-width: 1320px;
                margin: 0 auto;
                padding: 0 1rem;
            }

            .category-nav-scroll {
                display: flex;
                gap: 1rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
            }

            /* Hide scrollbar but keep functionality */
            .category-nav-scroll::-webkit-scrollbar {
                height: 4px;
            }

            .category-nav-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .category-nav-scroll::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 2px;
            }

            .category-nav-item {
                display: inline-flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.75rem 1.25rem;
                background: #ffffff;
                border: 1.5px solid #e5e7eb;
                border-radius: 999px;
                text-decoration: none;
                color: #475569;
                font-weight: 600;
                font-size: 0.9rem;
                transition: all 0.2s ease;
                white-space: nowrap;
                flex-shrink: 0;
                cursor: pointer;
            }

            .category-nav-item:hover {
                background: #f1f5f9;
                border-color: #ff7a1a;
                color: #ff7a1a;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(255, 122, 26, 0.12);
            }

            .category-nav-item.is-active {
                background: linear-gradient(135deg, #ff8533 0%, #ff6b1a 100%);
                border-color: #ff6b1a;
                color: #ffffff;
                box-shadow: 0 6px 16px rgba(255, 107, 26, 0.25);
            }

            .category-nav-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
                flex-shrink: 0;
            }

            .category-nav-icon svg {
                width: 100%;
                height: 100%;
                display: block;
            }

            .icon-placeholder {
                font-size: 1rem;
                line-height: 1;
            }

            .category-nav-text {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.15rem;
            }

            .category-nav-name {
                display: block;
                font-weight: 600;
                font-size: 0.9rem;
            }

            .category-nav-count {
                display: block;
                font-size: 0.75rem;
                opacity: 0.7;
                font-weight: 500;
            }

            .category-nav-empty {
                padding: 2rem;
                text-align: center;
                color: #94a3b8;
            }

            @media (max-width: 768px) {
                .category-nav-section {
                    padding: 1.5rem 0;
                }

                .category-nav-item {
                    padding: 0.6rem 1rem;
                    font-size: 0.85rem;
                }

                .category-nav-name {
                    font-size: 0.85rem;
                }

                .category-nav-count {
                    font-size: 0.7rem;
                }
            }

            /* RTL Support */
            [dir="rtl"] .category-nav-text {
                align-items: flex-end;
            }

            [dir="rtl"] .category-nav-scroll {
                flex-direction: row-reverse;
            }
        </style>
    @endpush
@endonce
