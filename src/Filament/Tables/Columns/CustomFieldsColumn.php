<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Tables\Filters\BaseFilter;
use Relaticle\CustomFields\Models\CustomField;

final readonly class CustomFieldsColumn
{
    /**
     * @return array<int, BaseFilter>
     */
    public static function all($instance): array
    {
        $fieldColumnFactory = new FieldColumnFactory(app());

        return $instance->customFields()
            ->with('options')
            ->get()
            ->map(fn(CustomField $customField) => $fieldColumnFactory->create($customField)
                ->toggleable(
                    condition: config('custom-fields.resource.table.columns_toggleable.enabled', true),
                    isToggledHiddenByDefault: config('custom-fields.resource.table.columns_toggleable.hidden_by_default', true)
                )
            )
            ->toArray();
    }
}
