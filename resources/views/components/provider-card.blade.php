@props([
    'provider',
    'showBio' => true,
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

    $serviceTags = $provider->relationLoaded('subcategories')
        ? $provider->subcategories->take(3)
        : collect();

    $whatsappNumber = $provider->whatsapp ? preg_replace('/[^0-9]/', '', $provider->whatsapp) : null;
    $whatsappMessage = rawurlencode('السلام عليكم، وجدتك عبر دلني وأرغب بالاستفسار عن الخدمة.');

    $initials = mb_substr($businessName, 0, 1);

    $isFavorited = in_array($provider->id, $favoriteProfileIds, true);
    $isTopRated = (bool) ($provider->stats?->is_top_rated ?? false);
@endphp

<article class="flex h-full min-h-[410px] flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-xs transition-all duration-300 group hover:border-primary/20 hover:shadow-md dark:border-slate-700/60 dark:bg-slate-800 dark:hover:border-primary/30">
    {{-- Image banner with logo badge --}}
    <div class="relative flex-none">
        <a href="{{ route('public.provider', $provider->slug) }}" class="block h-[156px] w-full overflow-hidden bg-gradient-to-br from-slate-900 to-slate-800 md:h-[168px]" aria-label="{{ $businessName }}">
            @if($coverImage)
                <img src="{{ $coverImage }}" alt="{{ $businessName }}" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-103">
            @elseif($logoImage)
                <img src="{{ $logoImage }}" alt="{{ $businessName }}" loading="lazy" decoding="async" class="w-full h-full object-contain p-6 bg-gradient-to-br from-slate-900 to-slate-800">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <span class="text-4xl md:text-5xl font-black text-primary/60">{{ $initials }}</span>
                </div>
            @endif

            {{-- Logo badge floating at bottom-left of banner --}}
            @if($coverImage && $logoImage)
                <div class="absolute bottom-3 left-3 w-12 h-12 rounded-xl overflow-hidden border-2 border-white/90 dark:border-slate-800/95 bg-white dark:bg-slate-900 shadow-md">
                    <img src="{{ $logoImage }}" alt="{{ $businessName }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                </div>
            @endif

            {{-- Rating badge floating top-right --}}
            @if($rating > 0)
                <div class="absolute top-3 right-3 flex items-center gap-1 min-h-[26px] px-2 py-0.5 border border-white/20 dark:border-slate-700/40 rounded-full bg-white/90 dark:bg-slate-950/85 backdrop-blur-xs text-slate-900 dark:text-slate-100 text-[11px] font-black shadow-md" aria-label="متوسط التقييم {{ number_format($rating, 1) }} من 5">
                    <span class="text-amber-500 text-xs">★</span>
                    <span>{{ number_format($rating, 1) }}</span>
                </div>
            @endif
        </a>

        {{-- Favorite toggle top-left --}}
        @auth
            <button
                class="pc-fav-btn absolute top-3 left-3 w-10 h-10 rounded-full bg-slate-950/45 dark:bg-slate-900/60 backdrop-blur-xs flex items-center justify-center text-white/90 hover:text-primary transition-all active:scale-90 hover:bg-slate-950/55 [&>.pc-fav-filled]:hidden [&>.pc-fav-outline]:block [&.is-favorited>.pc-fav-filled]:block [&.is-favorited>.pc-fav-outline]:hidden [&.is-favorited]:text-primary [&.is-favorited]:bg-primary/20 {{ $isFavorited ? 'is-favorited' : '' }}"
                data-toggle-url="{{ route('favorites.toggle', $provider) }}"
                aria-label="{{ $isFavorited ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة' }}"
                type="button"
            >
                <x-render-icon icon="app-heart" class="pc-fav-outline w-5 h-5" />
                <x-render-icon icon="app-heart-filled" class="pc-fav-filled w-5 h-5" />
            </button>
        @else
            <button class="absolute top-3 left-3 w-10 h-10 rounded-full bg-slate-950/45 dark:bg-slate-900/60 backdrop-blur-xs flex items-center justify-center text-white/90 hover:text-primary transition-all active:scale-90 hover:bg-slate-950/55 pc-fav-guest" type="button" aria-label="أضف إلى المفضلة">
                <x-render-icon icon="app-heart" class="pc-fav-outline w-5 h-5" />
            </button>
        @endauth
    </div>

    {{-- Card body --}}
    <div class="flex min-h-0 flex-1 flex-col gap-3 p-4">
        <h4 class="m-0 min-h-[3rem] text-sm font-black leading-snug text-slate-900 line-clamp-2 dark:text-slate-100 md:text-base">
            <a href="{{ route('public.provider', $provider->slug) }}" class="hover:text-primary text-decoration-none transition-colors">{{ $businessName }}</a>
        </h4>

        <div class="flex min-h-[20px] items-start">
            @if($isTopRated)
                <span class="inline-flex items-center self-start gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[10px] font-black leading-none text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300">★ الأعلى تقييماً</span>
            @endif
        </div>

        <div class="flex h-[56px] flex-wrap content-start gap-1.5 overflow-hidden">
            @if($categoryName)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 border border-slate-200 dark:border-slate-800 rounded-full bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 text-[10px] font-bold [&>svg]:w-3.5 [&>svg]:h-3.5 [&>svg]:text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" /><path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" /></svg>
                    {{ $categoryName }}
                </span>
            @endif
            @if($cityName)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 border border-slate-200 dark:border-slate-800 rounded-full bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 text-[10px] font-bold [&>svg]:w-3.5 [&>svg]:h-3.5 [&>svg]:text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                    {{ $cityName }}
                </span>
            @endif
        </div>

        <div class="flex h-[57px] flex-wrap content-start gap-1.5 overflow-hidden">
            @if($serviceTags->isNotEmpty())
                @foreach($serviceTags as $subcategory)
                    <a href="{{ route('public.subcategory', $subcategory->slug) }}" class="inline-flex items-center px-2.5 py-0.5 border border-slate-200 dark:border-slate-800/80 rounded-full bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-300 hover:text-primary hover:border-orange-500/20 hover:bg-orange-50/50 dark:hover:bg-orange-950/20 transition-colors text-[10px] font-bold truncate max-w-full text-decoration-none">
                        {{ $subcategory->localized_name ?? $subcategory->name }}
                    </a>
                @endforeach
            @endif
        </div>

        <div class="flex gap-2.5 mt-auto pt-2">
            <a href="{{ route('public.provider', $provider->slug) }}" class="flex-1 min-h-[42px] inline-flex items-center justify-center gap-1.5 rounded-xl text-xs md:text-sm font-black transition-all active:scale-97 cursor-pointer text-decoration-none bg-primary hover:bg-primary-dark text-white shadow-sm shadow-orange-500/10 hover:shadow-orange-500/25">
                عرض الملف
            </a>
            @if($whatsappNumber)
                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener noreferrer" class="flex-1 min-h-[42px] inline-flex items-center justify-center gap-1.5 rounded-xl text-xs md:text-sm font-black transition-all active:scale-97 cursor-pointer text-decoration-none bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100/60 dark:hover:bg-emerald-950/40 [&>svg]:w-4 [&>svg]:h-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.125.557 4.122 1.529 5.857L0 24l6.335-1.507A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.788 9.788 0 01-5.027-1.384l-.36-.214-3.762.895.952-3.659-.235-.375A9.786 9.786 0 012.182 12C2.182 6.57 6.57 2.182 12 2.182S21.818 6.57 21.818 12 17.43 21.818 12 21.818z"/></svg>
                    واتساب
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
                    window.DelniAuthToast.show('سجّل دخولك لإضافة المزود إلى المفضلة', 'دخول', '{{ route('login') }}');
                    return;
                }

                if (toast) { return; }
                toast = document.createElement('div');
                toast.className = 'pc-fav-toast';
                toast.setAttribute('role', 'status');
                toast.setAttribute('aria-live', 'polite');
                toast.innerHTML = '<span>سجّل دخولك لإضافة مزودين إلى المفضلة</span><a href="{{ route('login') }}">دخول</a>';
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
