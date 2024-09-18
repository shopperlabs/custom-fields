<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\ListRecords;
use ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource;
use ManukMinasyan\FilamentCustomField\Services\CustomFieldEntityTypeService;

final class ListCustomFields extends ListRecords
{
    protected static string $resource = CustomFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(function (array $data): string {
                    return CustomFieldResource::getUrl('create', [
                        ...$data,
                        'entity_type' => request('entity_type'),
                    ]);
                }),
        ];
    }

    public function getSubNavigation(): array
    {
        return CustomFieldEntityTypeService::options()
            ->map(fn ($label, $value) => NavigationItem::make($label)
                ->url(CustomFieldResource::getUrl('index', ['entity_type' => $value]))
                ->isActiveWhen(fn () => request('entity_type', CustomFieldEntityTypeService::default()) === $value)
            )
            ->values()
            ->toArray();
    }
}
