<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentAttribute\Filament\Concerns;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\Relation;
use ManukMinasyan\FilamentAttribute\Enums\AttributeType;
use ManukMinasyan\FilamentAttribute\Models\Attribute;

trait InteractsWithCustomAttributes
{
    /**
     * Returns the table with custom attributes added as columns.
     */
    public function getTable(): Table
    {
        $instance = app(self::getModel());

        // Fetch custom attributes with their options
        $customAttributes = $instance->customAttributes()
            ->with('options')
            ->get()
            ->map(fn($attribute) => $this->createCustomAttributeColumn($attribute)->toggleable(isToggledHiddenByDefault: true))
            ->toArray();

        // Merge custom attribute columns with existing table columns
        $this->table->columns([...$this->table->getColumns(), ...$customAttributes]);

        return $this->table;
    }

    /**
     * @param Attribute $attribute
     * @return IconColumn|TextColumn
     */
    private function createCustomAttributeColumn(Attribute $attribute): TextColumn|IconColumn
    {
        return match ($attribute->type) {
            AttributeType::TOGGLE => $this->createColumnForToggle($attribute),
            AttributeType::SELECT => $this->createColumnForSelect($attribute),
            AttributeType::MULTISELECT => $this->createColumnForMultiSelect($attribute),
            default => $this->createColumnForText($attribute), // Fallback to text
        };
    }

    private function createColumnForText(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(fn($record) => $record->getCustomAttributeValue($attribute->code) ?? '-');
    }

    private function createColumnForToggle(Attribute $attribute): IconColumn
    {
        return IconColumn::make("custom_attributes.$attribute->code")
            ->boolean()
            ->label($attribute->name)
            ->getStateUsing(fn($record) => $record->getCustomAttributeValue($attribute->code) ?? false);
    }

    private function createColumnForSelect(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(function ($record) use ($attribute) {
                $value = $record->getCustomAttributeValue($attribute->code);
                $selectedOption = $attribute->options->firstWhere('id', $value);

                return $selectedOption?->name ?? '-';
            });
    }

    private function createColumnForMultiSelect(Attribute $attribute): TextColumn
    {
        return TextColumn::make("custom_attributes.$attribute->code")
            ->label($attribute->name)
            ->getStateUsing(function ($record) use ($attribute) {
                $value = $record->getCustomAttributeValue($attribute->code) ?? [];

                if (isset($attribute->lookup_type)) {
                    $lookupMorphedModelPath = Relation::getMorphedModel($attribute->lookup_type);
                    $lookupEntity = new $lookupMorphedModelPath;
                    $selectedOptions = $lookupEntity->whereIn('id', $value)->pluck('name'); // TODO: Get label column from attribute/resource
                } else {
                    $selectedOptions = $attribute->options->whereIn('id', $value)->pluck('name'); // TODO: Get label column from attribute/resource
                }

                return $selectedOptions->isNotEmpty() ? $selectedOptions->implode(', ') : '-';
            });
    }
}
