<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Imports;

use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Relaticle\CustomFields\Models\CustomField;
use Relaticle\CustomFields\Enums\CustomFieldType;
use Relaticle\CustomFields\Services\EntityTypeService;
use Relaticle\CustomFields\Services\FilamentResourceService;
use Illuminate\Database\Eloquent\Model;
use Relaticle\CustomFields\Data\ValidationRuleData;
use Illuminate\Support\Arr;
use Throwable;

/**
 * Helper class for integrating custom fields with Filament's import system.
 *
 * This class provides methods for generating import columns for custom fields
 * and for saving custom field values from imported data.
 */
final class CustomFieldsImporter
{
    /**
     * Get import columns for all custom fields of a model.
     *
     * @param string $modelClass The fully qualified class name of the model
     * @return array<int, ImportColumn> Array of import columns for custom fields
     */
    public static function getColumns(string $modelClass, ?Model $tenant = null): array
    {
        $model = app($modelClass);

        return $model->customFields()
            ->with('options')
            ->active()
            ->get()
            ->map(fn(CustomField $field) => self::createColumn($field))
            ->toArray();
    }

    /**
     * Create an import column for a single custom field.
     *
     * @param CustomField $customField
     * @return ImportColumn
     */
    public static function createColumn(CustomField $customField): ImportColumn
    {
        $column = ImportColumn::make("custom_fields_{$customField->code}")
            ->label($customField->name);

        // Configure based on field type
        self::configureColumnByFieldType($column, $customField);

        // Apply validation rules
        self::applyValidationRules($column, $customField);

        // Set example value
        self::setExampleValue($column, $customField);

        return $column;
    }

    /**
     * Configure an import column based on the custom field type.
     *
     * @param ImportColumn $column
     * @param CustomField $customField
     * @return void
     */
    private static function configureColumnByFieldType(ImportColumn $column, CustomField $customField): void
    {
        // Apply specific configuration based on field type
        match ($customField->type) {
            // Numeric types
            CustomFieldType::NUMBER => $column->numeric(),

            // Currency with special formatting
            CustomFieldType::CURRENCY => $column->numeric()->castStateUsing(function ($state) {
                if (blank($state)) return null;

                // Remove currency symbols and formatting chars
                if (is_string($state)) {
                    $state = preg_replace('/[^0-9.-]/', '', $state);
                }

                return round(floatval($state), 2);
            }),

            // Boolean fields
            CustomFieldType::CHECKBOX, CustomFieldType::TOGGLE => $column->boolean(),

            // Date fields
            CustomFieldType::DATE => $column->date(),
            CustomFieldType::DATE_TIME => $column->dateTime(),

            // Select/Radio fields
            CustomFieldType::SELECT, CustomFieldType::RADIO => self::configureSelectColumn($column, $customField),

            // Multi-value fields
            CustomFieldType::MULTI_SELECT,
            CustomFieldType::CHECKBOX_LIST,
            CustomFieldType::TAGS_INPUT,
            CustomFieldType::TOGGLE_BUTTONS => self::configureMultiSelectColumn($column, $customField),

            // No special configuration for other types
            default => null,
        };
    }

    /**
     * Configure an import column for a select/radio field.
     *
     * @param ImportColumn $column
     * @param CustomField $customField
     * @return void
     */
    private static function configureSelectColumn(ImportColumn $column, CustomField $customField): void
    {
        if ($customField->lookup_type) {
            // Configure column to use lookup relationship
            $column->castStateUsing(function ($state) use ($customField) {
                if (blank($state)) return null;

                try {
                    $entityInstance = FilamentResourceService::getModelInstance($customField->lookup_type);
                    $recordTitleAttribute = FilamentResourceService::getRecordTitleAttribute($customField->lookup_type);

                    $record = $entityInstance::query()
                        ->where($recordTitleAttribute, $state)
                        ->first();

                    if (!$record) {
                        throw new RowImportFailedException("No {$customField->lookup_type} record found with {$recordTitleAttribute} value '{$state}'");
                    }

                    return $record->getKey();
                } catch (Throwable $e) {
                    if ($e instanceof RowImportFailedException) {
                        throw $e;
                    }

                    throw new RowImportFailedException("Error resolving lookup value for {$customField->name}: {$e->getMessage()}");
                }
            });

            // Set example values for lookup types
            self::setLookupTypeExamples($column, $customField);
        } else {
            // Configure column to use options
            $column->castStateUsing(function ($state) use ($customField) {
                if (blank($state)) return null;

                $option = $customField->options
                    ->where('name', $state)
                    ->first();

                if (!$option) {
                    throw new RowImportFailedException("Invalid option value '{$state}' for {$customField->name}. Valid options are: " .
                        $customField->options->pluck('name')->implode(', '));
                }

                return $option->id;
            });

            // Set example options
            self::setOptionExamples($column, $customField);
        }
    }

