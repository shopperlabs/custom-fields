<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentAttribute\Filament\Resources\AttributeResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use ManukMinasyan\FilamentAttribute\Filament\Resources\AttributeResource;
use ManukMinasyan\FilamentAttribute\Services\AttributeEntityTypeService;

final class ListAttributes extends ListRecords
{
    protected static string $resource = AttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
              ->url(function (array $data): string {
                  return AttributeResource::getUrl('create', [
                      'entity_type' => request('activeTab')
                  ]);
              })
        ];
    }

    public function getTabs(): array
    {
        return AttributeEntityTypeService::options()
            ->mapWithKeys(fn($label, $value) => [$value => Tab::make($label)->query(fn($query) => $query->forEntity($value))])
            ->toArray();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }
}
