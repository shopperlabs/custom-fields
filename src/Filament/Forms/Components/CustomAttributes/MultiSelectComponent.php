<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentAttribute\Filament\Forms\Components\CustomAttributes;

use Filament\Forms\Components\Select;
use ManukMinasyan\FilamentAttribute\Models\Attribute;

final readonly class MultiSelectComponent implements AttributeComponentInterface
{
    public function __construct(private CommonAttributeConfigurator $configurator) {}

    public function make(Attribute $attribute): Select
    {
        return (new SelectComponent($this->configurator))->make($attribute)->multiple();
    }
}
