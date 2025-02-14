<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Exports;

use Filament\Actions\Exports\ExportColumn;
use Relaticle\CustomFields\Models\CustomField;

final class FieldExportFactory
{
    public function create(CustomField $customField): ExportColumn
    {
        return ExportColumn::make($customField->name)
            ->label($customField->label)
            ->state(function ($record) use ($customField) {
                return $record->getCustomFieldValue($customField->code);
            });
    }
}
