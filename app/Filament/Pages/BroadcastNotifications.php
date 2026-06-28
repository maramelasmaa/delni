<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Http\Requests\Admin\BroadcastNotificationRequest;
use App\Jobs\BroadcastAppNotificationJob;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Validator;

class BroadcastNotifications extends Page
{
    protected string $view = 'filament.pages.broadcast-notifications';

    protected static string $routePath = '/broadcast-notifications';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'إشعارات التطبيق';

    protected static string|\UnitEnum|null $navigationGroup = 'النظام';

    protected static ?int $navigationSort = 55;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'data' => [],
        ]);
    }

    public function getTitle(): string
    {
        return 'إرسال إشعار للتطبيق';
    }

    public function getHeading(): string
    {
        return 'إرسال إشعار للتطبيق';
    }

    public function getSubheading(): ?string
    {
        return 'يرسل إشعارا داخليا وإشعار Push إلى المستخدمين النشطين غير الموقوفين.';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('محتوى الإشعار')
                    ->schema([
                        TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(120),
                        Textarea::make('body')
                            ->label('المحتوى')
                            ->required()
                            ->rows(5)
                            ->maxLength(500),
                    ])
                    ->columns(1),
                Section::make('بيانات اختيارية')
                    ->description('أضف رابطا أو مسارا داخل التطبيق إذا كان الإشعار يوجّه المستخدم إلى شاشة محددة.')
                    ->schema([
                        TextInput::make('data.url')
                            ->label('رابط خارجي')
                            ->maxLength(255)
                            ->url(),
                        TextInput::make('data.pathname')
                            ->label('مسار داخل التطبيق')
                            ->maxLength(255)
                            ->placeholder('/provider/example'),
                        TextInput::make('data.provider_slug')
                            ->label('Slug مقدم الخدمة')
                            ->maxLength(120),
                        TextInput::make('data.category_slug')
                            ->label('Slug التصنيف')
                            ->maxLength(120),
                        TextInput::make('data.subcategory_slug')
                            ->label('Slug التصنيف الفرعي')
                            ->maxLength(120),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $payload = Validator::make(
            $this->form->getState(),
            app(BroadcastNotificationRequest::class)->rules(),
        )->validate();

        BroadcastAppNotificationJob::dispatch($payload, auth()->id());

        $this->form->fill([
            'data' => [],
        ]);

        Notification::make()
            ->title('تمت جدولة الإشعار بنجاح')
            ->body('سيتم إرسال الإشعار للمستخدمين المؤهلين عبر قاعدة البيانات و Push.')
            ->success()
            ->send();
    }
}
