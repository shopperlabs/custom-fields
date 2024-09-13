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
use ManukMinasyan\FilamentCustomField\Models\Attribute;
use Throwable;

trait InteractsWithCustomAttributes
{
    /**
     * Returns the table with custom attributes added as columns.
     * @throws Throwable
     */
    public function getTable(): Table
    {
        $modelClass = self::getModel();
        $instance = app($modelClass);

        $customAttributes = $this->getCustomAttributeColumns($instance);

        // Merge custom attribute columns with existing table columns
        $this->table->columns([...$this->table->getColumns(), ...$customAttributes]);

        return $this->table;
    }

    /**
     * Get custom attribute columns for the table.
     */
    private function getCustomAttributeColumns($instance): Collection
    {
        return $instance->customAttributes()
            ->with('options')
            ->get()
            ->map(fn(Attribute $attribute) => $this->createCustomAttributeColumn($attribute)
                ->toggleable(isToggledHiddenByDefault: true)
            );
    }

    /**
     * Create a custom attribute column based on its type.
     */
    private function createCustomAttributeColumn(Attribute $attribute): TextColumn|IconColumn
    {
        return match ($attribute->type) {
            AttributeType::TOGGLE => $this->createColumnForToggle($attribute),
            AttributeType::SELECT => $this->createColumnForSelect($attribute),
            AttributeType::MULTISELECT => $this->createColumnForMultiSelect($attribute),
            default => $this->createColumnForText($attribute),
        };
    }

    /**
     * Create a text column for the attribute.
     */
    private function createColumnForText(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(fn($record) => $record->getCustomAttributeValue($attribute->code) ?? '-');
    }

    /**
     * Create a toggle column for the attribute.
     */
    private function createColumnForToggle(Attribute $attribute): IconColumn
    {
        return IconColumn::make("custom_attributes.$attribute->code")
            ->boolean()
            ->label($attribute->name)
            ->getStateUsing(fn($record) => $record->getCustomAttributeValue($attribute->code) ?? false);
    }

    /**
     * Create a select column for the attribute.
     */
    private function createColumnForSelect(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(fn($record) => $this->getSelectColumnValue($record, $attribute));
    }

    /**
     * Create a multi-select column for the attribute.
     */
    private function createColumnForMultiSelect(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(fn($record) => $this->getMultiSelectColumnValue($record, $attribute));
    }

    /**
     * Get the value for a select column.
     * @throws Throwable
     */
    private function getSelectColumnValue($record, Attribute $attribute): string
    {
        $value = $record->getCustomAttributeValue($attribute->code);
        $selectedOption = $this->resolveLookupOption($value, $attribute);

        return $selectedOption ? $selectedOption : '-';
    }

    /**
     * Get the value for a multi-select column.
     * @throws Throwable
     */
    private function getMultiSelectColumnValue($record, Attribute $attribute): string
    {
        $value = $record->getCustomAttributeValue($attribute->code) ?? [];
        $selectedOptions = $this->resolveLookupOptions($value, $attribute);

        return $selectedOptions->isNotEmpty() ? $selectedOptions->implode(', ') : '-';
    }

    /**
     * Resolve a single lookup option based on the attribute configuration.
     * @throws Throwable
     */
    private function resolveLookupOption($value, Attribute $attribute): ?string
    {
        if (isset($attribute->lookup_type)) {
            $lookupMorphedModelPath = Relation::getMorphedModel($attribute->lookup_type);
            $lookupEntityInstance = app($lookupMorphedModelPath);

            $resourcePath = Filament::getModelResource($lookupMorphedModelPath);
            $resourceInstance = app($resourcePath);
            $recordTitleAttribute = $resourceInstance->getRecordTitleAttribute();

            throw_if($recordTitleAttribute === null, new \Exception("The `{$resourcePath}` does not have a record title attribute."));

            return $lookupEntityInstance->find($value)?->{$recordTitleAttribute};
        }

        return $attribute->options->firstWhere('id', $value)?->name;
    }

    /**
     * Resolve multiple lookup options based on the attribute configuration.
     * @throws Throwable
     */
    private function resolveLookupOptions(array $values, Attribute $attribute): Collection
    {
        if (isset($attribute->lookup_type)) {
            $lookupMorphedModelPath = Relation::getMorphedModel($attribute->lookup_type);
            $lookupEntityInstance = app($lookupMorphedModelPath);

            $resourcePath = Filament::getModelResource($lookupMorphedModelPath);
            $resourceInstance = app($resourcePath);
            $recordTitleAttribute = $resourceInstance->getRecordTitleAttribute();

            throw_if($recordTitleAttribute === null, new \Exception("The `{$resourcePath}` does not have a record title attribute."));

            return $lookupEntityInstance->whereIn('id', $values)->pluck($recordTitleAttribute);
        }

        return $attribute->options->whereIn('id', $values)->pluck('name');
    }
}
