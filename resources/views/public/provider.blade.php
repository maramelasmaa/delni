@extends('public.layout')

@section('title', ($profile->business_name ?? $profile->user?->name ?? 'مقدم خدمة') . ' - ' . config('app.name'))

@section('content')
@php
    $businessName = $profile->business_name ?? $profile->user?->name ?? 'مقدم خدمة';

    $logo = $profile->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($profile->logo) : null;
    $cover = $profile->cover_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($profile->cover_image) : null;

    $categoryName = $profile->category ? ($profile->category->localized_name ?? $profile->category->name) : null;
    $cityName = $profile->city ? ($profile->city->localized_name ?? $profile->city->name) : null;
    $subcategories = $profile->subcategories ?? collect();

    $phoneNumber = $profile->phone ? preg_replace('/\s+/', '', $profile->phone) : null;
    $whatsappNumber = $profile->whatsapp ? preg_replace('/[^0-9]/', '', $profile->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وصلت لملفك عبر دلني وأرغب بالاستفسار عن الخدمة.');

    $portfolioItems = ($portfolioItems ?? collect())->filter(fn ($item) => $item->is_active ?? true)->take(4);
    $reviews = $reviews ?? collect();
    $rating = $reviews->isNotEmpty()
        ? round((float) $reviews->avg('rating'), 1)
        : 0.0;
    $reviewsCount = $reviews->count();
    $credentials = $credentials ?? ($profile->credentials ?? collect());
    $sortedReviews = $reviews->sortByDesc('created_at')->values();
    $isFavorited = (bool) ($isFavorited ?? false);
    $previousUrl = url()->previous();
    $invalidBackUrls = ['/sw.js', '/offline.html', '/favicon.ico'];
    $isInvalidBackUrl = false;
    foreach ($invalidBackUrls as $invalidPath) {
        if (str_ends_with($previousUrl, $invalidPath)) {
            $isInvalidBackUrl = true;
            break;
        }
    }
    $backUrl = ($previousUrl !== url()->current() && !$isInvalidBackUrl) ? $previousUrl : route('public.categories');

    $contactActions = collect([
        $whatsappNumber ? [
            'label'    => __('messages.public.whatsapp'),
            'url'      => "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}",
            'icon'     => 'heroicon-o-chat-bubble-left-ellipsis',
            'class'    => 'is-whatsapp',
            'external' => true,
            'primary'  => true,
        ] : null,
        $phoneNumber ? [
            'label'    => __('messages.public.call'),
            'url'      => "tel:{$phoneNumber}",
            'icon'     => 'heroicon-o-phone',
            'class'    => '',
            'external' => false,
            'primary'  => true,
        ] : null,
        $profile->website ? [
            'label'    => __('messages.public.website'),
            'url'      => $profile->website,
            'icon'     => 'heroicon-o-globe-alt',
            'class'    => '',
            'external' => true,
            'primary'  => false,
        ] : null,
        $profile->map_url ? [
            'label'    => __('messages.public.directions'),
            'url'      => $profile->map_url,
            'icon'     => 'heroicon-o-map-pin',
            'class'    => '',
            'external' => true,
            'primary'  => false,
        ] : null,
        $profile->instagram ? [
            'label'    => __('filament.link_types.instagram'),
            'url'      => $profile->instagram,
            'icon'     => 'brand-instagram',
            'class'    => '',
            'external' => true,
            'primary'  => false,
        ] : null,
        $profile->facebook ? [
            'label'    => __('filament.link_types.facebook'),
            'url'      => $profile->facebook,
            'icon'     => 'brand-facebook',
            'class'    => '',
            'external' => true,
            'primary'  => false,
        ] : null,
        $profile->linkedin ? [
            'label'    => __('filament.link_types.linkedin'),
            'url'      => $profile->linkedin,
            'icon'     => 'brand-linkedin',
            'class'    => '',
            'external' => true,
            'primary'  => false,
        ] : null,
    ])->filter();

    // Facts are shown in header meta to prevent repetition.

@endphp

<article class="provider-profile">
    <section class="pp-identity">
        <div class="pp-identity__media">
            <a href="{{ $backUrl }}" class="pp-back" aria-label="رجوع">
                <x-render-icon icon="heroicon-o-arrow-right" />
            </a>

            @auth
                <button
                    class="pp-favorite pp-favorite-btn {{ $isFavorited ? 'is-favorited' : '' }}"
                    data-profile-id="{{ $profile->id }}"
                    data-toggle-url="{{ route('favorites.toggle', $profile) }}"
                    type="button"
                    aria-label="{{ $isFavorited ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة' }}"
                >
                    <span class="pp-favorite-icon pp-favorite-icon--outline">
                        <x-render-icon icon="app-heart" />
                    </span>
                    <span class="pp-favorite-icon pp-favorite-icon--filled">
                        <x-render-icon icon="app-heart-filled" />
                    </span>
                </button>
            @else
                <button class="pp-favorite pp-favorite-btn is-favorite-guest" type="button" aria-label="أضف للمفضلة">
                    <span class="pp-favorite-icon pp-favorite-icon--outline">
                        <x-render-icon icon="app-heart" />
                    </span>
                </button>
            @endauth

            @if($cover)
                <img src="{{ $cover }}" alt="{{ $businessName }}" class="pp-cover" loading="eager" decoding="async">
            @endif
        </div>

        <div class="pp-identity__content">
            <div class="pp-avatar">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $businessName }}" loading="eager" decoding="async">
                @else
                    <span>{{ mb_substr($businessName, 0, 1) }}</span>
                @endif
            </div>

            <div class="pp-title">
                @if($categoryName)
                    <span class="pp-eyebrow">{{ $categoryName }}</span>
                @endif

                <h1>{{ $businessName }}</h1>

                @if($reviewsCount > 0)
                    <a href="#reviews" class="pp-header-rating">
                        <span class="pp-header-rating__star">★</span>
                        <strong class="pp-header-rating__score">{{ number_format($rating, 1) }}</strong>
                        <span class="pp-header-rating__count">({{ $reviewsCount }})</span>
                    </a>
                @endif

                @php
                    $metaParts = collect([
                        $cityName ? ['icon' => 'heroicon-o-map-pin', 'value' => $cityName] : null,
                        $profile->provider_type ? ['icon' => 'heroicon-o-building-office-2', 'value' => \App\Models\ProviderType::labelFor($profile->provider_type)] : null,
                        $profile->experience_years ? ['icon' => 'heroicon-o-briefcase', 'value' => $profile->experience_years . ' سنوات خبرة'] : null,
                        $profile->offers_remote_work ? ['icon' => 'heroicon-o-globe-alt', 'value' => 'خدمة عن بعد'] : null,
                    ])->filter();
                @endphp
                @if($metaParts->isNotEmpty())
                    <div class="pp-meta">
                        @foreach($metaParts as $part)
                            <span>
                                <x-render-icon :icon="$part['icon']" />
                                <span>{{ $part['value'] }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if($contactActions->isNotEmpty())
            <nav class="pp-actions" aria-label="طرق التواصل السريعة">
                <div class="pp-actions-primary-row">
                    @foreach($contactActions->where('primary', true) as $action)
                        <a href="{{ $action['url'] }}"
                           class="pp-action-primary {{ $action['class'] }}"
                           aria-label="{{ $action['label'] }}"
                           @if($action['external']) target="_blank" rel="noopener noreferrer nofollow" @endif>
                            <x-render-icon :icon="$action['icon']" />
                            <span>{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                @if($contactActions->where('primary', false)->isNotEmpty())
                    <div class="pp-actions-secondary-row">
                        @foreach($contactActions->where('primary', false) as $action)
                            <a href="{{ $action['url'] }}"
                               class="pp-action-icon {{ $action['class'] }}"
                               aria-label="{{ $action['label'] }}"
                               title="{{ $action['label'] }}"
                               @if($action['external']) target="_blank" rel="noopener noreferrer nofollow" @endif>
                                <x-render-icon :icon="$action['icon']" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </nav>
        @endif
    </section>

    <div class="pp-layout">
        <main class="pp-main">
            @if($profile->bio || $subcategories->isNotEmpty() || $profile->service_area_note || $profile->map_url)
                <section class="pp-card pp-info-group">
                    @if($profile->bio)
                        <div class="pp-info-section">
                            <h2 class="pp-info-section__title">
                                <x-render-icon icon="heroicon-o-user" />
                                <span>عن مقدم الخدمة</span>
                            </h2>
                            <p class="pp-text">{{ $profile->bio }}</p>
                        </div>
                    @endif

                    @if($subcategories->isNotEmpty())
                        <div class="pp-info-section">
                            <h2 class="pp-info-section__title">
                                <x-render-icon icon="heroicon-o-wrench-screwdriver" />
                                <span>الخدمات المتاحة</span>
                            </h2>
                            <div class="pp-service-list">
                                @foreach($subcategories as $subcategory)
                                    <a href="{{ route('public.subcategory', $subcategory->slug) }}">
                                        {{ $subcategory->localized_name ?? $subcategory->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($profile->service_area_note || $profile->map_url)
                        <div class="pp-info-section">
                            <h2 class="pp-info-section__title">
                                <x-render-icon icon="heroicon-o-map" />
                                <span>نطاق التغطية والعمل</span>
                            </h2>

                            @if($profile->service_area_note)
                                <p class="pp-note">{{ $profile->service_area_note }}</p>
                            @endif

                            @if($profile->map_url)
                                <a href="{{ $profile->map_url }}" class="pp-inline-link" target="_blank" rel="noopener noreferrer nofollow">
                                    <x-render-icon icon="heroicon-o-map-pin" />
                                    <span>فتح الموقع على الخريطة</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </section>
            @endif

            @if($portfolioItems->isNotEmpty() || $credentials->isNotEmpty())
                <section class="pp-card pp-showcase-group">
                    @if($portfolioItems->isNotEmpty())
                        <div class="pp-showcase-section">
                            <h2 class="pp-info-section__title">
                                <x-render-icon icon="heroicon-o-photo" />
                                <span>نماذج من الأعمال</span>
                            </h2>
                            <div class="pp-gallery">
                                @foreach($portfolioItems as $itemIdx => $item)
                                    @php
                                        $images = $item->images?->sortBy('sort_order')->values() ?? collect();
                                        $imgCount = $images->count();
                                        $sliderId = 'proj-' . $itemIdx;
                                    @endphp
                                    <article class="pp-proj">
                                        <div class="pp-proj-slider" id="{{ $sliderId }}">
                                            <div class="pp-proj-slides">
                                                @if($imgCount > 0)
                                                    @foreach($images as $img)
                                                        <div class="pp-proj-slide">
                                                            @php
                                                                $imageUrl = Storage::disk('public')->url($img->path);
                                                                $imageAlt = $img->alt ?: $item->title;
                                                            @endphp
                                                            <button
                                                                type="button"
                                                                class="pp-proj-image"
                                                                data-portfolio-image="{{ $imageUrl }}"
                                                                data-portfolio-alt="{{ e($imageAlt) }}"
                                                                aria-label="عرض صورة {{ $imageAlt }}"
                                                            >
                                                                <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" loading="lazy" decoding="async">
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="pp-proj-slide">
                                                        <div class="pp-gallery__empty"><x-render-icon icon="heroicon-o-photo" /></div>
                                                    </div>
                                                @endif
                                            </div>

                                            @if($imgCount > 1)
                                                <button type="button" class="pp-proj-nav pp-proj-nav--prev" data-slider-nav="{{ $sliderId }}" data-dir="-1" aria-label="الصورة السابقة">
                                                    <x-render-icon icon="heroicon-o-chevron-right" />
                                                </button>
                                                <button type="button" class="pp-proj-nav pp-proj-nav--next" data-slider-nav="{{ $sliderId }}" data-dir="1" aria-label="الصورة التالية">
                                                    <x-render-icon icon="heroicon-o-chevron-left" />
                                                </button>
                                                <span class="pp-proj-counter" aria-live="polite">1 / {{ $imgCount }}</span>
                                                <div class="pp-proj-dots" role="tablist" aria-label="صور المشروع">
                                                    @foreach($images as $di => $img)
                                                        <button class="pp-proj-dot {{ $di === 0 ? 'is-active' : '' }}"
                                                                data-slider="{{ $sliderId }}"
                                                                data-idx="{{ $di }}"
                                                                role="tab"
                                                                aria-label="صورة {{ $di + 1 }}"
                                                                aria-selected="{{ $di === 0 ? 'true' : 'false' }}"
                                                                type="button"></button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div class="pp-proj-info">
                                            <h3>{{ $item->title }}</h3>
                                            @if($item->short_description)
                                                <p>{{ $item->short_description }}</p>
                                            @elseif($item->description)
                                                <p>{{ Str::limit(strip_tags($item->description), 120) }}</p>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="pp-lightbox" id="portfolioLightbox" aria-hidden="true" role="dialog" aria-label="معاينة الصورة">
                                <button type="button" class="pp-lightbox__close" aria-label="إغلاق">
                                    <x-render-icon icon="heroicon-o-x-mark" />
                                </button>
                                <img src="" alt="">
                            </div>
                        </div>
                    @endif

                    @if($credentials->isNotEmpty())
                        <div class="pp-showcase-section {{ $portfolioItems->isNotEmpty() ? 'has-divider' : '' }}">
                            <h2 class="pp-info-section__title">
                                <x-render-icon icon="heroicon-o-academic-cap" />
                                <span>الشهادات والمؤهلات</span>
                            </h2>
                            <div class="pp-credentials">
                                @foreach($credentials as $credential)
                                    <article>
                                        <h3>{{ $credential->title }}</h3>
                                        @if($credential->issuer)<p>{{ $credential->issuer }}</p>@endif
                                        <div>
                                            @if($credential->issue_date)<span>{{ optional($credential->issue_date)->format('Y') }}</span>@endif
                                            @if($credential->verification_url)
                                                <a href="{{ $credential->verification_url }}" target="_blank" rel="noopener noreferrer nofollow">تحقق</a>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            <section id="reviews" class="pp-card">
                <x-profile-section-title title="التقييمات والآراء" />

                @if(session('success'))
                    <div class="pp-review-flash is-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="pp-review-flash is-error">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="pp-review-flash is-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="pp-review-summary">
                    <div class="pp-rs-score-wrap">
                        <span class="pp-rs-star" aria-hidden="true">★</span>
                        <span class="pp-rs-score">{{ number_format($rating, 1) }}</span>
                        <span class="pp-rs-label">المتوسط</span>
                    </div>
                    <div class="pp-rs-count">
                        <strong>{{ number_format($reviewsCount) }}</strong>
                        <span>آراء معتمدة</span>
                    </div>
                </div>

                @if(!auth()->check())
                    <div class="pp-review-notice">
                        <span>سجل الدخول لكتابة تقييم بعد التعامل مع مقدم الخدمة.</span>
                        <a href="{{ route('login') }}">تسجيل الدخول</a>
                    </div>
                @elseif(!auth()->user()->hasRole('user'))
                    <div class="pp-review-notice">مقدمو الخدمات لا يمكنهم كتابة تقييمات.</div>
                @elseif($profile->user_id === auth()->id())
                    <div class="pp-review-notice">لا يمكنك تقييم ملفك الخاص.</div>
                @elseif($userHasActiveReview ?? false)
                    <div class="pp-review-notice">لقد أضفت تقييماً لمقدم الخدمة هذا من قبل.</div>
                @else
                    <button type="button" class="pp-write-review-toggle" id="writeReviewToggleBtn">
                        <x-render-icon icon="heroicon-o-pencil-square" />
                        <span>كتابة تقييم أو رأي</span>
                    </button>

                    <form method="POST" action="{{ route('review.store', $profile) }}" class="pp-review-form is-collapsed" id="reviewForm" style="display: none;">
                        @csrf
                        <div class="pp-form-rating-row">
                            <span>التقييم بالنجوم:</span>
                            <div class="pp-star-selector" id="starSelector" dir="ltr">
                                @for($r = 1; $r <= 5; $r++)
                                    <button type="button" class="pp-star-btn" data-value="{{ $r }}" aria-label="تقييم {{ $r }} من 5">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                        </svg>
                                    </button>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="{{ old('rating') }}">
                        </div>

                        <textarea id="comment" name="comment" rows="2" maxlength="2000" placeholder="اكتب تعليقك هنا (اختياري)...">{{ old('comment') }}</textarea>
                        
                        <div class="pp-form-actions">
                            <button type="submit" id="submitReviewBtn">إرسال التقييم</button>
                            <button type="button" class="pp-form-cancel" id="cancelReviewBtn">إلغاء</button>
                        </div>
                    </form>
                @endif

                <div class="pp-review-list">
                    @forelse($sortedReviews as $index => $review)
                        <article class="{{ $index >= 3 ? 'is-hidden-review' : '' }}" data-review-item>
                            <div>
                                <strong>{{ $review->user?->name ?? $review->reviewer_name ?? 'مستخدم دلني' }}</strong>
                                @if($review->rating)
                                    <span class="pp-review-badge">★ {{ $review->rating }}</span>
                                @endif
                            </div>
                            @if($review->comment)<p>{{ $review->comment }}</p>@endif
                            @if($review->created_at)<small>{{ $review->created_at->diffForHumans() }}</small>@endif
                        </article>
                    @empty
                        <x-empty-state
                            icon="heroicon-o-star"
                            title="لا توجد تقييمات بعد"
                            message="ستظهر تقييمات العملاء هنا بعد اعتمادها."
                        />
                    @endforelse
                </div>

                @if($sortedReviews->count() > 3)
                    <button type="button" class="pp-show-more" id="showAllReviewsBtn">عرض كل التقييمات</button>
                @endif
            </section>
        </main>
    </div>
</article>

@push('styles')
<style>
    .provider-profile {
        max-width: 760px;
        margin-inline: auto;
        display: grid;
        gap: .75rem;
        padding-bottom: 2rem;
        width: 100%;
    }

    .pp-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: .75rem;
        width: 100%;
    }

    .pp-main {
        display: grid;
        gap: .75rem;
        width: 100%;
        min-width: 0;
    }

    .pp-card {
        padding: 1.25rem;
        border: 1px solid var(--delni-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: var(--delni-shadow-sm);
    }

    .pp-identity {
        position: relative;
        overflow: hidden;
        border: 1px solid var(--delni-border);
        border-radius: 16px;
        background: #fff;
        color: var(--delni-navy);
        box-shadow: var(--delni-shadow-sm);
        display: flex;
        flex-direction: column;
    }

    .pp-identity__media {
        position: relative;
        height: 140px;
        background: linear-gradient(135deg, #0B1A34 0%, #1E293B 100%);
    }
    @media (min-width: 640px) {
        .pp-identity__media {
            height: 180px;
        }
    }

    .pp-back, .pp-favorite-btn {
        position: absolute;
        top: .75rem;
        z-index: 2;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid rgba(15,23,42,.1);
        background: rgba(255,255,255,.92);
        color: var(--delni-navy);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        cursor: pointer;
        transition: all .2s ease;
    }

    .pp-back {
        inset-inline-start: .75rem;
    }
    .pp-favorite-btn {
        inset-inline-end: .75rem;
    }

    .pp-back:hover, .pp-favorite-btn:hover {
        background: #fff;
        transform: scale(1.05);
    }

    .pp-back svg, .pp-favorite-btn svg {
        width: 18px;
        height: 18px;
    }

    html[dir="rtl"] .pp-back svg {
        transform: scaleX(-1);
    }

    .pp-back span {
        display: none;
    }

    .pp-favorite-icon--filled {
        display: none;
    }
    .pp-favorite-btn.is-favorited .pp-favorite-icon--outline {
        display: none;
    }
    .pp-favorite-btn.is-favorited .pp-favorite-icon--filled {
        display: inline-flex;
        color: var(--delni-primary);
    }

    .pp-cover {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: .92;
    }

    .pp-identity__media::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(11,26,52,.35), rgba(11,26,52,.05));
    }

    .pp-identity__content {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 1.25rem;
        padding: 1.25rem;
        background: #fff;
    }
    @media (max-width: 640px) {
        .pp-identity__content {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 0.75rem;
        }
    }

    .pp-avatar {
        width: 80px;
        height: 80px;
        display: grid;
        place-items: center;
        overflow: hidden;
        margin-top: -3.5rem;
        border-radius: 16px;
        border: 4px solid #fff;
        background: linear-gradient(135deg, #0B1A34 0%, #1E293B 100%);
        box-shadow: 0 10px 30px rgba(15,23,42,.2);
        flex-shrink: 0;
        z-index: 2;
    }
    .pp-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pp-avatar span { color: var(--delni-primary); font-size: 2.2rem; font-weight: 950; }

    .pp-title {
        display: flex;
        flex-direction: column;
        gap: .35rem;
        flex: 1;
        min-width: 0;
    }
    @media (max-width: 640px) {
        .pp-title {
            align-items: center;
        }
    }

    .pp-eyebrow {
        width: fit-content;
        color: var(--delni-primary);
        border: 1px solid rgba(241,98,15,.2);
        border-radius: 999px;
        background: rgba(241,98,15,.06);
        padding: .25rem .5rem;
        font-size: .7rem;
        font-weight: 850;
        line-height: 1.1;
    }

    .pp-title h1 {
        margin: 0;
        color: var(--delni-navy);
        font-size: 1.4rem;
        line-height: 1.25;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .pp-header-rating {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        font-size: .8rem;
        color: var(--delni-navy);
        width: fit-content;
        text-decoration: none;
    }
    .pp-header-rating__star {
        color: #F59E0B;
        font-size: .95rem;
    }
    .pp-header-rating__score {
        font-weight: 900;
    }
    .pp-header-rating__count {
        color: var(--delni-muted);
    }

    .pp-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-top: .2rem;
    }
    @media (max-width: 640px) {
        .pp-meta {
            justify-content: center;
        }
    }

    .pp-meta > span {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .2rem .5rem;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        background: #F8FAFC;
        color: #475569;
        font-size: .72rem;
        font-weight: 750;
    }
    .pp-meta svg {
        width: 14px;
        height: 14px;
        color: var(--delni-primary);
        flex-shrink: 0;
    }

    .pp-actions {
        display: flex;
        flex-direction: column;
        gap: .5rem;
        padding: 1rem 1.25rem 1.25rem;
        border-top: 1px solid var(--delni-border);
        background: #FAFAFA;
    }

    .pp-actions-primary-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: .5rem;
    }

    .pp-action-primary {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        padding: .5rem 1rem;
        border-radius: 12px;
        border: 1px solid #E2E8F0;
        background: #fff;
        color: var(--delni-navy);
        font-size: .84rem;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        transition: all .2s ease;
    }
    .pp-action-primary:hover {
        border-color: #CBD5E1;
        background: #F8FAFC;
        transform: translateY(-1px);
    }

    .pp-action-primary.is-whatsapp {
        background: linear-gradient(135deg, #22C55E 0%, #16A34A 100%);
        border-color: #22C55E;
        color: #fff;
    }
    .pp-action-primary.is-whatsapp:hover {
        border-color: #16A34A;
        background: linear-gradient(135deg, #16A34A 0%, #15803D 100%);
    }

    .pp-action-primary svg {
        width: 18px;
        height: 18px;
    }

    .pp-actions-secondary-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: .5rem;
        margin-top: .25rem;
    }

    .pp-action-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid #E2E8F0;
        background: #fff;
        color: #475569;
        text-decoration: none;
        transition: all .2s ease;
    }
    .pp-action-icon:hover {
        border-color: #CBD5E1;
        background: #F8FAFC;
        transform: translateY(-1px);
        color: var(--delni-navy);
    }
    .pp-action-icon svg {
        width: 18px;
        height: 18px;
    }

    .pp-info-group {
        display: grid;
        gap: 1.25rem;
    }

    .pp-info-section:not(:first-child) {
        border-top: 1px solid var(--delni-border);
        padding-top: 1.25rem;
    }

    .pp-info-section__title {
        display: flex;
        align-items: center;
        gap: .4rem;
        margin: 0 0 .75rem 0;
        color: var(--delni-navy);
        font-size: .95rem;
        font-weight: 900;
    }
    .pp-info-section__title svg {
        width: 18px;
        height: 18px;
        color: var(--delni-primary);
        flex-shrink: 0;
    }

    .pp-text, .pp-note {
        margin: 0;
        color: var(--delni-muted);
        font-size: .86rem;
        line-height: 1.75;
        font-weight: 500;
    }

    .pp-service-list {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
    }
    .pp-service-list a {
        display: inline-flex;
        align-items: center;
        padding: .25rem .6rem;
        border-radius: 8px;
        border: 1px solid rgba(241,98,15,.12);
        background: rgba(241,98,15,.04);
        color: var(--delni-primary);
        font-size: .75rem;
        font-weight: 800;
        transition: all .2s ease;
    }
    .pp-service-list a:hover {
        background: rgba(241,98,15,.08);
        transform: translateY(-1px);
    }

    .pp-inline-link {
        margin-top: .5rem;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        color: var(--delni-primary);
        font-size: .8rem;
        font-weight: 850;
    }
    .pp-inline-link svg {
        width: 16px;
        height: 16px;
    }

    .pp-showcase-group {
        display: grid;
        gap: 1.5rem;
    }

    .pp-showcase-section:not(:first-child) {
        border-top: 1px solid var(--delni-border);
        padding-top: 1.5rem;
    }

    .pp-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: .75rem;
    }

    .pp-proj {
        overflow: hidden;
        border: 1px solid var(--delni-border);
        border-radius: 12px;
        background: #FAF8F8;
    }

    .pp-proj-slider {
        position: relative;
        overflow: hidden;
        background: #0B1A34;
        height: 150px;
    }
    .pp-proj-slides {
        display: flex;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        scrollbar-width: none;
        direction: ltr;
        background: #0B1A34;
    }
    .pp-proj-slides::-webkit-scrollbar { display: none; }
    .pp-proj-slide {
        flex: 0 0 100%;
        min-width: 100%;
        scroll-snap-align: center;
        direction: rtl;
    }
    .pp-proj-image {
        width: 100%;
        display: block;
        border: 0;
        padding: 0;
        background: transparent;
        cursor: zoom-in;
    }
    .pp-proj-slide img,
    .pp-gallery__empty {
        width: 100%;
        height: 150px;
        object-fit: cover;
        display: block;
        background: #0B1A34;
    }
    .pp-gallery__empty {
        display: grid;
        place-items: center;
        color: rgba(255,255,255,.45);
    }

    .pp-proj-nav {
        position: absolute;
        top: 50%;
        z-index: 2;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transform: translateY(-50%);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 999px;
        background: rgba(15,23,42,.6);
        color: #fff;
        cursor: pointer;
        transition: all .2s;
    }
    .pp-proj-nav:hover {
        background: rgba(15,23,42,.8);
    }
    .pp-proj-nav svg {
        width: 14px;
        height: 14px;
    }
    .pp-proj-nav--prev { inset-inline-start: .5rem; }
    .pp-proj-nav--next { inset-inline-end: .5rem; }

    .pp-proj-counter {
        position: absolute;
        inset-inline-start: .5rem;
        bottom: .5rem;
        z-index: 2;
        padding: .15rem .45rem;
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 999px;
        background: rgba(15,23,42,.6);
        color: #fff;
        font-size: .65rem;
        font-weight: 800;
        backdrop-filter: blur(8px);
    }

    .pp-proj-dots {
        position: absolute;
        inset-inline: 0;
        bottom: .5rem;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .2rem;
        pointer-events: none;
    }
    .pp-proj-dot {
        width: 5px;
        height: 5px;
        border-radius: 999px;
        border: none;
        background: rgba(255,255,255,.4);
        cursor: pointer;
        padding: 0;
        transition: width .2s;
        pointer-events: auto;
    }
    .pp-proj-dot.is-active {
        width: 14px;
        background: #fff;
    }

    .pp-proj-info {
        padding: .6rem;
    }
    .pp-proj-info h3 {
        margin: 0;
        color: var(--delni-navy);
        font-size: .84rem;
        font-weight: 850;
    }
    .pp-proj-info p {
        margin: .15rem 0 0;
        font-size: .75rem;
        line-height: 1.5;
        color: var(--delni-muted);
    }

    .pp-credentials {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: .5rem;
    }
    .pp-credentials article {
        padding: .65rem;
        border-radius: 12px;
        background: #FAF8F8;
        border: 1px solid var(--delni-border);
    }
    .pp-credentials h3 {
        margin: 0 0 .15rem;
        color: var(--delni-navy);
        font-size: .82rem;
        font-weight: 850;
    }
    .pp-credentials p {
        margin: 0;
        font-size: .74rem;
        color: var(--delni-muted);
    }
    .pp-credentials article > div {
        margin-top: .4rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: .72rem;
        color: var(--delni-gray);
        font-weight: 700;
    }
    .pp-credentials a {
        color: var(--delni-primary);
        text-decoration: none;
    }

    .pp-lightbox {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(2,6,23,.85);
    }
    .pp-lightbox.is-open {
        display: flex;
    }
    .pp-lightbox img {
        max-width: min(100%, 900px);
        max-height: 80vh;
        border-radius: 12px;
        object-fit: contain;
    }
    .pp-lightbox__close {
        position: absolute;
        top: 1rem;
        inset-inline-end: 1rem;
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.15);
        background: rgba(15,23,42,.6);
        color: #fff;
        cursor: pointer;
    }

    .pp-review-summary {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: .75rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid var(--delni-border);
    }

    .pp-rs-score-wrap, .pp-rs-count {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .2rem .5rem;
        border: 1px solid var(--delni-border);
        border-radius: 999px;
        background: #F8FAFC;
        color: #475569;
        font-size: .74rem;
    }

    .pp-rs-star {
        color: #F59E0B;
    }
    .pp-rs-score, .pp-rs-count strong {
        color: var(--delni-navy);
        font-weight: 850;
    }
    .pp-rs-label, .pp-rs-count span {
        color: var(--delni-muted);
    }

    .pp-review-flash {
        margin-bottom: .75rem;
        padding: .5rem .75rem;
        border-radius: 10px;
        font-size: .78rem;
        font-weight: 800;
    }
    .pp-review-flash.is-success {
        background: #F0FDF4;
        border-color: #BBF7D0;
        color: #166534;
    }
    .pp-review-flash.is-error {
        background: #FFF7ED;
        border-color: #FED7AA;
        color: #9A3412;
    }

    .pp-review-notice {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .75rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--delni-border);
        background: #FAF8F8;
        color: var(--delni-muted);
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.5;
    }
    .pp-review-notice a {
        background: var(--delni-primary);
        color: #fff;
        padding: .4rem .8rem;
        border-radius: 8px;
        font-size: .76rem;
        font-weight: 850;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
        transition: opacity .2s;
    }
    .pp-review-notice a:hover {
        opacity: 0.9;
    }

    .pp-write-review-toggle {
        width: 100%;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        padding: .5rem;
        border-radius: 10px;
        border: 1px dashed var(--delni-primary);
        background: rgba(241,98,15,.02);
        color: var(--delni-primary);
        font-size: .8rem;
        font-weight: 850;
        cursor: pointer;
        transition: all .2s;
    }
    .pp-write-review-toggle:hover {
        background: rgba(241,98,15,.06);
    }
    .pp-write-review-toggle svg {
        width: 16px;
        height: 16px;
    }

    .pp-review-form {
        display: grid;
        gap: .5rem;
        padding: .75rem;
        border-radius: 12px;
        border: 1px solid var(--delni-border);
        background: #FAF8F8;
    }

    .pp-form-rating-row {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .78rem;
        font-weight: 850;
        color: var(--delni-navy);
    }

    .pp-star-selector {
        display: inline-flex;
        gap: .2rem;
    }
    .pp-star-btn {
        width: 32px;
        height: 32px;
        border: 0;
        background: transparent;
        color: #E2E8F0;
        cursor: pointer;
        padding: 0;
        transition: all .1s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .pp-star-btn svg {
        width: 24px;
        height: 24px;
    }
    .pp-star-btn.is-active, .pp-star-btn.is-hovered {
        color: #F59E0B;
    }

    .pp-review-form textarea {
        width: 100%;
        border: 1px solid var(--delni-border);
        border-radius: 10px;
        background: #fff;
        padding: .5rem;
        font: inherit;
        font-size: .8rem;
        outline: none;
        resize: vertical;
    }

    .pp-form-actions {
        display: flex;
        gap: .5rem;
    }
    .pp-form-actions button {
        min-height: 34px;
        padding: .25rem .75rem;
        border-radius: 8px;
        border: 0;
        font: inherit;
        font-size: .76rem;
        font-weight: 850;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    #submitReviewBtn {
        background: var(--delni-primary);
        color: #fff;
    }
    .pp-form-cancel {
        background: #E2E8F0;
        color: #475569;
    }

    .pp-review-list {
        display: grid;
        gap: 0;
        margin-top: .75rem;
    }
    .pp-review-list article {
        padding: .75rem 0;
        border-bottom: 1px solid var(--delni-border);
        background: transparent;
        border-radius: 0;
    }
    .pp-review-list article:last-of-type {
        border-bottom: 0;
    }
    .pp-review-list article > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .2rem;
    }
    .pp-review-list strong {
        font-size: .82rem;
        font-weight: 850;
        color: var(--delni-navy);
    }
    .pp-review-badge {
        display: inline-flex;
        align-items: center;
        gap: .15rem;
        padding: .1rem .4rem;
        border-radius: 999px;
        background: #FEF3C7;
        border: 1px solid #FDE68A;
        color: #B45309;
        font-size: .68rem;
        font-weight: 850;
    }
    .pp-review-list p {
        margin: .15rem 0;
        font-size: .82rem;
        line-height: 1.5;
        color: var(--delni-muted);
    }
    .pp-review-list small {
        display: block;
        font-size: .68rem;
        color: var(--delni-gray);
    }

    .pp-show-more {
        width: 100%;
        min-height: 36px;
        margin-top: .5rem;
        background: rgba(241,98,15,.04);
        color: var(--delni-primary);
        border: 1px solid rgba(241,98,15,.12);
        border-radius: 10px;
        font-size: .78rem;
        font-weight: 850;
        cursor: pointer;
    }
    .pp-show-more:hover {
        background: rgba(241,98,15,.08);
    }

    [data-theme="dark"] .pp-identity,
    [data-theme="dark"] .pp-identity__content,
    [data-theme="dark"] .pp-card {
        background: var(--delni-card);
        border-color: var(--delni-border);
    }
    [data-theme="dark"] .pp-title h1,
    [data-theme="dark"] .pp-header-rating {
        color: var(--delni-navy);
    }
    [data-theme="dark"] .pp-avatar {
        border-color: var(--delni-card);
        background: var(--delni-bg);
    }
    [data-theme="dark"] .pp-back,
    [data-theme="dark"] .pp-favorite-btn {
        background: rgba(15,23,42,.8);
        border-color: rgba(255,255,255,.1);
        color: var(--delni-navy);
    }
    [data-theme="dark"] .pp-back:hover,
    [data-theme="dark"] .pp-favorite-btn:hover {
        background: rgba(15,23,42,.95);
    }
    [data-theme="dark"] .pp-meta > span {
        background: var(--delni-bg);
        border-color: var(--delni-border);
        color: var(--delni-muted);
    }
    [data-theme="dark"] .pp-actions {
        background: var(--delni-bg);
        border-color: var(--delni-border);
    }
    [data-theme="dark"] .pp-action-primary,
    [data-theme="dark"] .pp-action-icon {
        background: var(--delni-card);
        border-color: var(--delni-border);
        color: var(--delni-muted);
    }
    [data-theme="dark"] .pp-action-primary:hover,
    [data-theme="dark"] .pp-action-icon:hover {
        background: var(--delni-bg);
        border-color: var(--delni-border);
        color: var(--delni-navy);
    }
    [data-theme="dark"] .pp-info-section__title,
    [data-theme="dark"] .pp-proj-info h3,
    [data-theme="dark"] .pp-credentials h3,
    [data-theme="dark"] .pp-review-list strong {
        color: var(--delni-navy);
    }
    [data-theme="dark"] .pp-text,
    [data-theme="dark"] .pp-note,
    [data-theme="dark"] .pp-proj-info p,
    [data-theme="dark"] .pp-credentials p,
    [data-theme="dark"] .pp-review-list p {
        color: var(--delni-muted);
    }
    [data-theme="dark"] .pp-info-section:not(:first-child),
    [data-theme="dark"] .pp-showcase-section:not(:first-child),
    [data-theme="dark"] .pp-proj,
    [data-theme="dark"] .pp-credentials article,
    [data-theme="dark"] .pp-review-summary,
    [data-theme="dark"] .pp-review-list article,
    [data-theme="dark"] .pp-review-form {
        border-color: var(--delni-border);
    }
    [data-theme="dark"] .pp-proj,
    [data-theme="dark"] .pp-credentials article,
    [data-theme="dark"] .pp-review-form {
        background: var(--delni-bg);
    }
    [data-theme="dark"] .pp-rs-score-wrap,
    [data-theme="dark"] .pp-rs-count,
    [data-theme="dark"] .pp-review-notice {
        background: var(--delni-bg);
        border-color: var(--delni-border);
        color: var(--delni-muted);
    }
    [data-theme="dark"] .pp-rs-score,
    [data-theme="dark"] .pp-rs-count strong {
        color: var(--delni-navy);
    }
    [data-theme="dark"] .pp-rs-star {
        color: #FCD34D;
    }
    [data-theme="dark"] .pp-star-btn {
        color: var(--delni-border);
    }
    [data-theme="dark"] .pp-star-btn.is-active,
    [data-theme="dark"] .pp-star-btn.is-hovered {
        color: #F59E0B;
    }
    [data-theme="dark"] .pp-review-form textarea {
        background: var(--delni-card);
        border-color: var(--delni-border);
        color: var(--delni-navy);
    }
    [data-theme="dark"] .pp-form-cancel {
        background: var(--delni-border);
        color: var(--delni-muted);
    }
    [data-theme="dark"] .pp-review-badge {
        background: rgba(245, 158, 11, .15);
        border-color: rgba(245, 158, 11, .3);
        color: #FBBF24;
    }
    [data-theme="dark"] .pp-show-more {
        background: rgba(241, 98, 15, .08);
        border-color: rgba(241, 98, 15, .2);
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var toast = null;

    function showLoginToast() {
        if (window.DelniAuthToast) {
            window.DelniAuthToast.show('سجّل دخولك لإضافة مقدم الخدمة إلى المفضلة', 'تسجيل الدخول', '{{ route('login') }}');
            return;
        }

        if (toast) { return; }
        toast = document.createElement('div');
        toast.className = 'pc-fav-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.innerHTML = '<span>سجل دخولك لإضافة مقدمي خدمات إلى المفضلة</span><a href="{{ route('login') }}">تسجيل الدخول</a>';
        document.body.appendChild(toast);
        requestAnimationFrame(function () { toast.classList.add('is-visible'); });
        setTimeout(function () {
            toast.classList.remove('is-visible');
            setTimeout(function () { toast && toast.remove(); toast = null; }, 300);
        }, 5000);
    }

    document.querySelector('.is-favorite-guest')?.addEventListener('click', showLoginToast);

    const btn = document.querySelector('.pp-favorite[data-toggle-url]');
    if (btn) {
        btn.addEventListener('click', function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            btn.disabled = true;

            fetch(btn.dataset.toggleUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                btn.classList.toggle('is-favorited', data.favorited);
                const span = btn.querySelector('span');
                if (span) { span.textContent = data.favorited ? 'في المفضلة' : 'أضف للمفضلة'; }
            })
            .catch(function () {})
            .finally(function () { btn.disabled = false; });
        });
    }

    // portfolio sliders
    document.querySelectorAll('.pp-proj-slider').forEach(function (slider) {
        var slidesEl = slider.querySelector('.pp-proj-slides');
        var dots = slider.querySelectorAll('.pp-proj-dot');
        var counter = slider.querySelector('.pp-proj-counter');
        if (!slidesEl || dots.length === 0) { return; }
        var total = dots.length;

        function getActiveIdx() {
            var w = slidesEl.clientWidth;
            return w > 0 ? Math.round(slidesEl.scrollLeft / w) : 0;
        }

        function syncUI(idx) {
            dots.forEach(function (d, i) {
                d.classList.toggle('is-active', i === idx);
                d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            if (counter) { counter.textContent = (idx + 1) + ' / ' + total; }
        }

        slidesEl.addEventListener('scroll', function () {
            syncUI(getActiveIdx());
        }, { passive: true });

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                var idx = parseInt(dot.dataset.idx, 10);
                slidesEl.scrollTo({ left: idx * slidesEl.clientWidth, behavior: 'smooth' });
            });
        });

        slider.querySelectorAll('[data-slider-nav]').forEach(function (button) {
            button.addEventListener('click', function () {
                var current = getActiveIdx();
                var dir = parseInt(button.dataset.dir, 10);
                var next = Math.max(0, Math.min(total - 1, current + dir));
                slidesEl.scrollTo({ left: next * slidesEl.clientWidth, behavior: 'smooth' });
                syncUI(next);
            });
        });
    });

    var lightbox = document.getElementById('portfolioLightbox');
    var lightboxImage = lightbox?.querySelector('img');
    var closeLightbox = lightbox?.querySelector('.pp-lightbox__close');

    function closePortfolioLightbox() {
        if (!lightbox || !lightboxImage) { return; }
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        lightboxImage.removeAttribute('src');
        lightboxImage.removeAttribute('alt');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-portfolio-image]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!lightbox || !lightboxImage) { return; }
            lightboxImage.src = button.dataset.portfolioImage;
            lightboxImage.alt = button.dataset.portfolioAlt || '';
            lightbox.classList.add('is-open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        });
    });

    closeLightbox?.addEventListener('click', closePortfolioLightbox);
    lightbox?.addEventListener('click', function (event) {
        if (event.target === lightbox) {
            closePortfolioLightbox();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePortfolioLightbox();
        }
    });

    // Interactive Star Rating Selector
    const starSelector = document.getElementById('starSelector');
    const ratingInput = document.getElementById('ratingValue');
    if (starSelector && ratingInput) {
        const stars = starSelector.querySelectorAll('.pp-star-btn');
        
        function updateStars(val, isHover = false) {
            stars.forEach(star => {
                const starVal = parseInt(star.dataset.value, 10);
                if (isHover) {
                    star.classList.toggle('is-hovered', starVal <= val);
                } else {
                    star.classList.toggle('is-active', starVal <= val);
                }
            });
        }

        if (ratingInput.value) {
            updateStars(parseInt(ratingInput.value, 10));
        }

        stars.forEach(star => {
            star.addEventListener('click', function () {
                const val = parseInt(this.dataset.value, 10);
                ratingInput.value = val;
                updateStars(val);
            });

            star.addEventListener('mouseenter', function () {
                const val = parseInt(this.dataset.value, 10);
                updateStars(val, true);
            });
        });

        starSelector.addEventListener('mouseleave', function () {
            stars.forEach(star => star.classList.remove('is-hovered'));
        });
    }

    // AJAX Review Form Submission Flow
    const reviewForm = document.getElementById('reviewForm');
    const submitBtn = document.getElementById('submitReviewBtn');
    if (reviewForm && submitBtn) {
        reviewForm.addEventListener('submit', function (event) {
            event.preventDefault();
            
            const commentVal = document.getElementById('comment')?.value.trim();
            if (!ratingInput.value && !commentVal) {
                alert('يجب إدخال تقييم بالنجوم أو كتابة تعليق لإرسال التقييم.');
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            submitBtn.disabled = true;
            const originalBtnText = submitBtn.textContent;
            submitBtn.textContent = 'جاري الإرسال...';

            const formData = new FormData(reviewForm);

            fetch(reviewForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.indexOf('application/json') !== -1) {
                    return response.json().then(data => {
                        if (!response.ok) {
                            throw data;
                        }
                        return data;
                    });
                } else {
                    window.location.reload();
                    return new Promise(() => {});
                }
            })
            .then(data => {
                reviewForm.reset();
                ratingInput.value = '';
                updateStars(0);

                let flash = document.querySelector('.pp-review-flash.is-success');
                if (!flash) {
                    flash = document.createElement('div');
                    flash.className = 'pp-review-flash is-success';
                    const targetParent = reviewForm.parentNode;
                    targetParent.insertBefore(flash, reviewForm);
                }
                flash.textContent = data.message || 'تم إرسال التقييم بنجاح.';
                flash.style.display = 'block';
                flash.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
                const errorFlash = document.querySelector('.pp-review-flash.is-error');
                if (errorFlash) { errorFlash.style.display = 'none'; }

                // Hide the form and show the toggle button again
                const toggleBtn = document.getElementById('writeReviewToggleBtn');
                if (toggleBtn && reviewForm) {
                    reviewForm.style.display = 'none';
                    reviewForm.classList.add('is-collapsed');
                    toggleBtn.style.display = 'inline-flex';
                }
            })
            .catch(err => {
                console.error(err);
                let flash = document.querySelector('.pp-review-flash.is-error');
                if (!flash) {
                    flash = document.createElement('div');
                    flash.className = 'pp-review-flash is-error';
                    const targetParent = reviewForm.parentNode;
                    targetParent.insertBefore(flash, reviewForm);
                }
                flash.textContent = err.message || err.errors?.profile?.[0] || err.errors?.rating?.[0] || err.errors?.comment?.[0] || 'تعذر إرسال التقييم. يرجى المحاولة لاحقاً.';
                flash.style.display = 'block';
                flash.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            });
        });
    }

    document.getElementById('showAllReviewsBtn')?.addEventListener('click', function () {
        document.querySelectorAll('[data-review-item].is-hidden-review').forEach(function (item) {
            item.classList.remove('is-hidden-review');
        });
        this.remove();
    });

    // Write review toggle flow
    const toggleBtn = document.getElementById('writeReviewToggleBtn');
    const cancelBtn = document.getElementById('cancelReviewBtn');
    const formEl = document.getElementById('reviewForm');
    
    if (toggleBtn && formEl) {
        toggleBtn.addEventListener('click', function() {
            toggleBtn.style.display = 'none';
            formEl.style.display = 'grid';
            formEl.classList.remove('is-collapsed');
            formEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }
    
    if (cancelBtn && toggleBtn && formEl) {
        cancelBtn.addEventListener('click', function() {
            formEl.style.display = 'none';
            formEl.classList.add('is-collapsed');
            toggleBtn.style.display = 'inline-flex';
        });
    }
}());
</script>
@endpush
@endsection
