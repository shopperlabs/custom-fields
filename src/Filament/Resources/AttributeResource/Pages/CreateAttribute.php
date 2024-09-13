<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Resources\AttributeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use ManukMinasyan\FilamentCustomField\Filament\Resources\AttributeResource;

final class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
