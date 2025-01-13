<?php

namespace Relaticle\CustomFields\Filament\Pages;

use Relaticle\CustomFields\Models\CustomFieldSection;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldResource\CustomFieldValidationComponent;
use Relaticle\CustomFields\Filament\Forms\Components\TypeField;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Services\EntityTypeService;
use Relaticle\CustomFields\Services\LookupTypeService;
use Relaticle\CustomFields\Support\Utils;
use Filament\Forms;
use Livewire\Attributes\Url;

class CustomFieldsNext extends Page
{
    protected static ?string $navigationIcon = 'heroicon-m-document-text';

    protected static string $view = 'custom-fields::filament.pages.custom-fields-next';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    #[Url(history: true, keep: true)]
    public $currentEntityType;

    public $currentCustomFieldSectionId;

    public function mount()
    {
        if (!$this->currentEntityType) {
            $this->setCurrentEntityType(EntityTypeService::getDefaultOption());
        } else {
            $this->storeDefaultSection();
        }
    }

    #[On('field-width-updated')]
    public function fieldWidthUpdated($fieldId, $width): void
    {
        CustomField::query()
            ->where('id', $fieldId)
            ->update([
                'width' => $width,
            ]);
    }

    #[Computed]
    public function entityTypes()
    {
        return EntityTypeService::getOptions();
    }

    public function setCurrentEntityType($entityType): void
    {
        $this->currentEntityType = $entityType;
        $this->storeDefaultSection();
    }

    public function setCurrentCustomFieldSectionId($sectionId): void
    {
        $this->currentCustomFieldSectionId = $sectionId;
    }

    #[Computed]
    public function sections()
    {
        return CustomFieldSection::query()
            ->forEntityType($this->currentEntityType)
            ->with([
                'fields' => function ($query) {
                    $query->forMorphEntity($this->currentEntityType)
                        ->orderBy('sort_order');
                }
            ])
            ->orderBy('sort_order') // Adjust as necessary based on your sorting preference
            ->get()
            ->map(function ($section) {
                return $section;
            });
    }

    public function createFieldAction(): Action
    {
        return Action::make('createField')
            ->size(ActionSize::ExtraSmall)
            ->label('Create Field')
            ->form(function(){
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
//                                            ->relationship()
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
                'entity_type' => $this->currentEntityType,
            ])
            ->mutateFormDataUsing(function (array $data): array {
                if (Utils::isTenantEnabled()) {
                    $data[config('custom-fields.column_names.tenant_foreign_key')] = Filament::getTenant()?->id;
                }

                $data['custom_field_section_id'] = null; //

                return $data;
            })
            ->action(fn(array $data) => CustomField::create($data))
            ->slideOver();
    }

    public function createSectionAction(): Action
    {
        return Action::make('createSection')
            ->size(ActionSize::ExtraSmall)
            ->label('Add Section')
            ->icon('heroicon-s-plus')
            ->color('gray')
            ->button()
            ->outlined()
            ->extraAttributes([
                'class' => 'h-36 flex justify-center items-center rounded-lg border-gray-300 hover:border-gray-400 border-dashed',
            ])
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
            ->mutateFormDataUsing(function (array $data): array {
                $data[config('custom-fields.column_names.tenant_foreign_key')] = Filament::getTenant()?->id;

                $data['entity_type'] = $this->currentEntityType;

                return $data;
            })
            ->action(fn(array $data) => $this->storeSection($data))
            ->modalWidth('max-w-2xl');
    }

    public function updateSectionsOrder($sections): void
    {
        foreach ($sections as $index => $section) {
            CustomFieldSection::query()
                ->where('id', $section)
                ->update([
                    'sort_order' => $index,
                ]);
        }
    }

    public function updateFieldsOrder($sectionId, $fields): void
    {
        foreach ($fields as $index => $field) {
            CustomField::query()
                ->where('id', $field)
                ->update([
                    'custom_field_section_id' => $sectionId != 0 ? $sectionId : null,
                    'sort_order' => $index,
                ]);
        }
    }

    public function storeDefaultSection(): void
    {
        if ($this->sections->isEmpty()) {
            $newSection = $this->storeSection([
                'entity_type' => $this->currentEntityType,
                'name' => 'New Section',
                'code' => 'new_section',
            ]);

            CustomField::query()
                ->forMorphEntity($this->currentEntityType)
                ->whereNull('custom_field_section_id')
                ->orderBy('sort_order')
                ->update([
                    'custom_field_section_id' => $newSection->id,
                ]);

            $this->sections = $this->sections->push($newSection->load('fields'));
        }
    }

    public function storeSection(array $data): CustomFieldSection
    {
        return CustomFieldSection::create($data);
    }

    public function deleteSection(CustomFieldSection $section): void
    {
        $section->delete();
    }

    public function storeField(array $data): CustomField
    {
        return CustomField::create($data);
    }
}
