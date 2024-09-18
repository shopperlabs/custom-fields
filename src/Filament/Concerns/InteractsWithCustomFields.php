<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Concerns;

use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use ManukMinasyan\FilamentCustomField\Enums\CustomFieldType;
use ManukMinasyan\FilamentCustomField\Exceptions\MissingRecordTitleAttributeException;
use ManukMinasyan\FilamentCustomField\Models\CustomField;
use Throwable;

trait InteractsWithCustomFields
{
    /**
     * Returns the table with custom fields added as columns.
     *
     * @throws Throwable
     */
    public function getTable(): Table
    {
        $instance = app(self::getModel());

        $this->table->columns([
            ...$this->table->getColumns(),
            ...$this->getCustomFieldColumns($instance),
        ]);

        return $this->table;
    }

    /**
     * Get custom custom field columns for the table.
     */
    private function getCustomFieldColumns($instance): array
    {
        return $instance->customFields()
            ->with('options')
            ->get()
            ->map(fn (CustomField $customField) => $this->createCustomAttributeColumn($customField)
                ->toggleable(isToggledHiddenByDefault: true)
            )
            ->toArray();
    }

    /**
     * Create a custom custom field column based on its type.
     */
    private function createCustomAttributeColumn(CustomField $customField): TextColumn|IconColumn
    {
        return match ($customField->type) {
            CustomFieldType::TOGGLE => $this->createColumnForToggle($customField),
            CustomFieldType::DATE => $this->createColumnForDate($customField),
            CustomFieldType::DATETIME => $this->createColumnForDateTime($customField),
            CustomFieldType::SELECT => $this->createColumnForSelect($customField),
            CustomFieldType::MULTISELECT => $this->createColumnForMultiSelect($customField),
            default => $this->createColumnForText($customField),
        };
    }

    /**
     * Create a date column for the custom field.
     */
    private function createColumnForDate(CustomField $customField): TextColumn
    {
        return TextColumn::make("custom_fields.$customField->code")
            ->date()
            ->label($customField->name)
            ->getStateUsing(fn ($record) => $record->getCustomAttributeValue($customField->code));
    }

    /**
     * Create a date time column for the custom field.
     */
    private function createColumnForDateTime(CustomField $customField): TextColumn
    {
        return TextColumn::make("custom_fields.$customField->code")
            ->dateTime()
            ->label($customField->name)
            ->getStateUsing(fn ($record) => $record->getCustomAttributeValue($customField->code));
    }

    /**
     * Create a text column for the custom field.
     */
    private function createColumnForText(CustomField $customField): TextColumn
    {
        return TextColumn::make("custom_fields.$customField->code")
            ->label($customField->name)
            ->getStateUsing(fn ($record) => $record->getCustomAttributeValue($customField->code));
    }

    /**
     * Create a toggle column for the custom field.
     */
    private function createColumnForToggle(CustomField $customField): IconColumn
    {
        return IconColumn::make("custom_fields.$customField->code")
            ->boolean()
            ->label($customField->name)
            ->getStateUsing(fn ($record) => $record->getCustomAttributeValue($customField->code) ?? false);
    }

    /**
     * Create a select column for the custom field.
     */
    private function createColumnForSelect(CustomField $customField): TextColumn
    {
        return TextColumn::make("custom_fields.$customField->code")
            ->label($customField->name)
            ->getStateUsing(fn ($record) => $this->getSelectColumnValue($record, $customField));
    }

    /**
     * Create a multi-select column for the custom field.
     */
    private function createColumnForMultiSelect(CustomField $customField): TextColumn
    {
        return TextColumn::make("custom_fields.$customField->code")
            ->label($customField->name)
            ->getStateUsing(fn ($record) => $this->getMultiSelectColumnValue($record, $customField));
    }

    /**
     * Get the value for a select column.
     *
     * @throws Throwable
     */
    private function getSelectColumnValue($record, CustomField $customField): string
    {
        $value = $record->getCustomAttributeValue($customField->code);
        $lookupValue = $this->resolveLookupValues([$value], $customField)->first();

        return (string) $lookupValue;
    }

    /**
     * Get the value for a multi-select column.
     *
     * @throws Throwable
     */
    private function getMultiSelectColumnValue($record, CustomField $customField): string
    {
        $value = $record->getCustomAttributeValue($customField->code) ?? [];
        $lookupValues = $this->resolveLookupValues($value, $customField);

        return $lookupValues->isNotEmpty() ? $lookupValues->implode(', ') : '';
    }

    /**
     * Resolve multiple lookup options based on the custom field configuration.
     *
     * @throws Throwable
     */
    private function resolveLookupValues(array $values, CustomField $customField): Collection
    {
        if (! isset($customField->lookup_type)) {
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
        $lookupModelPath = Relation::getMorphedModel($lookupType);
        $lookupInstance = app($lookupModelPath);

        $resourcePath = Filament::getModelResource($lookupModelPath);
        $resourceInstance = app($resourcePath);
        $recordTitleAttribute = $resourceInstance->getRecordTitleAttribute();

        throw_if(
            $recordTitleAttribute === null,
            new MissingRecordTitleAttributeException("The `{$resourcePath}` does not have a record title custom field.")
        );

        return [$lookupInstance, $recordTitleAttribute];
    }
}
