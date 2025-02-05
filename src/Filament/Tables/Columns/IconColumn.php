<?php

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Tables\Columns\Column;
use Relaticle\CustomFields\Models\CustomField;
use Filament\Tables\Columns\IconColumn as BaseIconColumn;

class IconColumn implements ColumnInterface
{

    public function make(CustomField $customField): Column
    {
        return BaseIconColumn::make("custom_fields.$customField->code")
            ->boolean()
            ->label($customField->name)
            ->getStateUsing(fn($record) => $record->getCustomFieldValue($customField->code) ?? false);
    }
}
