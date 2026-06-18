<x-filament-panels::page>
    @php
        $profile = $this->getProfile();

        $profileIsActive = $profile
            && $profile->provider_access_ends_at?->isFuture()
            && $profile->is_complete;

        $profileEditUrl = $profile
            ? \App\Filament\Provider\Resources\ProfileResource::getUrl('edit', ['record' => $profile->slug])
            : \App\Filament\Provider\Resources\ProfileResource::getUrl('create');
    @endphp

    <x-filament::section
        heading="{{ $profile?->business_name ?? auth()->user()->name }}"
        description="{{ $profileIsActive ? 'حسابك نشط ويظهر الآن للعملاء على دلني.' : 'أكمل ملفك التجاري لتجهيز حسابك.' }}"
        icon="heroicon-o-home"
    >
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
            <x-filament::button
                :href="$profileEditUrl"
                icon="heroicon-o-pencil-square"
            >
                {{ $profile ? 'تعديل الملف الشخصي' : 'إنشاء الملف التجاري' }}
            </x-filament::button>

            @if($profile)
                <x-filament::button
                    :href="route('public.provider', ['profile' => $profile->slug])"
                    icon="heroicon-o-eye"
                    tag="a"
                    target="_blank"
                    color="gray"
                    outlined
                >
                    معاينة الملف العام
                </x-filament::button>
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
