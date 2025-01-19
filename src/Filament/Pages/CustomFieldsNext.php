<?php

namespace Relaticle\CustomFields\Filament\Pages;

use Illuminate\Support\Collection;
use Relaticle\CustomFields\Models\CustomFieldSection;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Services\EntityTypeService;
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

    public function mount()
    {
        if (!$this->currentEntityType) {
            $this->setCurrentEntityType(EntityTypeService::getDefaultOption());
        } else {
            $this->storeDefaultSection();
        }
    }

    #[Computed]
    public function sections(): Collection
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

    #[Computed]
    public function entityTypes(): Collection
    {
        return EntityTypeService::getOptions();
    }

    public function setCurrentEntityType($entityType): void
    {
        $this->currentEntityType = $entityType;
        $this->storeDefaultSection();
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
                Forms\Components\Textarea::make('description')
                    ->maxLength(255)
                    ->nullable()
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
}
