@extends('public.layout')

@section('title', ($profile->business_name ?? $profile->user?->name ?? 'مزود خدمة') . ' - ' . config('app.name'))

@section('content')
@php
    $businessName = $profile->business_name ?? $profile->user?->name ?? 'مزود خدمة';
    $rating = (float) ($profile->stats?->rating_avg ?? 0);
    $reviewsCount = (int) ($profile->stats?->reviews_count ?? 0);

    $logo = $profile->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($profile->logo) : null;
    $cover = $profile->cover_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($profile->cover_image) : null;

    $categoryName = $profile->category ? ($profile->category->localized_name ?? $profile->category->name) : null;
    $cityName = $profile->city ? ($profile->city->localized_name ?? $profile->city->name) : null;

    $phoneNumber = $profile->phone ? preg_replace('/\s+/', '', $profile->phone) : null;
    $whatsappNumber = $profile->whatsapp ? preg_replace('/[^0-9]/', '', $profile->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وصلت لملفك عبر دلني وأرغب بالاستفسار عن الخدمة.');

    $portfolioItems = ($portfolioItems ?? collect())->take(2);
    $reviews = $reviews ?? collect();
    $credentials = $credentials ?? ($profile->credentials ?? collect());
@endphp

<section class="profile-hero">
    @if($cover)
        <img src="{{ $cover }}" alt="{{ $businessName }}" class="profile-cover">
    @endif

    <div class="profile-hero__overlay">
        <div class="container">
            <div class="profile-head" style="align-items: flex-start;">
                <div class="profile-logo" style="flex-shrink: 0;">
                    @if($logo)
                        <img src="{{ $logo }}" alt="{{ $businessName }}" style="width: 400px; height: 400px; object-fit: cover;">
                    @else
                        <span>{{ mb_substr($businessName, 0, 1) }}</span>
                    @endif
                </div>

                <div class="profile-intro">
                    <h1>{{ $businessName }}</h1>

                    @if($profile->provider_type)
                        <p>{{ $profile->provider_type }}</p>
                    @endif

                    <div class="profile-meta">
                        @if($categoryName)
                            <span><x-render-icon icon="heroicon-o-briefcase" /> {{ $categoryName }}</span>
                        @endif

                        @if($cityName)
                            <span><x-render-icon icon="heroicon-o-map-pin" /> {{ $cityName }}</span>
                        @endif

                        @if($profile->experience_years)
                            <span>{{ $profile->experience_years }} سنوات خبرة</span>
                        @endif

                        @if($profile->offers_remote_work)
                            <span><x-render-icon icon="heroicon-o-globe-alt" /> عن بعد</span>
                        @endif
                    </div>

                    <div class="profile-rating">
                        <span class="stars">
                            @for($i = 1; $i <= 5; $i++)
                                <b class="{{ $i <= round($rating) ? '' : 'is-muted' }}">★</b>
                            @endfor
                        </span>
                        <strong>{{ number_format($rating, 1) }}</strong>
                        <a href="#reviews">{{ $reviewsCount }} تقييم</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="profile-jumpbar">
    <div class="container">
        <nav>
            @if($profile->bio)<a href="#about">نبذة</a>@endif
            @if($portfolioItems->isNotEmpty())<a href="#portfolio">الأعمال</a>@endif
            @if($credentials->isNotEmpty())<a href="#credentials">الشهادات</a>@endif
            <a href="#reviews">التقييمات</a>
            <a href="#contact">التواصل</a>
        </nav>
    </div>
</div>

<section class="profile-page">
    <div class="container">
        <div class="profile-layout">
            <main class="profile-main">
                @if($profile->bio)
                    <section id="about" class="profile-card">
                        <div class="section-head">
                            <span>نبذة</span>
                            <h2>عن مقدم الخدمة</h2>
                        </div>

                        <p class="profile-text">{{ $profile->bio }}</p>
                    </section>
                @endif

                @if($profile->subcategories->isNotEmpty() || $profile->service_area_note || $profile->offers_remote_work || $cityName)
                    <section class="profile-card">
                        <div class="section-head">
                            <span>الخدمات</span>
                            <h2>ماذا يقدم؟</h2>
                        </div>

                        @if($profile->subcategories->isNotEmpty())
                            <div class="tag-list">
                                @foreach($profile->subcategories as $subcategory)
                                    <span>{{ $subcategory->localized_name ?? $subcategory->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="info-grid">
                            @if($cityName)
                                <div>
                                    <strong>المدينة</strong>
                                    <span>{{ $cityName }}</span>
                                </div>
                            @endif

                            @if($profile->offers_remote_work)
                                <div>
                                    <strong>خدمة عن بعد</strong>
                                    <span>متاحة</span>
                                </div>
                            @endif

                            @if($profile->service_area_note)
                                <div class="wide">
                                    <strong>نطاق الخدمة</strong>
                                    <span>{{ $profile->service_area_note }}</span>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                @if($portfolioItems->isNotEmpty())
                    <section id="portfolio" class="profile-card">
                        <div class="section-head split">
                            <div>
                                <span>الأعمال</span>
                                <h2>مشاريع مختارة</h2>
                            </div>
                            <small>مشروعان فقط، وكل مشروع يحتوي معرض صور</small>
                        </div>

                        <div class="project-row">
                            @foreach($portfolioItems as $item)
                                @php
                                    $images = $item->images?->sortBy('sort_order') ?? collect();
                                    $firstImage = $images->first();
                                @endphp

                                <article class="project-card" data-project-card>
                                    <div class="project-slider" data-slider>
                                        @if($images->isNotEmpty())
                                            @foreach($images as $index => $image)
                                                <img
                                                    src="{{ Storage::disk('public')->url($image->path) }}"
                                                    alt="{{ $image->alt ?: $item->title }}"
                                                    class="{{ $index === 0 ? 'is-active' : '' }}"
                                                    data-slide
                                                >
                                            @endforeach
                                        @else
                                            <div class="project-empty">
                                                <x-render-icon icon="heroicon-o-photo" />
                                            </div>
                                        @endif

                                        @if($images->count() > 1)
                                            <button type="button" class="slider-btn slider-prev" data-prev>‹</button>
                                            <button type="button" class="slider-btn slider-next" data-next>›</button>
                                            <span class="slider-count">
                                                <b data-current>1</b> / {{ $images->count() }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="project-body">
                                        <h3>{{ $item->title }}</h3>

                                        @if($item->short_description)
                                            <p>{{ $item->short_description }}</p>
                                        @elseif($item->description)
                                            <p>{{ Str::limit(strip_tags($item->description), 130) }}</p>
                                        @endif

                                        <div class="project-actions">
                                            @if($images->count() > 1)
                                                <button type="button" data-next>
                                                    تصفح الصور
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($credentials->isNotEmpty())
                    <section id="credentials" class="profile-card">
                        <div class="section-head split">
                            <div>
                                <span>الثقة</span>
                                <h2>الشهادات والاعتمادات</h2>
                            </div>

                            <small>{{ $credentials->count() }} شهادة</small>
                        </div>

                        <div class="cert-strip">
                            @foreach($credentials as $credential)
                                <article class="cert-card">
                                    <h3>{{ $credential->title }}</h3>

                                    @if($credential->issuer)
                                        <p>{{ $credential->issuer }}</p>
                                    @endif

                                    <div class="cert-meta">
                                        @if($credential->issue_date)
                                            <span>{{ optional($credential->issue_date)->format('Y') }}</span>
                                        @endif

                                        @if($credential->verification_url)
                                            <a href="{{ $credential->verification_url }}" target="_blank" rel="noopener noreferrer nofollow">
                                                تحقق
                                            </a>
                                        @endif
                                    </div>

                                    @if($credential->notes)
                                        <small>{{ Str::limit($credential->notes, 120) }}</small>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section id="reviews" class="profile-card reviews-card">
                    <div class="section-head split">
                        <div>
                            <span>التقييمات</span>
                            <h2>آراء العملاء</h2>
                        </div>

                        <div class="review-score">
                            <strong>{{ number_format($rating, 1) }}</strong>
                            <span>{{ $reviewsCount }} تقييم</span>
                        </div>
                    </div>

                    @if(!auth()->check())
                        <div class="review-notice">
                            <p>سجل الدخول لكتابة تقييمك بعد التعامل مع مقدم الخدمة.</p>
                            <div>
                                <a href="{{ route('login') }}">تسجيل الدخول</a>
                                <a href="{{ route('register') }}">إنشاء حساب</a>
                            </div>
                        </div>
                    @elseif(!auth()->user()->hasRole('user'))
                        <div class="review-notice">مزودو الخدمات لا يمكنهم كتابة تقييمات.</div>
                    @elseif($profile->user_id === auth()->id())
                        <div class="review-notice">لا يمكنك تقييم ملفك الخاص.</div>
                    @else
                        <form method="POST" action="{{ route('review.store', $profile) }}" class="review-form">
                            @csrf

                            <div>
                                <label for="rating">التقييم</label>
                                <select id="rating" name="rating" required>
                                    <option value="">اختر التقييم</option>
                                    @for($r = 5; $r >= 1; $r--)
                                        <option value="{{ $r }}" @selected(old('rating') == $r)>{{ $r }} / 5</option>
                                    @endfor
                                </select>
                            </div>

                            <div>
                                <label for="comment">رأيك</label>
                                <textarea id="comment" name="comment" rows="4" maxlength="2000" placeholder="شارك تجربتك باختصار...">{{ old('comment') }}</textarea>
                            </div>

                            <button type="submit">إرسال التقييم</button>
                        </form>
                    @endif

                    @php
                        $sortedReviews = $reviews->sortByDesc('created_at')->values();
                    @endphp

                    <div class="reviews-list" id="reviewsList">
                        @forelse($sortedReviews as $index => $review)
                            <article class="review-item {{ $index >= 3 ? 'is-hidden-review' : '' }}" data-review-item>
                                <div class="review-top">
                                    <strong>{{ $review->user?->name ?? $review->reviewer_name ?? 'مستخدم دلني' }}</strong>

                                    <span>
                                        @for($i = 1; $i <= 5; $i++)
                                            <b class="{{ $i <= (int) $review->rating ? '' : 'is-muted' }}">★</b>
                                        @endfor
                                    </span>
                                </div>

                                @if($review->comment)
                                    <p>{{ $review->comment }}</p>
                                @endif

                                @if($review->created_at)
                                    <small>{{ $review->created_at->diffForHumans() }}</small>
                                @endif
                            </article>
                        @empty
                            <div class="empty-reviews">
                                <h3>لا توجد تقييمات بعد</h3>
                                <p>ستظهر تقييمات العملاء هنا بعد اعتمادها.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($sortedReviews->count() > 3)
                        <button type="button" class="show-all-reviews-btn" id="showAllReviewsBtn">
                            عرض كل التقييمات
                        </button>
                    @endif
                </section>
            </main>

            <aside id="contact" class="profile-sidebar">
                <div class="contact-panel">
                    <h2>تواصل بسرعة</h2>
                    <p>ابدأ من هنا، أو انتقل مباشرة للتقييمات قبل التواصل.</p>

                    <div class="contact-actions">
                        @if($whatsappNumber)
                            <a class="is-whatsapp" href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener">
                                واتساب
                            </a>
                        @endif

                        @if($phoneNumber)
                            <a href="tel:{{ $phoneNumber }}">اتصال</a>
                        @endif

                        @if($profile->map_url)
                            <a href="{{ $profile->map_url }}" target="_blank" rel="noopener noreferrer nofollow">الموقع</a>
                        @endif

                        <a href="#reviews" class="is-review">شوف التقييمات</a>
                    </div>

                    <div class="contact-stats">
                        <div>
                            <strong>{{ number_format($rating, 1) }}</strong>
                            <span>التقييم</span>
                        </div>

                        <div>
                            <strong>{{ $reviewsCount }}</strong>
                            <span>مراجعات</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-project-card]').forEach(function (card) {
            let slides = Array.from(card.querySelectorAll('[data-slide]'));
            let currentLabel = card.querySelector('[data-current]');
            let index = 0;

            function show(nextIndex) {
                if (!slides.length) return;
                index = (nextIndex + slides.length) % slides.length;

                slides.forEach(function (slide, i) {
                    slide.classList.toggle('is-active', i === index);
                });

                if (currentLabel) currentLabel.textContent = index + 1;
            }

            card.querySelectorAll('[data-next]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    show(index + 1);
                });
            });

            card.querySelectorAll('[data-prev]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    show(index - 1);
                });
            });
        });

        let showReviewsBtn = document.getElementById('showAllReviewsBtn');

        if (showReviewsBtn) {
            showReviewsBtn.addEventListener('click', function () {
                document.querySelectorAll('[data-review-item].is-hidden-review').forEach(function (item) {
                    item.classList.remove('is-hidden-review');
                });

                showReviewsBtn.remove();
            });
        }
    });
