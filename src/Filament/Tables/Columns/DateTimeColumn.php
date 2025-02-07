<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Tables\Columns\Column as BaseColumn;
use Illuminate\Database\Eloquent\Builder;
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
            ->sortable(query: function (Builder $query, string $direction) use ($customField): Builder {
                $table = $query->getModel()->getTable();
                $key = $query->getModel()->getKeyName();

                return $query->orderBy(
                    $customField->values()
                        ->selectRaw($customField->type->getCast('text_value'))
                        ->whereColumn('custom_field_values.entity_id', "$table.$key")
                        ->limit(1),
                    $direction
                );
            })
            ->label($customField->name)
            ->getStateUsing(fn($record) => $record->getCustomFieldValue($customField->code));
    }
}
