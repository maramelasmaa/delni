@extends('public.layout')

@section('title', $profile->business_name . ' - ' . config('app.name'))

@section('content')
<!-- Inline styles moved to components.css -->

<div class="provider-hero">
    @if($profile->cover_image)
        <img src="{{ asset('storage/' . $profile->cover_image) }}" alt="{{ $profile->business_name }}">
    @endif
</div>

<div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">

            {{-- Provider Header --}}
            <div class="card provider-header-card mb-4">
                <div class="card-body p-4">
                    <div class="provider-header-body d-flex gap-4 align-items-start">

                        @if($profile->logo)
                            <img src="{{ asset('storage/' . $profile->logo) }}" alt="{{ $profile->business_name }}" class="provider-logo">
                        @else
                            <div class="provider-logo-fallback">
                                {{ mb_substr($profile->business_name, 0, 1) }}
                            </div>
                        @endif

                        <div class="flex-grow-1">
                            <h1 class="h3 fw-bold mb-1">{{ $profile->business_name }}</h1>

                            @if($profile->user?->name)
                                <p class="text-muted mb-3">{{ $profile->user->name }}</p>
                            @endif

                            <div class="meta-pills mb-3">
                                @if($profile->category)
                                    <span class="meta-pill">
                                        <x-render-icon icon="heroicon-o-briefcase" />
                                        {{ $profile->category->localized_name }}
                                    </span>
                                @endif

                                @if($profile->city)
                                    <span class="meta-pill">
                                        <x-render-icon :icon="$profile->city->icon ?: 'heroicon-o-map-pin'" />
                                        {{ $profile->city->localized_name }}
                                    </span>
                                @endif

                                @if($profile->offers_remote_work)
                                    <span class="meta-pill">
                                        <x-render-icon icon="heroicon-o-globe-alt" />
                                        {{ __('messages.public.remote_work') }}
                                    </span>
                                @endif
                            </div>

                            @if($profile->stats)
                                <div class="rating-line">
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span style="{{ $i <= floor($profile->stats->rating_avg) ? '' : 'opacity:.28;' }}">★</span>
                                        @endfor
                                    </div>

                                    <strong class="text-dark">{{ number_format($profile->stats->rating_avg, 1) }}</strong>

                                    <span>
                                        ({{ $profile->stats->reviews_count }} {{ __('messages.public.reviews') }})
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bio --}}
            @if($profile->bio)
                <div class="card provider-section-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">{{ __('messages.public.bio') }}</h3>
                        <p class="text-muted mb-0">{{ $profile->bio }}</p>
                    </div>
                </div>
            @endif

            {{-- Service Area --}}
            @if($profile->service_area_note)
                <div class="card provider-section-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 fw-bold mb-3">{{ __('messages.public.service_area') }}</h3>
                        <p class="text-muted mb-0">{{ $profile->service_area_note }}</p>
                    </div>
                </div>
            @endif

            {{-- Details --}}
            <div class="card provider-section-card mb-4">
                <div class="card-body p-4">
                    <h3 class="h5 fw-bold mb-3">{{ __('messages.public.details') }}</h3>

                    <div class="row g-3">
                        @if($profile->category)
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">{{ __('messages.public.category') }}</small>
                                <strong>{{ $profile->category->localized_name }}</strong>
                            </div>
                        @endif

                        @if($profile->city)
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">{{ __('messages.public.city') }}</small>
                                <strong>{{ $profile->city->localized_name }}</strong>
                            </div>
                        @endif

                        @if($profile->subcategories->isNotEmpty())
                            <div class="col-12">
                                <small class="text-muted d-block mb-2">{{ __('messages.public.subcategories') }}</small>
                                <div class="meta-pills">
                                    @foreach($profile->subcategories as $subcategory)
                                        <span class="meta-pill">{{ $subcategory->localized_name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Portfolio --}}
            @if($portfolioItems->isNotEmpty())
                <section class="mb-4">
                    <h2 class="h4 fw-bold mb-3 section-title-icon d-flex align-items-center gap-2">
                        <x-render-icon icon="heroicon-o-photo" />
                        {{ __('messages.public.portfolio') }}
                    </h2>

                    <small class="text-muted d-block mb-3">اضغط على أي عمل لمشاهدة جميع الصور</small>

                    <div class="row g-4">
                        @foreach($portfolioItems as $item)
                            <div class="col-md-6">
                                <div class="card h-100 provider-section-card" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#portfolio-modal-{{ $item->id }}">
                                    @if($item->images->isNotEmpty())
                                        <img src="{{ asset('storage/' . $item->images->first()->path) }}" alt="{{ $item->title }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top d-flex align-items-center justify-content-center" style="height:200px;background:#f8fafc;color:#cbd5e1;">
                                            <x-render-icon icon="heroicon-o-photo" style="width: 48px; height: 48px;" />
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <h5 class="card-title">{{ $item->title }}</h5>

                                        @if($item->short_description)
                                            <p class="card-text text-muted small">{{ $item->short_description }}</p>
                                        @endif

                                        @if($item->images->count() > 1)
                                            <small class="text-muted">
                                                {{ $item->images->count() }} صور
                                            </small>
                                        @endif

                                        @if($item->main_url || $item->link)
                                            <div class="mt-2">
                                                <a href="{{ $item->main_url ?: $item->link }}" target="_blank" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();">
                                                    {{ __('messages.public.view_link') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="portfolio-modal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content border-0">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title">{{ $item->title }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body p-0">
                                            @if($item->images->isNotEmpty())
                                                <div id="portfolio-carousel-{{ $item->id }}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500">
                                                    <div class="carousel-inner">
                                                        @foreach($item->images as $index => $image)
                                                            <div class="carousel-item @if($index === 0) active @endif">
                                                                <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->alt ?: $item->title }}" class="d-block w-100" style="max-height:500px;object-fit:contain;background:#f8fafc;">
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    @if($item->images->count() > 1)
                                                        <button class="carousel-control-prev" type="button" data-bs-target="#portfolio-carousel-{{ $item->id }}" data-bs-slide="prev">
                                                            <span class="carousel-control-prev-icon"></span>
                                                        </button>

                                                        <button class="carousel-control-next" type="button" data-bs-target="#portfolio-carousel-{{ $item->id }}" data-bs-slide="next">
                                                            <span class="carousel-control-next-icon"></span>
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center justify-content-center" style="height:400px;background:#f8fafc;color:#cbd5e1;">
                                                    <x-render-icon icon="heroicon-o-photo" style="width: 64px; height: 64px;" />
                                                </div>
                                            @endif
                                        </div>

                                        @if($item->description || $item->main_url || $item->link)
                                            <div class="modal-body border-top">
                                                @if($item->description)
                                                    <p class="text-muted mb-3">{{ $item->description }}</p>
                                                @endif

                                                @if($item->main_url || $item->link)
                                                    <a href="{{ $item->main_url ?: $item->link }}" target="_blank" class="btn btn-primary">
                                                        {{ __('messages.public.view_link') }} ↗
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Links --}}
            @if($links->isNotEmpty())
                <section class="mb-4">
                    <h2 class="h4 fw-bold mb-3">{{ __('messages.public.links') }}</h2>

                    <div class="row g-3">
                        @foreach($links as $link)
                            <div class="col-12">
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary w-100 text-start">
                                    {{ $link->label ?: $link->url }}
                                    <span class="float-end">↗</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Credentials --}}
            @if($credentials->isNotEmpty())
                <section class="mb-4">
                    <h2 class="h4 fw-bold mb-3">{{ __('messages.public.credentials') }}</h2>

                    <div class="row g-3">
                        @foreach($credentials as $credential)
                            <div class="col-12">
                                <div class="card provider-section-card">
                                    <div class="card-body">
                                        @if($credential->title)
                                            <h5 class="card-title">{{ $credential->title }}</h5>
                                        @endif

                                        @if($credential->issuer)
                                            <small class="text-muted d-block">{{ $credential->issuer }}</small>
                                        @endif

                                        @if($credential->issue_date)
                                            <small class="text-muted d-block">{{ $credential->issue_date->toDateString() }}</small>
                                        @endif

                                        @if($credential->notes)
                                            <p class="card-text text-muted mt-2 mb-0">{{ $credential->notes }}</p>
                                        @endif

                                        @if($credential->verification_url)
                                            <a href="{{ $credential->verification_url }}" target="_blank" class="btn btn-sm btn-link p-0 mt-2">
                                                {{ __('messages.public.verify') }} ↗
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Reviews --}}
            <section class="mb-4">
                <h2 class="h4 fw-bold mb-3">{{ __('messages.public.reviews') }} ({{ $reviews->count() }})</h2>

                <div class="card provider-section-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-3">{{ __('messages.public.leave_review') }}</h3>

                        @if(!auth()->check())
                            <div class="alert alert-info mb-0">
                                <p class="mb-2">{{ __('messages.public.login_to_review') }}</p>
                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">{{ __('messages.login') }}</a>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm">{{ __('messages.register') }}</a>
                            </div>
                        @elseif(!auth()->user()->hasRole('user'))
                            <div class="alert alert-warning mb-0">
                                {{ __('messages.public.providers_cannot_review') }}
                            </div>
                        @elseif($profile->user_id === auth()->id())
                            <div class="alert alert-warning mb-0">
                                {{ __('messages.public.cannot_review_own') }}
                            </div>
                        @else
                            <form method="POST" action="{{ route('review.store', $profile) }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="rating" class="form-label">{{ __('messages.public.rating') }}</label>
                                    <select id="rating" name="rating" class="form-select" required>
                                        <option value="">{{ __('messages.public.select') }}</option>
                                        @for($rating = 5; $rating >= 1; $rating--)
                                            <option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} / 5.0</option>
                                        @endfor
                                    </select>

                                    @error('rating')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="comment" class="form-label">{{ __('messages.public.review_comment') }}</label>
                                    <textarea id="comment" name="comment" class="form-control" rows="4" maxlength="2000" placeholder="{{ __('messages.public.share_your_experience') }}">{{ old('comment') }}</textarea>

                                    @error('comment')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    {{ __('messages.public.submit_review') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @forelse($reviews as $review)
                    <div class="card provider-section-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2 gap-3">
                                <strong>{{ $review->user?->name ?? __('messages.public.anonymous') }}</strong>

                                <div class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                            </div>

                            @if($review->comment)
                                <p class="text-muted mb-2">{{ $review->comment }}</p>
                            @endif

                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>

                            @can('flag', $review)
                                <form method="POST" action="{{ route('reviews.flag', $review) }}" class="mt-3">
                                    @csrf

                                    <label for="flag-reason-{{ $review->id }}" class="form-label small text-muted">
                                        {{ __('messages.public.flag_review') }}
                                    </label>

                                    <textarea id="flag-reason-{{ $review->id }}" name="reason" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" required></textarea>

                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ __('messages.public.submit_flag') }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @empty
                    <x-empty-state
                        icon="heroicon-o-chat-bubble-left-right"
                        title="{{ __('messages.public.no_reviews') }}"
                        message="{{ __('messages.public.be_first_review') }}"
                    />
                @endforelse
            </section>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <x-contact-card :provider="$profile" />
        </div>
    </div>
</div>
@endsection
