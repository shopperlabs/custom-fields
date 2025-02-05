<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Concerns;

use Filament\Tables\Table;
use Relaticle\CustomFields\Filament\Tables\Columns\CustomFieldsColumn;
use Relaticle\CustomFields\Filament\Tables\Filter\CustomFieldsFilter;
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
            ...CustomFieldsColumn::all($instance),
        ])->filters([
            ...$this->table->getFilters(),
            ...CustomFieldsFilter::all($instance)
        ]);

        return $this->table;
    }
}
