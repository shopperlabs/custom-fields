<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Filter;

use Filament\Tables\Filters\BaseFilter;
use Relaticle\CustomFields\Models\CustomField;

final readonly class CustomFieldsFilter
{
    /**
     * @return array<int, BaseFilter>
     */
    public static function all($instance): array
    {
        $fieldFilterFactory = new FieldFilterFactory(app());

        return $instance->customFields()
            ->with('options')
            ->whereIn('type', ['toggle', 'select'])
            ->get()
            ->map(fn (CustomField $customField) => $fieldFilterFactory->create($customField))
            ->toArray();
    }
}
