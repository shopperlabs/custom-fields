<?php

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\CustomFields\Models\CustomField;
use Filament\Tables\Columns\IconColumn as BaseIconColumn;

class IconColumn implements ColumnInterface
{

    public function make(CustomField $customField): Column
    {
        return BaseIconColumn::make("custom_fields.$customField->code")
            ->boolean()
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
            ->getStateUsing(fn($record) => $record->getCustomFieldValue($customField->code) ?? false);
    }
}