    /**
     * Configure an import column for a multi-select field.
     *
     * @param ImportColumn $column
     * @param CustomField $customField
     * @return void
     */
    private static function configureMultiSelectColumn(ImportColumn $column, CustomField $customField): void
    {
        $column->array(',');

        if ($customField->lookup_type) {
            // Configure column to use lookup relationship
            $column->castStateUsing(function ($state) use ($customField) {
                if (blank($state)) return [];
                if (!is_array($state)) $state = [$state];

                try {
                    $entityInstance = FilamentResourceService::getModelInstance($customField->lookup_type);
                    $recordTitleAttribute = FilamentResourceService::getRecordTitleAttribute($customField->lookup_type);

                    $records = $entityInstance::query()
                        ->whereIn($recordTitleAttribute, $state)
                        ->pluck('id', $recordTitleAttribute)
                        ->all();

                    // Check if all values were found
                    $missingValues = array_diff($state, array_keys($records));
                    if (!empty($missingValues)) {
                        throw new RowImportFailedException("Could not find {$customField->lookup_type} records with {$recordTitleAttribute} values: " .
                            implode(', ', $missingValues));
                    }

                    return array_values($records);
                } catch (Throwable $e) {
                    if ($e instanceof RowImportFailedException) {
                        throw $e;
                    }

                    throw new RowImportFailedException("Error resolving lookup values for {$customField->name}: {$e->getMessage()}");
                }
            });

            // Set example values for lookup types
            self::setLookupTypeExamples($column, $customField, true);
        } else {
            // Configure column to use options
            $column->castStateUsing(function ($state) use ($customField) {
                if (blank($state)) return [];
                if (!is_array($state)) $state = [$state];

                $optionsMap = $customField->options
                    ->pluck('id', 'name')
                    ->all();

                // Check if all values exist as options
                $missingValues = array_diff($state, array_keys($optionsMap));
                if (!empty($missingValues)) {
                    throw new RowImportFailedException("Invalid option values for {$customField->name}: " .
                        implode(', ', $missingValues) . ". Valid options are: " .
                        implode(', ', array_keys($optionsMap)));
                }

                return array_values(Arr::only($optionsMap, $state));
            });

            // Set example options
            self::setOptionExamples($column, $customField, true);
        }
    }

    /**
     * Apply validation rules to an import column.
     *
     * @param ImportColumn $column
     * @param CustomField $customField
     * @return void
     */
    private static function applyValidationRules(ImportColumn $column, CustomField $customField): void
    {
        // Check if validation_rules exists and has items
        if ($customField->validation_rules && $customField->validation_rules->count() > 0) {
            $rules = $customField->validation_rules->map(function (ValidationRuleData $rule) {
                // Check for rules with parameters
                if (!empty($rule->parameters)) {
                    return $rule->name . ':' . implode(',', $rule->parameters);
                }

                return $rule->name;
            })->toArray();

            if (!empty($rules)) {
                $column->rules($rules);
            }
        }
    }

    /**
     * Set example values for the import column based on the custom field type.
     *
     * @param ImportColumn $column
     * @param CustomField $customField
     * @return void
     */
    private static function setExampleValue(ImportColumn $column, CustomField $customField): void
    {
        // Generate appropriate example values based on field type
        $example = match ($customField->type) {
            CustomFieldType::TEXT => 'Sample text',
            CustomFieldType::NUMBER => '42',
            CustomFieldType::CURRENCY => '99.99',
            CustomFieldType::CHECKBOX, CustomFieldType::TOGGLE => 'Yes',
            CustomFieldType::DATE => now()->format('Y-m-d'),
            CustomFieldType::DATE_TIME => now()->format('Y-m-d H:i:s'),
            CustomFieldType::TEXTAREA => 'Sample longer text with multiple words',
            CustomFieldType::RICH_EDITOR, CustomFieldType::MARKDOWN_EDITOR => "# Sample Header\nSample content with **formatting**",
            CustomFieldType::LINK => 'https://example.com',
            CustomFieldType::COLOR_PICKER => '#3366FF',
            // Multi-value fields and option fields are handled by other methods
            default => null,
        };

        if ($example !== null) {
            $column->example($example);
        }
    }

