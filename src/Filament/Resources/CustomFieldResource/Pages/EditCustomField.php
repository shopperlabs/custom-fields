<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use ManukMinasyan\FilamentCustomField\Filament\Resources\CustomFieldResource;

final class EditCustomField extends EditRecord
{
    protected static string $resource = CustomFieldResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['lookup_type'] ??= null;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
