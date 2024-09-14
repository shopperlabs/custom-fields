<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Concerns;

use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use ManukMinasyan\FilamentCustomField\Enums\AttributeType;
use ManukMinasyan\FilamentCustomField\Exceptions\MissingRecordTitleAttributeException;
use ManukMinasyan\FilamentCustomField\Models\Attribute;
use Throwable;

trait InteractsWithCustomAttributes
{
    /**
     * Returns the table with custom attributes added as columns.
     *
     * @throws Throwable
     */
    public function getTable(): Table
    {
        $instance = app(self::getModel());

        $this->table->columns([
            ...$this->table->getColumns(),
            ...$this->getCustomAttributeColumns($instance),
        ]);

        return $this->table;
    }

    /**
     * Get custom attribute columns for the table.
     */
    private function getCustomAttributeColumns($instance): array
    {
        return $instance->customAttributes()
            ->with('options')
            ->get()
            ->map(fn (Attribute $attribute) => $this->createCustomAttributeColumn($attribute)
                ->toggleable(isToggledHiddenByDefault: true)
            )
            ->toArray();
    }

    /**
     * Create a custom attribute column based on its type.
     */
    private function createCustomAttributeColumn(Attribute $attribute): TextColumn|IconColumn
    {
        return match ($attribute->type) {
            AttributeType::TOGGLE => $this->createColumnForToggle($attribute),
            AttributeType::DATE => $this->createColumnForDate($attribute),
            AttributeType::DATETIME => $this->createColumnForDateTime($attribute),
            AttributeType::SELECT => $this->createColumnForSelect($attribute),
            AttributeType::MULTISELECT => $this->createColumnForMultiSelect($attribute),
            default => $this->createColumnForText($attribute),
        };
    }

    /**
     * Create a date column for the attribute.
     */
    private function createColumnForDate(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->date()
            ->label($attribute->name)
            ->getStateUsing(fn ($record) => $record->getCustomAttributeValue($attribute->code));
    }

    /**
     * Create a date time column for the attribute.
     */
    private function createColumnForDateTime(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->dateTime()
            ->label($attribute->name)
            ->getStateUsing(fn ($record) => $record->getCustomAttributeValue($attribute->code));
    }

    /**
     * Create a text column for the attribute.
     */
    private function createColumnForText(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(fn ($record) => $record->getCustomAttributeValue($attribute->code));
    }

    /**
     * Create a toggle column for the attribute.
     */
    private function createColumnForToggle(Attribute $attribute): IconColumn
    {
        return IconColumn::make("custom_attributes.$attribute->code")
            ->boolean()
            ->label($attribute->name)
            ->getStateUsing(fn ($record) => $record->getCustomAttributeValue($attribute->code) ?? false);
    }

    /**
     * Create a select column for the attribute.
     */
    private function createColumnForSelect(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(fn ($record) => $this->getSelectColumnValue($record, $attribute));
    }

    /**
     * Create a multi-select column for the attribute.
     */
    private function createColumnForMultiSelect(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(fn ($record) => $this->getMultiSelectColumnValue($record, $attribute));
    }

    /**
     * Get the value for a select column.
     *
     * @throws Throwable
     */
    private function getSelectColumnValue($record, Attribute $attribute): string
    {
        $value = $record->getCustomAttributeValue($attribute->code);
        $lookupValue = $this->resolveLookupValues([$value], $attribute)->first();

        return (string) $lookupValue;
    }

    /**
     * Get the value for a multi-select column.
     *
     * @throws Throwable
     */
    private function getMultiSelectColumnValue($record, Attribute $attribute): string
    {
        $value = $record->getCustomAttributeValue($attribute->code) ?? [];
        $lookupValues = $this->resolveLookupValues($value, $attribute);

        return $lookupValues->isNotEmpty() ? $lookupValues->implode(', ') : '';
    }

    /**
     * Resolve multiple lookup options based on the attribute configuration.
     *
     * @throws Throwable
     */
    private function resolveLookupValues(array $values, Attribute $attribute): Collection
    {
        if (! isset($attribute->lookup_type)) {
            return $attribute->options->whereIn('id', $values)->pluck('name');
        }

        [$lookupInstance, $recordTitleAttribute] = $this->getLookupAttributes($attribute->lookup_type);

        return $lookupInstance->whereIn('id', $values)->pluck($recordTitleAttribute);
    }

    /**
     * Get the lookup instance and record title attribute based on the attribute configuration.
     *
     * @throws Throwable
     */
    private function getLookupAttributes(string $lookupType): array
    {
        $lookupModelPath = Relation::getMorphedModel($lookupType);
        $lookupInstance = app($lookupModelPath);

        $resourcePath = Filament::getModelResource($lookupModelPath);
        $resourceInstance = app($resourcePath);
        $recordTitleAttribute = $resourceInstance->getRecordTitleAttribute();

        throw_if(
            $recordTitleAttribute === null,
            new MissingRecordTitleAttributeException("The `{$resourcePath}` does not have a record title attribute.")
        );

        return [$lookupInstance, $recordTitleAttribute];
    }
}