    /**
     * Set example values for a lookup type import column.
     *
     * @param ImportColumn $column
     * @param CustomField $customField
     * @param bool $isMultiple Whether the field accepts multiple values
     * @return void
     */
    private static function setLookupTypeExamples(ImportColumn $column, CustomField $customField, bool $isMultiple = false): void
    {
        try {
            $entityInstance = FilamentResourceService::getModelInstance($customField->lookup_type);
            $recordTitleAttribute = FilamentResourceService::getRecordTitleAttribute($customField->lookup_type);

            // Get sample values from the lookup model
            $sampleValues = $entityInstance::query()
                ->limit(2)
                ->pluck($recordTitleAttribute)
                ->toArray();

            if (!empty($sampleValues)) {
                if ($isMultiple) {
                    // For multi-value fields, combine examples with commas
                    $column->example(implode(', ', $sampleValues));
                    $column->helperText('Separate multiple values with commas');
                } else {
                    // For single-value fields, use the first example
                    $column->example($sampleValues[0]);
                }
            }
        } catch (Throwable $e) {
            // If there's an error getting example lookup values, provide generic examples
            if ($isMultiple) {
                $column->example('Value1, Value2');
                $column->helperText('Separate multiple values with commas');
            } else {
                $column->example('Example value');
            }
        }
    }

    /**
     * Set example values for an options-based import column.
     *
     * @param ImportColumn $column
     * @param CustomField $customField
     * @param bool $isMultiple Whether the field accepts multiple values
     * @return void
     */
    private static function setOptionExamples(ImportColumn $column, CustomField $customField, bool $isMultiple = false): void
    {
        $options = $customField->options->pluck('name')->toArray();

        if (!empty($options)) {
            if ($isMultiple) {
                // Get up to 2 options for the example
                $exampleOptions = array_slice($options, 0, 2);
                $column->example(implode(', ', $exampleOptions));
                $column->helperText('Separate multiple values with commas. Valid options: ' . implode(', ', $options));
            } else {
                // For single-select, use the first option
                $column->example($options[0]);
                $column->helperText('Valid options: ' . implode(', ', $options));
            }
        }
    }

    /**
     * Save custom field values from imported data.
     *
     * Call this method in your importer's afterFill() method to save
     * the custom field values that were imported.
     *
     * @param Model $record The model record to save custom fields for
     * @param array<string, mixed> $data The import data containing custom fields values
     * @param Model|null $tenant
     * @return void
     */
    public static function saveCustomFieldValues(Model $record, array $data, ?Model $tenant = null): void
    {
        $customFieldsData = [];

        // Extract custom fields data from the import data
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'custom_fields_')) {
                $fieldCode = str_replace('custom_fields_', '', $key);
                $customFieldsData[$fieldCode] = $value;
            }
        }

        info('Custom fields data to save: ', [
            'record' => $record,
            'customFieldsData' => $customFieldsData,
            'tenant' => $tenant,
        ]);

        // If there are custom fields to save, save them to the model
        if (!empty($customFieldsData)) {
            $record->saveCustomFields($customFieldsData, $tenant);
        }
    }

    /**
     * Get custom fields data from import data.
     *
     * This method extracts custom field values from the import data
     * and returns them in a format ready to be saved with saveCustomFields().
     *
     * @param array<string, mixed> $data The import data
     * @return array<string, mixed> Custom fields data
     */
    public static function extractCustomFieldsData(array $data): array
    {
        $customFieldsData = [];

        // Extract custom fields data from the import data
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'custom_fields_')) {
                $fieldCode = str_replace('custom_fields_', '', $key);
                $customFieldsData[$fieldCode] = $value;
            }
        }

        return $customFieldsData;
    }

    /**
     * Filter out custom fields from the data that will be used to fill the model.
     *
     * This method should be called in the beforeFill() hook to remove custom fields
     * data from the data array before the model is filled.
     *
     * @param array<string, mixed> $data The import data to filter
     * @return array<string, mixed> Filtered data without custom fields
     */
    public static function filterCustomFieldsFromData(array $data): array
    {
        $filteredData = [];

        foreach ($data as $key => $value) {
            // Only include non-custom fields data
            if (!str_starts_with($key, 'custom_fields_')) {
                $filteredData[$key] = $value;
            }
        }

        return $filteredData;
    }

    /**
     * Generate custom field import columns for a specific model and set of field codes.
     *
     * @param string $modelClass The fully qualified class name of the model
     * @param array<string> $fieldCodes List of custom field codes to include
     * @return array<int, ImportColumn> Array of import columns for the specified custom fields
     */
    public static function getColumnsByFieldCodes(string $modelClass, array $fieldCodes): array
    {
        $model = app($modelClass);
        $entityType = EntityTypeService::getEntityFromModel($modelClass);

        return CustomField::query()
            ->forMorphEntity($entityType)
            ->with('options')
            ->whereIn('code', $fieldCodes)
            ->active()
            ->get()
            ->map(fn(CustomField $field) => self::createColumn($field))
            ->toArray();
    }
}
