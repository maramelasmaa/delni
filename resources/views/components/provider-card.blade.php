@props([
    'provider',
    'showBio' => false,
    'isGrid' => true,
    'favoriteProfileIds' => [],
])

@php
    $businessName = $provider->business_name ?? __('messages.public.provider');

    $coverImage = $provider->cover_image
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($provider->cover_image)
        : null;

    $logoImage = $provider->logo
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($provider->logo)
        : null;

    $reviewsCount = (int) ($provider->getAttribute('approved_reviews_count') ?? 0);
    $rating = $reviewsCount > 0
        ? (float) ($provider->getAttribute('approved_reviews_avg_rating') ?? 0)
        : 0.0;

    $categoryName = $provider->category
        ? ($provider->category->localized_name ?? $provider->category->name)
        : null;

    $cityName = $provider->city
        ? ($provider->city->localized_name ?? $provider->city->name)
        : null;

    $whatsappNumber = $provider->whatsapp ? preg_replace('/[^0-9]/', '', $provider->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وجدتك عبر دلني وأرغب بالاستفسار عن الخدمة.');

    $initials = mb_substr($businessName, 0, 1);

    $isFavorited = in_array($provider->id, $favoriteProfileIds, true);
    $isTopRated = (bool) ($provider->stats?->is_top_rated ?? false) || ($reviewsCount >= 5 && $rating >= 4.5);
@endphp

<article class="flex flex-col bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all duration-300 group {{ $isGrid ? 'w-full' : 'w-[250px] min-w-[250px] md:w-auto md:min-w-0 lg:w-auto lg:min-w-0 snap-start' }}">
    {{-- Non-dominating header --}}
    <div class="relative h-11 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-800/60 overflow-hidden">
        @if($coverImage)
            <img src="{{ $coverImage }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover opacity-85">
        @endif
        
        <div class="absolute top-2 right-2 flex flex-wrap gap-1 items-center z-10">
            @if($provider->stats?->is_homepage_featured)
                <span class="inline-flex items-center gap-0.5 bg-orange-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-sm shrink-0 whitespace-nowrap">
                    مميز
                </span>
            @endif
            @if($isTopRated)
                <span class="inline-flex items-center gap-0.5 bg-amber-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-sm shrink-0 whitespace-nowrap">
                    ★ الأعلى تقييماً
                </span>
            @endif
        </div>

        {{-- Favorite button --}}
        @auth
            <button
                class="pc-fav-btn absolute top-2 left-2 w-7 h-7 rounded-full bg-slate-950/45 dark:bg-slate-900/60 backdrop-blur-xs flex items-center justify-center text-white/90 hover:text-primary transition-all active:scale-90 hover:bg-slate-950/55 [&>.pc-fav-filled]:hidden [&>.pc-fav-outline]:block [&.is-favorited>.pc-fav-filled]:block [&.is-favorited>.pc-fav-outline]:hidden [&.is-favorited]:text-primary [&.is-favorited]:bg-primary/20 {{ $isFavorited ? 'is-favorited' : '' }}"
                data-toggle-url="{{ route('favorites.toggle', $provider) }}"
                aria-label="{{ $isFavorited ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة' }}"
                type="button"
            >
                <x-render-icon icon="app-heart" class="pc-fav-outline w-4 h-4" />
                <x-render-icon icon="app-heart-filled" class="pc-fav-filled w-4 h-4" />
            </button>
        @else
            <button class="absolute top-2 left-2 w-7 h-7 rounded-full bg-slate-950/45 dark:bg-slate-900/60 backdrop-blur-xs flex items-center justify-center text-white/90 hover:text-primary transition-all active:scale-90 hover:bg-slate-950/55 pc-fav-guest" type="button" aria-label="أضف إلى المفضلة">
                <x-render-icon icon="app-heart" class="pc-fav-outline w-4 h-4" />
            </button>
        @endauth
    </div>

    {{-- Avatar Offset --}}
    <div class="px-3.5 -mt-5 flex items-end justify-between relative z-10">
        <div class="w-10 h-10 rounded-xl border-2 border-white dark:border-slate-900 overflow-hidden bg-white dark:bg-slate-950 flex items-center justify-center shadow-xs">
            @if($logoImage)
                <img src="{{ $logoImage }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover">
            @else
                <span class="text-base font-black text-primary">{{ $initials }}</span>
            @endif
        </div>

        {{-- Rating display --}}
        @if($rating > 0)
            <div class="flex items-center gap-0.5 text-slate-800 dark:text-slate-200 text-xs font-black bg-slate-50 dark:bg-slate-950/70 border border-slate-200/50 dark:border-slate-800/80 px-2 py-0.5 rounded-full mb-1">
                <span class="text-amber-500 text-xs leading-none">★</span>
                <span dir="ltr" class="font-sans">{{ number_format($rating, 1) }}</span>
                <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold font-sans" dir="ltr">({{ $reviewsCount }})</span>
            </div>
        @else
            <span class="text-slate-400 dark:text-slate-500 text-[9px] font-bold mb-1 px-1">جديد</span>
        @endif
    </div>

    {{-- Content --}}
    <div class="flex flex-col gap-1.5 p-3 flex-grow">
        <h4 class="m-0 text-slate-900 dark:text-slate-100 text-[11px] font-black leading-tight line-clamp-1">
            <a href="{{ route('public.provider', $provider->slug) }}" class="hover:text-primary text-decoration-none transition-colors">{{ $businessName }}</a>
        </h4>

        <div class="flex flex-wrap gap-1 items-center mt-0.5">
            @if($categoryName)
                <span class="text-[8.5px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                    {{ $categoryName }}
                </span>
            @endif
            @if($cityName)
                <span class="text-[8.5px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                    {{ $cityName }}
                </span>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex gap-2 mt-2.5 pt-1.5 border-t border-slate-100 dark:border-slate-800/60">
            <a href="{{ route('public.provider', $provider->slug) }}" class="flex-grow min-h-[30px] inline-flex items-center justify-center rounded-lg text-[10px] font-black transition-all active:scale-97 cursor-pointer text-decoration-none bg-primary hover:bg-orange-600 text-white shadow-xs">
                عرض الملف
            </a>
            @if($whatsappNumber)
                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener noreferrer" class="w-7.5 h-7.5 inline-flex items-center justify-center rounded-lg text-decoration-none bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100/60 dark:hover:bg-emerald-950/40 transition-colors cursor-pointer shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.125.557 4.122 1.529 5.857L0 24l6.335-1.507A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.788 9.788 0 01-5.027-1.384l-.36-.214-3.762.895.952-3.659-.235-.375A9.786 9.786 0 012.182 12C2.182 6.57 6.57 2.182 12 2.182S21.818 6.57 21.818 12 17.43 21.818 12 21.818z"/></svg>
                </a>
            @endif
        </div>
    </div>
</article>

@once
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
                toast.innerHTML = '<span>سجّل دخولك لإضافة مقدمي خدمات إلى المفضلة</span><a href="{{ route('login') }}">تسجيل الدخول</a>';
                document.body.appendChild(toast);
                requestAnimationFrame(function () { toast.classList.add('is-visible'); });
                setTimeout(function () {
                    toast.classList.remove('is-visible');
                    setTimeout(function () { toast && toast.remove(); toast = null; }, 300);
                }, 5000);
            }

            document.addEventListener('click', function (e) {
                if (e.target.closest('.pc-fav-guest')) {
                    e.preventDefault();
                    e.stopPropagation();
                    showLoginToast();
                    return;
                }

                const btn = e.target.closest('.pc-fav-btn[data-toggle-url]');
                if (!btn) { return; }

                e.preventDefault();
                e.stopPropagation();

                const url = btn.dataset.toggleUrl;
                const token = document.querySelector('meta[name="csrf-token"]')?.content;

                btn.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                })
                .then(r => r.json())
                .then(data => {
                    btn.classList.toggle('is-favorited', data.favorited);
                    btn.setAttribute('aria-label', data.favorited ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة');
                })
                .catch(function () {})
                .finally(function () { btn.disabled = false; });
            });
        }());
        </script>
    @endpush
@endonce
