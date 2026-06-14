<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchProfilesRequest;
use App\Models\Category;
use App\Models\City;
use App\Models\ContactInfo;
use App\Models\Profile;
use App\Models\Subcategory;
use App\Services\PublicFrontendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class FrontendController extends Controller
{
    public function __construct(private readonly PublicFrontendService $frontendService) {}

    private function ctaWhatsappUrl(): ?string
    {
        $contact = ContactInfo::first();
        if (! $contact?->whatsapp) {
            return null;
        }

        return 'https://wa.me/'.preg_replace('/[^0-9]/', '', $contact->whatsapp);
    }

    public function home(): View
    {
        $payload = $this->frontendService->homepage();

        return view('public.home', $payload['data'] + [
            'queryStats' => $payload['queryStats'],
            'ctaWhatsappUrl' => $this->ctaWhatsappUrl(),
        ]);
    }

    public function search(SearchProfilesRequest $request): View
    {
        $searchPayload = $this->frontendService->search($request->validated());

        return view('public.home', $searchPayload['data'] + [
            'queryStats' => $searchPayload['queryStats'],
            'ctaWhatsappUrl' => $this->ctaWhatsappUrl(),
        ]);
    }

    public function topRated(Request $request): View
    {
        $payload = $this->frontendService->topRated($request);

        return view('public.top-rated', $payload['data'] + [
            'queryStats' => $payload['queryStats'],
        ]);
    }

    public function topRatedInCity(City $city, Request $request): View
    {
        abort_unless($city->is_active, 404);

        return $this->withCityFilter($request, $city, fn () => $this->topRated($request));
    }

    public function categories(): View
    {
        $payload = $this->frontendService->allCategories();

        return view('public.categories', $payload['data'] + [
            'queryStats' => $payload['queryStats'],
            'ctaWhatsappUrl' => $this->ctaWhatsappUrl(),
        ]);
    }

    public function category(Category $category, Request $request): View
    {
        abort_unless($category->is_active, 404);

        $payload = $this->frontendService->category($category, $request);

        return view('public.category', $payload['data'] + [
            'queryStats' => $payload['queryStats'],
        ]);
    }

    public function categoryInCity(Category $category, City $city, Request $request): View
    {
        abort_unless($city->is_active, 404);

        return $this->withCityFilter($request, $city, fn () => $this->category($category, $request));
    }

    public function subcategory(Subcategory $subcategory, Request $request): View
    {
        abort_unless($subcategory->is_active, 404);
        abort_unless($subcategory->category?->is_active, 404);

        $payload = $this->frontendService->subcategory($subcategory, $request);

        return view('public.subcategory', $payload['data'] + [
            'queryStats' => $payload['queryStats'],
        ]);
    }

    public function subcategoryInCity(Subcategory $subcategory, City $city, Request $request): View
    {
        abort_unless($city->is_active, 404);

        return $this->withCityFilter($request, $city, fn () => $this->subcategory($subcategory, $request));
    }

    public function city(City $city, Request $request): View
    {
        abort_unless($city->is_active, 404);

        $payload = $this->frontendService->city($city, $request);

        return view('public.city', $payload['data'] + [
            'queryStats' => $payload['queryStats'],
        ]);
    }

    public function provider(Profile $profile, Request $request): View
    {
        $payload = $this->frontendService->provider($profile);

        return view('public.provider', $payload['data'] + [
            'queryStats' => $payload['queryStats'],
            'isFavorited' => (bool) $request->user()?->favorites()
                ->where('profile_id', $profile->id)
                ->exists(),
        ]);
    }

    public function switchLocale(string $locale, Request $request): RedirectResponse
    {
        // Delni is Arabic-only for MVP — locale is always forced to 'ar' intentionally.
        $request->session()->put('locale', 'ar');
        Cookie::queue('locale', 'ar', 60 * 24 * 365);

        return back();
    }

    private function withCityFilter(Request $request, City $city, callable $callback): View
    {
        $request->merge(['city_id' => $city->id]);
        $request->attributes->set('active_city', $city);

        return $callback();
    }
}
