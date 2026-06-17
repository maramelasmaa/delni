@extends('public.layout')

@section('title', ($profile->business_name ?? $profile->user?->name ?? 'مزود خدمة') . ' - ' . config('app.name'))

@section('content')
@php
    $businessName = $profile->business_name ?? $profile->user?->name ?? 'مزود خدمة';

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

    $facts = collect([
        $categoryName ? ['label' => 'الفئة', 'value' => $categoryName] : null,
        $cityName ? ['label' => 'المدينة', 'value' => $cityName] : null,
        $profile->provider_type ? ['label' => 'نوع المزود', 'value' => \App\Models\ProviderType::labelFor($profile->provider_type)] : null,
        $profile->experience_years ? ['label' => 'الخبرة', 'value' => $profile->experience_years . ' سنوات'] : null,
        $profile->offers_remote_work ? ['label' => 'الخدمة عن بعد', 'value' => 'متاحة'] : null,
    ])->filter();
@endphp

<article class="provider-profile">
    <section class="pp-identity">
        <div class="pp-identity__media">
        <a href="{{ $backUrl }}" class="pp-back" aria-label="رجوع">
            <x-render-icon icon="heroicon-o-arrow-right" />
            <span>رجوع</span>
        </a>

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
    </section>

    @if($contactActions->isNotEmpty())
        <nav class="pp-actions" aria-label="طرق التواصل السريعة">
            @foreach($contactActions->where('primary', true) as $action)
                <a href="{{ $action['url'] }}"
                   class="pp-action-primary {{ $action['class'] }}"
                   aria-label="{{ $action['label'] }}"
                   @if($action['external']) target="_blank" rel="noopener noreferrer nofollow" @endif>
                    <x-render-icon :icon="$action['icon']" />
                    <span>{{ $action['label'] }}</span>
                </a>
            @endforeach

            @foreach($contactActions->where('primary', false) as $action)
                <a href="{{ $action['url'] }}"
                   class="pp-action-icon {{ $action['class'] }}"
                   aria-label="{{ $action['label'] }}"
                   title="{{ $action['label'] }}"
                   @if($action['external']) target="_blank" rel="noopener noreferrer nofollow" @endif>
                    <x-render-icon :icon="$action['icon']" />
                </a>
            @endforeach
        </nav>
    @endif

    <div class="pp-layout">
        <main class="pp-main">
            @if($profile->bio)
                <section id="about" class="pp-card">
                    <x-profile-section-title eyebrow="نبذة" title="عن المزود" />
                    <p class="pp-text">{{ $profile->bio }}</p>
                </section>
            @endif

            @if($subcategories->isNotEmpty())
                <section class="pp-card">
                    <x-profile-section-title eyebrow="الخدمات" title="ما يقدمه" />
                    <div class="pp-service-list">
                        @foreach($subcategories as $subcategory)
                            <a href="{{ route('public.subcategory', $subcategory->slug) }}">
                                {{ $subcategory->localized_name ?? $subcategory->name }}
                            </a>
                        @endforeach
                    </div>

                </section>
            @endif

            @if($facts->isNotEmpty() || $profile->service_area_note || $profile->map_url)
                <section class="pp-card">
                    <x-profile-section-title eyebrow="التغطية" title="نطاق العمل" />
                    <div class="pp-facts">
                        @foreach($facts as $fact)
                            <div>
                                <span>{{ $fact['label'] }}</span>
                                <strong>{{ $fact['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>

                    @if($profile->service_area_note)
                        <p class="pp-note">{{ $profile->service_area_note }}</p>
                    @endif

                    @if($profile->map_url)
                        <a href="{{ $profile->map_url }}" class="pp-inline-link" target="_blank" rel="noopener noreferrer nofollow">
                            <x-render-icon icon="heroicon-o-map-pin" />
                            <span>فتح الموقع على الخريطة</span>
                        </a>
                    @endif
                </section>
            @endif

            @if($portfolioItems->isNotEmpty())
                <section id="portfolio" class="pp-card">
                    <x-profile-section-title eyebrow="الأعمال" title="نماذج من العمل" />
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
                </section>
            @endif

            @if($credentials->isNotEmpty())
                <section id="credentials" class="pp-card">
                    <x-profile-section-title eyebrow="الثقة" title="الشهادات والاعتمادات" />
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
                </section>
            @endif

            <section id="reviews" class="pp-card">
                <x-profile-section-title title="التقييمات" />

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
                        <span>سجل الدخول لكتابة تقييم بعد التعامل مع المزود.</span>
                        <a href="{{ route('login') }}">دخول</a>
                    </div>
                @elseif(!auth()->user()->hasRole('user'))
                    <div class="pp-review-notice">مزودو الخدمات لا يمكنهم كتابة تقييمات.</div>
                @elseif($profile->user_id === auth()->id())
                    <div class="pp-review-notice">لا يمكنك تقييم ملفك الخاص.</div>
                @else
                    <form method="POST" action="{{ route('review.store', $profile) }}" class="pp-review-form" id="reviewForm">
                        @csrf
                        <label>التقييم <span class="pp-optional-label">(اختياري)</span></label>
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

                        <label for="comment">رأيك <span class="pp-optional-label">(اختياري)</span></label>
                        <textarea id="comment" name="comment" rows="4" maxlength="2000" placeholder="شارك تجربتك باختصار...">{{ old('comment') }}</textarea>
                        <span class="pp-review-tip">يمكنك اختيار تقييم بالنجوم، أو كتابة تعليق، أو الاثنين معاً.</span>

                        <button type="submit" id="submitReviewBtn">إرسال التقييم</button>
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

        <aside class="pp-sidebar" id="contact">
            @auth
                <button
                    class="pp-favorite {{ $isFavorited ? 'is-favorited' : '' }}"
                    data-profile-id="{{ $profile->id }}"
                    data-toggle-url="{{ route('favorites.toggle', $profile) }}"
                    type="button"
                >
                    <x-render-icon icon="{{ $isFavorited ? 'app-heart-filled' : 'app-heart' }}" />
                    <span>{{ $isFavorited ? 'في المفضلة' : 'أضف للمفضلة' }}</span>
                </button>
            @else
                <button class="pp-favorite is-favorite-guest" type="button" aria-label="أضف إلى المفضلة">
                    <x-render-icon icon="app-heart" />
                    <span>أضف للمفضلة</span>
                </button>
            @endauth
        </aside>
    </div>
</article>

@push('styles')
<style>
    .provider-profile {
        display: grid;
        gap: .85rem;
        padding-bottom: 2rem;
    }

    .pp-identity {
        position: relative;
        overflow: hidden;
        display: grid;
        border: 1px solid var(--delni-border);
        border-radius: 20px;
        background: #fff;
        color: var(--delni-navy);
        box-shadow: var(--delni-shadow-sm);
    }

    .pp-identity__media {
        position: relative;
        min-height: clamp(120px, 18vw, 170px);
        overflow: hidden;
        background: linear-gradient(135deg, #0B1A34 0%, #1E293B 100%);
    }

    .pp-back {
        position: absolute;
        top: .85rem;
        inset-inline-start: .85rem;
        z-index: 2;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .38rem;
        padding: .5rem .75rem;
        border-radius: 999px;
        border: 1px solid rgba(15,23,42,.1);
        background: rgba(255,255,255,.92);
        color: var(--delni-navy);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        font-size: .82rem;
        font-weight: 900;
        text-decoration: none;
    }

    .pp-back svg {
        width: 18px;
        height: 18px;
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
        background: linear-gradient(to top, rgba(11,26,52,.28), rgba(11,26,52,.04));
    }

    .pp-identity__content {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 1.35rem;
        align-items: flex-start;
        padding: 2rem 2rem 1.35rem;
        background: #fff;
    }

    .pp-avatar {
        width: clamp(90px, 12vw, 120px);
        height: clamp(90px, 12vw, 120px);
        display: grid;
        place-items: center;
        overflow: hidden;
        align-self: start;
        margin-top: -3rem;
        border-radius: 20px;
        border: 4px solid #fff;
        background: linear-gradient(135deg, #0B1A34 0%, #1E293B 100%);
        box-shadow: 0 18px 48px rgba(15,23,42,.24);
        flex-shrink: 0;
    }
    .pp-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pp-avatar span { color: var(--delni-primary); font-size: 2.8rem; font-weight: 950; }

    .pp-eyebrow {
        width: fit-content;
        max-width: 100%;
        display: inline-flex;
        align-items: center;
        color: var(--delni-primary);
        border: 1.5px solid rgba(241,98,15,.24);
        border-radius: 999px;
        background: rgba(241,98,15,.08);
        padding: .35rem .68rem;
        font-size: .74rem;
        font-weight: 900;
        line-height: 1.2;
        overflow-wrap: anywhere;
        letter-spacing: .3px;
    }

    .pp-title {
        display: grid;
        gap: .68rem;
        align-content: start;
    }

    .pp-title h1 {
        margin: 0;
        color: var(--delni-navy);
        font-size: clamp(1.65rem, 4vw, 2.5rem);
        line-height: 1.15;
        font-weight: 970;
        letter-spacing: -.3px;
        overflow-wrap: anywhere;
    }

    .pp-meta {
        display: grid;
        grid-auto-rows: max-content;
        gap: .6rem;
        margin: 0;
    }

    .pp-meta > span {
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        max-width: 100%;
        padding: .38rem .68rem;
        border: 1.2px solid #E2E8F0;
        border-radius: 999px;
        background: linear-gradient(to bottom, #FFFFFF 0%, #F8FAFC 100%);
        color: #334155;
        font-size: .76rem;
        font-weight: 850;
        line-height: 1.35;
        transition: all .2s ease;
    }

    .pp-meta > span:hover {
        border-color: #CBD5E1;
        background: #F8FAFC;
    }

    .pp-meta > span:not(:last-child)::after {
        content: none;
    }

    .pp-meta svg {
        width: 16px;
        height: 16px;
        color: var(--delni-primary);
        flex-shrink: 0;
    }

    .pp-meta > span > span {
        min-width: 0;
        color: inherit;
        overflow-wrap: anywhere;
    }

    .pp-rating-chip {
        gap: .55rem;
        min-width: 120px;
        padding: .85rem .95rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #FFF7ED 0%, #FEF3C7 100%);
        border: 1.5px solid #FED7AA;
        color: var(--delni-navy);
        font-size: .85rem;
        font-weight: 900;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
        align-self: start;
        margin-top: 0;
    }

    .pp-rating-chip:hover {
        border-color: #FDBA74;
        background: linear-gradient(135deg, #FEF3C7 0%, #FCD34D 100%);
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(241,98,15,.12);
    }

    .pp-rating-chip b { color: #F59E0B; font-style: normal; }
    .pp-rating-chip span { opacity: 1; }

    .pp-rating-chip__star {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #F59E0B;
        color: #fff;
        font-size: 1.1rem;
        line-height: 1;
        flex-shrink: 0;
    }

    .pp-rating-chip__body {
        display: grid;
        gap: .12rem;
    }

    .pp-rating-chip strong {
        color: var(--delni-navy);
        font-size: 1.1rem;
        font-weight: 950;
        line-height: 1;
    }

    .pp-rating-chip small {
        color: #64748B;
        font-size: .75rem;
        font-weight: 850;
        line-height: 1.25;
        white-space: nowrap;
    }

    .is-muted { opacity: .28; }

    .pp-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .6rem;
        padding: 0 2rem 1.35rem;
        justify-content: flex-start;
    }

    .pp-action-primary,
    .pp-favorite {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        padding: 0 1.35rem;
        border-radius: 16px;
        border: 1.2px solid #E2E8F0;
        background: linear-gradient(to bottom, #FFFFFF 0%, #F8FAFC 100%);
        color: var(--delni-navy);
        font: inherit;
        font-size: .87rem;
        font-weight: 950;
        text-decoration: none;
        cursor: pointer;
        transition: all .2s ease;
        flex: 0 1 auto;
    }

    .pp-action-primary:hover,
    .pp-favorite:hover {
        border-color: #CBD5E1;
        background: #F1F5F9;
        transform: translateY(-1px);
    }

    .pp-action-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        border: 1.2px solid #E2E8F0;
        background: linear-gradient(to bottom, #FFFFFF 0%, #F8FAFC 100%);
        color: var(--delni-navy);
        text-decoration: none;
        flex-shrink: 0;
        transition: all .2s ease;
    }

    .pp-action-icon:hover {
        border-color: #CBD5E1;
        background: #F1F5F9;
        transform: translateY(-1px);
    }

    .pp-action-primary svg,
    .pp-action-icon svg,
    .pp-favorite svg { width: 20px; height: 20px; }

    .pp-action-primary.is-whatsapp {
        background: linear-gradient(135deg, #22C55E 0%, #16A34A 100%);
        border-color: #22C55E;
        color: #fff;
    }

    .pp-action-primary.is-whatsapp:hover {
        border-color: #16A34A;
        background: linear-gradient(135deg, #16A34A 0%, #15803D 100%);
    }

    .pp-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(280px, 340px);
        gap: .9rem;
        align-items: start;
    }
    .pp-main { display: grid; gap: .85rem; min-width: 0; }
    .pp-card {
        padding: 1rem;
        border: 1px solid var(--delni-border);
        border-radius: 20px;
        background: #fff;
        box-shadow: var(--delni-shadow-sm);
    }

    .profile-section-title { margin-bottom: .75rem; }
    .profile-section-title span {
        display: block;
        color: var(--delni-primary);
        font-size: .72rem;
        font-weight: 950;
    }
    .profile-section-title h2 {
        margin: .12rem 0 0;
        color: var(--delni-navy);
        font-size: 1.08rem;
        line-height: 1.35;
        font-weight: 950;
        letter-spacing: 0;
    }

    .pp-text,
    .pp-note,
    .pp-proj-info p,
    .pp-credentials p,
    .pp-review-list p {
        margin: 0;
        color: var(--delni-muted);
        font-size: .92rem;
        line-height: 1.85;
        font-weight: 650;
    }

    .pp-service-list {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }
    .pp-service-list a {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        padding: .45rem .7rem;
        border-radius: 999px;
        border: 1px solid rgba(241,98,15,.14);
        background: rgba(241,98,15,.07);
        color: var(--delni-primary);
        font-size: .8rem;
        font-weight: 900;
        text-decoration: none;
    }

    .pp-facts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .55rem;
    }
    .pp-facts div {
        padding: .75rem;
        border-radius: 16px;
        background: #FCFBFB;
        border: 1px solid var(--delni-border);
    }
    .pp-facts span,
    .pp-facts strong {
        display: block;
    }
    .pp-facts span {
        color: #64748B;
        font-size: .72rem;
        font-weight: 900;
    }
    .pp-facts strong {
        margin-top: .15rem;
        color: var(--delni-navy);
        font-size: .9rem;
        font-weight: 950;
    }
    .pp-note { margin-top: .75rem; }
    .pp-inline-link {
        margin-top: .75rem;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: var(--delni-primary);
        font-size: .84rem;
        font-weight: 950;
        text-decoration: none;
    }
    .pp-inline-link svg { width: 18px; height: 18px; }

    .pp-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
        gap: .85rem;
    }

    .pp-proj {
        overflow: hidden;
        border: 1px solid var(--delni-border);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(11, 26, 52, .055);
    }

    .pp-proj-slider {
        position: relative;
        overflow: hidden;
        background: #0B1A34;
        height: 210px;
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
    .pp-proj-image:focus-visible {
        outline: 3px solid rgba(241,98,15,.55);
        outline-offset: -5px;
    }
    .pp-proj-slide img,
    .pp-gallery__empty {
        width: 100%;
        height: 210px;
        object-fit: cover;
        display: block;
        background: #0B1A34;
    }
    .pp-gallery__empty {
        display: grid;
        place-items: center;
        color: rgba(255,255,255,.55);
    }

    .pp-proj-nav {
        position: absolute;
        top: 50%;
        z-index: 2;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transform: translateY(-50%);
        border: 1px solid rgba(255,255,255,.24);
        border-radius: 999px;
        background: rgba(15,23,42,.72);
        color: #fff;
        box-shadow: 0 12px 24px rgba(2,6,23,.22);
        backdrop-filter: blur(10px);
        cursor: pointer;
        transition: background .16s ease, transform .16s ease;
    }
    .pp-proj-nav:hover {
        background: rgba(15,23,42,.9);
        transform: translateY(-50%) scale(1.04);
    }
    .pp-proj-nav svg {
        width: 18px;
        height: 18px;
    }
    .pp-proj-nav--prev { inset-inline-start: .65rem; }
    .pp-proj-nav--next { inset-inline-end: .65rem; }

    .pp-proj-counter {
        position: absolute;
        inset-inline-start: .7rem;
        bottom: .7rem;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: .22rem .58rem;
        border: 1px solid rgba(255,255,255,.24);
        border-radius: 999px;
        background: rgba(15,23,42,.7);
        color: #fff;
        font-size: .72rem;
        font-weight: 900;
        backdrop-filter: blur(10px);
    }

    .pp-proj-dots {
        position: absolute;
        inset-inline: 0;
        bottom: .75rem;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .28rem;
        pointer-events: none;
    }
    .pp-proj-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        border: none;
        background: rgba(255,255,255,.48);
        cursor: pointer;
        padding: 0;
        transition: width .2s, background .2s;
        pointer-events: auto;
    }
    .pp-proj-dot.is-active {
        width: 22px;
        background: #fff;
    }

    .pp-lightbox {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: max(1rem, env(safe-area-inset-top)) 1rem max(1rem, env(safe-area-inset-bottom));
        background: rgba(2,6,23,.86);
    }
    .pp-lightbox.is-open {
        display: flex;
    }
    .pp-lightbox img {
        max-width: min(100%, 980px);
        max-height: 86vh;
        border-radius: 18px;
        object-fit: contain;
        box-shadow: 0 24px 70px rgba(0,0,0,.42);
    }
    .pp-lightbox__close {
        position: absolute;
        top: max(1rem, env(safe-area-inset-top));
        inset-inline-end: 1rem;
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.18);
        background: rgba(15,23,42,.72);
        color: #fff;
        cursor: pointer;
    }
    .pp-lightbox__close svg {
        width: 20px;
        height: 20px;
    }

    .pp-proj-info { padding: .85rem; }
    .pp-proj-info p {
        margin: .25rem 0 0;
        font-size: .82rem;
        line-height: 1.65;
    }

    .pp-gallery h3,
    .pp-credentials h3 {
        margin: 0 0 .25rem;
        color: var(--delni-navy);
        font-size: .95rem;
        line-height: 1.45;
        font-weight: 950;
    }

    .pp-credentials {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 230px), 1fr));
        gap: .65rem;
    }
    .pp-credentials article {
        padding: .85rem;
        border-radius: 16px;
        background: #FCFBFB;
        border: 1px solid var(--delni-border);
    }
    .pp-credentials article > div {
        margin-top: .55rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .6rem;
        color: #64748B;
        font-size: .78rem;
        font-weight: 900;
    }
    .pp-credentials a { color: var(--delni-primary); text-decoration: none; }

    .pp-review-summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .45rem;
        margin-bottom: .85rem;
        padding: .25rem 0 .8rem;
        border-bottom: 1px solid var(--delni-border);
        background: transparent;
    }
    .pp-rs-score-wrap {
        min-width: 0;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        min-height: 28px;
        padding: .28rem .52rem;
        border: 1px solid var(--delni-border);
        border-radius: 999px;
        background: #F8FAFC;
        color: #475569;
    }
    .pp-rs-star {
        width: auto;
        height: auto;
        background: transparent;
        color: #B45309;
        font-size: .76rem;
        line-height: 1;
    }
    .pp-rs-score {
        color: var(--delni-navy);
        font-size: .78rem;
        font-weight: 950;
        line-height: 1;
        letter-spacing: 0;
    }
    .pp-rs-label {
        color: #64748B;
        font-size: .74rem;
        font-weight: 850;
        line-height: 1.25;
        white-space: nowrap;
    }
    .pp-rs-count {
        min-height: 28px;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .28rem .52rem;
        border: 1px solid var(--delni-border);
        border-radius: 999px;
        background: #F8FAFC;
        color: #475569;
        line-height: 1.35;
    }
    .pp-rs-count strong {
        color: var(--delni-navy);
        font-size: .78rem;
        font-weight: 950;
        line-height: 1;
    }
    .pp-rs-count span {
        font-size: .74rem;
        font-weight: 850;
        white-space: nowrap;
    }
    .pp-review-badge {
        display: inline-flex;
        align-items: center;
        gap: .18rem;
        min-height: 24px;
        padding: .18rem .42rem;
        border-radius: 999px;
        background: #F8FAFC;
        border: 1px solid var(--delni-border);
        color: #B45309;
        font-size: .7rem;
        font-weight: 950;
        letter-spacing: .01em;
        white-space: nowrap;
        flex-shrink: 0;
    }
    [data-theme="dark"] .pp-rs-score { color: #F1F5F9; }
    [data-theme="dark"] .pp-rs-label { color: #CBD5E1; }
    [data-theme="dark"] .pp-review-summary {
        border-color: #334155;
    }
    [data-theme="dark"] .pp-rs-score-wrap,
    [data-theme="dark"] .pp-rs-count {
        background: #0F172A;
        border-color: #334155;
        color: #CBD5E1;
    }
    [data-theme="dark"] .pp-rs-star { color: #FCD34D; }
    [data-theme="dark"] .pp-rs-count strong { color: #F1F5F9; }
    [data-theme="dark"] .pp-review-badge { background: #0F172A; border-color: #334155; color: #FCD34D; }

    .pp-review-flash {
        margin-bottom: .8rem;
        padding: .75rem .85rem;
        border-radius: 14px;
        border: 1px solid var(--delni-border);
        font-size: .84rem;
        font-weight: 850;
        line-height: 1.65;
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
    [data-theme="dark"] .pp-review-flash.is-success {
        background: rgba(34,197,94,.12);
        border-color: rgba(34,197,94,.25);
        color: #86EFAC;
    }
    [data-theme="dark"] .pp-review-flash.is-error {
        background: rgba(241,98,15,.12);
        border-color: rgba(241,98,15,.28);
        color: #FDBA74;
    }

    .pp-review-notice,
    .pp-review-form {
        margin-bottom: .8rem;
        padding: .85rem;
        border-radius: 16px;
        border: 1px solid var(--delni-border);
        background: #FCFBFB;
        color: var(--delni-muted);
        font-size: .86rem;
        font-weight: 750;
    }
    .pp-review-notice {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }
    .pp-review-notice a,
    .pp-review-form button,
    .pp-show-more {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .55rem .85rem;
        border-radius: 13px;
        border: 0;
        background: var(--delni-primary);
        color: #fff;
        font: inherit;
        font-size: .84rem;
        font-weight: 950;
        text-decoration: none;
        cursor: pointer;
    }
    .pp-review-form {
        display: grid;
        gap: .55rem;
    }
    .pp-review-form label {
        color: var(--delni-navy);
        font-size: .8rem;
        font-weight: 950;
    }
    .pp-star-selector {
        display: inline-flex;
        gap: 0.35rem;
        margin-bottom: 0.25rem;
    }
    .pp-star-btn {
        width: 42px;
        height: 42px;
        border: 0;
        background: transparent;
        color: #E2E8F0;
        cursor: pointer;
        padding: 0;
        transition: color 0.15s ease, transform 0.1s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        outline: none;
    }
    [data-theme="dark"] .pp-star-btn {
        color: #334155;
    }
    .pp-star-btn svg {
        width: 32px;
        height: 32px;
        fill: currentColor;
    }
    .pp-star-btn:hover {
        transform: scale(1.12);
    }
    .pp-star-btn.is-active,
    .pp-star-btn.is-hovered {
        color: #F59E0B;
    }
    .pp-optional-label {
        font-weight: normal;
        font-size: 0.75rem;
        color: var(--delni-muted);
        margin-inline-start: 0.25rem;
    }
    .pp-review-tip {
        font-size: 0.72rem;
        color: var(--delni-muted);
        margin-top: -0.25rem;
        margin-bottom: 0.25rem;
        display: block;
        font-weight: 600;
    }

    .pp-review-form select,
    .pp-review-form textarea {
        width: 100%;
        border: 1px solid var(--delni-border);
        border-radius: 14px;
        background: #fff;
        padding: .72rem;
        font: inherit;
        outline: none;
    }

    .pp-review-list {
        display: grid;
        gap: .65rem;
    }
    .pp-review-list article {
        padding: .85rem;
        border-radius: 16px;
        background: #FCFBFB;
        border: 1px solid var(--delni-border);
    }
    .pp-review-list article > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .35rem;
    }
    .pp-review-list strong {
        color: var(--delni-navy);
        font-size: .88rem;
        font-weight: 950;
    }
    .pp-review-list small {
        display: block;
        margin-top: .45rem;
        color: #64748B;
        font-size: .74rem;
        font-weight: 850;
    }
    .is-hidden-review { display: none; }
    .pp-show-more { width: 100%; margin-top: .75rem; background: rgba(241,98,15,.08); color: var(--delni-primary); border: 1px solid rgba(241,98,15,.18); }

    .pp-sidebar {
        position: sticky;
        top: calc(var(--pwa-header-height) + env(safe-area-inset-top) + .9rem);
    }
    .pp-favorite {
        width: 100%;
        background: #F8FAFC;
        color: #64748B;
    }
    .pp-favorite.is-favorited {
        background: rgba(241,98,15,.08);
        border-color: rgba(241,98,15,.25);
        color: var(--delni-primary);
    }

    @media (max-width: 980px) {
        .pp-layout { grid-template-columns: 1fr; }
        .pp-sidebar {
            position: static;
            order: -1;
        }
    }

    @media (max-width: 640px) {
        .pp-identity { border-radius: 18px; }
        .pp-identity__media { min-height: 118px; }
        .pp-identity__content {
            grid-template-columns: auto minmax(0, 1fr);
            align-items: start;
            justify-items: stretch;
            gap: .7rem;
            text-align: start;
        }
        .pp-rating-chip {
            grid-column: 1 / -1;
            justify-self: stretch;
            justify-content: center;
        }
        .pp-review-summary {
            align-items: center;
        }
        .pp-gallery {
            grid-template-columns: 1fr;
            gap: .75rem;
        }
        .pp-proj-slider,
        .pp-proj-slide img,
        .pp-gallery__empty {
            height: 190px;
        }
        .pp-proj-nav {
            width: 34px;
            height: 34px;
        }
        .pp-proj-nav--prev { inset-inline-start: .5rem; }
        .pp-proj-nav--next { inset-inline-end: .5rem; }
        .pp-proj-counter {
            inset-inline-start: .55rem;
            bottom: .55rem;
        }
        .pp-proj-dots {
            bottom: .62rem;
        }
        .pp-rs-score-wrap {
            justify-content: flex-start;
        }
        .pp-eyebrow { text-align: start; }
        .pp-action-primary { flex: 1 1 calc(50% - .3rem); }
    }

    [data-theme="dark"] .pp-identity,
    [data-theme="dark"] .pp-identity__content {
        background: #1E293B;
        border-color: #334155;
    }
    [data-theme="dark"] .pp-title h1,
    [data-theme="dark"] .pp-rating-chip strong { color: #F1F5F9; }
    [data-theme="dark"] .pp-avatar {
        border-color: #1E293B;
        background: #0F172A;
    }
    [data-theme="dark"] .pp-meta > span {
        background: #0F172A;
        border-color: #334155;
        color: #CBD5E1;
    }
    [data-theme="dark"] .pp-rating-chip {
        background: rgba(241,98,15,.12);
        border-color: rgba(241,98,15,.28);
    }
    [data-theme="dark"] .pp-rating-chip small { color: #CBD5E1; }
    [data-theme="dark"] .pp-back {
        background: rgba(15,23,42,.78);
        border-color: rgba(255,255,255,.12);
        color: #F1F5F9;
    }

    [data-theme="dark"] .pp-card,
    [data-theme="dark"] .pp-action-primary,
    [data-theme="dark"] .pp-action-icon,
    [data-theme="dark"] .pp-favorite {
        background: #1E293B;
        border-color: #334155;
        color: #F1F5F9;
    }
    [data-theme="dark"] .profile-section-title h2,
    [data-theme="dark"] .pp-facts strong,
    [data-theme="dark"] .pp-proj-info h3,
    [data-theme="dark"] .pp-credentials h3,
    [data-theme="dark"] .pp-review-list strong,
    [data-theme="dark"] .pp-review-form label { color: #F1F5F9; }
    [data-theme="dark"] .pp-text,
    [data-theme="dark"] .pp-note,
    [data-theme="dark"] .pp-proj-info p,
    [data-theme="dark"] .pp-credentials p,
    [data-theme="dark"] .pp-review-list p { color: #CBD5E1; }
    [data-theme="dark"] .pp-facts div,
    [data-theme="dark"] .pp-proj,
    [data-theme="dark"] .pp-credentials article,
    [data-theme="dark"] .pp-review-list article,
    [data-theme="dark"] .pp-review-notice,
    [data-theme="dark"] .pp-review-form {
        background: #0F172A;
        border-color: #334155;
    }
    [data-theme="dark"] .pp-review-form select,
    [data-theme="dark"] .pp-review-form textarea {
        background: #1E293B;
        border-color: #334155;
        color: #F1F5F9;
    }
    [data-theme="dark"] .pp-facts span,
    [data-theme="dark"] .pp-review-list small { color: #94A3B8; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var toast = null;

    function showLoginToast() {
        if (window.DelniAuthToast) {
            window.DelniAuthToast.show('سجّل دخولك لإضافة المزود إلى المفضلة', 'دخول', '{{ route('login') }}');
            return;
        }

        if (toast) { return; }
        toast = document.createElement('div');
        toast.className = 'pc-fav-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.innerHTML = '<span>سجل دخولك لإضافة مزودين إلى المفضلة</span><a href="{{ route('login') }}">دخول</a>';
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
                alert('يجب إدخال تقييم بالنجوم أو كتابة تعليق لإرسال المراجعة.');
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
                    // It returned HTML (likely redirected back due to middleware, session expiry, etc.)
                    // Reload the page to surface the redirect/flash message naturally
                    window.location.reload();
                    return new Promise(() => {}); // Stop promise chain
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
                    starSelector.parentNode.insertBefore(flash, starSelector);
                }
                flash.textContent = data.message || 'تم إرسال التقييم بنجاح.';
                flash.style.display = 'block';
                flash.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
                const errorFlash = document.querySelector('.pp-review-flash.is-error');
                if (errorFlash) { errorFlash.style.display = 'none'; }
            })
            .catch(err => {
                console.error(err);
                let flash = document.querySelector('.pp-review-flash.is-error');
                if (!flash) {
                    flash = document.createElement('div');
                    flash.className = 'pp-review-flash is-error';
                    starSelector.parentNode.insertBefore(flash, starSelector);
                }
                flash.textContent = err.message || err.errors?.rating?.[0] || err.errors?.comment?.[0] || 'تعذر إرسال التقييم. يرجى المحاولة لاحقاً.';
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
}());
</script>
@endpush
@endsection
