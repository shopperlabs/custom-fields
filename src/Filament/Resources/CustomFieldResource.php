<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Resources;

use Filament\Forms;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use ManukMinasyan\FilamentCustomField\Enums\CustomFieldType;
use ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomFieldResource\CustomFieldValidationComponent;
use ManukMinasyan\FilamentCustomField\Filament\Forms\Components\TypeField;
use ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource\Pages;
use ManukMinasyan\FilamentCustomField\Filament\Tables\Columns\TypeColumn;
use ManukMinasyan\FilamentCustomField\Models\CustomField;
use ManukMinasyan\FilamentCustomField\Models\Scopes\ActivableScope;
use ManukMinasyan\FilamentCustomField\Services\EntityTypeOptionsService;
use ManukMinasyan\FilamentCustomField\Services\LookupTypeOptionsService;

final class CustomFieldResource extends Resource
{
    protected static ?string $model = CustomField::class;

    protected static ?string $label = 'Custom Field';

    protected static ?string $slug = 'custom-fields';

    protected static bool $shouldRegisterNavigation = false;

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
                                    ->disabled(fn (?CustomField $record): bool => (bool) $record?->exists)
                                    ->options(EntityTypeOptionsService::getOptions())
                                    ->searchable()
                                    ->default(fn () => request('entityType', EntityTypeOptionsService::getDefaultOption()))
                                    ->required(),
                                TypeField::make('type')
                                    ->disabled(fn (?CustomField $record): bool => (bool) $record?->exists)
                                    ->reactive()
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->live(onBlur: true)
                                    ->required()
                                    ->maxLength(50)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $old, ?string $state): void {
                                        $old ??= '';
                                        $state ??= '';

                                        if (($get('code') ?? '') !== Str::of($old)->slug('_')->toString()) {
                                            return;
                                        }

                                        $set('code', Str::of($state)->slug('_')->toString());
                                    }),
                                Forms\Components\TextInput::make('code')
                                    ->live(onBlur: true)
                                    ->required()
                                    ->alphaDash()
                                    ->unique(table: CustomField::class, column: 'code', ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Forms\Get $get) => $rule->where('entity_type', $get('entity_type')))
                                    ->validationMessages([
                                        'unique' => __('validation.custom.$customFields.code.unique'),
                                    ])
                                    ->maxLength(50)
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                        $set('code', Str::of($state)->slug('_')->toString());
                                    }),
                                Forms\Components\Select::make('options_lookup_type')
                                    ->visible(fn (Forms\Get $get): bool => in_array($get('type'), CustomFieldType::optionables()->pluck('value')->toArray()))
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
                                    ->visible(fn (Forms\Get $get): bool => $get('options_lookup_type') === 'lookup')
                                    ->reactive()
                                    ->options(LookupTypeOptionsService::getOptions())
                                    ->default(LookupTypeOptionsService::getDefaultOption())
                                    ->required(),
                                Forms\Components\Repeater::make('options')
                                    ->visible(fn (Forms\Get $get): bool => $get('options_lookup_type') === 'options' && in_array($get('type'), CustomFieldType::optionables()->pluck('value')->toArray()))
                                    ->relationship()
                                    ->simple(
                                        Forms\Components\TextInput::make('name')
                                            ->columnSpanFull()
                                            ->required(),
                                    )
                                    ->columns(2)
                                    ->label('Options')
                                    ->helperText('Add options for the select field.')
                                    ->defaultItems(1)
                                    ->addActionLabel('Add Option')
                                    ->reorderable()
                                    ->orderColumn('sort_order')
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Validation')
                            ->schema([
                                CustomFieldValidationComponent::make(),
                            ]),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TypeColumn::make('type'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->multiple()
                    ->options(CustomFieldType::class),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    EditAction::make(),
                    Tables\Actions\RestoreAction::make('Restore'),
                    Tables\Actions\Action::make('activate')
                        ->icon('heroicon-o-archive-box')
                        ->requiresConfirmation()
                        ->visible(fn (CustomField $record): bool => ! $record->isActive())
                        ->action(fn (CustomField $record) => $record->activate()),
                    Tables\Actions\Action::make('deactivate')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->requiresConfirmation()
                        ->visible(fn (CustomField $record): bool => $record->isActive())
                        ->action(fn (CustomField $record) => $record->deactivate()),
                    DeleteAction::make()->visible(fn (CustomField $record): bool => ! $record->isActive()),
                ])->iconButton(),

            ])
            ->defaultGroup('active')
            ->groups([
                Tables\Grouping\Group::make('active')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (CustomField $record): string => $record->active ? 'Active' : 'Inactive')
                    ->orderQueryUsing(fn (Builder $query, string $direction) => $query->orderByDesc('active'))
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
            'index' => Pages\ListCustomFields::route('/'),
            'create' => Pages\CreateCustomField::route('/create'),
            'edit' => Pages\EditCustomField::route('/{record}/edit'),
        ];
    }
}
