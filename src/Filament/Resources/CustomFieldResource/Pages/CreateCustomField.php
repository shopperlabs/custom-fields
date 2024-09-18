<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource;

final class CreateCustomField extends CreateRecord
{
    protected static string $resource = CustomFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
