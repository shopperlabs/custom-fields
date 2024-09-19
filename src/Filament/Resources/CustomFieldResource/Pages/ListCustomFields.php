<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource;
use ManukMinasyan\FilamentCustomField\Services\EntityTypeOptionsService;

final class ListCustomFields extends ListRecords
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
                ->url(function (array $data): string {
                    return CustomFieldResource::getUrl('create', [
                        ...$data,
                        'entityType' => $this->entityType ?? EntityTypeOptionsService::getDefaultOption(),
                    ]);
                }),
        ];
    }

    public function getSubNavigation(): array
    {
        return EntityTypeOptionsService::getOptions()
            ->map(fn ($label, $value) => NavigationItem::make($label)
                ->url(CustomFieldResource::getUrl('index', ['entityType' => $value]))
                ->isActiveWhen(fn () => ($this->entityType ?? EntityTypeOptionsService::getDefaultOption()) === $value)
            )
            ->values()
            ->toArray();
    }

    public function table(Table $table): Table
    {
        return parent::table($table)->modifyQueryUsing(fn (Builder $query): Builder => $query->forMorphEntity($this->entityType ?? EntityTypeOptionsService::getDefaultOption()));
    }
}
