<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentAttribute\Filament\Concerns;

use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use ManukMinasyan\FilamentAttribute\Enums\AttributeType;
use ManukMinasyan\FilamentAttribute\Models\Attribute;

trait InteractsWithCustomAttributes
{
    /**
     * Returns the table with custom attributes added as columns.
     */
    public function getTable(): Table
    {
        $modelClass = self::getModel();
        $instance = app($modelClass);
        $resource = app(Filament::getModelResource($modelClass));
        $recordTitleAttribute = $resource->getRecordTitleAttribute();

        // Fetch custom attributes with their options
        $customAttributes = $this->getCustomAttributeColumns($instance, $recordTitleAttribute);

        // Merge custom attribute columns with existing table columns
        $this->table->columns([...$this->table->getColumns(), ...$customAttributes]);

        return $this->table;
    }

    /**
     * Get custom attribute columns for the table.
     */
    private function getCustomAttributeColumns($instance, string $recordTitleAttribute): array
    {
        return $instance->customAttributes()
            ->with('options')
            ->get()
            ->map(fn (Attribute $attribute) => $this->createCustomAttributeColumn($attribute, $recordTitleAttribute)
                ->toggleable(isToggledHiddenByDefault: true)
            )
            ->toArray();
    }

    /**
     * Create a custom attribute column based on its type.
     */
    private function createCustomAttributeColumn(Attribute $attribute, string $recordTitleAttribute): TextColumn|IconColumn
    {
        return match ($attribute->type) {
            AttributeType::TOGGLE => $this->createColumnForToggle($attribute),
            AttributeType::SELECT => $this->createColumnForSelect($attribute, $recordTitleAttribute),
            AttributeType::MULTISELECT => $this->createColumnForMultiSelect($attribute, $recordTitleAttribute),
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
            ->getStateUsing(fn ($record) => $record->getCustomAttributeValue($attribute->code) ?? '-');
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
    private function createColumnForSelect(Attribute $attribute, string $recordTitleAttribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(fn ($record) => $this->getSelectColumnValue($record, $attribute, $recordTitleAttribute));
    }

    /**
     * Create a multi-select column for the attribute.
     */
    private function createColumnForMultiSelect(Attribute $attribute, string $recordTitleAttribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(fn ($record) => $this->getMultiSelectColumnValue($record, $attribute, $recordTitleAttribute));
    }

    /**
     * Get the value for a select column.
     */
    private function getSelectColumnValue($record, Attribute $attribute, string $recordTitleAttribute): string
    {
        $value = $record->getCustomAttributeValue($attribute->code);
        $selectedOption = $this->resolveLookupOption($value, $attribute, $recordTitleAttribute);

        return $selectedOption ? $selectedOption->{$recordTitleAttribute} : '-';
    }

    /**
     * Get the value for a multi-select column.
     */
    private function getMultiSelectColumnValue($record, Attribute $attribute, string $recordTitleAttribute): string
    {
        $value = $record->getCustomAttributeValue($attribute->code) ?? [];
        $selectedOptions = $this->resolveLookupOptions($value, $attribute, $recordTitleAttribute);

        return $selectedOptions->isNotEmpty() ? $selectedOptions->implode(', ') : '-';
    }

    /**
     * Resolve a single lookup option based on the attribute configuration.
     */
    private function resolveLookupOption($value, Attribute $attribute, string $recordTitleAttribute)
    {
        if (isset($attribute->lookup_type)) {
            $lookupMorphedModelPath = Relation::getMorphedModel($attribute->lookup_type);
            $lookupEntity = app($lookupMorphedModelPath);

            return $lookupEntity->find($value);
        }

        return $attribute->options->firstWhere('id', $value);
    }

    /**
     * Resolve multiple lookup options based on the attribute configuration.
     */
    private function resolveLookupOptions(array $values, Attribute $attribute, string $recordTitleAttribute): Collection
    {
        if (isset($attribute->lookup_type)) {
            $lookupMorphedModelPath = Relation::getMorphedModel($attribute->lookup_type);
            $lookupEntity = app($lookupMorphedModelPath);

            return $lookupEntity->whereIn('id', $values)->pluck($recordTitleAttribute);
        }

        return $attribute->options->whereIn('id', $values)->pluck('name');
    }
}
