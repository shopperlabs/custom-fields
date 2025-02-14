<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Tables\Columns;

use Filament\Tables\Columns\Column as BaseColumn;
use Relaticle\CustomFields\Models\CustomField;
use Filament\Tables\Columns\TextColumn as BaseTextColumn;
use Relaticle\CustomFields\Services\MultiValueResolver;

final readonly class MultiValueColumn implements ColumnInterface
{
    public function __construct(public MultiValueResolver $valueResolver)
    {
    }
    public function make(CustomField $customField): BaseColumn
    {
        return BaseTextColumn::make("custom_fields.$customField->code")
            ->label($customField->name)
            ->sortable(false)
            ->getStateUsing(fn($record) => $this->valueResolver->resolve($record, $customField));
    }
}
