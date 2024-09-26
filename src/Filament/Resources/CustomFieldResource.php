<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldResource\CustomFieldValidationComponent;
use Relaticle\CustomFields\Filament\Forms\Components\TypeField;
use Relaticle\CustomFields\Filament\Resources\CustomFieldResource\Pages;
use Relaticle\CustomFields\Filament\Tables\Columns\TypeColumn;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Models\Scopes\ActivableScope;
use Relaticle\CustomFields\Services\EntityTypeOptionsService;
use Relaticle\CustomFields\Services\LookupTypeOptionsService;
use Relaticle\CustomFields\Support\Utils;

final class CustomFieldResource extends Resource
{
    protected static ?string $model = CustomField::class;

    protected static ?string $tenantOwnershipRelationshipName = 'team';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('General')
                            ->schema([
                                Forms\Components\Select::make('entity_type')
                                    ->disabled(fn(?CustomField $record): bool => (bool)$record?->exists)
                                    ->options(EntityTypeOptionsService::getOptions())
                                    ->searchable()
                                    ->default(fn() => request('entityType', EntityTypeOptionsService::getDefaultOption()))
                                    ->required(),
                                TypeField::make('type')
                                    ->disabled(fn(?CustomField $record): bool => (bool)$record?->exists)
                                    ->reactive()
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->helperText("The field's label shown in the table's and form's.")
                                    ->live(onBlur: true)
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(table: CustomField::class, column: 'name', ignoreRecord: true, modifyRuleUsing: fn(Unique $rule, Forms\Get $get) => $rule->where('entity_type', $get('entity_type')))
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $old, ?string $state): void {
                                        $old ??= '';
                                        $state ??= '';

                                        if (($get('code') ?? '') !== Str::of($old)->slug('_')->toString()) {
                                            return;
                                        }

                                        $set('code', Str::of($state)->slug('_')->toString());
                                    }),
                                Forms\Components\TextInput::make('code')
                                    ->helperText('Unique code to identify this field throughout the resource.')
                                    ->live(onBlur: true)
                                    ->required()
                                    ->alphaDash()
                                    ->unique(table: CustomField::class, column: 'code', ignoreRecord: true, modifyRuleUsing: fn(Unique $rule, Forms\Get $get) => $rule->where('entity_type', $get('entity_type')))
                                    ->maxLength(50)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                        $set('code', Str::of($state)->slug('_')->toString());
                                    }),
                                Forms\Components\Select::make('options_lookup_type')
                                    ->visible(fn(Forms\Get $get): bool => in_array($get('type'), CustomFieldType::optionables()->pluck('value')->toArray()))
                                    ->reactive()
                                    ->options([
                                        'options' => 'Options',
                                        'lookup' => 'Lookup',
                                    ])
                                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record): void {
                                        if (blank($state)) {
                                            $optionsLookupType = $record?->lookup_type ? 'lookup' : 'options';
                                            $component->state($optionsLookupType);
                                        }
                                    })
                                    ->dehydrated(false)
                                    ->required(),
                                Forms\Components\Select::make('lookup_type')
                                    ->visible(fn(Forms\Get $get): bool => $get('options_lookup_type') === 'lookup')
                                    ->reactive()
                                    ->options(LookupTypeOptionsService::getOptions())
                                    ->default(LookupTypeOptionsService::getDefaultOption())
                                    ->required(),
                                Forms\Components\Fieldset::make('Options')
                                    ->visible(fn(Forms\Get $get): bool => $get('options_lookup_type') === 'options' && in_array($get('type'), CustomFieldType::optionables()->pluck('value')->toArray()))
                                    ->schema([
                                        Forms\Components\Repeater::make('options')
                                            ->relationship()
                                            ->simple(
                                                Forms\Components\TextInput::make('name')
                                                    ->columnSpanFull()
                                                    ->required(),
                                            )
                                            ->columns(2)
                                            ->required()
                                            ->hiddenLabel()
                                            ->defaultItems(1)
                                            ->addActionLabel('Add Option')
                                            ->reorderable()
                                            ->orderColumn('sort_order')
                                            ->columnSpanFull(),
                                    ])
                            ]),
                        Forms\Components\Tabs\Tab::make('Validation')
                            ->schema([
                                CustomFieldValidationComponent::make(),
                            ]),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->contained(false),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                TypeColumn::make('type'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->multiple()
                    ->options(CustomFieldType::class),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    EditAction::make()->slideOver(),
                    Tables\Actions\RestoreAction::make('Restore'),
                    Tables\Actions\Action::make('activate')
                        ->icon('heroicon-o-archive-box')
                        ->requiresConfirmation()
                        ->visible(fn(CustomField $record): bool => !$record->isActive())
                        ->action(fn(CustomField $record) => $record->activate()),
                    Tables\Actions\Action::make('deactivate')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->requiresConfirmation()
                        ->visible(fn(CustomField $record): bool => $record->isActive())
                        ->action(fn(CustomField $record) => $record->deactivate()),
                    DeleteAction::make()->visible(fn(CustomField $record): bool => !$record->isActive()),
                ])->iconButton(),

            ])
            ->defaultGroup('active')
            ->groups([
                Tables\Grouping\Group::make('active')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn(CustomField $record): string => $record->active ? 'Active' : 'Inactive')
                    ->orderQueryUsing(fn(Builder $query, string $direction) => $query->orderByDesc('active'))
                    ->label('Active')
                    ->collapsible(),
            ])
            ->groupingSettingsHidden()
            ->paginated(false)
            ->reorderable('sort_order');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActivableScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomFields::route('/'),
        ];
    }

    public static function getCluster(): ?string
    {
        return Utils::getResourceCluster() ?? static::$cluster;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Utils::isResourceNavigationRegistered();
    }

    public static function getNavigationGroup(): ?string
    {
        return Utils::isResourceNavigationGroupEnabled()
            ? __('custom-fields::custom-fields.nav.group')
            : '';
    }

    public static function getNavigationLabel(): string
    {
        return __('custom-fields::custom-fields.nav.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('custom-fields::custom-fields.nav.icon');
    }

    public static function getNavigationSort(): ?int
    {
        return Utils::getResourceNavigationSort();
    }

    public static function getSlug(): string
    {
        return Utils::getResourceSlug();
    }

    public static function getNavigationBadge(): ?string
    {
        return Utils::isResourceNavigationBadgeEnabled()
            ? strval(static::getEloquentQuery()->count())
            : null;
    }

    public static function isScopedToTenant(): bool
    {
        return Utils::isScopedToTenant();
    }

    public static function canGloballySearch(): bool
    {
        return Utils::isResourceGloballySearchable() && count(static::getGloballySearchableAttributes()) && static::canViewAny();
    }
}
