<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Tables\Columns\Column as BaseColumn;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\CustomFields\Models\CustomField;
use Filament\Tables\Columns\TextColumn as BaseTextColumn;

final readonly class TextColumn implements ColumnInterface
{
    public function make(CustomField $customField): BaseColumn
    {
        return BaseTextColumn::make("custom_fields.$customField->code")
            ->label($customField->name)
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
            ->getStateUsing(fn($record) => $record->getCustomFieldValue($customField->code));
    }
}
