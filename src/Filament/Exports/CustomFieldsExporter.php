<?php

namespace Relaticle\CustomFields\Filament\Exports;

use Filament\Tables\Filters\BaseFilter;
use Relaticle\CustomFields\Models\CustomField;

class CustomFieldsExporter
{
    /**
     * @return array<int, BaseFilter>
     */
    public static function getColumns($instance): array
    {
        $fieldFilterFactory = new FieldExportFactory();
        $instance = app($instance);

        return $instance->customFields()
            ->with('options')
            ->whereHas('section', fn($query) => $query->active())
            ->nonEncrypted()
            ->get()
            ->map(fn (CustomField $customField) => $fieldFilterFactory->create($customField))
            ->toArray();
    }
}