</script>

<style>
    .profile-hero {
        position: relative;
        min-height: 390px;
        overflow: hidden;
        background: linear-gradient(135deg, #0B1A34, #14284d);
    }

    .profile-cover {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: saturate(.92);
    }

    .profile-hero__overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: end;
        padding: 3rem 0;
        background: linear-gradient(to top, rgba(11,26,52,.96), rgba(11,26,52,.68), rgba(11,26,52,.34));
    }

    .profile-head {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: end;
        gap: 1.25rem;
        color: #fff;
    }

    @media (max-width: 900px) {
        .profile-head {
            gap: 1rem;
        }
    }

    .profile-logo {
        width: 128px;
        height: 128px;
        border-radius: 30px;
        overflow: hidden;
        border: 4px solid rgba(255,255,255,.9);
        background: #0B1A34;
        box-shadow: 0 22px 48px rgba(0,0,0,.24);
    }

    .profile-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-logo span {
        width: 100%;
        height: 100%;
        display: grid;
        place-items: center;
        color: #F1620F;
        font-size: 3rem;
        font-weight: 950;
    }

    .profile-intro h1 {
        margin: 0;
        font-size: clamp(2rem, 5vw, 3.5rem);
        line-height: 1.08;
        font-weight: 950;
        letter-spacing: -.055em;
    }

    .profile-intro p {
        margin: .45rem 0 0;
        color: rgba(255,255,255,.75);
        font-size: .95rem;
        font-weight: 750;
    }

    .profile-meta,
    .profile-rating {
        margin-top: .75rem;
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        align-items: center;
    }

    .profile-meta span {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        padding: .45rem .7rem;
        border-radius: 999px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.14);
        color: rgba(255,255,255,.86);
        font-size: .82rem;
        font-weight: 900;
    }

    .profile-meta svg {
        width: 17px;
        height: 17px;
        color: #F1620F;
    }

    .stars b,
    .review-item b {
        color: #F59E0B;
    }

    .is-muted {
        opacity: .25;
    }

    .profile-rating {
        color: rgba(255,255,255,.8);
        font-size: .88rem;
        font-weight: 850;
    }

    .profile-rating strong {
        color: #fff;
    }

    .profile-rating a {
        color: #ffb079;
        font-weight: 950;
        text-decoration: none;
    }

    .profile-jumpbar {
        position: sticky;
        top: 76px;
        z-index: 30;
        background: rgba(252,251,251,.9);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid #E7E7E7;
    }

    .profile-jumpbar nav {
        display: flex;
        gap: .55rem;
        overflow-x: auto;
        padding: .7rem 0;
    }

    .profile-jumpbar a {
        flex: 0 0 auto;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        padding: .5rem .8rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
        text-decoration: none;
        font-size: .84rem;
        font-weight: 900;
    }

    .profile-page {
        padding: 1.5rem 0 4rem;
        background: #FCFBFB;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 330px;
        gap: 1.25rem;
        align-items: start;
    }

    .profile-main {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-width: 0;
    }

    .profile-card {
        padding: 1.35rem;
        border-radius: 24px;
        background: #fff;
        border: 1px solid #E7E7E7;
        box-shadow: 0 12px 28px rgba(11,26,52,.045);
        scroll-margin-top: 140px;
    }

    .section-head {
        margin-bottom: .9rem;
    }

    .section-head.split {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
    }

    .section-head span {
        display: block;
        margin-bottom: .3rem;
        color: #F1620F;
        font-size: .8rem;
        font-weight: 950;
    }

    .section-head h2 {
        margin: 0;
        color: #0B1A34;
        font-size: 1.35rem;
        font-weight: 950;
        letter-spacing: -.035em;
    }

    .section-head small {
        color: #5D5959;
        font-size: .82rem;
        font-weight: 850;
    }

    .profile-text,
    .profile-card p {
        margin: 0;
        color: #5D5959;
        font-size: .95rem;
        line-height: 1.9;
        font-weight: 600;
    }

    .tag-list {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-bottom: 1rem;
    }

    .tag-list span {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        padding: .45rem .75rem;
        border-radius: 999px;
        background: rgba(241,98,15,.08);
        color: #F1620F;
        border: 1px solid rgba(241,98,15,.12);
        font-size: .84rem;
        font-weight: 900;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .info-grid div {
        padding: .9rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
    }

    .info-grid .wide {
        grid-column: 1 / -1;
    }

    .info-grid strong,
    .info-grid span {
        display: block;
    }

    .info-grid strong {
        color: #0B1A34;
        font-size: .9rem;
        font-weight: 950;
    }

    .info-grid span {
        margin-top: .2rem;
        color: #5D5959;
        font-size: .85rem;
        line-height: 1.7;
        font-weight: 700;
    }

    .project-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .9rem;
    }

    .project-card {
        overflow: hidden;
        border-radius: 22px;
        background: #fff;
        border: 1px solid #E7E7E7;
        box-shadow: 0 10px 24px rgba(11,26,52,.04);
    }

    .project-slider {
        position: relative;
        height: 235px;
        overflow: hidden;
        background: #0B1A34;
    }

    .project-slider img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transform: scale(1.02);
        transition: .25s ease;
    }

    .project-slider img.is-active {
        opacity: 1;
        transform: scale(1);
    }

    .project-empty {
        height: 100%;
        display: grid;
        place-items: center;
        color: rgba(255,255,255,.5);
    }

    .project-empty svg {
        width: 44px;
        height: 44px;
    }

    .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 999px;
        background: rgba(255,255,255,.92);
        color: #0B1A34;
        font-size: 1.6rem;
        line-height: 1;
        cursor: pointer;
        z-index: 2;
    }

    .slider-prev {
        inset-inline-start: .75rem;
    }

    .slider-next {
        inset-inline-end: .75rem;
    }

    .slider-count {
        position: absolute;
        bottom: .75rem;
        inset-inline-end: .75rem;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        padding: .35rem .65rem;
        border-radius: 999px;
        background: rgba(11,26,52,.72);
        color: #fff;
        font-size: .8rem;
        font-weight: 900;
        z-index: 2;
    }

    .project-body {
        padding: 1rem;
    }

    .project-body h3 {
        margin: 0 0 .45rem;
        color: #0B1A34;
        font-size: 1rem;
        line-height: 1.5;
        font-weight: 950;
    }

    .project-actions {
        margin-top: .85rem;
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .project-actions a,
    .project-actions button {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .55rem .75rem;
        border-radius: 13px;
        border: 1px solid rgba(241,98,15,.18);
        background: rgba(241,98,15,.08);
        color: #F1620F;
        font: inherit;
        font-size: .82rem;
        font-weight: 950;
        text-decoration: none;
        cursor: pointer;
    }

    .cert-strip {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(260px, 320px);
        gap: .75rem;
        overflow-x: auto;
        padding-bottom: .4rem;
        scroll-snap-type: x mandatory;
    }

    .cert-card {
        scroll-snap-align: start;
        padding: 1rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
    }

    .cert-card h3 {
        margin: 0 0 .35rem;
        color: #0B1A34;
        font-size: .95rem;
        line-height: 1.5;
        font-weight: 950;
    }

    .cert-card p {
        font-size: .84rem;
    }

    .cert-meta {
        margin-top: .6rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .6rem;
    }

    .cert-meta span,
    .cert-meta a {
        font-size: .78rem;
        font-weight: 950;
    }

    .cert-meta a {
        color: #F1620F;
        text-decoration: none;
    }

    .cert-card small {
        display: block;
        margin-top: .6rem;
        color: #5D5959;
        line-height: 1.7;
    }

    .reviews-card {
        border-color: rgba(241,98,15,.2);
    }

    .review-score {
        padding: .6rem .8rem;
        border-radius: 16px;
        background: rgba(241,98,15,.08);
        color: #F1620F;
        text-align: center;
    }

    .review-score strong,
    .review-score span {
        display: block;
    }

    .review-score strong {
        font-size: 1.25rem;
        font-weight: 950;
    }

    .review-score span {
        font-size: .75rem;
        font-weight: 900;
    }

    .review-notice,
    .review-form {
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
    }

    .review-notice div {
        margin-top: .75rem;
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .review-notice a,
    .review-form button {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .6rem .85rem;
        border-radius: 13px;
        border: 0;
        background: #F1620F;
        color: #fff;
        font: inherit;
        font-size: .84rem;
        font-weight: 950;
        text-decoration: none;
        cursor: pointer;
    }

    .review-form {
        display: grid;
        gap: .75rem;
    }

    .review-form label {
        display: block;
        margin-bottom: .35rem;
        color: #0B1A34;
        font-size: .84rem;
        font-weight: 950;
    }

    .review-form select,
    .review-form textarea {
        width: 100%;
        border: 1px solid #E7E7E7;
        border-radius: 15px;
        background: #fff;
        padding: .75rem;
        font: inherit;
        outline: none;
    }

    .reviews-list {
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    .is-hidden-review {
        display: none;
    }

    .show-all-reviews-btn {
        width: 100%;
        min-height: 44px;
        margin-top: .85rem;
        border: 1px solid rgba(241,98,15,.2);
        border-radius: 15px;
        background: rgba(241,98,15,.08);
        color: #F1620F;
        font: inherit;
        font-size: .88rem;
        font-weight: 950;
        cursor: pointer;
    }

    .show-all-reviews-btn:hover {
        background: rgba(241,98,15,.12);
    }

    .review-item {
        padding: 1rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
    }

    .review-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .45rem;
    }

    .review-top strong {
        color: #0B1A34;
        font-size: .92rem;
        font-weight: 950;
    }

    .review-item small {
        display: block;
        margin-top: .5rem;
        color: #5D5959;
        font-size: .75rem;
        font-weight: 800;
    }

    .empty-reviews {
        text-align: center;
        padding: 2rem 1rem;
        border-radius: 18px;
        background: #FCFBFB;
        border: 1px dashed #E7E7E7;
    }

    .empty-reviews h3 {
        margin: 0 0 .35rem;
        color: #0B1A34;
        font-size: 1.05rem;
        font-weight: 950;
    }

    .profile-sidebar {
        position: sticky;
        top: 130px;
    }

    .contact-panel {
        padding: 1.15rem;
        border-radius: 24px;
        background: #fff;
        border: 1px solid #E7E7E7;
        box-shadow: 0 16px 36px rgba(11,26,52,.07);
    }

    .contact-panel h2 {
        margin: 0;
        color: #0B1A34;
        font-size: 1.2rem;
        font-weight: 950;
        letter-spacing: -.035em;
    }

    .contact-panel p {
        margin: .4rem 0 1rem;
        color: #5D5959;
        font-size: .88rem;
        line-height: 1.7;
        font-weight: 600;
    }

    .contact-actions {
        display: flex;
        flex-direction: column;
        gap: .65rem;
    }

    .contact-actions a {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: #FCFBFB;
        border: 1px solid #E7E7E7;
        color: #0B1A34;
        text-decoration: none;
        font-size: .92rem;
        font-weight: 950;
    }

    .contact-actions .is-whatsapp {
        background: #22C55E;
        border-color: #22C55E;
        color: #fff;
        box-shadow: 0 14px 28px rgba(34,197,94,.2);
    }

    .contact-actions .is-review {
        background: rgba(241,98,15,.08);
        border-color: rgba(241,98,15,.16);
        color: #F1620F;
    }

    .contact-stats {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #E7E7E7;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .7rem;
    }

    .contact-stats div {
        padding: .75rem;
        border-radius: 16px;
        background: #FCFBFB;
        text-align: center;
    }

    .contact-stats strong {
        display: block;
        color: #0B1A34;
        font-size: 1.15rem;
        font-weight: 950;
    }

    .contact-stats span {
        display: block;
        margin-top: .25rem;
        color: #5D5959;
        font-size: .75rem;
        font-weight: 850;
    }

    @media (max-width: 1080px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }

        .profile-sidebar {
            position: static;
            order: -1;
        }

        .contact-panel {
            display: grid;
            grid-template-columns: minmax(0, .8fr) minmax(0, 1.2fr);
            gap: 1rem;
            align-items: start;
        }

        .contact-stats {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 900px) {
        .profile-hero {
            min-height: 420px;
        }

        .profile-hero__overlay {
            padding: 2.5rem 0;
        }

        .profile-logo {
            width: 100px;
            height: 100px;
            border-radius: 22px;
            border-width: 3px;
        }

        .profile-intro h1 {
            font-size: clamp(1.75rem, 4vw, 2.8rem);
        }

        .profile-intro p {
            font-size: .88rem;
        }

        .profile-meta span,
        .profile-rating {
            font-size: .76rem;
            padding: .4rem .65rem;
        }
    }

    @media (max-width: 760px) {
        .profile-hero {
            min-height: 480px;
        }

        .profile-hero__overlay {
            padding: 2rem 0;
        }

        .profile-head {
            grid-template-columns: 1fr;
            text-align: center;
            justify-items: center;
            gap: .8rem;
        }

        .profile-logo {
            width: 90px;
            height: 90px;
            border-radius: 20px;
            border-width: 3px;
        }

        .profile-meta,
        .profile-rating {
            justify-content: center;
        }

        .profile-intro h1 {
            font-size: 1.5rem;
            margin-top: .3rem;
        }

        .profile-intro p {
            font-size: .82rem;
            margin-top: .25rem;
        }

        .profile-meta {
            margin-top: .5rem;
            gap: .35rem;
        }

        .profile-meta span {
            font-size: .7rem;
            padding: .35rem .6rem;
        }

        .profile-rating {
            margin-top: .5rem;
            font-size: .75rem;
            gap: .3rem;
        }

        .stars b {
            font-size: .9rem;
        }

        .profile-hero-actions {
            width: 100%;
            max-width: 360px;
        }

        .profile-card {
            padding: 1rem;
            border-radius: 22px;
        }

        .section-head.split {
            align-items: start;
            flex-direction: column;
        }

        .project-row,
        .info-grid {
            grid-template-columns: 1fr;
        }

        .project-slider {
            height: 215px;
        }

        .contact-panel {
            display: block;
        }

        .contact-stats {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .profile-jumpbar {
            top: 68px;
        }
    }

    @media (max-width: 480px) {
        .profile-hero {
            min-height: 420px;
        }

        .profile-hero__overlay {
            padding: 1.5rem 0;
        }

        .profile-head {
            gap: .6rem;
        }

        .profile-logo {
            width: 80px;
            height: 80px;
            border-radius: 18px;
            border-width: 2px;
        }

        .profile-logo span {
            font-size: 2.2rem;
        }

        .profile-intro h1 {
            font-size: 1.35rem;
            margin-top: .2rem;
        }

        .profile-intro p {
            font-size: .75rem;
        }

        .profile-meta {
            margin-top: .4rem;
            gap: .25rem;
        }

        .profile-meta span {
            font-size: .65rem;
            padding: .3rem .55rem;
            min-height: 30px;
            gap: .25rem;
        }

        .profile-meta svg {
            width: 14px;
            height: 14px;
        }

        .profile-rating {
            margin-top: .4rem;
            font-size: .7rem;
            gap: .25rem;
        }

        .profile-page {
            padding-top: 1rem;
        }

        .profile-jumpbar {
            top: 64px;
        }

        .cert-strip {
            grid-auto-columns: minmax(240px, 88vw);
        }

        .project-slider {
            height: 180px;
        }
    }
</style>
@endsection

 