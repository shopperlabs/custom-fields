<?php

namespace Relaticle\CustomFields\Filament\Resources\CustomFieldResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\ManageRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Relaticle\CustomFields\Filament\Resources\CustomFieldResource;
use Relaticle\CustomFields\Services\EntityTypeService;

class ManageCustomFields extends ManageRecords
{
    protected static string $resource = CustomFieldResource::class;

    #[Url]
    public ?string $entityType;

    protected $queryString = [
        'entityType',
        'tableFilters',
        'tableSortColumn',
        'tableSortDirection',
        'tableSearchQuery' => ['except' => ''],
        'tableColumnSearchQueries',
    ];

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->fillForm([
                    'entity_type' => $this->entityType ?? EntityTypeService::getDefaultOption(),
                ])
                ->url(function (array $data): string {
                    return CustomFieldResource::getUrl(parameters: [
                        ...$data,
                        'action' => CreateAction::getDefaultName(),
                        'entityType' => $this->entityType ?? EntityTypeService::getDefaultOption()
                    ]);
                }),
        ];
    }

    public function getSubNavigation(): array
    {
        return EntityTypeService::getOptions()
            ->map(fn ($label, $value) => NavigationItem::make($label)
                ->url(CustomFieldResource::getUrl('index', ['entityType' => $value]))
                ->isActiveWhen(fn () => ($this->entityType ?? EntityTypeService::getDefaultOption()) === $value)
            )
            ->values()
            ->toArray();
    }

    public function table(Table $table): Table
    {
        return parent::table($table)->modifyQueryUsing(fn (Builder $query): Builder => $query->forMorphEntity($this->entityType ?? EntityTypeService::getDefaultOption()));
    }
}
