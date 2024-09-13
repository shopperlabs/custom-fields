<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomAttributes;

use Filament\Forms\Components\Field;
use ManukMinasyan\FilamentCustomField\Models\Attribute;

interface AttributeComponentInterface
{
    public function make(Attribute $attribute): Field;
}
