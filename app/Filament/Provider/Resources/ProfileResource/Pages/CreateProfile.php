<?php

namespace App\Filament\Provider\Resources\ProfileResource\Pages;

use App\Filament\Provider\Resources\ProfileResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateProfile extends CreateRecord
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
        $data['slug'] = Str::slug($data['business_name'] ?? 'profile');

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record->id]);
    }
}
