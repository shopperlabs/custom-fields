<?php

namespace Relaticle\CustomFields\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Illuminate\View\View;
use Livewire\Component;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Filament\Forms\Components\CustomFieldResource\CustomFieldValidationComponent;
use Relaticle\CustomFields\Filament\Forms\Components\TypeField;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Services\EntityTypeService;
use Relaticle\CustomFields\Services\LookupTypeService;
use Relaticle\CustomFields\Support\Utils;
use Filament\Forms;

class ManageCustomField extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithRecord;

    public CustomField $field;

    public function editAction(): Action
    {
        return Action::make('edit')
            ->size(ActionSize::ExtraSmall)
            ->iconButton()
            ->icon('heroicon-o-pencil')
            ->color('gray')
            ->model(CustomField::class)
            ->record($this->field)
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
            ->fillForm($this->field->toArray())
            ->mutateFormDataUsing(function (array $data): array {
                if (Utils::isTenantEnabled()) {
                    $data[config('custom-fields.column_names.tenant_foreign_key')] = Filament::getTenant()?->id;
                }

                return $data;
            })
            ->action(fn(array $data) => $this->field->update($data))
            ->slideOver();
    }


    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->requiresConfirmation()
            ->iconButton()
            ->icon('heroicon-o-trash')
            ->size(ActionSize::ExtraSmall)
            ->color('gray')
            ->action(fn () => $this->field->delete());
    }

    public function setWidth(int $fieldId, int $width): void
    {
        $this->dispatch('field-width-updated', $fieldId, $width);
    }

    public function render(): View
    {
        return view('custom-fields::livewire.manage-custom-field');
    }
}
