<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Facades\Filament;
use Filament\Tables\Columns\Column as BaseColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Relaticle\CustomFields\Exceptions\MissingRecordTitleAttributeException;
use Relaticle\CustomFields\Models\CustomField;
use Filament\Tables\Columns\TextColumn as BaseTextColumn;
use Throwable;

final readonly class SingleValueColumn implements ColumnInterface
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
            ->getStateUsing(fn($record) => $this->getSelectColumnValue($record, $customField));
    }

    /**
     * Get the value for a select column.
     *
     * @throws Throwable
     */
    private function getSelectColumnValue($record, CustomField $customField): string
    {
        $value = $record->getCustomFieldValue($customField->code);
        $lookupValue = $this->resolveLookupValues([$value], $customField)->first();

        return (string)$lookupValue;
    }

    /**
     * Resolve multiple lookup options based on the custom field configuration.
     *
     * @throws Throwable
     */
    private function resolveLookupValues(array $values, CustomField $customField): Collection
    {
        if (!isset($customField->lookup_type)) {
            return $customField->options->whereIn('id', $values)->pluck('name');
        }

        [$lookupInstance, $recordTitleAttribute] = $this->getLookupAttributes($customField->lookup_type);

        return $lookupInstance->whereIn('id', $values)->pluck($recordTitleAttribute);
    }

    /**
     * Get the lookup instance and record title custom field based on the custom field configuration.
     *
     * @throws Throwable
     */
    private function getLookupAttributes(string $lookupType): array
    {
        $lookupModelPath = Relation::getMorphedModel($lookupType) ?: $lookupType;
        $lookupInstance = app($lookupModelPath);

        $resourcePath = Filament::getModelResource($lookupModelPath);
        $resourceInstance = app($resourcePath);
        $recordTitleAttribute = $resourceInstance->getRecordTitleAttribute();

        throw_if(
            $recordTitleAttribute === null,
            new MissingRecordTitleAttributeException("The `{$resourcePath}` does not have a record title custom attribute.")
        );

        return [$lookupInstance, $recordTitleAttribute];
    }
}
