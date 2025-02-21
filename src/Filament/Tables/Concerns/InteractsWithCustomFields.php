<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Concerns;

use Filament\Resources\RelationManagers\RelationManager;
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
        $model = $this instanceof RelationManager ? $this->getRelationship()->getModel()::class : $this->getModel();

        $instance = app($model);

        $this->table
            ->columns([
                ...$this->table->getColumns(),
                ...CustomFieldsColumn::all($instance),
            ])
            ->filters([
                ...$this->table->getFilters(),
                ...CustomFieldsFilter::all($instance)
            ]);

        return $this->table;
    }
}
