@extends('public.layout')

@section('title', __('messages.public.search_results') . ' - ' . config('app.name'))

@section('content')

<!-- Hero Section -->
<section class="bg-navy-800 text-white section-compact">
    <div class="container">
        <h1 class="text-4xl font-black mb-2">
            {{ __('messages.public.search_results') }}
        </h1>
        <p class="text-lg text-white/75">
            {{ __('messages.public.find_trusted_professionals') }}
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <x-search-filters
                    :categories="$categories"
                    :cities="$cities"
                    :providerTypes="$providerTypes ?? null"
                />

                @if(request()->anyFilled(['keyword', 'category_id', 'city_id', 'provider_type', 'sort']))
                    <div class="mt-4">
                        <a href="{{ route('public.search') }}" class="btn btn-outline btn-sm w-full text-center">
                            <x-render-icon icon="heroicon-o-arrow-path" class="w-4 h-4 inline-block mr-2" />
                            {{ __('messages.public.clear_filters') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Results Section -->
        <div class="lg:col-span-3">
            <!-- Results Header -->
            <div class="mb-8 pb-6 border-b border-gray-200">
                <div class="flex flex-wrap justify-between items-start gap-4">
                    <div>
                        <p class="text-gray-600 mb-0">
                            <strong class="text-gray-900">{{ $profiles->total() }}</strong>
                            {{ __('messages.public.professionals') }}
                            @if(request('keyword'))
                                {{ __('messages.public.for') }}
                                <strong class="text-gray-900">"{{ request('keyword') }}"</strong>
                            @endif
                        </p>

                        <!-- Active Filters Display -->
                        @if(request()->anyFilled(['category_id', 'city_id', 'provider_type', 'remote']))
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if(request('category_id') && $categories->find(request('category_id')))
                                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-bold">
                                        {{ $categories->find(request('category_id'))->name }}
                                        <a href="{{ request()->fullUrlWithQuery(['category_id' => null]) }}" class="hover:opacity-70">×</a>
                                    </span>
                                @endif

                                @if(request('city_id') && $cities->find(request('city_id')))
                                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-bold">
                                        {{ $cities->find(request('city_id'))->name }}
                                        <a href="{{ request()->fullUrlWithQuery(['city_id' => null]) }}" class="hover:opacity-70">×</a>
                                    </span>
                                @endif

                                @if(request('provider_type') && isset($providerTypes))
                                    @php $selectedType = request('provider_type'); @endphp
                                    @if(isset($providerTypes[$selectedType]))
                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-bold">
                                            {{ $providerTypes[$selectedType] }}
                                            <a href="{{ request()->fullUrlWithQuery(['provider_type' => null]) }}" class="hover:opacity-70">×</a>
                                        </span>
                                    @endif
                                @endif

                                @if(request('remote') == 1)
                                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-bold">
                                        <x-render-icon icon="heroicon-o-globe-alt" class="w-4 h-4" />
                                        {{ __('messages.public.remote_work') }}
                                        <a href="{{ request()->fullUrlWithQuery(['remote' => null]) }}" class="hover:opacity-70">×</a>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Results Grid -->
            @if($profiles->count() > 0)
                <x-provider-grid :providers="$profiles" :columns="1" />

                @if($profiles->hasPages())
                    <nav aria-label="Page navigation" class="mt-8">
                        {{ $profiles->links('pagination::tailwind') }}
                    </nav>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <x-render-icon icon="heroicon-o-magnifying-glass" class="w-16 h-16" />
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-2">{{ __('messages.public.no_results') }}</h5>
                    <p class="text-gray-600 mb-6">
                        @if(request('keyword'))
                            {{ __('messages.public.no_results_for_keyword', ['keyword' => request('keyword')]) }}
                        @else
                            {{ __('messages.public.no_results_found') }}
                        @endif
                    </p>
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        ← {{ __('messages.public.back_to_home') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>

@endsection


