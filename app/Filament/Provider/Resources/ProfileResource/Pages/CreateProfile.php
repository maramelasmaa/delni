<?php

namespace App\Filament\Provider\Resources\ProfileResource\Pages;

use App\Filament\Provider\Resources\ProfileResource;
use App\Filament\Support\Pages\CreateRecordWithBack;
use App\Models\Profile;
use App\Services\ProfileCompletenessService;
use Illuminate\Support\Str;

class CreateProfile extends CreateRecordWithBack
{
    protected static string $resource = ProfileResource::class;

    public function getHeading(): string
    {
        return 'إنشاء الملف التجاري';
    }

    public function getSubheading(): ?string
    {
        return 'أنشئ ملفك التجاري على منصة دلني لعرض خدماتك والتواصل مع العملاء';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['slug'] = $this->uniqueProfileSlug((string) ($data['business_name'] ?? 'profile'));

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record->slug], panel: 'provider');
    }

    protected function afterCreate(): void
    {
        app(ProfileCompletenessService::class)->evaluate($this->record->refresh());
    }

    private function uniqueProfileSlug(string $businessName): string
    {
        $base = Str::slug($businessName) ?: 'provider-profile';
        $slug = $base;
        $counter = 2;

        while (Profile::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
