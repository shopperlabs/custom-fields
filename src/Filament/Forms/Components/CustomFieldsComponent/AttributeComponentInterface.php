<?php

declare(strict_types=1);

namespace ManukMinasyan\FilamentCustomField\Filament\Forms\Components\CustomFieldsComponent;

use Filament\Forms\Components\Field;
use ManukMinasyan\FilamentCustomField\Models\CustomField;

interface AttributeComponentInterface
{
    public function make(CustomField $customField): Field;
}
