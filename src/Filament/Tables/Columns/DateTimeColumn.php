<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Tables\Columns\Column as BaseColumn;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Models\CustomField;
use Filament\Tables\Columns\TextColumn as BaseTextColumn;
use Relaticle\CustomFields\Support\FieldTypeUtils;

final readonly class DateTimeColumn implements ColumnInterface
{
    public function make(CustomField $customField): BaseColumn
    {
        return BaseTextColumn::make("custom_fields.$customField->code")
            ->when($customField->type === CustomFieldType::DATE_TIME, function($column){
                $column->dateTime(FieldTypeUtils::getDateTimeFormat());
            })
            ->when($customField->type === CustomFieldType::DATE, function($column){
                $column->date(FieldTypeUtils::getDateFormat());
            })
            ->label($customField->name)
            ->getStateUsing(fn($record) => $record->getCustomFieldValue($customField->code));
    }
}
