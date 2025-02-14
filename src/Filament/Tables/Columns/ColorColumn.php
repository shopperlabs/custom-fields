<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Tables\Columns\Column as BaseColumn;
use Relaticle\CustomFields\Models\CustomField;
use Filament\Tables\Columns\ColorColumn as BaseColorColumn;

final readonly class ColorColumn implements ColumnInterface
{
    public function make(CustomField $customField): BaseColumn
    {
        return BaseColorColumn::make("custom_fields.$customField->code")
            ->label($customField->name)
            ->getStateUsing(fn($record) => $record->getCustomFieldValue($customField));
    }
}
