<?php

namespace Relaticle\CustomFields\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldResource\CustomFieldValidationComponent;
use Relaticle\CustomFields\Filament\Forms\Components\TypeField;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Models\CustomFieldSection;
use Relaticle\CustomFields\Services\EntityTypeService;
use Relaticle\CustomFields\Services\LookupTypeService;
use Relaticle\CustomFields\Support\Utils;
use Filament\Forms;

class ManageCustomFieldSection extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $entityType;
    public CustomFieldSection $section;

    #[Computed]
    public function fields()
    {
        return $this->section->fields()->orderBy('sort_order')->get();
    }

    #[On('field-width-updated')]
    public function fieldWidthUpdated(int $fieldId, int $width): void
    {
        // Update the width
        CustomField::where('id', $fieldId)->update(['width' => $width]);

        // Re-fetch the fields
        $this->section->refresh();
    }

    public function updateFieldsOrder($sectionId, $fields): void
    {
        foreach ($fields as $index => $field) {
            CustomField::query()
                ->where('id', $field)
                ->update([
                    'custom_field_section_id' => $sectionId,
                    'sort_order' => $index,
                ]);
        }
    }

    public function createFieldAction(): Action
    {
        return Action::make('createField')
            ->size(ActionSize::ExtraSmall)
            ->label('Create Field')
            ->model(CustomField::class)
            ->form(function () {
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
                                        ->default(fn() => CustomFieldType::TEXT->value)
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
            })
            ->fillForm([
                'entity_type' => $this->entityType,
            ])
            ->mutateFormDataUsing(function (array $data): array {
                if (Utils::isTenantEnabled()) {
                    $data[config('custom-fields.column_names.tenant_foreign_key')] = Filament::getTenant()?->id;
                }

                $data['custom_field_section_id'] = $this->section->id;

                return $data;
            })
            ->action(fn(array $data) => CustomField::create($data))
            ->slideOver();
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->iconButton()
            ->icon('heroicon-m-pencil')
            ->color('gray')
            ->model(CustomFieldSection::class)
            ->record($this->section)
            ->form([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->maxLength(50)
                    ->unique(
                        table: CustomFieldSection::class,
                        column: 'name',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                            return $rule->when(
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
                    ->required()
                    ->alphaDash()
                    ->maxLength(50)
                    ->unique(
                        table: CustomFieldSection::class,
                        column: 'code',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                            return $rule->when(
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
            ])
            ->fillForm($this->section->toArray())
            ->action(fn(array $data) => $this->section->update($data));
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->requiresConfirmation()
            ->iconButton()
            ->icon('heroicon-m-trash')
            ->color('gray')
            ->action(fn() => $this->section->delete());
    }

    public function render()
    {
        return view('custom-fields::livewire.manage-custom-field-section');
    }
}
