<?php

declare(strict_types=1);

namespace Relaticle\CustomFields\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Select;
use Relaticle\CustomFields\Models\CustomField;

final readonly class MultiSelectComponent implements AttributeComponentInterface
{
    public function __construct(private Configurator $configurator) {}

    public function make(CustomField $customField): Select
    {
        return (new SelectComponent($this->configurator))->make($customField)->multiple();
    }
}
