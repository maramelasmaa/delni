<x-filament-panels::page>
    @php
        $profile = $this->getProfile();
        $checklist = $this->getChecklist();

        $profileIsActive = $profile
            && $profile->provider_access_ends_at?->isFuture()
            && $profile->is_complete;

        $profileEditUrl = $profile
            ? \App\Filament\Provider\Resources\ProfileResource::getUrl('edit', ['record' => $profile->slug])
            : \App\Filament\Provider\Resources\ProfileResource::getUrl('create');

        $steps = [
            [
                'done' => $checklist['profile_created'],
                'title' => 'إكمال البيانات الأساسية',
                'description' => 'اسم النشاط، التصنيف، المدينة، ووسائل التواصل الأساسية.',
                'url' => $checklist['profile_created'] ? null : \App\Filament\Provider\Resources\ProfileResource::getUrl('create'),
                'action' => 'إكمال البيانات',
            ],
            [
                'done' => $checklist['has_bio'],
                'title' => 'إضافة نبذة تعريفية',
                'description' => 'نبذة قصيرة وواضحة تساعد العميل يفهم خدمتك بسرعة.',
                'url' => $profile && ! $checklist['has_bio'] ? $profileEditUrl : null,
                'action' => 'إضافة النبذة',
            ],
            [
                'done' => $checklist['portfolio_complete'],
                'title' => 'إضافة أعمال إلى المعرض',
                'description' => 'يفضل إضافة عملين على الأقل حتى يظهر نشاطك بشكل أقوى.',
                'url' => $profile && ! $checklist['portfolio_complete'] ? \App\Filament\Provider\Resources\PortfolioResource::getUrl('create') : null,
                'action' => 'إضافة عمل',
            ],
            [
                'done' => $checklist['credentials_added'],
                'title' => 'إضافة الشهادات والخبرات',
                'description' => 'أضف الشهادات أو الخبرات التي تدعم ثقة العميل.',
                'url' => $profile && ! $checklist['credentials_added'] ? \App\Filament\Provider\Resources\CredentialsResource::getUrl('create') : null,
                'action' => 'إضافة شهادة',
            ],
            [
                'done' => $checklist['contacts_added'],
                'title' => 'تأكيد وسائل التواصل',
                'description' => 'تأكد من وجود رقم هاتف أو واتساب صالح للعملاء.',
                'url' => $profile && ! $checklist['contacts_added'] ? $profileEditUrl : null,
                'action' => 'تحديث التواصل',
            ],
        ];
    @endphp

    <x-filament::section
        heading="{{ $profile?->business_name ?? auth()->user()->name }}"
        description="{{ $profileIsActive ? 'حسابك نشط ويظهر الآن للعملاء على دلني.' : 'أكمل العناصر الأساسية أدناه لتجهيز حسابك بسرعة.' }}"
        icon="heroicon-o-home"
    >
        <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
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

        <div style="display: grid; gap: 12px;">
            @foreach($steps as $step)
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding: 16px; border: 1px solid rgba(148, 163, 184, 0.22); border-radius: 16px;">
                    <div style="display: grid; gap: 6px;">
                        <div style="display: flex; align-items: center; gap: 8px; font-weight: 700;">
                            <span style="color: {{ $step['done'] ? '#16A34A' : '#F1620F' }};">
                                {{ $step['done'] ? '✓' : '•' }}
                            </span>
                            <span>{{ $step['title'] }}</span>
                        </div>

                        <p style="margin: 0; color: rgb(148 163 184); line-height: 1.7;">
                            {{ $step['description'] }}
                        </p>
                    </div>

                    <div style="flex-shrink: 0;">
                        @if($step['done'])
                            <span style="display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 999px; background: rgba(34, 197, 94, 0.12); color: #22C55E; font-size: 12px; font-weight: 700;">
                                مكتمل
                            </span>
                        @elseif($step['url'])
                            <x-filament::button
                                :href="$step['url']"
                                tag="a"
                                color="gray"
                                outlined
                                size="sm"
                            >
                                {{ $step['action'] }}
                            </x-filament::button>
                        @else
                            <span style="font-size: 12px; color: rgb(148 163 184);">
                                يتطلب إنشاء الملف أولاً
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
