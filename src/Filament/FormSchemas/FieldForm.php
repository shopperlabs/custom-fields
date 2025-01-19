<?php

namespace Relaticle\CustomFields\Filament\FormSchemas;

use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldResource\CustomFieldValidationComponent;
use Relaticle\CustomFields\Filament\Forms\Components\TypeField;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Services\EntityTypeService;
use Relaticle\CustomFields\Services\LookupTypeService;
use Relaticle\CustomFields\Support\Utils;
use Filament\Forms;

class FieldForm implements FormInterface
{
    public static function schema(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('General')
                        ->schema([
                            Forms\Components\Select::make('entity_type')
                                ->disabled(fn(?CustomField $record): bool => (bool)$record?->exists)
                                ->options(EntityTypeService::getOptions())
                                ->searchable()
                                ->default(fn() => request('entityType', EntityTypeService::getDefaultOption()))
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
                                ->unique(
                                    table: CustomField::class,
                                    column: 'name',
                                    ignoreRecord: true,
                                    modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                                        return $rule->where('entity_type', $get('entity_type'))
                                            ->when(
                                                Utils::isTenantEnabled(),
                                                function (Unique $rule) {
                                                    return $rule->where(
                                                        config('custom-fields.column_names.tenant_foreign_key'),
                                                        Filament::getTenant()?->id
                                                    );
                                                });
                                    },
                                )
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
                                ->maxLength(50)
                                ->unique(
                                    table: CustomField::class,
                                    column: 'code',
                                    ignoreRecord: true,
                                    modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                                        return $rule->where('entity_type', $get('entity_type'))
                                            ->when(
                                                Utils::isTenantEnabled(),
                                                function (Unique $rule) {
                                                    return $rule->where(
                                                        config('custom-fields.column_names.tenant_foreign_key'),
                                                        Filament::getTenant()?->id
                                                    );
                                                });
                                    },
                                )
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
                                ->options(LookupTypeService::getOptions())
                                ->default(LookupTypeService::getDefaultOption())
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
                                        ->requiredUnless('type', CustomFieldType::TAGS_INPUT->value)
                                        ->hiddenLabel()
                                        ->defaultItems(1)
                                        ->addActionLabel('Add Option')
                                        ->reorderable()
                                        ->orderColumn('sort_order')
                                        ->columnSpanFull()
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                            if (Utils::isTenantEnabled()) {
                                                $data[config('custom-fields.column_names.tenant_foreign_key')] = Filament::getTenant()?->id;
                                            }

                                            return $data;
                                        })
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
        ];
    }
}
