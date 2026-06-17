<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Traits\AdminAccessOnly;
use App\Filament\Resources\Traits\HasProviderFormSupport;
use App\Models\ActivityLog;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\User;
use App\Services\SuperAdminGuardService;
use App\Services\UserSuspensionService;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction as FilamentDeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    use AdminAccessOnly;
    use HasProviderFormSupport;

    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.models.user_plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament.models.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.models.user_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make(__('filament.labels.manage_user'))
                    ->tabs([
                        Tabs\Tab::make(__('filament.labels.account_tab'))->schema(static::accountSchema()),
                        Tabs\Tab::make(__('filament.labels.profile_tab'))
                            ->schema(static::profileSummarySchema())
                            ->visible(fn (?User $record) => (bool) $record?->hasRole('provider')),
                        Tabs\Tab::make(__('filament.labels.visibility_tab'))
                            ->schema(static::accessSchema())
                            ->visible(fn ($get, ?User $record) => $record?->hasRole('provider') || $get('role') === 'provider'),
                        Tabs\Tab::make(__('filament.labels.marketplace_tab'))
                            ->schema(static::marketplaceSchema())
                            ->visible(fn ($get, ?User $record) => $record?->hasRole('provider') || $get('role') === 'provider'),
                        Tabs\Tab::make(__('filament.labels.reviews_tab'))
                            ->schema(static::reviewsSchema())
                            ->visible(fn (?User $record) => (bool) $record?->hasRole('provider')),
                        Tabs\Tab::make(__('filament.labels.activity_tab'))
                            ->schema(static::activitySchema())
                            ->visibleOn('edit'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('filament.fields.id'))->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('filament.fields.name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label(__('filament.fields.email'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label(__('filament.fields.phone')),
                Tables\Columns\IconColumn::make('is_active')->label(__('filament.fields.active'))->boolean()->sortable(),
                Tables\Columns\IconColumn::make('is_suspended')->label(__('filament.fields.suspended'))->boolean()->sortable(),
                Tables\Columns\IconColumn::make('security_flagged')->label(__('filament.fields.security_flagged'))->boolean()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label(__('filament.fields.created_at'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label(__('filament.filters.active')),
                Tables\Filters\Filter::make('suspended')
                    ->query(fn ($query) => $query->where('is_suspended', true))
                    ->label(__('filament.filters.suspended')),
                Tables\Filters\Filter::make('security_flagged')
                    ->query(fn ($query) => $query->where('security_flagged', true))
                    ->label(__('filament.filters.security_flagged')),
            ])
            ->paginated([25, 50, 100])
            ->recordActions([
                EditAction::make()
                    ->modal(),

                Action::make('suspend')
                    ->label(__('filament.actions.suspend'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! $record->is_suspended && $record->id !== auth()->id())
                    ->schema([
                        Forms\Components\Textarea::make('suspension_reason')
                            ->label(__('filament.fields.reason'))
                            ->required(),
                    ])
                    ->action(function ($record, array $data, UserSuspensionService $service): void {
                        $service->suspend($record, $data['suspension_reason']);
                    }),

                Action::make('reinstate')
                    ->label(__('filament.actions.reinstate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->is_suspended)
                    ->schema([
                        Forms\Components\Textarea::make('reinstatement_reason')
                            ->label(__('filament.fields.reason'))
                            ->required(),
                    ])
                    ->action(function ($record, array $data, UserSuspensionService $service): void {
                        $service->reinstate($record, $data['reinstatement_reason']);
                    }),

                Action::make('clear_security_flag')
                    ->label(__('filament.actions.clear_security_flag'))
                    ->icon('heroicon-o-shield-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->security_flagged)
                    ->action(function ($record): void {
                        $record->update(['security_flagged' => false]);
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->before(fn (DeleteBulkAction $action) => $this->validateBulkDelete($action)),
            ]);
    }

    public static function fillUserFormData(array $data, ?User $record): array
    {
        $profile = $record?->profile;
        $stats = $profile?->stats;

        $data['role'] = $record?->roles()->value('name') ?? ($data['role'] ?? 'user');
        $data['profile'] = [
            'business_name' => $profile?->business_name,
            'bio' => $profile?->bio,
            'city_id' => $profile?->city_id,
            'category_id' => $profile?->category_id,
            'subcategory_id' => $profile?->subcategories()->value('subcategories.id'),
            'whatsapp' => $profile?->whatsapp,
            'phone' => $profile?->phone,
            'provider_access_ends_at' => $profile?->provider_access_ends_at,
        ];
        $data['marketplace'] = [
            'homepage_featured' => $stats?->is_homepage_featured ?? false,
            'homepage_featured_until' => $stats?->homepage_featured_until,
        ];

        return $data;
    }

    public static function saveProviderTabs(User $record, array $data): void
    {
        if (! $record->hasRole('provider')) {
            return;
        }

        static::validateProviderTabs($record, $data);

        $profileData = $data['profile'] ?? [];
        $marketplaceData = $data['marketplace'] ?? [];

        $hasEditableProfileData = collect($profileData)
            ->except('provider_access_ends_at')
            ->contains(fn ($value) => filled($value));
        $hasMarketplaceData = array_key_exists('homepage_featured', $marketplaceData)
            || array_key_exists('homepage_featured_until', $marketplaceData);

        $profile = $record->profile;

        if ($hasEditableProfileData) {
            $profile = $record->profile()->updateOrCreate(
                ['user_id' => $record->id],
                [
                    'business_name' => $profileData['business_name'] ?? null,
                    'type' => 'business',
                    'bio' => $profileData['bio'] ?? null,
                    'city_id' => $profileData['city_id'] ?? null,
                    'category_id' => $profileData['category_id'] ?? null,
                    'whatsapp' => $profileData['whatsapp'] ?? null,
                    'phone' => $profileData['phone'] ?? null,
                    'slug' => static::profileSlug($record, $profileData),
                ],
            );

            $profile->subcategories()->sync(filled($profileData['subcategory_id'] ?? null) ? [$profileData['subcategory_id']] : []);
        }

        if (! $profile) {
            return;
        }

        static::saveProviderAccess($profile, $profileData['provider_access_ends_at'] ?? null);

        if ($hasMarketplaceData) {
            static::saveHomepagePlacement($profile, $marketplaceData);
        }
    }

    protected static function accountSchema(): array
    {
        return [
            Section::make(__('filament.sections.account_details'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('filament.fields.name'))
                        ->placeholder(__('filament.fields.name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label(__('filament.fields.email'))
                        ->email()
                        ->placeholder(__('filament.placeholders.email'))
                        ->required()
                        ->maxLength(255)
                        ->unique(User::class, 'email', ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')
                        ->label(__('filament.fields.phone'))
                        ->placeholder(__('filament.placeholders.phone_local'))
                        ->maxLength(20),
                    Forms\Components\TextInput::make('password')
                        ->label(__('filament.fields.password'))
                        ->password()
                        ->placeholder(__('filament.placeholders.password'))
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->maxLength(255),
                ])
                ->columns(2),
            Section::make(__('filament.sections.role'))
                ->schema([
                    Forms\Components\Select::make('role')
                        ->label(__('filament.fields.role'))
                        ->placeholder(__('filament.placeholders.select_role'))
                        ->options([
                            'provider' => __('filament.models.provider'),
                            'user' => __('filament.models.user'),
                        ])
                        ->required()
                        ->live()
                        ->hiddenOn('edit')
                        ->helperText(__('filament.help_text.super_admin_assignment')),
                ]),
            Section::make(__('filament.sections.account_status'))
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label(__('filament.fields.active'))
                        ->default(true),
                    Forms\Components\Toggle::make('is_suspended')
                        ->label(__('filament.fields.suspended'))
                        ->default(false),
                ])
                ->columns(2),
        ];
    }

    protected static function reviewsSchema(): array
    {
        return [
            Section::make(__('filament.sections.reviews'))
                ->schema([
                    Forms\Components\Placeholder::make('reviews_table')
                        ->label('')
                        ->content(fn (?User $record) => new HtmlString(static::reviewsTable($record))),
                ]),
        ];
    }

    protected static function activitySchema(): array
    {
        return [
            Section::make(__('filament.sections.activity'))
                ->schema([
                    Forms\Components\Placeholder::make('activity_table')
                        ->label('')
                        ->content(fn (?User $record) => new HtmlString(static::activityTable($record))),
                ]),
        ];
    }

    protected static function validateProviderTabs(User $record, array $data): void
    {
        $profile = $data['profile'] ?? [];

        if (filled($profile['subcategory_id'] ?? null)) {
            $validSubcategory = Subcategory::query()
                ->whereKey($profile['subcategory_id'])
                ->where('category_id', $profile['category_id'] ?? null)
                ->exists();

            if (! $validSubcategory) {
                throw ValidationException::withMessages([
                    'profile.subcategory_id' => __('filament.messages.invalid_subcategory_for_category'),
                ]);
            }
        }

        static::validateMarketplaceData($data['marketplace'] ?? []);
    }

    protected static function reviewsTable(?User $record): string
    {
        if (! $record?->profile) {
            return '<p>'.e(__('filament.help_text.no_profile_yet')).'</p>';
        }

        $rows = Review::query()
            ->with('user')
            ->where('profile_id', $record->profile->id)
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Review $review) => sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                e($review->user?->name ?? __('filament.fields.unknown')),
                e((string) $review->rating),
                e(static::reviewStatus($review)),
                $review->is_flagged ? __('filament.labels.yes') : __('filament.labels.no'),
                e($review->created_at?->format('Y-m-d H:i') ?? '-'),
            ))
            ->implode('');

        return static::tableHtml([
            __('filament.labels.reviews_reviewer'),
            __('filament.fields.rating'),
            __('filament.fields.status'),
            __('filament.labels.reviews_flagged'),
            __('filament.labels.created_at_short'),
        ], $rows);
    }

    protected static function activityTable(?User $record): string
    {
        if (! $record) {
            return '<p>'.e(__('filament.help_text.activity_after_create')).'</p>';
        }

        $profileId = $record->profile?->id;

        $rows = ActivityLog::query()
            ->with('user')
            ->where(function (Builder $query) use ($record, $profileId): void {
                $query->where('user_id', $record->id)
                    ->orWhere(fn (Builder $query) => $query->where('subject_type', User::class)->where('subject_id', $record->id));

                if ($profileId) {
                    $query->orWhere(fn (Builder $query) => $query->where('subject_type', Profile::class)->where('subject_id', $profileId));
                }
            })
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (ActivityLog $log) => sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                e($log->user?->name ?? __('filament.fields.system')),
                e($log->action),
                e(Str::limit($log->description ?? '', 80)),
                e($log->created_at?->format('Y-m-d H:i') ?? '-'),
            ))
            ->implode('');

        return static::tableHtml([
            __('filament.labels.activity_user'),
            __('filament.fields.action'),
            __('filament.fields.description'),
            __('filament.labels.created_at_short'),
        ], $rows);
    }

    protected static function tableHtml(array $headers, string $rows): string
    {
        $headerHtml = collect($headers)
            ->map(fn (string $header) => '<th style="text-align:right;padding:8px;border-bottom:1px solid #ddd;">'.e($header).'</th>')
            ->implode('');

        return '<table dir="rtl" style="width:100%;border-collapse:collapse;"><thead><tr>'.$headerHtml.'</tr></thead><tbody>'.($rows ?: '<tr><td colspan="'.count($headers).'" style="padding:8px;">'.e(__('filament.help_text.no_records')).'</td></tr>').'</tbody></table>';
    }

    protected static function reviewStatus(Review $review): string
    {
        return $review->status instanceof \BackedEnum
            ? $review->status->value
            : (string) $review->status;
    }

    public function validateBulkDelete(FilamentDeleteBulkAction $action): void
    {
        $selectedRecordKeys = $action->getSelectedRecordKeys();

        if (! SuperAdminGuardService::canBulkDeleteUsers($selectedRecordKeys)) {
            $this->notify(
                'danger',
                __('filament.help_text.super_admin_delete_blocked'),
            );

            $action->cancel();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'provider'));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => UserResource\Pages\ListUsers::route('/'),
            'create' => UserResource\Pages\CreateUser::route('/create'),
            'edit' => UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
